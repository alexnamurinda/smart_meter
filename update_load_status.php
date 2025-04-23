<?php
session_start();
require 'db_connection.php';

// Handle the GET request for fetching power status
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'get_power_status') {
    // Get the PZEM ID from the query parameters
    if (!isset($_GET['pzem_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing PZEM ID']);
        exit;
    }
    
    $pzem_id = (int)$_GET['pzem_id'];
    $load_id = $pzem_id; // Simple 1:1 mapping as in your update_power_data.php
    
    // Query the database for the current power status
    $stmt = $conn->prepare("SELECT power_status FROM loads WHERE load_id = ?");
    $stmt->bind_param("i", $load_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Return the power status as JSON
        echo json_encode(['power_status' => $row['power_status']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Load not found']);
    }
    
    $stmt->close();
    exit;
}

// Original POST handling for client-side control
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['client_id']) || !isset($_SESSION['room_id'])) {
        echo "Unauthorized access";
        exit();
    }

    $client_id = $_SESSION['client_id'];
    $room_id = $_SESSION['room_id'];
    
    // Get load_id and power_status from POST data
    $load_id = $_POST['load_id'];
    $power_status = $_POST['power_status'];
    
    // Validate power status
    if ($power_status != "ON" && $power_status != "OFF") {
        echo "Invalid power status";
        exit();
    }
    
    try {
        // First check if this load belongs to the user's room
        $checkQuery = "SELECT room_id FROM loads WHERE load_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $load_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $load = $checkResult->fetch_assoc();
        
        // Verify the load belongs to the user's room
        if (!$load || $load['room_id'] != $room_id) {
            echo "Unauthorized: This load does not belong to your room";
            exit();
        }
        
        // Get the current status to store as previous status
        $currentStatusQuery = "SELECT power_status FROM loads WHERE load_id = ?";
        $currentStatusStmt = $conn->prepare($currentStatusQuery);
        $currentStatusStmt->bind_param("i", $load_id);
        $currentStatusStmt->execute();
        $currentStatusResult = $currentStatusStmt->get_result();
        $currentStatus = $currentStatusResult->fetch_assoc();
        
        // Update the load status
        $updateQuery = "UPDATE loads SET 
                        power_status = ?, 
                        previous_status = ?,
                        changed_by = 'client'
                        WHERE load_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssi", $power_status, $currentStatus['power_status'], $load_id);
        $updateStmt->execute();
        
        if ($updateStmt->affected_rows > 0) {
            echo "Success: Load status updated to " . $power_status;
        } else {
            echo "No changes made";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else if ($_SERVER["REQUEST_METHOD"] != "GET") {
    echo "Invalid request method";
}

$conn->close();
?>