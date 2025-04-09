<?php
header('Content-Type: application/json');

// Include the database connection
include 'databaseconnection.php';

try {
    // Check if POST request has required parameters
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['room_id']) && isset($_POST['energy_consumed'])) {
        $room_id = $_POST['room_id'];
        $energy_consumed = floatval($_POST['energy_consumed']);

        // Prepare the update query
        $stmt = $conn->prepare("UPDATE room_energy SET energy_consumed = energy_consumed + ?, remaining_units = remaining_units - ? WHERE room_id = ?");
        $stmt->execute([$energy_consumed, $energy_consumed, $room_id]);

        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Energy consumption updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No record found for the given room ID']);
        }
    } else {
        echo json_encode(['error' => 'Invalid request or missing parameters']);
    }
} catch (PDOException $e) {
    // Handle database connection errors
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
