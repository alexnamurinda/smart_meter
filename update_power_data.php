<?php
// update_power_data.php - Receives power data from ESP32 and updates the database

// Database connection
$servername = "localhost";
$username = "root";
$password = "Alex@mysql123";
$dbname = "homewatt";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

// Map PZEM ID to load ID in the room
// PZEM 1 = Load 1, PZEM 2 = Load 2, Load 3 remains default
$load_id = $pzem_id; // Simple 1:1 mapping

// Prepare SQL statement to update the specific load
$stmt = $conn->prepare("
    UPDATE loads 
    SET 
        voltage = ?,
        current = ?,
        power = ?,
        energy_consumed = ?,
        updated_at = NOW()
    WHERE load_id = ?
");

// Bind parameters
$stmt->bind_param(
    "ddddi",
    $voltage,
    $current,
    $power,
    $energy_consumed,
    $load_id
);

// Execute statement
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Load data updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error updating data: ' . $stmt->error]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>