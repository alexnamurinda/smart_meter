<?php
header('Content-Type: application/json');

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'kooza_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['room_id'])) {
        $room_id = $_GET['room_id'];

        // Fetch remaining units
        $stmt = $pdo->prepare("SELECT remaining_units FROM room_energy WHERE room_id = ?");
        $stmt->execute([$room_id]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['room_id' => $room_id, 'remaining_units' => $row['remaining_units']]);
        } else {
            echo json_encode(['error' => 'Room ID not found']);
        }
    } else {
        echo json_encode(['error' => 'No Room ID provided']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
