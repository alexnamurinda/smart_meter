<?php
session_start();
include 'db.php';

header("Content-Type: application/json");

// Ensure user is authenticated
if (!isset($_SESSION['user']) || $_SESSION['user']['authenticated'] !== true) {
    echo json_encode(["error" => "Unauthorized access."]);
    exit();
}

$phone_number = $_SESSION['user']['phone_number'];

// Fetch room_id from the clients table
$sql = "SELECT room_id FROM clients WHERE phone_number = :phone_number LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo json_encode(["error" => "User not found."]);
    exit();
}

$room_id = $client['room_id'];

// Fetch transactions for this room_id
$sql = "SELECT transaction_date, amount, kwh, payment_method, transaction_id FROM transactions 
        WHERE client_id IN (SELECT client_id FROM clients WHERE room_id = :room_id) 
        ORDER BY transaction_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($transactions);
?>
