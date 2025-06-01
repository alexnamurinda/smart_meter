<?php
include 'db_connection.php'; // This ensures the database is created before continuing

// Create tables (rooms, clients, and loads)
$createRoomsTable = "
CREATE TABLE IF NOT EXISTS rooms (
    room_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
 
";

$createClientsTable = "
CREATE TABLE IF NOT EXISTS clients (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    room_id VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE SET NULL
) ENGINE=InnoDB;
";

$createLoadsTable = "
CREATE TABLE IF NOT EXISTS loads (
    load_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id VARCHAR(50),
    load_name VARCHAR(50),
    power_status ENUM('ON', 'OFF') DEFAULT 'OFF',
    voltage DECIMAL(10,2) DEFAULT 0.00,
    current DECIMAL(10,2) DEFAULT 0.00,
    power DECIMAL(10,2) DEFAULT 0.00,
    energy_consumed DECIMAL(10,2) DEFAULT 0.00,
    previous_status ENUM('ON', 'OFF') DEFAULT NULL,
    changed_by VARCHAR(50) DEFAULT 'system',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

$createFeedbacksTable = "
CREATE TABLE IF NOT EXISTS feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

// Execute table creation queries
$conn->query($createRoomsTable) or die("Error creating rooms: " . $conn->error);
$conn->query($createClientsTable) or die("Error creating clients: " . $conn->error);
$conn->query($createLoadsTable) or die("Error creating loads: " . $conn->error);
$conn->query($createFeedbacksTable) or die("Error creating feedbacks: " . $conn->error);
?>
