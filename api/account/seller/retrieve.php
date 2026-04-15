<?php
require_once __DIR__ . "/../../utils/cors.php";
require '../../session.php';
header("Content-Type: application/json");

$conn = require __DIR__ .'/../../conn.php';

// Filters
$category = isset($_GET['category']) && $_GET['category'] !== '' ? (string)$_GET['category']: null;
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (int)$_GET['min'] : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (int)$_GET['max'] : null;
$page = isset($_GET['p']) && $_GET['p'] !== '' ? (string)$_GET['p']: null;

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["redirect" => "/"]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, product_name, image, price, category, is_active, location FROM Products WHERE id = :id");
    $stmt->execute(['id' => $_SESSION["user_id"]]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    return;
}

http_response_code(200);
echo json_encode([
    "products" => $results,
    "user" => $_SESSION['username'] ?? null

]);
