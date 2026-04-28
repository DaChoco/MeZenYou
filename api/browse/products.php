<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header('Content-Type: application/json');

// Filters
$category = isset($_GET['category']) && $_GET['category'] !== '' ? (string)$_GET['category']: null;
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (int)$_GET['min'] : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (int)$_GET['max'] : null;
$page = isset($_GET['pg']) && $_GET['pg'] !== '' ? (string)$_GET['pg']: null;

//THE REAL RESULTS
$conn = require __DIR__ . "/../conn.php";

try {
    $filters = [];
    $whereClauses = [];

    $whereClauses[] = "is_active = TRUE";

    if ($min !== null) {
        $whereClauses[] = "price >= :minprice";
        $filters[":minprice"] = $min;
    }

    if ($max !== null) {
        $whereClauses[] = "price <= :maxprice";
        $filters[":maxprice"] = $max;
    }

    if ($category !== null) {
        $whereClauses[] = "category = :category";
        $filters[":category"] = $category;
    }

    if ($page === null || $page < 1){
        $page = 0;
    }
    else{
        --$page;
    }

    $page = max(0, (int)$page);
    $offset = $page * 10;

    $whereSQL = "";
    if (count($whereClauses)> 0){
        $whereSQL  = "WHERE ".implode(" AND ", $whereClauses);
    }

    $stmt = $conn->prepare("SELECT id, product_name, image, price, category, location FROM Products $whereSQL ORDER BY id LIMIT 10 OFFSET $offset");
    $stmt->execute($filters);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM Products 
    $whereSQL");
    $countStmt->execute($filters);

    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    $limit = 10;
    $totalPages = ceil($totalRows / $limit);

    http_response_code(200);
    echo json_encode([
    "products" => $results,
    "user" => $_SESSION['username'] ?? null,
    "totalpages"=> $totalPages]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}


