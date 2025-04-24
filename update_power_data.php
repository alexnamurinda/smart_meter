<?php
// update_power_data.php - Receives power data from ESP32 and updates the database

// Include the centralized DB connection
require_once 'db_connection.php'; // Make sure this path is correct

// Get JSON data from request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate the required fields
if (!isset($data['pzem_id']) ||
    !isset($data['voltage']) ||
    !isset($data['current']) ||
    !isset($data['power']) ||
    !isset($data['energy_consumed'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Sanitize inputs
$pzem_id = (int)$data['pzem_id'];
$voltage = (float)$data['voltage'];
$current = (float)$data['current'];
$power = (float)$data['power'];
$energy_consumed = (float)$data['energy_consumed'];

// Map PZEM ID to load ID
$load_id = $pzem_id; // Assuming direct mapping

// Prepare SQL statement
$stmt = $conn->prepare("
    UPDATE loads
    SET voltage = ?, current = ?, power = ?, energy_consumed = ?, updated_at = NOW()
    WHERE load_id = ?
");

// Bind parameters
$stmt->bind_param("ddddi", $voltage, $current, $power, $energy_consumed, $load_id);

// Execute and respond
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Load data updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error updating data: ' . $stmt->error]);
}

// Clean up
$stmt->close();
$conn->close();
?>
