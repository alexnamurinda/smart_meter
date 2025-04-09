<?php
require 'databaseconnection.php';

function fetchData($query, $params = [])
{
    global $conn;
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchSingleData($query, $params = [])
{
    global $conn;
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function sendJsonResponse($data)
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'fetch_apartments') {
        $apartments = fetchData("SELECT * FROM apartments");
        sendJsonResponse($apartments);
    }

    if ($action === 'fetch_rooms' && isset($_GET['apartment_id'])) {
        $apartmentId = $_GET['apartment_id'];
        $rooms = fetchData("SELECT * FROM rooms WHERE apartment_id = ?", [$apartmentId]);
        sendJsonResponse($rooms);
    }

    if ($action === 'fetch_room_details' && isset($_GET['room_id'])) {
        $roomId = $_GET['room_id'];
        $room = fetchSingleData("
            SELECT r.*, e.energy_consumed, e.remaining_units 
            FROM rooms r
            LEFT JOIN room_energy e ON r.room_id = e.room_id
            WHERE r.room_id = :room_id
        ", ['room_id' => $roomId]);

        if ($room) {
            sendJsonResponse([
                'room_id' => $room['room_id'],
                'name' => $room['name'],
                'energy_consumed' => $room['energy_consumed'] ?? 0,
                'remaining_units' => $room['remaining_units'] ?? 0,
                'power_status' => $room['power_status']
            ]);
        } else {
            http_response_code(404);
            sendJsonResponse(['error' => 'No data found for this room']);
        }
    }

    // Fetch the power status for ESP32
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    if ($action === 'get_power_status' && isset($_GET['room_id'])) {
        $roomId = $_GET['room_id'];
        $row = fetchSingleData("SELECT power_status FROM rooms WHERE room_id = :room_id", ['room_id' => $roomId]);

        if ($row) {
            sendJsonResponse(['room_id' => $roomId, 'power_status' => $row['power_status']]);
        } else {
            http_response_code(404);
            sendJsonResponse(['error' => 'Room not found']);
        }
    }

    // New endpoint for updating power status
    if ($action === 'update_power_status' && isset($_POST['room_id']) && isset($_POST['power_status'])) {
        $roomId = $_POST['room_id'];
        $powerStatus = $_POST['power_status'];

        try {
            $updateStmt = $conn->prepare("UPDATE rooms SET power_status = :power_status WHERE room_id = :room_id");
            $result = $updateStmt->execute([
                'power_status' => $powerStatus,
                'room_id' => $roomId
            ]);

            sendJsonResponse(['success' => $result, 'status' => $powerStatus]);
        } catch (Exception $e) {
            http_response_code(500);
            sendJsonResponse(['error' => 'Failed to update power status']);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Overview</title>
    <link rel="stylesheet" href="admnstyling.css">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <style>
        form#filterForm {
            display: flex;
            gap: 20px;
            max-width: 60%;
            margin: 20px auto;
            padding: 20px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
            justify-content: space-around;
            align-items: center;
        }

        form#filterForm label {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            display: none;
        }

        form#filterForm select {
            width: 100%;
            padding: 10px;
            border: 0px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background-color: #fff;
            color: #333;
            outline: none;
            transition: border-color 0.3s;
        }

        form#filterForm select:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        form#filterForm select option {
            padding: 10px;
            background-color: #fff;
            color: #333;
        }

        form#filterForm select option:hover {
            background-color: #007BFF;
            color: #fff;
        }


        form#filterForm button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form#filterForm button:hover {
            background-color: #0056b3;
        }


        #roomWidgets {
            display: none;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
            max-width: 90%;
            margin-left: 5%;
        }

        .widget {
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            text-align: center;
        }

        .widget h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 20px;
        }

        /* Power Toggle Switch Styling */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 120px;
            height: 50px;
            margin: 10px 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 40px;
            width: 40px;
            left: 5px;
            bottom: 5px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: #2196F3;
        }

        input:focus+.toggle-slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(70px);
        }

        .toggle-status {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
            margin-bottom: 70px;
        }

        .btn {
            text-decoration: none;
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            form#filterForm {
                display: flex;
                gap: 10px;
                max-width: 100%;
                margin: 10px auto;
                padding: 20px 10px;
            }

            form#filterForm label {
                font-size: 12px;
            }

            form#filterForm select {
                font-size: 12px;
            }

            form#filterForm button {
                padding: 5px 10px;
                font-size: 14px;
            }

            #roomWidgets {
                max-width: 100%;
                margin-left: 0;
            }

            .toggle-slider {
                cursor: zoom-out;
            }
        }
    </style>

</head>

