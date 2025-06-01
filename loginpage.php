<?php
session_start();
include 'db_connection.php';

// Initialize variables to avoid "undefined variable" warnings
$errorMsg = "";
$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM clients WHERE phone_number = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['client_id'] = $user['client_id'];
        $_SESSION['room_id'] = $user['room_id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $errorMsg = "Invalid phone or password.";
    }
}
?>

<!-- Login Form -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('http://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
        }

        .login-container {
            max-width: 400px;
            margin: 10px auto;
            padding: 30px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .login-container h3 {
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
        }

        .alert {
            margin-top: 10px;
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

    <div class="login-container">
        <div class="text-center mb-3">
            <img src="images/logo.png" alt="Logo" style="width: 150px;">
        </div>

        <div class="message">
            <?php if ($errorMsg): ?>
                <p class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $errorMsg; ?></p>
            <?php elseif ($successMsg): ?>
                <p class="success"><i class="fas fa-check-circle"></i> <?php echo $successMsg; ?></p>
            <?php endif; ?>
        </div>

        <!-- Login Form -->
        <form method="POST">
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter your phone number" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>

            <!-- Already have an account link -->
            <div class="already-account">
                <p>Don't have an account? <a href="registrationpage.php">Sign Up</a></p>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>