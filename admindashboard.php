<?php
include 'databaseconnection.php';
session_start();

if (
    !isset($_SESSION['admin']) ||
    !is_array($_SESSION['admin']) ||
    empty($_SESSION['admin']) ||
    !isset($_SESSION['admin']['name']) ||
    !isset($_SESSION['admin']['admin_name']) ||
    !isset($_SESSION['admin']['authenticated']) ||
    $_SESSION['admin']['authenticated'] !== true
) {

    // Clear session and redirect
    session_unset();
    session_destroy();
    header("Location: getstarted.php?error=unauthorized");
    exit();
}


// Set timeout for inactivity (e.g., 30 minutes)
$inactive = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    // Session has expired
    session_unset();
    session_destroy();
    header("Location: getstarted.php?error=session_expired");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Optional: Check if IP has changed (potential session hijacking)
if (!isset($_SESSION['ip_address'])) {
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
} elseif ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    // IP address has changed, possible session hijacking
    session_unset();
    session_destroy();
    header("Location: getstarted.php?error=security_violation");
    exit();
}

$admin_name = $_SESSION['admin']['name'];  // Admin's full name
$admin_username = $_SESSION['admin']['admin_name'];  // Admin's username
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admnstyling.css">
</head>

<body>
    <div class="admin-dashboard">
        <header class="admin-header">
            <h1>Admin Dashboard - SMART_METER_PROJECT</h1>
            <p>Welcome, <?php echo htmlspecialchars($admin_name . ' (' . $admin_username . ')'); ?>.</p>
        </header>


        <div class="admin-content">
            <div class="admin-section">
                <h2>User Management</h2>
                <a href="manage_users.php">Manage Users</a>
                <a href="add-client.php">Add new Client</a>
                <a href="user_feedbacks.php">Feedbacks</a>
                <a href="#">Transactions</a>
            </div>

            <div class="admin-section">
                <h2>Device Management</h2>
                <a href="overview3.php">Device Overview</a>
                <a href="assign_apartment.php">Assign Apartment / Room</a>
                <a href="registration_approval.php">Pending approvals</a>
                <!-- <a href="#">Add new device</a> -->

            </div>

            <div class="admin-section">
                <h2>Admin account summary</h2>
                <a href="#">Account Overview</a>
                <a href="#">Top up units</a>
            </div>

            <!-- <div class="admin-section">
            <h2>Leave Requests</h2>
            <a href="view_leave_requests.php">View Leave Requests</a>
            <a href="view_leave_requests.php">Approve/Reject Leave</a>
        </div>

        <div class="admin-section">
            <h2>Payroll Management</h2>
            <a href="generate_payslips.php" onclick="showAlert(); return false;">Generate Payslips</a>
        </div>

        <div class="admin-section">
            <h2>Company Resources</h2>
            <a href="manage_resources.php" onclick="showAlert(); return false;">Manage Resources</a>
        </div>

        <script>
            function showAlert() {
                alert("This section includes project future prospect plan. Thank you!");
            }
        </script> -->

        </div>
    </div>

    <footer class="admin-footer">
        <p>&copy; 2024 kooza technologies. All Rights Reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
    </footer>

</body>

</html>