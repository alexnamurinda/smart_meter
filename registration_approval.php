<?php
session_start();
include 'databaseconnection.php';

// // Ensure only admin can access
// if (!isset($_SESSION['admin']) || $_SESSION['admin']['authenticated'] !== true) {
//     header("Location: admin_login.php?error=unauthorized");
//     exit();
// }

// Handle approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $clientId = $_POST['client_id'];
    $action = $_POST['action']; // 'approve' or 'reject'

    if ($action === 'approve') {
        $updateStmt = $conn->prepare("UPDATE clients SET registration_status = 'approved' WHERE client_id = ?");
    } else {
        $updateStmt = $conn->prepare("UPDATE clients SET registration_status = 'rejected' WHERE client_id = ?");
    }

    $updateStmt->execute([$clientId]);
    header("Location: admindashboard.php");
    // header("Location: admindashboard.php?success=1");
    exit();
}

// Fetch pending requests
$stmt = $conn->query("SELECT * FROM clients WHERE registration_status = 'under review'");
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Approval</title>
    <link rel="stylesheet" href="admnstyling.css">
</head>
<body>
    <h2>Pending Residence Approvals</h2>
    <?php if (!empty($pendingRequests)): ?>
        <table border="1">
            <tr>
                <th>Client ID</th>
                <th>Phone Number</th>
                <th>Apartment</th>
                <th>Room</th>
                <th>Action</th>
            </tr>
            <?php foreach ($pendingRequests as $request): ?>
                <tr>
                    <td><?= htmlspecialchars($request['client_id']) ?></td>
                    <td><?= htmlspecialchars($request['phone_number']) ?></td>
                    <td><?= htmlspecialchars($request['apartment_id']) ?></td>
                    <td><?= htmlspecialchars($request['room_id']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="client_id" value="<?= $request['client_id'] ?>">
                            <button type="submit" name="action" value="approve">Approve</button>
                            <button type="submit" name="action" value="reject" style="color:red;">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No pending requests.</p>
    <?php endif; ?>
</body>
</html>
