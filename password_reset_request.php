<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone_number = $_POST['phone_number'];

    // Format phone number to Uganda's standard format
    if (strpos($phone_number, '0') === 0) {
        $phone_number = '+256' . substr($phone_number, 1);
    }

    try {
        // Check if the phone number exists in the database
        $stmt = $conn->prepare("SELECT * FROM clients WHERE phone_number = :phone_number");
        $stmt->execute([':phone_number' => $phone_number]);

        if ($stmt->rowCount() > 0) {
            // Phone number exists, generate and send OTP
            $otp = rand(100000, 999999); 
            $_SESSION['reset_otp'] = $otp; // Store OTP in session
            $_SESSION['reset_phone'] = $phone_number; 
            $_SESSION['reset_timestamp'] = time(); // timestamp for expiry check

            $apiUsername = 'agritech_info';
            $apiKey = 'atsk_d30afdc12c16b290766e27594e298b4c82fa0ca3d87f723f7a2576aa9a6d0b9d096fa012';
            $apiUrl = 'https://api.africastalking.com/version1/messaging';

            $message = "Your password reset code is: $otp";

            // Set up the cURL request
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

            // Execute the request
            $response = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            $responseDecoded = json_decode($response, true);

            if (isset($responseDecoded['SMSMessageData']['Recipients']) && count($responseDecoded['SMSMessageData']['Recipients']) > 0) {
                // Redirect to OTP verification page
                header("Location: password_reset_verification.php");
                exit();
            } else {

                header("Location: password_reset_verification.php");
                exit();
            }
        } else {
            $error_message = "Phone number not found.";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
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
            font-size: 20px;
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
                font-size: 20px;
                color: #fff;
                text-align: center;
                margin-bottom: -20px;
                font-weight: bold;
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
                <h2 class="animation" style="--i:0">Welcome Back<br /></h2>
                <p class="animation" style="--i:1">Kooza Smart_meter</p>
            </div>
            <div class="form-box login">
                <h1 class="animation" style="--i:0;">Enter your phone number</h1>
                <div class="error-message" id="errorMessage" style="display: <?php echo !empty($error_message) ? 'block' : 'none'; ?>">
                    <?php if (!empty($error_message)) echo htmlspecialchars($error_message); ?>
                </div>
                <script>
                    const errorMessage = document.getElementById('errorMessage');
                    if (errorMessage.textContent.trim() !== "") {
                        errorMessage.style.display = 'block';
                        setTimeout(() => {
                            errorMessage.style.display = 'none';
                        }, 3000);
                    }
                </script>
                <form action="" method="post" class="sig" enctype="multipart/form-data">
                    <div class="input-box animation" style="--i:1;">
                        <input type="text" name="phone_number" placeholder="phone number" required>
                    </div>

                    <button type="submit" class="btn animation" style="--i:3;">Continue</button>

                    <div class="logreg-link animation" style="--i:3;">
                        <p>
                            <a href="getstarted.php" class="register-link">
                                <i class="bx bx-arrow-back" style="margin-right: 5px; font-size: 20px;"></i> Back to Login
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>