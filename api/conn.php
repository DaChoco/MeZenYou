<?php
$config = require '../config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
try {
    $conn = new PDO(
        "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4",
        $config['DB_USER'],
        $config['DB_PASS']
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $conn;

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>