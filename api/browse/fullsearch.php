<?php
require_once __DIR__ . "/../utils/cors.php";
header('Content-Type: application/json');

$query = isset($_GET['q']) && $_GET['q'] !== '' ? (string)$_GET['q']: null;

if (!isset($_GET['q']) || $_GET['q'] === '') {
    http_response_code(400);
    echo json_encode(["error" => "Query parameter 'q' is required"]);
    exit;
}


try{
    $conn = require __DIR__ . "/../conn.php";

    $whereSQLsearch = "product_name LIKE :pname OR author LIKE :author OR category LIKE :category";
    $stmt = $conn->prepare("SELECT id, product_name, image, price, category, location FROM Products WHERE $whereSQLsearch ORDER BY id LIMIT 10 OFFSET 0");
    $stmt->execute(["pname"=> "%$query%", "author"=> "%$query%", "category"=> "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "products" => $results,
    ]);

}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    
}

