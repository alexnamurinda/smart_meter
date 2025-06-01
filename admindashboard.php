<?php
include 'db_connection.php';

// Fetch all rooms to display for managing
$roomsQuery = "SELECT * FROM rooms";
$roomsResult = $conn->query($roomsQuery);

// Fetch all clients with their room info
$clientsQuery = "SELECT c.client_id, c.name, c.phone_number, r.name AS room_name, r.room_id, c.address FROM clients c LEFT JOIN rooms r ON c.room_id = r.room_id";
$clientsResult = $conn->query($clientsQuery);

// Fetch all feedbacks
$feedbacksQuery = "SELECT * FROM feedbacks ORDER BY submitted_at DESC";
$feedbacksResult = $conn->query($feedbacksQuery);


// Handle Room Creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_room'])) {
    $room_id = $_POST['room_id'];  // Get the room ID from the form
    $room_name = $_POST['room_name'];
    $description = $_POST['description'];
    $location = $_POST['location'];

    // Check if the room ID already exists in the rooms table
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id = ?");
    $stmt->bind_param("s", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If room ID exists, show an error message
        echo "<script>alert('Error: Room ID already exists!');</script>";
    } else {
        // Create the room
        $stmt = $conn->prepare("INSERT INTO rooms (room_id, name, description, location) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $room_id, $room_name, $description, $location);
        if ($stmt->execute()) {
            // Insert 3 loads with default values
            $loads = ['Load 1', 'Load 2', 'Load 3'];
            foreach ($loads as $load_name) {
                $stmt = $conn->prepare("INSERT INTO loads (room_id, load_name, power_status, voltage, current, power, energy_consumed) VALUES (?, ?, 'OFF', 0.00, 0.00, 0.00, 0.00)");
                $stmt->bind_param("ss", $room_id, $load_name);
                $stmt->execute();
            }
            echo "<script>alert('Room created successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    }
}
?>

<!-- Admin Dashboard -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        .tab-content {
            margin-top: 30px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Smart Homewatt - Admin Dashboard</h2>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#createRoom">Create Room</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#viewClients">View Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#viewFeedbacks">View Feedbacks</a>
            </li>
            <!-- <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#manageRooms">Manage Rooms</a>
        </li> -->
        </ul>

        <div class="tab-content">
            <!-- Create Room Tab -->
            <div id="createRoom" class="container tab-pane active">
                <h3>Create a New Room</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Room ID</label>
                        <input type="text" class="form-control" name="room_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="room_name" class="form-label">Room Name</label>
                        <input type="text" class="form-control" name="room_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Room Description</label>
                        <textarea class="form-control" name="description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" required>
                    </div>
                    <button type="submit" name="create_room" class="btn btn-primary">Create Room</button>
                </form>
            </div>

            <!-- View Clients Tab -->
            <div id="viewClients" class="container tab-pane fade">
                <h3>Registered Clients</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Room</th>
                            <th>Address</th>
                            <!-- <th>Action</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $clientsResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['phone_number']; ?></td>
                                <td><?php echo $row['room_name']; ?></td>
                                <td><?php echo $row['address']; ?></td>
                                <!-- <td> -->
                                <!-- Turn ON/OFF Room -->
                                <!-- <a href="toggle_room_status.php?room_id=<?php echo $row['room_id']; ?>&status=ON" class="btn btn-success">Turn ON</a>
                                <a href="toggle_room_status.php?room_id=<?php echo $row['room_id']; ?>&status=OFF" class="btn btn-danger">Turn OFF</a> -->
                                <!-- </td> -->
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- View Feedbacks Tab -->
            <div id="viewFeedbacks" class="container tab-pane fade">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fb = $feedbacksResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fb['name']); ?></td>
                                <td><?php echo htmlspecialchars($fb['email']); ?></td>
                                <td><?php echo htmlspecialchars($fb['subject']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($fb['message'])); ?></td>
                                <td><?php echo $fb['submitted_at']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>


            <!-- Manage Rooms Tab -->
            <div id="manageRooms" class="container tab-pane fade">
                <h3>Manage Rooms</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Room Name</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($room = $roomsResult->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $room['name']; ?></td>
                                <td><?php echo $room['location']; ?></td>
                                <td>
                                    <a href="view_room_loads.php?room_id=<?php echo $room['room_id']; ?>" class="btn btn-info">Manage Loads</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>