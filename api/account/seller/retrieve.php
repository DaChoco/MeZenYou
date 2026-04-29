<?php
require_once __DIR__ . "/../../utils/cors.php";
require '../../session.php';
header("Content-Type: application/json");

$conn = require __DIR__ .'/../../conn.php';

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Filters

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    error_log(print_r($_SESSION, true));
    echo json_encode(["redirect" => "/"]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, product_name, image, price, category, is_active, location FROM Products WHERE id = :id");
    $stmt->execute([':id' => $_SESSION["user_id"]]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    http_response_code(200);
    echo json_encode(["products" => $results, "user" => $_SESSION['username'] ?? null]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB ERROR500" ]);
    exit;
}


