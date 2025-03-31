<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $clientName = $_POST['client_name'];
    $clientCategory = $_POST['client_category'];
    $phoneNumber = $_POST['phone_number'];
    $clientPassword = password_hash($_POST['client_password'], PASSWORD_BCRYPT);
    $profilePic = $_FILES['profile_pic']['name'] ? $_FILES['profile_pic']['name'] : null;
    $apartmentId = $_POST['apartment_id'] ? $_POST['apartment_id'] : null;
    $roomId = $_POST['room_id'] ? $_POST['room_id'] : null;

    // Handle profile picture upload
    if ($profilePic) {
        $uploadDir = 'images/';
        $uploadFile = $uploadDir . basename($profilePic);

        // Validate the uploaded file
        $fileType = mime_content_type($_FILES['profile_pic']['tmp_name']);
        if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {
            echo "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
            exit;
        }

        if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadFile)) {
            echo "Failed to upload profile picture.";
            exit;
        }
    }

    $phoneNumber = preg_replace('/^0/', '+256', $phoneNumber);

    // Insert query
    $insertQuery = "INSERT INTO clients (client_name, client_category, phone_number, client_password, profile_pic, apartment_id, room_id)
                    VALUES (:client_name, :client_category, :phone_number, :client_password, :profile_pic, :apartment_id, :room_id)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bindParam(':client_name', $clientName);
    $insertStmt->bindParam(':client_category', $clientCategory);
    $insertStmt->bindParam(':phone_number', $phoneNumber);
    $insertStmt->bindParam(':client_password', $clientPassword);
    $insertStmt->bindParam(':profile_pic', $profilePic);
    $insertStmt->bindParam(':apartment_id', $apartmentId);
    $insertStmt->bindParam(':room_id', $roomId);

    // Execute the insert query
    if ($insertStmt->execute()) {
        echo "<script>
                alert('New client added successfully!');
                window.location.href = 'manage_users.php';
             </script>";
        exit();
    } else {
        echo "Failed to add new client.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>add_new client</title>
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

        h2 {
            color: #34495e;
            margin: 0;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            width: 90%;
            margin: 0 auto;
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            font-size: 1.1rem;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"],
        input[type="submit"] {
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
            background-color: #3498db;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
            grid-column: span 2;
            width: 40%;
            margin-left: 30%;
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

            h2 {
                font-size: 22px;
                text-align: left;
            }

            button {
                padding: 5px 10px;
                font-size: 10px;
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
            <h2>Add New Client</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-grid">
                    <div>
                        <label for="client_name"><i class="fa fa-user"></i> Client Name:</label>
                        <input type="text" name="client_name" required>

                        <label for="client_category"><i class="fa fa-tag"></i> Client Category:</label>
                        <input type="text" name="client_category" required>

                        <label for="phone_number"><i class="fa fa-phone"></i> Phone Number:</label>
                        <input type="text" name="phone_number" required>

                        <label for="client_password"><i class="fa fa-lock"></i> Password:</label>
                        <input type="password" name="client_password" required>
                    </div>
                    <div>
                        <label for="profile_pic"><i class="fa fa-image"></i> Profile Picture:</label>
                        <input type="file" name="profile_pic">

                        <label for="apartment_id"><i class="fa fa-building"></i> Apartment ID:</label>
                        <input type="text" name="apartment_id">

                        <label for="room_id"><i class="fa fa-door-open"></i> Room ID:</label>
                        <input type="text" name="room_id">
                    </div>
                </div>

                <input type="submit" value="Add Client">
            </form>
        </div>
    </div>

    <footer class="admin-footer">
        <p>&copy; 2024 kooza technologies. All Rights Reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
    </footer>

</body>
</html>