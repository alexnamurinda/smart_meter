<?php
// get_dashboard_data.php
session_start();
require 'db_connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['client_id']) || !isset($_SESSION['room_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$client_id = $_SESSION['client_id'];
$room_id = $_SESSION['room_id'];

try {
    // Use a single query with JOIN to get all data more efficiently
    $query = "
        SELECT 
            r.*, 
            l.*,
            c.name as client_name
        FROM rooms r
        JOIN loads l ON r.room_id = l.room_id
        JOIN clients c ON c.client_id = ?
        WHERE r.room_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $client_id, $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $loads = [];
    $room = null;
    $client_name = null;

    // Calculate totals and stats
    $totalEnergy = 0;
    $voltageCount = 0;
    $averageVoltage = 0;
    $activeLoads = 0;
    $totalPower = 0;

    while ($row = $result->fetch_assoc()) {
        // Extract room and client info on first iteration
        if ($room === null) {
            $room = [
                'room_id' => $row['room_id'],
                'name' => $row['name'],
                'location' => $row['location']
            ];
            $client_name = $row['client_name'];
        }

        // Build load object
        $load = [
            'load_id' => $row['load_id'],
            'load_name' => $row['load_name'],
            'power_status' => $row['power_status'],
            'energy_consumed' => $row['energy_consumed'],
            'current' => $row['current'],
            'power' => $row['power'],
            'voltage' => $row['voltage'],
            'changed_by' => $row['changed_by'],
            'updated_at' => $row['updated_at']
        ];

        $loads[] = $load;

        // Calculate stats
        $totalEnergy += $load['energy_consumed'];
        $totalPower += $load['power'];

        if (isset($load['voltage']) && is_numeric($load['voltage']) && $load['voltage'] > 0) {
            $averageVoltage += $load['voltage'];
            $voltageCount++;
        }

        if ($load['power_status'] == 'ON') {
            $activeLoads++;
        }
    }

    $totalLoads = count($loads);

    // Calculate average voltage based on available readings
    $averageVoltage = ($voltageCount > 0) ? $averageVoltage / $voltageCount : 0;

    // Format for display
    $totalEnergy = number_format($totalEnergy, 2);
    $averageVoltage = number_format($averageVoltage, 2);
    $totalPower = number_format($totalPower, 2);

    // Prepare response data
    $response = [
        'client' => ['name' => $client_name],
        'room' => $room,
        'loads' => $loads,
        'totalEnergy' => $totalEnergy,
        'averageVoltage' => $averageVoltage,
        'totalPower' => $totalPower,
        'activeLoads' => $activeLoads,
        'totalLoads' => $totalLoads,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => 'Could not fetch data: ' . $e->getMessage()]);
}
