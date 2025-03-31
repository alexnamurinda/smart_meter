<!-- Backend logic -->
<?php
include 'db.php'; //database connection.
include 'databasecreation.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['phone_number']); // Used for both phone numbers and admin names
    $password = $_POST['password'];

    // Database connection
    try {
        $conn = new PDO("mysql:host=localhost;dbname=kooza_db", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    // Check if the username is one of the predefined admin names
    if (in_array($username, ['admin1', 'admin2', 'admin3'])) {
        // Admin authentication
        $query = "SELECT * FROM admin WHERE admin_name = :username LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['admin_password'])) {
            // Clear any user session before logging in as admin
            unset($_SESSION['user']);

            // Set admin session
            $_SESSION['admin'] = [
                'name' => $admin['admin_fullname'],
                'admin_name' => $admin['admin_name'],
                'authenticated' => true
            ];

            // Update last login time
            $updateLoginTime = $conn->prepare("UPDATE admin SET last_login = CURRENT_TIMESTAMP WHERE admin_name = :username");
            $updateLoginTime->bindParam(':username', $username);
            $updateLoginTime->execute();

            // Redirect to admin dashboard
            header("Location: admindashboard.php");
            exit();
        } else {
            $error_message = "Invalid Admin Credentials.";
        }
    } else {
        // Normalize phone number for clients
        if (strpos($username, '0') === 0) {
            $username = '+256' . substr($username, 1);
        }

        // Client authentication
        $query = "SELECT * FROM clients WHERE phone_number = :username LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['client_password'])) {
            // Clear any admin session before logging in as user
            unset($_SESSION['admin']);

            // Set user session
            $_SESSION['user'] = [
                'name' => $user['client_name'],
                'phone_number' => $user['phone_number'],
                'category' => $user['client_category'],
                'authenticated' => true
            ];

            // Update last login time for client
            $updateLoginTime = $conn->prepare("UPDATE clients SET last_login = CURRENT_TIMESTAMP WHERE phone_number = :username");
            $updateLoginTime->bindParam(':username', $username);
            $updateLoginTime->execute();

            // Redirect to user dashboard
            header("Location: userdashboard.php");
            exit();
        } else {
            $error_message = "Invalid Phone Number or Password.";
        }
    }
}


function normalizePhoneNumber($phone_number)
{
    $phone_number = preg_replace('/\D/', '', $phone_number); // Remove non-numeric characters
    if (substr($phone_number, 0, 1) === '0') {
        $phone_number = '+256' . substr($phone_number, 1); // Replace leading '0' with '+256'
    } elseif (substr($phone_number, 0, 4) !== '+256') {
        $phone_number = '+256' . $phone_number; // Ensure it starts with '+256'
    }
    return $phone_number;
}

