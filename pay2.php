<?php
// Include the database connection
include 'db.php';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $room_id = $_POST['room_id'];
        $units = floatval($_POST['units']);

        // Check if room exists
        $stmt = $conn->prepare("SELECT * FROM room_energy WHERE room_id = ?");
        $stmt->execute([$room_id]);

        if ($stmt->rowCount() > 0) {
            // Update remaining units
            $stmt = $conn->prepare("UPDATE room_energy SET remaining_units = remaining_units + ? WHERE room_id = ?");
            $stmt->execute([$units, $room_id]);

            // Notify ESP32
            file_get_contents("http://10.180.200.239/smart_METER_project/update_esp.php?room_id=" . urlencode($room_id));

            echo "<script>alert('Units loaded successfully! ESP notified.'); window.location.href='pay.php';</script>";
        } else {
            echo "<script>alert('Room ID not found!'); window.location.href='pay.php';</script>";
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
