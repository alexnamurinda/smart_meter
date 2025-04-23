<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['client_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$client_id = $_SESSION['client_id'];

// Fetch phone number
$stmt = $conn->prepare("SELECT phone_number FROM clients WHERE client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit();
}

function normalizePhoneNumber($phone_number)
{
    $phone_number = preg_replace('/\D/', '', $phone_number);
    if (substr($phone_number, 0, 1) === '0') {
        $phone_number = '+256' . substr($phone_number, 1);
    } elseif (substr($phone_number, 0, 4) !== '+256') {
        $phone_number = '+256' . $phone_number;
    }
    return $phone_number;
}

$phone_number = normalizePhoneNumber($user['phone_number']);
$message = "⚠️ ALERT: High power usage detected. Login to your dashboard: https://yourdomain.com/login.php";

// Africa's Talking credentials
$apiUsername = 'agritech_info';
$apiKey = 'atsk_d30afdc12c16b290766e27594e298b4c82fa0ca3d87f723f7a2576aa9a6d0b9d096fa012';
$apiUrl = 'https://api.africastalking.com/version1/messaging';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => $apiUsername,
    'to' => $phone_number,
    'message' => $message
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apiKey: ' . $apiKey,
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
curl_close($ch);

echo json_encode(['status' => 'success', 'message' => 'SMS sent if number is valid']);
