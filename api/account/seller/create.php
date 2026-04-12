<?php
require_once __DIR__ . "/../../utils/cors.php";
require '../../session.php';
header("Content-Type: application/json");
require __DIR__ ."/../../utils/aws.php";
require __DIR__ ."/../../utils/AWSCLIENTS.php";
$conn = require '../../conn.php';
$ACCESS = require __DIR__ ."/../../config.php";
$seller_id = $_SESSION['user_id'];

$product_name = $_POST['product_name'] ?? null;
$price = $_POST['price'] ?? null;
$category = $_POST['category'] ?? null;
$location = $_POST['location'] ?? null;
$author = $_POST['author'] ?? null;
$stock = $_POST['stock'] ?? null;

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(["error" => "Image is required"]);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== 0) {
    http_response_code(400);
    echo json_encode(["error" => "File upload error"]);
    exit;
}

$allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];
$realType = mime_content_type($file['tmp_name']);

if (!in_array($realType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid file type"]);
    exit;
}

if (!$product_name || !$price || !$category || !$location || !$seller_id || !$author || !$stock ){
    http_response_code(400);
    error_log("JSON ERROR: USER DIDNT SELECT PRODUCT");
    echo json_encode(["error" => "EMPTY FIELDS"]);
    exit;
}

try {
    $conn->beginTransaction();
    $s3 = createS3Client($ACCESS);
    $aws = new AWSservice($s3, null);

    $stmt = $conn->prepare("INSERT INTO Products 
    (product_name, price, category, location, seller_id, author, stock)
    VALUES 
    (:name, :price, :category, :location, :seller_id, :author, :stock)
");

    $stmt->execute([
        "name" => $product_name,
        "price" => $price,
        "category" => $category,
        "location" => $location,
        "seller_id" => $seller_id,
        "author" => $author,
        "stock" => $stock
    ]);
    $product_id = (int) $conn->lastInsertId();
    $image_url = $aws->uploadProductImage($product_id, $file['tmp_name'], $file['name']);

    if ($image_url === ""){
        throw new Exception("SOMETHING HAS GONE WRONG WITH AWS CLASS");
    }

    $stmt = $conn->prepare("UPDATE Products SET image = :image WHERE product_id = :id");

    $stmt->execute([
        "image" => $image_url,
        "id" => $product_id
    ]);
    $conn->commit();

    http_response_code(201);
    echo json_encode([
        "product_id" => $product_id,
        "status" => true
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage(), "status"=> false]);
}
finally{
    exit;
}

?>