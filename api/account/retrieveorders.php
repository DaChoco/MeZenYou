<?php
require_once __DIR__ . "/../utils/cors.php";
header("Content-Type: application/json");
require '../session.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$user_id = $_SESSION['user_id'];

$conn = require '../conn.php';

try{

    $statement = $conn->prepare("SELECT 
    Products.product_name AS name, 
    Orders.created_at, 
    Products.image,
    Orders.order_status, 
    Orders.total_price, 
    OrderItems.price_at_purchase AS price, 
    OrderItems.quantity,
    Orders.id 
    FROM Users 
    INNER JOIN Orders ON Users.id = Orders.buyer_id
    INNER JOIN OrderItems ON Orders.id = OrderItems.order_id
    INNER JOIN Products ON OrderItems.product_id = Products.id
    WHERE Users.id = :user_id LIMIT 5
    ");
    $statement->execute(["user_id"=>$user_id]);

    $results = $statement->fetchAll(PDO::FETCH_ASSOC);


    http_response_code(201);
    echo json_encode(["orders"=> $results, "id" => $user_id]);
}
catch (PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

?>