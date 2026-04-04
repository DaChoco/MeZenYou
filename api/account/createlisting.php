<?php
require_once __DIR__ . "../utils/cors.php";
require '../session.php';
header("Content-Type: application/json");

$conn = require '../conn.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$user_id = $_SESSION['user_id'];

$product_name = $data['product_name'];
$price = $data['price'];
$category = $data['category'];
$location = $data['location'];
$seller_id = $data['seller_id'];
$image = $data['image'];
$author = $data['author'];
$stock = $data['stock'];

try {
    $stmt = $conn->prepare("INSERT INTO Products 
    (product_name, price, category, location, seller_id, image, author, stock)
    VALUES 
    (:name, :price, :category, :location, :seller_id, :image, :author, :stock)
");

    $stmt->execute([
        "name" => $product_name,
        "price" => $price,
        "category" => $category,
        "location" => $location,
        "seller_id" => $seller_id,
        "image" => $image,
        "author" => $author,
        "stock" => $stock
    ]);

    $product_id = $conn->lastInsertId();

    http_response_code(201);
    echo json_encode(["product_id" => $product_id, "status" => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

?>