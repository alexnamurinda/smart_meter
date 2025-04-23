<?php
$hostname = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_DATABASE') ?: 'homewatt';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'Alex@mysql123';
$port = getenv('DB_PORT') ?: '3306';

// Connect to MySQL server without selecting the database
$conn = new mysqli($hostname, $username, $password, '', $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database if it doesn't exist
$db_check_query = "CREATE DATABASE IF NOT EXISTS `$db_name`";
if (!$conn->query($db_check_query)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
if (!$conn->select_db($db_name)) {
    die("Error selecting database: " . $conn->error);
}
