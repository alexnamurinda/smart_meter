<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['client_id']) || !isset($_SESSION['room_id'])) {
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['client_id'];
$room_id = $_SESSION['room_id'];

try {
    // Get client info
    $clientQuery = "SELECT name FROM clients WHERE client_id = ?";
    $clientStmt = $conn->prepare($clientQuery);
    $clientStmt->bind_param("i", $client_id);
    $clientStmt->execute();
    $clientResult = $clientStmt->get_result();
    $client = $clientResult->fetch_assoc();

    // Get room info
    $roomQuery = "SELECT * FROM rooms WHERE room_id = ?";
    $roomStmt = $conn->prepare($roomQuery);
    $roomStmt->bind_param("s", $room_id);
    $roomStmt->execute();
    $roomResult = $roomStmt->get_result();
    $room = $roomResult->fetch_assoc();

    // Get loads for this room
    $loadsQuery = "SELECT * FROM loads WHERE room_id = ?";
    $loadsStmt = $conn->prepare($loadsQuery);
    $loadsStmt->bind_param("s", $room_id);
    $loadsStmt->execute();
    $loads = $loadsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calculate totals and stats
    $totalEnergy = 0;
    $voltageCount = 0;
    $averageVoltage = 0;
    $activeLoads = 0;
    $totalLoads = count($loads);
    $totalPower = 0;

    foreach ($loads as $load) {
        $totalEnergy += $load['energy_consumed'];
        $totalPower += $load['power'];

        if (isset($load['voltage']) && is_numeric($load['voltage']) && $load['voltage'] > 0) {
            $averageVoltage += $load['voltage'];
            $voltageCount++;
        }

        if ($load['power_status'] == 'ON') {
            $activeLoads++;
        }
    }

    // Calculate average voltage based on available readings
    $averageVoltage = ($voltageCount > 0) ? $averageVoltage / $voltageCount : 0;

    // Format for display
    $totalEnergy = number_format($totalEnergy, 2);
    $averageVoltage = number_format($averageVoltage, 2);
    $totalPower = number_format($totalPower, 2);
} catch (Exception $e) {
    die("ERROR: Could not fetch data. " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard-Smart Homewatt</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/plotly.js/2.24.2/plotly.min.js"></script>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>

<body>
    <div class="container">
        <header>
            <div class="dashboard-title">
                <a class="navbar-brand" href="#">
                    <img src="images/userlog.png" alt="Smart HomeWatt Logo" style="width: 55px; height: 50px;">
                    Smart HomeWatt
                </a>
            </div>
            <div class="top-controls">
                <span class="last-updated">Last updated: <span id="updateTime"></span></span>
                <button class="btn btn-outline-secondary" id="refreshBtn">
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2" />
                    </svg>
                    Refresh -->
                </button>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </header>

        <!-- Room Information -->
        <div class="room-info">
            <h2 style="text-align: center; font-weight: bold;">Welcome, <?php echo htmlspecialchars(ucwords(strtolower(strtok($client['name'], ' ')))); ?> !</h2>
            <div class="room-details">
                <div class="room-detail-item">
                    <div class="detail-label">ROOM ID</div>
                    <div class="detail-value"><?php echo htmlspecialchars($room['room_id']); ?></div>
                </div>
                <div class="room-detail-item">
                    <div class="detail-label">ROOM NAME</div>
                    <div class="detail-value"><?php echo htmlspecialchars($room['name']); ?></div>
                </div>
                <div class="room-detail-item">
                    <div class="detail-label">LOCATION</div>
                    <div class="detail-value"><?php echo htmlspecialchars($room['location']); ?></div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="summary-stats">
            <div class="stat-card">
                <h3>TOTAL ENERGY CONSUMPTION</h3>
                <span class="value"><?php echo $totalEnergy; ?></span><span class="unit">kWh</span>
            </div>
            <div class="stat-card">
                <h3>VOLTAGE</h3>
                <span class="value"><?php echo $averageVoltage; ?></span><span class="unit">V</span>
            </div>
            <div class="stat-card">
                <h3>TOTAL POWER</h3>
                <span class="value"><?php echo $totalPower; ?></span><span class="unit">W</span>
            </div>
            <div class="stat-card">
                <h3>ACTIVE LOADS</h3>
                <span class="value"><?php echo $activeLoads; ?></span><span class="unit">/ <?php echo $totalLoads; ?></span>
            </div>
        </div>

        <!-- Loads Grid -->
        <div class="loads-grid">
            <?php foreach ($loads as $load): ?>
                <div class="load-card">
                    <div class="load-header">
                        <div class="load-name"><?php echo htmlspecialchars($load['load_name']); ?></div>
                        <div class="load-status <?php echo $load['power_status'] == 'ON' ? 'status-on' : 'status-off'; ?>">
                            <?php echo $load['power_status'] == 'ON' ? 'ACTIVE' : 'INACTIVE'; ?>
                        </div>
                    </div>
                    <div class="load-body">
                        <div class="metrics-grid">
                            <div class="metric-box">
                                <h4>ENERGY CONSUMED</h4>
                                <span class="metric-value"><?php echo number_format($load['energy_consumed'], 2); ?></span>
                                <span class="metric-unit">kWh</span>
                            </div>
                            <div class="metric-box">
                                <h4>CURRENT</h4>
                                <span class="metric-value"><?php echo number_format($load['current'], 2); ?></span>
                                <span class="metric-unit">A</span>
                            </div>
                            <div class="metric-box">
                                <h4>POWER</h4>
                                <span class="metric-value"><?php echo number_format($load['power'], 2); ?></span>
                                <span class="metric-unit">W</span>
                            </div>
                            <div class="metric-box">
                                <?php
                                // Determine alert message per load
                                $energyAlert = '';
                                $energyLevel = floatval($load['power']);

                                if ($energyLevel < 10) {
                                    $energyAlert = "<div class='alert alert-success mt-1'>✅ Power saving.</div>";
                                } elseif ($energyLevel < 30) {
                                    $energyAlert = "<div class='alert alert-info mt-1'>ℹ️ Moderate usage. Looks normal.</div>";
                                } else {
                                    $energyAlert = "<div class='alert alert-warning mt-1'>⚠️ High usage on this load.</div>";
                                }

                                // Output per load here
                                echo $energyAlert;
                                ?>
                            </div>
                        </div>
                        <div class="gauge-grid">
                            <div class="gauge-container" id="powerGauge<?php echo $load['load_id']; ?>"></div>
                        </div>
                        <div class="controls">
                            <div class="power-switch">
                                <label>Power Control:</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="powerToggle<?php echo $load['load_id']; ?>"
                                        <?php echo $load['power_status'] == 'ON' ? 'checked' : ''; ?>
                                        onchange="togglePower(<?php echo $load['load_id']; ?>, this.checked)">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <button class="details-btn" id="viewDetails<?php echo $load['load_id']; ?>">View Details</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Update the timestamp
        function updateTime() {
            const now = new Date();
            const options = {
                weekday: "long",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: true
            };
            document.getElementById("updateTime").textContent = `Today, ${now.toLocaleTimeString('en-US', options)}`;
        }

        updateTime();

        // Pass PHP array of loads with energy consumption to JavaScript
        const loadsData = <?php echo json_encode($loads); ?>;

        // Global variables
        let refreshInterval;
        const REFRESH_INTERVAL = 3000; // 3 seconds for faster updates

        // Initialize power gauges for each load
        function initGauges() {
            loadsData.forEach(load => {
                let loadId = load.load_id;
                let energyConsumed = parseFloat(load.energy_consumed) || 0;

                // Set gauge value
                let gaugeValue = energyConsumed;

                // Create the gauge
                Plotly.newPlot(`powerGauge${loadId}`, [{
                    type: 'indicator',
                    mode: 'gauge+number',
                    value: gaugeValue,
                    title: {
                        text: `Energy Consumption`,
                        font: {
                            size: 14,
                            color: "#000"
                        }
                    },
                    number: {
                        font: {
                            size: 25,
                            color: "#000",
                        },
                        suffix: ' kWh',
                        valueformat: '.2f'
                    },
                    gauge: {
                        axis: {
                            range: [0, 100], // Adjust based on your typical consumption values
                            tickwidth: 1,
                            tickcolor: "#ddd"
                        },
                        bar: {
                            color: "#F5F5DC",
                            thickness: 0.4
                        },
                        bgcolor: "#fff",
                        borderwidth: 2,
                        bordercolor: "#888",
                        steps: [{
                                range: [0, 30],
                                color: "#27ae60"
                            }, // Low consumption
                            {
                                range: [30, 70],
                                color: "#f1c40f"
                            }, // Medium consumption
                            {
                                range: [70, 100],
                                color: "#c0392b"
                            } // High consumption
                        ]
                    }
                }], {
                    margin: {
                        t: 20,
                        b: 10,
                        l: 10,
                        r: 10
                    },
                    height: 160,
                    // Add config to disable plotly toolbar and interactions for cleaner UI
                    config: {
                        displayModeBar: false,
                        responsive: true
                    }
                });
            });
        }

        // Function to toggle power state
        function togglePower(loadId, isChecked) {
            const powerStatus = isChecked ? "ON" : "OFF";

            // Send AJAX request to update the database
            fetch('update_load_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `load_id=${loadId}&power_status=${powerStatus}`
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    // Update UI to reflect the change without page reload
                    const statusElement = document.querySelector(`#powerToggle${loadId}`).closest('.load-card').querySelector('.load-status');
                    statusElement.textContent = isChecked ? 'ACTIVE' : 'INACTIVE';
                    statusElement.className = `load-status ${isChecked ? 'status-on' : 'status-off'}`;

                    // Refresh data after toggling to get updated metrics
                    setTimeout(fetchDashboardData, 500);
                })
                .catch(error => console.error('Error:', error));
        }

        // Setup view details buttons
        function setupViewDetailsButtons() {
            document.querySelectorAll('.details-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const loadId = this.id.replace('viewDetails', '');

                    // Fetch latest data before showing details
                    fetch('get_load_details.php?load_id=' + loadId)
                        .then(response => response.json())
                        .then(load => {
                            if (load) {
                                // Create a modal dialog with load details
                                alert(`
                            Load Details:
                            Name: ${load.load_name}
                            Status: ${load.power_status}
                            Voltage: ${load.voltage} V
                            Current: ${load.current} A
                            Power: ${load.power} W
                            Energy Consumed: ${load.energy_consumed} kWh
                            Last Changed By: ${load.changed_by}
                            Last Updated: ${new Date(load.updated_at).toLocaleString()}
                        `);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching load details:', error);
                            // Fallback to using local data if fetch fails
                            const load = loadsData.find(l => l.load_id == loadId);
                            if (load) {
                                alert(`
                            Load Details (Cached):
                            Name: ${load.load_name}
                            Status: ${load.power_status}
                            Voltage: ${load.voltage} V
                            Current: ${load.current} A
                            Power: ${load.power} W
                            Energy Consumed: ${load.energy_consumed} kWh
                            Last Changed By: ${load.changed_by}
                            Last Updated: ${new Date(load.updated_at).toLocaleString()}
                        `);
                            }
                        });
                });
            });
        }

        // Function to fetch fresh data via AJAX
        function fetchDashboardData() {
            // Show subtle loading indicator (optional)
            const refreshBtn = document.getElementById("refreshBtn");
            refreshBtn.classList.add('btn-loading');

            fetch('get_dashboard_data.php')
                .then(response => response.json())
                .then(data => {
                    // Update local data cache
                    window.loadsData = data.loads;

                    // Update summary stats
                    document.querySelector('.summary-stats .stat-card:nth-child(1) .value').textContent = data.totalEnergy;
                    document.querySelector('.summary-stats .stat-card:nth-child(2) .value').textContent = data.averageVoltage;
                    document.querySelector('.summary-stats .stat-card:nth-child(3) .value').textContent = data.totalPower;
                    document.querySelector('.summary-stats .stat-card:nth-child(4) .value').textContent = data.activeLoads;

                    // Update individual loads
                    data.loads.forEach(load => {
                        // Find the card for this load using better selector
                        const loadCard = document.querySelector(`.load-card:has([id="powerToggle${load.load_id}"]), .load-card:has([id^="powerToggle${load.load_id}"])`);
                        if (!loadCard) return;

                        // Update status
                        const statusElement = loadCard.querySelector('.load-status');
                        statusElement.textContent = load.power_status === 'ON' ? 'ACTIVE' : 'INACTIVE';
                        statusElement.className = `load-status ${load.power_status === 'ON' ? 'status-on' : 'status-off'}`;

                        // Update metrics
                        const metricBoxes = loadCard.querySelectorAll('.metric-box');
                        if (metricBoxes.length >= 3) {
                            metricBoxes[0].querySelector('.metric-value').textContent = parseFloat(load.energy_consumed).toFixed(2);
                            metricBoxes[1].querySelector('.metric-value').textContent = parseFloat(load.current).toFixed(2);
                            metricBoxes[2].querySelector('.metric-value').textContent = parseFloat(load.power).toFixed(2);
                        }

                        // Update alert message
                        const alertBox = loadCard.querySelector('.metric-box:nth-child(4)');
                        if (alertBox) {
                            const energyLevel = parseFloat(load.power);
                            let energyAlert = '';
                            const loadId = load.load_id; // Make sure each load has a unique ID from your DB
                            const alertKey = `sms_alert_sent_${loadId}`;

                            if (energyLevel < 10) {
                                energyAlert = "<div class='alert alert-success mt-1'>✅ Power saving.</div>";
                                localStorage.removeItem(alertKey); // Reset flag when usage is low
                            } else if (energyLevel < 30) {
                                energyAlert = "<div class='alert alert-info mt-1'>ℹ️ Moderate usage. Looks normal.</div>";
                                localStorage.removeItem(alertKey); // Reset flag when usage is normal
                            } else {
                                energyAlert = "<div class='alert alert-warning mt-1'>⚠️ High usage on this load.</div>";

                                // Only send SMS if alert hasn't been sent yet for this load
                                if (!localStorage.getItem(alertKey)) {
                                    // Send SMS alert
                                    fetch('send_sms.php', {
                                            method: 'POST'
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            console.log('SMS Alert:', data);
                                            // Mark alert as sent
                                            localStorage.setItem(alertKey, 'true');
                                        })
                                        .catch(error => {
                                            console.error('Error sending SMS:', error);
                                        });
                                }
                            }

                            alertBox.innerHTML = energyAlert;
                        }


                        // Update gauge with smoother animation
                        const gaugeElement = document.getElementById(`powerGauge${load.load_id}`);
                        if (gaugeElement) {
                            Plotly.animate(`powerGauge${load.load_id}`, {
                                data: [{
                                    value: parseFloat(load.energy_consumed)
                                }],
                                traces: [0],
                                layout: {}
                            }, {
                                transition: {
                                    duration: 800,
                                    easing: 'cubic-in-out'
                                },
                                frame: {
                                    duration: 800,
                                    redraw: false
                                }
                            });
                        }

                        // Update checkbox without triggering change event
                        const checkbox = document.getElementById(`powerToggle${load.load_id}`);
                        if (checkbox && checkbox.checked !== (load.power_status === 'ON')) {
                            checkbox.checked = load.power_status === 'ON';
                        }
                    });

                    // Update timestamp
                    updateTime();

                    // Remove loading indicator
                    refreshBtn.classList.remove('btn-loading');
                })
                .catch(error => {
                    console.error('Error fetching dashboard data:', error);
                    // Remove loading indicator even on error
                    refreshBtn.classList.remove('btn-loading');
                });
        }

        // Start auto-refresh
        function startAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            refreshInterval = setInterval(fetchDashboardData, REFRESH_INTERVAL);
            console.log("Auto-refresh started at interval: " + REFRESH_INTERVAL + "ms");
        }

        // Stop auto-refresh
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
                console.log("Auto-refresh stopped");
            }
        }

        // Initialize all components when page loads
        window.addEventListener('load', function() {
            // Add CSS for the loading indicator
            const style = document.createElement('style');
            style.textContent = `
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.8;
        }
        .btn-loading:after {
            content: '';
            display: inline-block;
            width: 1em;
            height: 1em;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.6s linear infinite;
            margin-left: 0.5em;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
            document.head.appendChild(style);

            // Initialize components
            initGauges();
            setupViewDetailsButtons();

            // Start auto-refresh after a small delay to ensure page is fully loaded
            setTimeout(function() {
                fetchDashboardData(); // Initial data fetch
                startAutoRefresh(); // Start periodic refresh
            }, 1000);

            // Stop refresh when page is not visible to save resources
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    startAutoRefresh();
                    fetchDashboardData(); // Immediately fetch fresh data when becoming visible
                } else {
                    stopAutoRefresh();
                }
            });
        });

        // Update the refresh button to manually trigger data refresh without page reload
        document.getElementById("refreshBtn").addEventListener("click", function(e) {
            e.preventDefault();
            fetchDashboardData();
        });
    </script>
</body>

</html>