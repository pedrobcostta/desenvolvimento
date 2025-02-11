<?php
require_once '../config/config.php';
require_once '../include/Vehicle.php';

header('Content-Type: application/json');

try {
    $vehicle = new Vehicle();
    
    // Get page number
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Build filters array
    $filters = [];
    
    if (!empty($_GET['search'])) {
        $filters['search'] = trim($_GET['search']);
    }
    
    if (!empty($_GET['year'])) {
        $filters['year'] = (int)$_GET['year'];
    }
    
    if (!empty($_GET['mileage'])) {
        $filters['mileage'] = $_GET['mileage'];
    }
    
    // Get sort parameter
    $sort = null;
    if (!empty($_GET['sort'])) {
        if ($_GET['sort'] === 'asc') {
            $sort = 'price_asc';
        } elseif ($_GET['sort'] === 'desc') {
            $sort = 'price_desc';
        }
    }
    
    // Get vehicles with pagination and filters
    $result = $vehicle->getAllVehicles($page, $filters, $sort);
    
    // Format response
    echo json_encode([
        'success' => true,
        'vehicles' => $result['vehicles'],
        'pagination' => $result['pagination']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
