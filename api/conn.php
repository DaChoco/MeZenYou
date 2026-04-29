<?php
$config = require 'config.php';
ini_set('display_errors', 0);
error_reporting(E_ALL);
try {
    $conn = new PDO(
        "mysql:host={$config['db']['DB_HOST']};dbname={$config['db']['DB_NAME']};charset=utf8mb4",
        $config["db"]['DB_USER'],
        $config["db"]['DB_PASS']
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); 
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $conn;

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>