<?php
session_start();
include 'databaseconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];

    if (isset($_SESSION['otp']) && $_SESSION['otp'] == $entered_otp) {
        // OTP is valid, save user to the database
        $registration_data = $_SESSION['registration_data'];

        // Convert phone number to standard format
        $phone_number = $registration_data['phone_number'];
        if (strpos($phone_number, '0') === 0) {
            $phone_number = '+256' . substr($phone_number, 1);
        }

        try {
            $stmt = $conn->prepare("INSERT INTO clients (client_name, phone_number, client_category, client_password) VALUES (:name, :phone_number, :category, :password)");
            $stmt->execute([
                ':name' => $registration_data['name'],
                ':phone_number' => $phone_number,
                ':category' => $registration_data['category'],
                ':password' => password_hash($registration_data['password'], PASSWORD_DEFAULT)
            ]);

            // Clear session data
            unset($_SESSION['otp']);
            unset($_SESSION['registration_data']);

            // Set success message in session
            $_SESSION['registration_success'] = "Registration successful! Please login with your credentials.";
            
            // Redirect to login page
            header("Location: getstarted.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error saving user: " . $e->getMessage();
        }
    } else {
        $error_message = "Invalid OTP. Please try again.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP verification</title>
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
            font-size: 23px;
            color: #fff;
            text-align: center;
            margin-bottom: -20px;
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
            font-size: 16px;
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

            .wrapper .form-box.login h1{
                font-size: 25px;
                color: #fff;
                text-align: center;
                padding-top: 10px;
                margin-bottom: -10px;

            }

            .wrapper .info-text.login h2{
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
                font-size: 16px;
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
                <h2 class="animation" style="--i:0">Welcome Back!<br /></h2>
                <p class="animation" style="--i:1">Kooza Smart_meter</p>
            </div>
            <div class="form-box login">
                <h1 class="animation" style="--i:0;">Enter OTP</h1>
                <div class="error-mesage" id="errorMessage">
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
                        <input type="text" name="otp" id="otp" required>
                    </div>

                    <button type="submit" class="btn animation" style="--i:3;">Continue</button>
                    <div class="logreg-link animation" style="--i:3;">
                        <p><a href="#" class="register-link">Resend OTP</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>