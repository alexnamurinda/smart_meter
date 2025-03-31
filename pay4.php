<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kooza_db";

// Get room_id from query parameter
$room_id = $_GET['room_id'];

// Validate room_id to prevent SQL injection
if (!preg_match('/^[A-Z0-9\-]+$/', $room_id)) {
    echo "0"; // Return 0 for invalid room IDs
    exit;
}

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo "0"; // Return 0 if connection fails
    exit;
}

// Prepare SQL query to get energy units for the specified room
$sql = "SELECT remaining_units FROM room_energy WHERE room_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Return the energy units value as a simple float
    $row = $result->fetch_assoc();
    echo number_format($row["remaining_units"], 2, '.', '');
} else {
    // Return 0 if no data found for the room
    echo "0";
}

$conn->close();
?>