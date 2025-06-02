<?php
$host = "localhost";
$user = "root";
$pass = "smartwatt@mysql123";
$dbname = "homewatt";

// Establish connection to MySQL server (no database selected yet)
$conn = new mysqli($host, $user, $pass);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the database exists, if not create it
$db_check_query = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($db_check_query) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Now select the database
$conn->select_db($dbname);

// If the database is successfully selected, proceed
?>
