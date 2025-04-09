<?php
session_start();
include 'databaseconnection.php';

// Check if phone number is set in session
if (!isset($_SESSION['reset_phone'])) {
    header("Location: password_reset_request.php");
    exit();
}

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if we're verifying OTP or setting a new password
    if (isset($_POST['otp'])) {
        // Verify OTP logic
        $user_otp = $_POST['otp'];
        $stored_otp = $_SESSION['reset_otp'] ?? '';
        $timestamp = $_SESSION['reset_timestamp'] ?? 0;

        // Check if OTP has expired (2 minutes)
        if (time() - $timestamp > 120) {
            $error_message = "OTP has expired. Please request a new one.";
            // Clear session data
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_timestamp']);
            header("Location: password_reset_request.php");
            exit();
        }

        if ($user_otp == $stored_otp) {
            // OTP is correct, set verification flag
            $_SESSION['otp_verified'] = true;
            $success_message = "";
        } else {
            $error_message = "Invalid OTP. Please try again.";
        }
    } else if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        // Check if OTP is verified
        if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
            $error_message = "Please verify your OTP first.";
        } else {
            // Set new password logic
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                $error_message = "Passwords do not match.";
            } else if (strlen($new_password) < 6) {
                $error_message = "Password must be at least 6 characters long.";
            } else {
                // Passwords match and are valid, update in database
                try {
                    $phone_number = $_SESSION['reset_phone'];
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("UPDATE clients SET client_password = :password WHERE phone_number = :phone_number");
                    $stmt->execute([
                        ':password' => $hashed_password,
                        ':phone_number' => $phone_number
                    ]);

                    if ($stmt->rowCount() > 0) {
                        // Password updated successfully
                        $success_message = "";

                        // Clear all reset-related session variables
                        unset($_SESSION['reset_otp']);
                        unset($_SESSION['reset_phone']);
                        unset($_SESSION['reset_timestamp']);
                        unset($_SESSION['otp_verified']);

                        // Redirect to login page after 2 seconds
                        header("refresh:2; url=getstarted.php");
                    } else {
                        $error_message = "Failed to update password. Please try again.";
                    }
                } catch (PDOException $e) {
                    $error_message = "Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Display appropriate form based on OTP verification status
$otp_verified = isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $otp_verified ? 'Set New Password' : 'Verify OTP'; ?></title>
    <link rel="stylesheet" href="getstarted.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        @import url('http://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;

        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(190deg, #E8F5E9, #E8F5E9);
        }

        .wrapper {
            position: relative;
            width: 55%;
            height: 500px;
            background: #9b9b9b;
            border-radius: 10px;
            overflow: hidden;
        }

        .wrapper .form-box {
            position: absolute;
            top: 0;
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .wrapper .form-box.login {
            left: 40px;
            padding: 0 60px 0 40px;
        }

        .input-box {
            font-size: 10px;
        }

        .form-box h1 {
            font-size: 18px;
            color: #fff;
            text-align: center;
            margin-bottom: -20px;
            font-weight: lighter;
        }

        .form-box .input-box {
            position: relative;
            width: 100%;
            height: 50px;
            margin: 25px 0;
        }

        .input-box input {
            width: 100%;
            height: 100%;
            background: transparent;
            border-radius: 5px;
            font-size: 15px;
            border-bottom: 2px solid #fff;
            color: #fff;
            transition: 0.5s;
            text-align: center;
        }

        .input-box input:focus~label,
        .input-box input:valid~label {
            top: -5px;
            color: #E8F5E9;
        }


        .input-box input:focus~i,
        .input-box input:valid~i {
            color: #f6921e;
        }

        .btn {
            position: relative;
            width: 100%;
            height: 45px;
            background: #f6921e;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 16px;
            color: #fff;
            font-weight: 600;
            z-index: 1;
            overflow: hidden;
        }

        .btn:hover {
            background: #1b75bb;
            transition: .1s;
        }

        .form-box .logreg-link {
            font-size: 20px;
            color: #fff;
            text-align: center;
            margin: 20px 0 10px;
        }

        .logreg-link p a {
            color: #1b66bb;
            text-decoration: none;
            font-weight: 600;
        }

        .logreg-link p a:hover {
            text-decoration: underline;
            color: #E8F5E9;
        }

        .wrapper .info-text {
            position: absolute;
            top: 0;
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .wrapper .info-text.login {
            right: 0;
            text-align: right;
            padding: 0 40px 60px 150px;
        }

        .info-text h2 {
            color: #fff;
            font-size: 36px;
            line-height: 1.3;
            text-transform: uppercase;
        }

        .info-text p {
            font-size: 16px;
            color: #fff;
        }

        .wrapper .bg-animate {
            position: absolute;
            top: -40px;
            right: 0;
            width: 950px;
            height: 650px;
            background: linear-gradient(50deg, #a09f9f, #1b75bb);
            border-bottom: 0px solid #1b75bb;
            transform: rotate(10deg) skewY(40deg);
            transform-origin: bottom right;
            transition: 1s ease;
        }

        img {
            border-radius: 50%;
            margin-bottom: 30px;
        }

        /* small screens */
        @media (max-width: 768px) {

            body {
                min-height: 90vh;
                min-width: 100%;
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
            }

            .wrapper {
                align-items: center;
                width: 100%;
                height: 550px;
                margin: 20px 10px;
                padding-left: 0;
                background: #9b9b9b;
                border-radius: 10px;
                overflow: hidden;
                height: 430px;
            }

            .wrapper .form-box {
                position: absolute;
                width: 100%;
            }

            img {
                max-width: 140px;
                margin: 50px auto;
                border-radius: 50%;
                margin-bottom: 15px;
                margin-left: -10px;
                margin-top: 150px;
            }

            .wrapper .form-box.login {
                transform: translateX(0);
                opacity: 1;
                filter: blur(0);
                margin-top: 150px;
                height: fit-content;
                left: 0;
            }

            .wrapper.active .form-box.login {
                transform: translateX(100%);
                opacity: 0;
                filter: blur(10px);
                transition-delay: calc(.1s * var(--i));
                transition: .7s ease right;

            }

            .wrapper .form-box.login h1 {
                font-size: 25px;
                color: #fff;
                text-align: center;
                padding-top: 10px;
                margin-bottom: -10px;

            }

            .wrapper .info-text.login h2 {
                display: none;
            }

            .wrapper .form-box .input-box {
                margin: 20px 0;
                position: relative;
                width: 100%;
            }

            .input-box input {
                width: 100%;
                height: 45px;
                background: transparent;
                border-radius: 5px;
                font-size: 15px;
                border-bottom: 2px solid #fff;
                color: #fff;
                transition: 0.5s;
            }

            .input-box input:focus~label,
            .input-box input:valid~label {
                top: -5px;
                color: #E8F5E9;
            }

            .input-box input:focus~i,
            .input-box input:valid~i {
                color: #f6921e;
            }

            .btn {
                width: 100%;
                height: 40px;
                background: #f6921e;
                border: none;
                border-radius: 20px;
                cursor: pointer;
                font-size: 14px;
                color: #fff;
                font-weight: 600;
                z-index: 1;
                overflow: hidden;
            }

            .btn:hover {
                background: #1b75bb;
                transition: .1s;
            }

            .form-box .logreg-link {
                font-size: 20px;
                color: #fff;
                text-align: center;
                margin: 15px 0 10px;
            }

            .logreg-link p a {
                color: #1b66bb;
                text-decoration: none;
                font-weight: 600;
                font-size: 15px;
            }

            .logreg-link p a:hover {
                text-decoration: underline;
                color: #E8F5E9;
            }

            .wrapper .bg-animate {
                position: absolute;
                top: -35px;
                right: 0;
                width: 500px;
                height: 600px;
                background: linear-gradient(150deg, #1b75bb, #a09f9f);
                border-bottom: 0px solid #1b75bb;
                transform: rotate(15deg) skewY(40deg);
                transform-origin: bottom right;
                transition: 1s ease;
            }

            .error-mesage {
                color: red;
                font-size: 10px;
                position: relative;
                top: -10px;
                text-align: center;
            }

            .form-box h1 {
                font-size: 18px;
                color: #fff;
                text-align: center;
                margin-bottom: -20px;
                font-weight: bold
            }

        }

        .error-mesage {
            color: red;
            font-size: 16px;
            position: relative;
            top: 30px;
            text-align: center;
        }
    </style>

</head>

<body>
    <div class="wrapper">
        <span class="bg-animate"></span>
        <div class="reg_box">
            <div class="info-text login">
                <img src="images/logo.png" class="animation" style="--i:0;">
                <h2 class="animation" style="--i:0">Password Reset<br /></h2>
                <p class="animation" style="--i:1">Kooza Smart_meter</p>
            </div>
            <div class="form-box login">
                <h1 class="animation" style="--i:0;">
                    <?php echo $otp_verified ? 'Set New Password' : 'Enter Verification Code'; ?>
                </h1>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message" id="errorMessage">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="success-message" id="successMessage">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <script>
                    // Handle error message display
                    const errorMessage = document.getElementById('errorMessage');
                    if (errorMessage && errorMessage.textContent.trim() !== "") {
                        errorMessage.style.display = 'block';
                        setTimeout(() => {
                            errorMessage.style.display = 'none';
                        }, 3000);
                    }

                    // Handle success message display
                    const successMessage = document.getElementById('successMessage');
                    if (successMessage && successMessage.textContent.trim() !== "") {
                        successMessage.style.display = 'block';
                        setTimeout(() => {
                            successMessage.style.display = 'none';
                        }, 3000);
                    }
                </script>

                <?php if (!$otp_verified): ?>
                    <!-- OTP Verification Form -->
                    <form action="" method="post" class="sig">
                        <div class="input-box animation" style="--i:1;">
                            <input type="text" name="otp" placeholder="Enter verification code" required>
                        </div>

                        <button type="submit" class="btn animation" style="--i:3;">Verify Code</button>

                        <div class="logreg-link animation" style="--i:3;">
                            <p>
                                <a href="password_reset_request.php" class="register-link">
                                    <i class="bx bx-arrow-back" style="margin-right: 5px; font-size: 20px;"></i> Back
                                </a>
                            </p>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- New Password Form -->
                    <form action="" method="post" class="sig">
                        <div class="input-box animation" style="--i:1;">
                            <input type="password" name="new_password" placeholder="Enter new password" required>
                        </div>

                        <div class="input-box animation" style="--i:2;">
                            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                        </div>

                        <button type="submit" class="btn animation" style="--i:3;">Reset Password</button>
                        <div class="logreg-link animation" style="--i:3;">
                            <p>
                                <a href="getstarted.php" class="register-link">
                                    <i class="bx bx-arrow-back" style="margin-right: 5px; font-size: 20px;"></i> Back
                                </a>
                            </p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>