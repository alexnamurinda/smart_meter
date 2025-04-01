<?php
// Start session and include the database connection
session_start();
include 'db.php';

// Fetch feedbacks from the database
$query = "SELECT client_name, client_email, feedback_subject, feedback_message, submitted_on FROM feedbacks ORDER BY submitted_on DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User feedbacks</title>
    <link rel="stylesheet" href="admnstyling.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 0px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            margin-top: -10px;
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            /* border-collapse: collapse; */
        }

        table th,
        table td {
            padding: 10px 15px;
            text-align: left;
            border: 1px solid #ccc;
        }

        table th {
            background-color: #8b8b8b;
            color: #fff;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .empty-message {
            text-align: center;
            font-size: 18px;
            color: #555;
            margin: 20px 0;
        }

        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 76%;
        }

        button:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            button {
                padding: 5px 10px;
                font-size: 10px;
                margin-left: 75%;
                margin-top: -10px;
            }

            button:hover {
                background-color: #2980b9;
            }

            table th,
            table td {
                padding: 5px;
                font-size: 13px;
            }

            .table-container {
                margin-bottom: 10px;
            }

            .empty-message {
                font-size: 16px;
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
            <!-- Feedback Table -->
            <?php if (!empty($feedbacks)): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Submitted On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($feedback['client_name']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($feedback['client_email']); ?>">
                                            <?php echo htmlspecialchars($feedback['client_email']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($feedback['feedback_subject']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['feedback_message']); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($feedback['submitted_on'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-message">No feedbacks found.</div>
            <?php endif; ?>
        </div>

    </div>

    <footer class="admin-footer">
        <p>&copy; 2024 kooza technologies. All Rights Reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
    </footer>

</body>

</html>