<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');
$conn = require __DIR__ . "/../conn.php";

$userID = $_SESSION['user_id'];
if (!$userID) {
    http_response_code(403);
    echo json_encode(["error" => "USER NOT SIGNED IN"]);
    exit;
}
try{
    $sql = 
"SELECT 
    Carts.id AS cart_id, 
    CartItems.id AS cart_item_id, 
    CartItems.quantity, 
    Carts.user_id,
    Products.id AS product_id, 
    Products.product_name, 
    Products.image, 
    Products.price, 
    (Products.price * CartItems.quantity) AS totalprice
FROM Carts 
INNER JOIN CartItems 
    ON Carts.id = CartItems.cart_id
INNER JOIN Products 
    ON CartItems.product_id = Products.id
WHERE Carts.user_id = :id";

    $statement = $conn->prepare($sql);
    $statement->execute([":id" => $userID]);

    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(200);
        echo json_encode(["message" => "user has no cart", "success" => false, "cart" => []]);
        exit;
    }
    

    echo json_encode(["message" => "User has a cart", "success" => true, "cart" => $result]);
}
catch (PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}