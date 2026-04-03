<?php
require '../session.php';
header("Content-Type: application/json");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$user_id = $_SESSION['user_id'];

$conn = require '../conn.php';

try{

    $statement = $conn->prepare("SELECT 
    Products.product_name, 
    Orders.created_at, 
    Products.image, 
    Orders.total_price, 
    OrderItems.price_at_purchase, 
    OrderItems.quantity 
    FROM Users 
    INNER JOIN Orders ON Users.id = Orders.buyer_id
    INNER JOIN OrderItems ON Orders.id = OrderItems.order_id
    INNER JOIN Products ON OrderItems.product_id = Products.id
    WHERE Users.id = :user_id
    ");

}
catch (PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

?>