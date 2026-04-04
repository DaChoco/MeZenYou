<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header('Content-Type: application/json');

// Filters
$category = isset($_GET['category']) && $_GET['category'] !== '' ? (string)$_GET['category']: null;
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (int)$_GET['min'] : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (int)$_GET['max'] : null;

//THE REAL RESULTS
$conn = require __DIR__ . "/../conn.php";

try {
    $filters = [];
    $whereClauses = [];

    if ($min !== null) {
        $whereClauses[] = "price >= :minprice";
        $filters["minprice"] = $min;
    }

    if ($max !== null) {
        $whereClauses[] = "price <= :maxprice";
        $filters["maxprice"] = $max;
    }

    if ($category !== null) {
        $whereClauses[] = "category = :category";
        $filters["category"] = $category;
    }

    $whereSQL = "";
    if (count($whereClauses)> 0){
        $whereSQL  = "WHERE ".implode(" AND ", $whereClauses);
    }
    $stmt = $conn->prepare("SELECT * FROM Products $whereSQL ORDER BY id LIMIT 10 OFFSET 0");
    $stmt->execute($filters);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

http_response_code(200);
echo json_encode([
    "products" => $results,
    "user" => $_SESSION['email'] ?? null

]);
