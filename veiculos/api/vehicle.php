<?php
require_once '../config/config.php';
require_once '../include/Vehicle.php';

header('Content-Type: application/json');

try {
    // Validate vehicle ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid vehicle ID');
    }

    $vehicleId = (int)$_GET['id'];
    $vehicle = new Vehicle();
    
    // Get vehicle details
    $vehicleData = $vehicle->getVehicle($vehicleId);
    
    if (!$vehicleData) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Vehicle not found'
        ]);
        exit;
    }
    
    // Format response
    echo json_encode([
        'success' => true,
        'vehicle' => $vehicleData
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