// Registration logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // Form data
    $username = $_POST['name'];
    $phone_number = $_POST['phone_number'];
    $category = $_POST['category'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password

    // List of admin usernames
    $adminUsers = ['admin1', 'admin2', 'admin3'];

    if (in_array($phone_number, $adminUsers)) {
        // Check if admin already exists
        $query = "SELECT * FROM admin WHERE admin_name = :admin_name";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':admin_name', $phone_number);
        $stmt->execute();
        $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingAdmin) {
            $error_message = "Credentials not allowed!";
        } else {
            // Insert admin details into the admin table
            $insertQuery = "INSERT INTO admin (admin_name, admin_fullname, admin_password, last_login) 
                            VALUES (:admin_name, :name, :admin_password, NOW())";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bindParam(':admin_name', $phone_number);
            $insertStmt->bindParam(':name', $username); // Insert full name into admin_fullname
            $insertStmt->bindParam(':admin_password', $hashedPassword);
            $insertStmt->execute();

            // Redirect admin to login page
            header("Location: getstarted.php");
            exit();
        }
    } else {
        // Normalize phone number to Uganda format (+256)
        $phone_number = normalizePhoneNumber($phone_number);

        // Check if the phone number already exists
        $query = "SELECT * FROM clients WHERE phone_number = :phone_number";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $error_message = "This phone number is already registered!";
        } else {
            // Temporarily store registration data
            $_SESSION['registration_data'] = [
                'name' => $username,
                'phone_number' => $phone_number,
                'category' => $category,
                'password' => $hashedPassword
            ];

            // OTP generation and storage
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;

            $apiUsername = 'agritech_info';
            $apiKey = 'atsk_d30afdc12c16b290766e27594e298b4c82fa0ca3d87f723f7a2576aa9a6d0b9d096fa012';
            $apiUrl = 'https://api.africastalking.com/version1/messaging';

            // Prepare the message
            $message = "Your OTP code is: $otp";

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
            curl_close($ch);

            // Decode the response
            $responseDecoded = json_decode($response, true);

            if (isset($responseDecoded['SMSMessageData']['Recipients']) && count($responseDecoded['SMSMessageData']['Recipients']) > 0) {
                // Redirect to OTP verification page
                header("Location: OTP_verification.php");
                exit();
            } else {
                header("Location: OTP_verification.php");
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login|sign_up</title>
    <link rel="stylesheet" href="getstarted.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body>
    <div class="wrapper">
        <span class="bg-animate"></span>
        <span class="bg-animate2"></span>

        <!--login option-->
        <div class="reg_box">
            <div class="info-text login">
                <img src="images/logo.png" class="animation" style="--i:0;">
                <h2 class="animation" style="--i:0">Welcome Back!<br /></h2>
                <p class="animation" style="--i:1">Kooza Smart_meter</p>
            </div>

            <div class="form-box login">
                <h2 class="animation" style="--i:0;">Log in to your account</h2>

                <div class="error-message" id="errorMessage">
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

                <form id="userForm" action="#" method="post" class="sig" enctype="multipart/form-data">

                    <div class="input-box animation" style="--i:1;">
                        <input type="tel" name="phone_number" required>
                        <label for="phone_number">Phone_number:</label>
                        <i class="bx bxs-phone"></i>
                    </div>

                    <div class="input-box animation" style="--i:1; position: relative;">
                        <input type="password" name="password" id="login_password" required>
                        <label for="login_password">Password:</label>
                        <i class="bx bx-show" id="toggleLoginPassword" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 20px;"></i>
                    </div>

                    <button type="submit" name="login" value="login" class="btn animation" style="--i:2;">Login</button>

                    <div class="logreg-link animation" style="--i:2;">
                        <a href="password_reset_request.php" class="Forgot">Forgot Password?</a><br /><br>
                        <p>Don't have an account? <a href="#" class="register-link">Register</a></p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sign-up option -->
        <div class="form-box register">
            <h2 class="animation" style="--i:3;">Register new account</h2>
            <form action="#" method="POST">
                <div class="colum">

                    <div class="input-box animation" style="--i:4;">
                        <input type="text" name="name" required>
                        <label for="name">Full Name:</label>
                        <i class="bx bxs-user"></i>
                    </div>

                    <div class="input-box animation" style="--i:4;">
                        <input type="text" name="phone_number" id="phone_number" required>
                        <label for="phone_number">Phone Number:</label>
                        <i class="bx bxs-phone"></i>
                        <small id="phone_error" style="color: red; display: none;">Format: 0 or +256</small>
                    </div>


                    <input type="hidden" name="category" value="tenant">

                    <div class="input-box animation" style="--i:5; position: relative;">
                        <input type="password" name="password" id="signup_password" required style="border-bottom: 2px solid #f2f2f2; position: relative;">
                        <label for="signup_password">Password:</label>
                        <i class="bx bx-show" id="toggleSignupPassword" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 20px;"></i>
                        <small id="password_error" style="color: red; display: none;"></small>
                    </div>

                    <div class="input-box animation" style="--i:6; position: relative;">
                        <input type="password" name="password" id="confirm_password" required style="border-bottom: 2px solid #f2f2f2; position: relative;">
                        <label for="confirm_password">Confirm Password:</label>
                        <i class="bx bx-show" id="toggleConfirmPassword" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 20px;"></i>
                        <small id="confirm_password_error" style="color: red; display: none;"></small>
                    </div>

                    <button type="submit" name="signup" value="sign up" class="btn animation" style="--i:7;">Register</button>
                    <div class="logreg-link animation" style="--i:8;">
                        <p>Already have an account? <a href="#" class="login-link">Login</a></p>
                    </div>
                </div>
            </form>
        </div>

        <div class="info-text register">
            <img src="images/logo.png" class="animation" style="--i:4;">
            <h2 class="animation" style="--i:4;">Welcome Back!<br /></h2>
            <p class="animation" style="--i:4;">Kooza Smart_meter</p>
        </div>
    </div>

    <!-- register_login switch animation -->
    <script>
        const wrapper = document.querySelector('.wrapper');
        const registerLink = document.querySelector('.register-link');
        const loginLink = document.querySelector('.login-link');

        registerLink.onclick = () => {
            document.querySelector('.form-box.login form').reset();
            wrapper.classList.add('active');
        };

        loginLink.onclick = () => {
            document.querySelector('.form-box.register form').reset();
            wrapper.classList.remove('active');
        };
    </script>
    <script>
        // Phone number validation
        const phoneInput = document.getElementById('phone_number');
        const phoneError = document.getElementById('phone_error');

        phoneInput.addEventListener('input', function() {
            const value = phoneInput.value;
            if (value === 'admin' || value.startsWith('0') || value.startsWith('+256')) {
                phoneError.style.display = 'none';
                phoneInput.style.borderColor = '';
            } else {
                phoneError.style.display = 'block';
                phoneInput.style.borderColor = 'red';
            }
        });

        // Category validation
        const categoryInput = document.getElementById('category');
        const categoryError = document.getElementById('category_error');

        categoryInput.addEventListener('input', function() {
            const value = categoryInput.value.toLowerCase();
            const validCategories = ['tenant', 'landlord', 'owner'];

            if (value === 'admin' || validCategories.includes(value)) {
                categoryError.style.display = 'none';
                categoryInput.style.borderColor = '';
            } else {
                categoryError.style.display = 'block';
                categoryInput.style.borderColor = 'red';
            }
        });
    </script>

    <script>
        const signupPasswordInput = document.getElementById('signup_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const signupPasswordError = document.getElementById('password_error');
        const confirmPasswordError = document.getElementById('confirm_password_error');

        // Login password visibility toggle
        const toggleLoginPassword = document.getElementById('toggleLoginPassword');
        const loginPasswordInput = document.getElementById('login_password');
        toggleLoginPassword.addEventListener('click', () => {
            const type = loginPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            loginPasswordInput.setAttribute('type', type);
            toggleLoginPassword.classList.toggle('bx-show');
            toggleLoginPassword.classList.toggle('bx-hide');
        });

        // Sign-up password visibility toggle
        const toggleSignupPassword = document.getElementById('toggleSignupPassword');
        toggleSignupPassword.addEventListener('click', () => {
            const type = signupPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            signupPasswordInput.setAttribute('type', type);
            toggleSignupPassword.classList.toggle('bx-show');
            toggleSignupPassword.classList.toggle('bx-hide');
        });

        // Confirm sign-up password visibility toggle
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        toggleConfirmPassword.addEventListener('click', () => {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            toggleConfirmPassword.classList.toggle('bx-show');
            toggleConfirmPassword.classList.toggle('bx-hide');
        });

        // Password Validation for Sign-Up
        signupPasswordInput.addEventListener('input', function() {
            const value = signupPasswordInput.value;

            let strength = 0;
            if (value.length >= 5) strength++;
            if (/[a-z]/.test(value)) strength++;
            if (/[0-9]/.test(value)) strength++;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(value)) strength++;

            if (strength < 2) {
                signupPasswordInput.style.borderBottomColor = 'red';
                signupPasswordError.style.display = 'block';
                signupPasswordError.textContent = 'Password is too weak.';
            } else if (strength < 4) {
                signupPasswordInput.style.borderBottomColor = 'orange';
                signupPasswordError.style.display = 'block';
                signupPasswordError.textContent = 'Password strength is moderate.';
            } else {
                signupPasswordInput.style.borderBottomColor = 'green';
                signupPasswordError.style.display = 'none';
            }
        });

        // Confirm Password Validation for Sign-Up
        confirmPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value !== signupPasswordInput.value) {
                confirmPasswordInput.style.borderBottomColor = 'red';
                confirmPasswordError.style.display = 'block';
                confirmPasswordError.textContent = 'Passwords do not match.';
            } else {
                confirmPasswordInput.style.borderBottomColor = 'green';
                confirmPasswordError.style.display = 'none';
            }
        });
    </script>


</body>

</html>