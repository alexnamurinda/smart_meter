<?php
$hostname = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_DATABASE') ?: 'kooza_db';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'Alex@mysql123';
$port = getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

// Connect without specifying a database initially
$dsn = "mysql:host=$hostname;port=$port;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}