<body>
    <div class="admin-dashboard">
        <header class="admin-header">
            <h1>Admin Dashboard - SMART_METER_PROJECT</h1>
            <p>Centralized Control for Smarter Energy Solutions and User Accounts.</p>
        </header>

        <form id="filterForm">
            <div>
                <label for="apartment">Select Apartment:</label>
                <select id="apartment" name="apartment" onchange="fetchRooms(this.value)">
                    <option value="">Select Apartment</option>
                </select>
            </div>
            <div>
                <label for="room">Select Room:</label>
                <select id="room" name="room" onchange="fetchRoomDetails(this.value)">
                    <option value="">Select Room</option>
                </select>
            </div>
            <div class="button-container">
                <button type="button" onclick="window.location.href='admindashboard.php'">Back</button>
            </div>
        </form>


        <div id="roomWidgets">
            <div class="widget">
                <h3>Units consumed</h3>
                <div id="energyConsumptionGauge"></div>
            </div>
            <div class="widget">
                <h3>Remaining Units</h3>
                <div id="remainingUnitsGauge"></div>
            </div>
            <div class="widget">
                <h3>Room Power Control</h3>
                <label class="toggle-switch">
                    <input type="checkbox" id="powerToggle">
                    <span class="toggle-slider"></span>
                </label>
                <div class="toggle-status" id="powerStatus">Status: OFF</div>
                <a href="#" id="viewDetailsLink" class="btn">View Details</a>
            </div>
        </div>
    </div>

    <script>
        // Fetch apartments on page load
        fetch('overview.php?action=fetch_apartments')
            .then(response => response.json())
            .then(data => {
                const apartmentSelect = document.getElementById('apartment');
                data.forEach(apartment => {
                    apartmentSelect.innerHTML += `<option value="${apartment.apartment_id}">${apartment.name}</option>`;
                });
            });

        // Fetch rooms based on selected apartment
        function fetchRooms(apartmentId) {
            const roomSelect = document.getElementById('room');
            roomSelect.innerHTML = '<option value="">Select Room</option>';
            document.getElementById('roomWidgets').style.display = 'none';

            if (!apartmentId) return;

            fetch(`overview.php?action=fetch_rooms&apartment_id=${apartmentId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(room => {
                        roomSelect.innerHTML += `<option value="${room.room_id}">${room.name}</option>`;
                    });
                });
        }

        // Fetch room details based on selected room
        function fetchRoomDetails(roomId) {
            const roomWidgets = document.getElementById('roomWidgets');
            roomWidgets.style.display = 'none';

            if (!roomId) return;

            fetch(`overview.php?action=fetch_room_details&room_id=${roomId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('No room data found');
                    }
                    return response.json();
                })
                .then(room => {
                    // Show widgets
                    roomWidgets.style.display = 'grid';

                    // Create Energy Consumption Gauge
                    Plotly.newPlot('energyConsumptionGauge', [{
                        type: 'indicator',
                        mode: 'gauge+number',
                        value: room.energy_consumed,
                        number: {
                            font: {
                                size: 35
                            },
                            suffix: "<span style='font-size:14px;'>kWh</span>",
                            valueformat: ".2f"
                        },
                        gauge: {
                            axis: {
                                range: [0, 150],
                                tickfont: {
                                    size: 15
                                }
                            },
                            steps: [{
                                    range: [0, 50],
                                    color: "green"
                                },
                                {
                                    range: [50, 100],
                                    color: "green"
                                },
                                {
                                    range: [100, 150],
                                    color: "green"
                                }
                            ],
                            bar: {
                                color: "#f6921e",
                                thickness: 0.5
                            }
                        }
                    }], {
                        responsive: true,
                        height: 220,
                        margin: {
                            t: 20,
                            b: 10,
                            l: 25,
                            r: 45
                        }
                    });

                    // Create Remaining Units Gauge
                    Plotly.newPlot('remainingUnitsGauge', [{
                        type: 'indicator',
                        mode: 'gauge+number',
                        value: room.remaining_units,

                        number: {
                            font: {
                                size: 35
                            },
                            suffix: "<span style='font-size:14px;'>kWh</span>",
                            valueformat: ".2f"
                        },
                        gauge: {
                            axis: {
                                range: [0, 150],
                                tickfont: {
                                    size: 15
                                }
                            },
                            steps: [{
                                    range: [0, 50],
                                    color: "green"
                                },
                                {
                                    range: [50, 100],
                                    color: "green"
                                },
                                {
                                    range: [100, 150],
                                    color: "green"
                                }
                            ],
                            bar: {
                                color: "#f6921e",
                                thickness: 0.5
                            }
                        }
                    }], {
                        responsive: true,
                        height: 220,
                        margin: {
                            t: 20,
                            b: 10,
                            l: 25,
                            r: 45
                        }
                    });

                    // Setup power toggle
                    setupPowerToggle(room);

                    // Update view details link
                    const viewDetailsLink = document.getElementById('viewDetailsLink');
                    viewDetailsLink.href = `view_details.php?room_id=${room.room_id}`;
                })
                .catch(error => {
                    console.error('Error:', error);
                    roomWidgets.style.display = 'none';
                });
        }

        // Power Toggle Setup
        function setupPowerToggle(room) {
            const powerToggle = document.getElementById('powerToggle');
            const powerStatus = document.getElementById('powerStatus');

            // Set initial state
            powerToggle.checked = room.power_status === 'ON';
            powerStatus.textContent = `Status: ${room.power_status}`;

            // Remove previous event listeners
            const oldToggle = powerToggle.cloneNode(true);
            powerToggle.parentNode.replaceChild(oldToggle, powerToggle);

            // Add new event listener
            oldToggle.addEventListener('change', (e) => {
                const newStatus = e.target.checked ? 'ON' : 'OFF';

                fetch('overview.php?action=update_power_status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `room_id=${room.room_id}&power_status=${newStatus}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        powerStatus.textContent = `Status: ${newStatus}`;
                    })
                    .catch(error => {
                        console.error('Error updating power status:', error);
                        // Revert toggle if update fails
                        oldToggle.checked = !e.target.checked;
                    });
            });
        }
    </script>
</body>

</html>