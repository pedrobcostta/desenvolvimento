<?php
require_once '../config/config.php';
require_once '../include/Vehicle.php';
require_once '../include/Image.php';

header('Content-Type: application/json');

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('Invalid vehicle ID');
    }

    $vehicleId = (int)$data['id'];
    
    $vehicle = new Vehicle();
    $imageHandler = new Image();

    // Get vehicle data to get image paths
    $vehicleData = $vehicle->getVehicle($vehicleId);
    
    if (!$vehicleData) {
        throw new Exception('Vehicle not found');
    }

    // Delete vehicle from database (this will also delete image records due to foreign key constraint)
    if ($vehicle->deleteVehicle($vehicleId)) {
        // Delete physical image files
        if (!empty($vehicleData['images'])) {
            foreach ($vehicleData['images'] as $imagePath) {
                $imageHandler->deleteImage($imagePath);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Vehicle deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete vehicle');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
