<?php
// Include error handler first
include 'error_handler.php';
include 'databaseconnection.php';
session_start();

try {
    // First check if the table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'clients'");
    if ($tableCheck->rowCount() == 0) {
        trigger_error("The 'clients' table does not exist in the database", E_USER_ERROR);
    }

    // Fetch employees from the employee table
    $clientQuery = "SELECT * FROM clients";
    $clientStmt = $conn->prepare($clientQuery);
    $clientStmt->execute();

    // Fetch unique apartment IDs for the filter dropdown
    $apartmentQuery = "SELECT DISTINCT apartment_id FROM clients";
    $apartmentStmt = $conn->prepare($apartmentQuery);
    $apartmentStmt->execute();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle AJAX request to delete client
        $userId = $_POST['id'];
        $userType = $_POST['client_category'];

        if ($userType === 'tenant') {
            $deleteQuery = "DELETE FROM clients WHERE client_id = :id";
        }

        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':id', $userId, PDO::PARAM_INT);

        if ($deleteStmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        exit;
    }
} catch (PDOException $e) {
    // Handle database errors
    if (getenv('ENVIRONMENT') === 'production') {
        trigger_error("Database error occurred", E_USER_ERROR);
    } else {
        trigger_error("Database error: " . $e->getMessage(), E_USER_ERROR);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Clients' List</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #eef2f7;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            text-decoration: underline;
            font-size: 2rem;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .action-bar select,
        .action-bar input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .action-bar button {
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .action-bar button:hover {
            background-color: #2980b9;
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: solid 2px #2c3e50;

        }

        .user-table th,
        .user-table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .user-table th {
            background-color: #3b536b;
            color: #fff;
        }

        .user-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .user-table tr:hover {
            background-color: #eaf2f8;
        }

        .delete-user {
            color: #e74c3c;
            cursor: pointer;
        }

        .delete-user:hover {
            color: #c0392b;
        }

        .edit-user {
            text-decoration: none;
            text-align: center;
        }

        @media (max-width: 768px) {

            h2 {
                margin: 10px 10px;
                font-size: 22px;
            }

            .action-bar {
                display: flex;
                justify-content: space-around;
                margin-bottom: 10px;
            }

            .action-bar select,
            .action-bar input {
                font-size: 13px;
                width: 40%;
            }

            .action-bar button {
                padding: 5px 10px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <h2>REGISTERED CLIENTS' LIST</h2>

    <!-- Action Bar -->
    <div class="action-bar">
        <div>
            <select id="apartmentFilter">
                <option value="">All Apartments</option>
                <?php while ($apartment = $apartmentStmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo htmlspecialchars($apartment['apartment_id']); ?>">
                        <?php echo htmlspecialchars($apartment['apartment_id']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="text" id="searchInput" placeholder="Search by name or phone">
        </div>
        <button onclick="window.location.href='admindashboard.php'">Back to Dashboard</button>
    </div>

    <!-- Users Table -->
    <div class="table-container">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Client ID</th>
                    <th>Client Name</th>
                    <th>Category</th>
                    <th>Phone Number</th>
                    <th>Apartment</th>
                    <th>Room</th>
                    <th>Date of Registration</th>
                    <th>Last Login</th>
                    <th>Action</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php while ($client = $clientStmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="user-row" data-id="<?php echo $client['client_id']; ?>" data-apartment="<?php echo $client['apartment_id']; ?>">
                        <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                        <td><?php echo htmlspecialchars($client['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($client['client_category']); ?></td>
                        <td><?php echo htmlspecialchars($client['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($client['apartment_id']); ?></td>
                        <td><?php echo htmlspecialchars($client['room_id']); ?></td>
                        <td><?php echo htmlspecialchars($client['registered_on']); ?></td>
                        <td><?php echo htmlspecialchars($client['last_login']); ?></td>
                        <td><a href="edit_client_details.php?id=<?php echo $client['client_id']; ?>" class="edit-user">Edit</a></td>
                        <td><span class="delete-user">Delete</span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Search and Filter Functionality
        const searchInput = document.getElementById('searchInput');
        const apartmentFilter = document.getElementById('apartmentFilter');
        const rows = document.querySelectorAll('.user-row');

        function filterRows() {
            const searchValue = searchInput.value.toLowerCase();
            const apartmentValue = apartmentFilter.value;

            rows.forEach(row => {
                const name = row.children[1].textContent.toLowerCase();
                const phone = row.children[3].textContent.toLowerCase();
                const apartment = row.getAttribute('data-apartment');

                const matchesSearch = name.includes(searchValue) || phone.includes(searchValue);
                const matchesApartment = apartmentValue === '' || apartment === apartmentValue;

                row.style.display = matchesSearch && matchesApartment ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterRows);
        apartmentFilter.addEventListener('change', filterRows);

        // Delete Functionality
        document.querySelectorAll('.delete-user').forEach(deleteBtn => {
            deleteBtn.addEventListener('click', function() {
                const row = deleteBtn.closest('tr');
                const userId = row.getAttribute('data-id');

                if (confirm('Are you sure you want to delete this user?')) {
                    fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `id=${userId}&client_category=tenant`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) row.remove();
                            else alert('Failed to delete user.');
                        });
                }
            });
        });
    </script>

</body>

</html>

<?php $conn = null; ?>