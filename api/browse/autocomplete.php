<?php
require_once __DIR__ . "/../utils/cors.php";
header('Content-Type: application/json');

$conn = require __DIR__ . "/../conn.php";
$name = $_GET['query'] ?? null;

if (!isset($name) || strlen($name)<2){
    http_response_code(403);
    echo json_encode(["results" =>[]]);
}
    $statement = $conn->prepare("SELECT id, product_name FROM Products WHERE (product_name LIKE :product_name OR author LIKE :author) AND is_active = TRUE LIMIT 10");
    $statement->execute(["product_name" => "%$name%", "author" => "%$name%"]);
try{


    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["results" => $results, "message" => "successfully retrieved list"]);

}
catch (PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}