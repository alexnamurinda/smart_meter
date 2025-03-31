<?php
session_start();
include 'db.php';

// Fetch client details based on the `client_id` from the query string
$clientId = $_GET['id'] ?? null;

if (!$clientId) {
    echo "Client ID not provided.";
    exit;
}

$clientQuery = "SELECT * FROM clients WHERE client_id = :id";
$clientStmt = $conn->prepare($clientQuery);
$clientStmt->bindParam(':id', $clientId, PDO::PARAM_INT);
$clientStmt->execute();
$client = $clientStmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo "Client not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for updating client details
    $clientId = $_POST['client_id'];
    $clientName = $_POST['client_name'];
    $clientCategory = $_POST['client_category'];
    $phoneNumber = $_POST['phone_number'];
    $apartment = $_POST['apartment_id'];
    $room = $_POST['room_id'];

    // Preprocess the phone number to ensure it uses the +256 format
    $phoneNumber = preg_replace('/^0/', '+256', $phoneNumber);

    // Update query
    $updateQuery = "UPDATE clients 
                    SET client_name = :client_name, 
                        client_category = :client_category, 
                        phone_number = :phone_number,
                        apartment_id = :apartment_id, 
                        room_id = :room_id
                    WHERE client_id = :client_id";

    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':client_id', $clientId, PDO::PARAM_INT);
    $updateStmt->bindParam(':client_name', $clientName);
    $updateStmt->bindParam(':client_category', $clientCategory);
    $updateStmt->bindParam(':phone_number', $phoneNumber);
    $updateStmt->bindParam(':apartment_id', $apartment);
    $updateStmt->bindParam(':room_id', $room);

    // Execute the update query
    if ($updateStmt->execute()) {
        header("Location: manage_users.php");
        exit;
    } else {
        echo "Failed to update client.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>update client details</title>
    <link rel="stylesheet" href="admnstyling.css">
    <style>
        .container {
            width: 80%;
            margin-left: 10%;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            box-sizing: border-box;
            margin-top: -38px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        h2 {
            color: #34495e;
            margin: 0;
        }

        .form-grid {
            display: grid;
            margin-top: 0px;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            width: 90%;
            margin-left: 50px;
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            font-size: 1.1rem;
        }

        input[type="text"],
        input[type="email"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            margin-bottom: 10px;
            background-color: #fff;
            box-sizing: border-box;
        }

        input[type="submit"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            width: 50%;
            margin-left: 30%;
            box-sizing: border-box;
            margin-bottom: 20px;
            background-color: #3498db;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
            grid-column: span 2;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 76.5%;
            margin-top: -10px;
        }

        button:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
                margin-left: 5%;
            }
            .form-grid {
                grid-template-columns: 1fr;
                width: 100%;
                margin-left: 0;               
            }

            input[type="submit"] {
            width: 80%;
            margin-left: 10px;
        }

        button {
            padding: 5px 10px;
            font-size: 12px;
            margin-left: 75%;
            margin-top: -10px;
        }

        button:hover {
            background-color: #2980b9;
        }

        }
    </style>
</head>

<body>
    <div class="admin-dashboard">
        <header class="admin-header">
            <h1>Admin Dashboard - SMART_METER_PROJECT</h1>
            <p>Centralized Control for Smarter Energy Solutions and User accounts.</p>
        </header>

        <button onclick="window.location.href='admindashboard.php'">Back to Dashboard</button>
        <div class="container">
            <div class="profile-header">
            <img src="<?php echo !empty($client['profile_pic']) ? htmlspecialchars($client['profile_pic']) : 'images/profile_pic.png'; ?>" alt="Profile Picture">
                <h2><?php echo htmlspecialchars($client['client_name']); ?></h2>
            </div>
            <form method="POST">
                <div class="form-grid">
                    <div>
                        <label for="client_id">Client ID:</label>
                        <input type="text" name="client_id" value="<?php echo htmlspecialchars($client['client_id']); ?>" readonly>

                        <label for="client_name">Full Name:</label>
                        <input type="text" name="client_name" value="<?php echo htmlspecialchars($client['client_name']); ?>" required>

                        <label for="client_category">Category:</label>
                        <input type="text" name="client_category" value="<?php echo htmlspecialchars($client['client_category']); ?>" required>
                    </div>
                    <div>
                        <label for="phone_number">Phone Number:</label>
                        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($client['phone_number']); ?>" required>

                        <label for="apartment_id">Apartment ID:</label>
                        <input type="text" name="apartment_id" value="<?php echo htmlspecialchars($client['apartment_id']); ?>">

                        <label for="room_id">Room ID:</label>
                        <input type="text" name="room_id" value="<?php echo htmlspecialchars($client['room_id']); ?>">
                    </div>
                </div>
                <input type="submit" value="Update Client">
            </form>
        </div>
    </div>

    <footer class="admin-footer">
        <p>&copy; 2024 kooza technologies. All Rights Reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
    </footer>

</body>

</html>