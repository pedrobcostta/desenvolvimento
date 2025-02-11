<?php
require_once __DIR__ . '/Database.php';

class Vehicle {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllVehicles($page = 1, $filters = [], $sort = null) {
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['search'])) {
            $where .= " AND (name LIKE ? OR model LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['year'])) {
            $where .= " AND year = ?";
            $params[] = $filters['year'];
        }
        
        if (!empty($filters['mileage'])) {
            list($min, $max) = explode('-', $filters['mileage']);
            if ($max === '+') {
                $where .= " AND mileage > ?";
                $params[] = $min;
            } else {
                $where .= " AND mileage BETWEEN ? AND ?";
                $params[] = $min;
                $params[] = $max;
            }
        }
        
        $orderBy = "created_at DESC";
        if ($sort === 'price_asc') {
            $orderBy = "price ASC";
        } elseif ($sort === 'price_desc') {
            $orderBy = "price DESC";
        }
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM vehicles WHERE $where";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        // Get vehicles
        $sql = "SELECT v.*, 
                       (SELECT image_path FROM vehicle_images 
                        WHERE vehicle_id = v.id AND is_primary = 1 
                        LIMIT 1) as primary_image
                FROM vehicles v 
                WHERE $where 
                ORDER BY $orderBy 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $vehicles = $this->db->fetchAll($sql, $params);
        
        return [
            'vehicles' => $vehicles,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    public function getVehicle($id) {
        $sql = "SELECT v.*, 
                       GROUP_CONCAT(vi.image_path) as images
                FROM vehicles v
                LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id
                WHERE v.id = ?
                GROUP BY v.id";
        
        $vehicle = $this->db->fetch($sql, [$id]);
        
        if ($vehicle) {
            // Update view count
            $this->incrementViews($id);
            
            // Convert images string to array
            $vehicle['images'] = $vehicle['images'] ? explode(',', $vehicle['images']) : [];
        }
        
        return $vehicle;
    }

    public function addVehicle($data, $images) {
        try {
            $this->db->getConnection()->beginTransaction();

            // Insert vehicle data
            $vehicleId = $this->db->insert('vehicles', [
                'name' => $data['name'],
                'model' => $data['model'],
                'year' => $data['year'],
                'mileage' => $data['mileage'],
                'price' => $data['price'],
                'description' => $data['description'] ?? null
            ]);

            // Handle images
            if (!empty($images)) {
                foreach ($images as $index => $image) {
                    $this->db->insert('vehicle_images', [
                        'vehicle_id' => $vehicleId,
                        'image_path' => $image,
                        'is_primary' => $index === 0 ? 1 : 0
                    ]);
                }
            }

            $this->db->getConnection()->commit();
            return $vehicleId;

        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    public function updateVehicle($id, $data, $newImages = []) {
        try {
            $this->db->getConnection()->beginTransaction();

            // Update vehicle data
            $this->db->update('vehicles', 
                [
                    'name' => $data['name'],
                    'model' => $data['model'],
                    'year' => $data['year'],
                    'mileage' => $data['mileage'],
                    'price' => $data['price'],
                    'description' => $data['description'] ?? null
                ],
                'id = ?',
                [$id]
            );

            // Handle new images if any
            if (!empty($newImages)) {
                // Delete existing images if specified
                if (!empty($data['replace_images'])) {
                    $this->db->delete('vehicle_images', 'vehicle_id = ?', [$id]);
                }

                foreach ($newImages as $index => $image) {
                    $this->db->insert('vehicle_images', [
                        'vehicle_id' => $id,
                        'image_path' => $image,
                        'is_primary' => $index === 0 ? 1 : 0
                    ]);
                }
            }

            $this->db->getConnection()->commit();
            return true;

        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    public function deleteVehicle($id) {
        try {
            $this->db->getConnection()->beginTransaction();

            // Delete images first (foreign key constraint will handle this, but let's be explicit)
            $this->db->delete('vehicle_images', 'vehicle_id = ?', [$id]);
            
            // Delete vehicle
            $this->db->delete('vehicles', 'id = ?', [$id]);

            $this->db->getConnection()->commit();
            return true;

        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    private function incrementViews($id) {
        $sql = "UPDATE vehicles SET views = views + 1 WHERE id = ?";
        $this->db->query($sql, [$id]);
    }

    public function getStats() {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM vehicles) as total_vehicles,
                (SELECT id FROM vehicles ORDER BY views DESC LIMIT 1) as most_viewed_id,
                (SELECT id FROM vehicles ORDER BY views ASC LIMIT 1) as least_viewed_id";
        
        $stats = $this->db->fetch($sql);
        
        if ($stats) {
            if ($stats['most_viewed_id']) {
                $stats['most_viewed'] = $this->getVehicle($stats['most_viewed_id']);
            }
            if ($stats['least_viewed_id']) {
                $stats['least_viewed'] = $this->getVehicle($stats['least_viewed_id']);
            }
        }
        
        return $stats;
    }
}
