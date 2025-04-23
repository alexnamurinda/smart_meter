<?php
include 'db_creation.php';
include 'db_connection.php';

// Initialize error and success messages
$errorMsg = "";
$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address'];
    $room_id = $_POST['room_id'];

    // Check if room is already assigned
    $checkRoom = $conn->prepare("SELECT * FROM clients WHERE room_id = ?");
    $checkRoom->bind_param("s", $room_id);
    $checkRoom->execute();
    $result = $checkRoom->get_result();

    if ($result->num_rows > 0) {
        $errorMsg = "Room already assigned!";
    } else {
        $stmt = $conn->prepare("INSERT INTO clients (name, phone_number, password, address, room_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $phone, $password, $address, $room_id);
        if ($stmt->execute()) {
            $successMsg = "Registration successful! Redirecting to login...";
            header("refresh:2; url=loginpage.php"); // Redirect after 2 seconds
        } else {
            $errorMsg = "Error: " . $stmt->error;
        }
    }
}
?>


<!-- Registration Form -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('http://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
        }

        .registration-container {
            max-width: 500px;
            margin: 10px auto;
            padding: 30px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            min-height: 400px;
        }

        .registration-container h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-control {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
        }

        .alert {
            text-align: center;
        }

        .already-account {
            text-align: center;
            margin-top: 15px;
        }

        .already-account a {
            color: #007bff;
            text-decoration: none;
        }

        .already-account a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="registration-container">
        <div class="text-center mb-3">
            <img src="images/logo.png" alt="Logo" style="width: 150px;">
        </div>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger text-center"><?php echo $errorMsg; ?></div>
        <?php elseif (!empty($successMsg)): ?>
            <div class="alert alert-success text-center"><?php echo $successMsg; ?></div>
        <?php endif; ?>



        <!-- Registration Form -->
        <form method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Enter your name" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter your phone number" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" name="address" id="address" placeholder="Enter your address">
            </div>

            <div class="form-group">
                <label for="room_id">Select Available Room</label>
                <select name="room_id" class="form-control" id="room_id" required>
                    <?php
                    // Fetch only unassigned rooms
                    $query = "SELECT room_id, name FROM rooms WHERE room_id NOT IN (SELECT room_id FROM clients WHERE room_id IS NOT NULL)";
                    $result = $conn->query($query);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['room_id']}'>{$row['name']}</option>";
                        }
                    } else {
                        echo "<option>No available rooms</option>"; // Handle if no rooms are available
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>

            <!-- Already have an account link -->
            <div class="already-account">
                <p>Already have an account? <a href="loginpage.php">Login here</a></p>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>