<?php
require_once __DIR__ . "/../utils/cors.php";
header("Content-Type: application/json");
require '../session.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$user_id = $_SESSION['user_id'];

$conn = require '../conn.php';

$products = [
    [
        "name" => "SHY Vol. 8",
        "price" => 280,
        "image" => "/images/SHYVol8.webp",
        "delivered" => "13 March 2026",
        "id" => 1,
        "placed" => "11 March 2026"
    ],
    [
        "name" => "Love Bullet Vol. 2",
        "price" => 310,
        "image" => "/images/37c5f1ca-d930-432c-9e4e-0c632f954b85.png",
        "delivered" => "cancelled",
        "id" => 2,
        "placed" => "14 March 2026"
    ]
];

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
    $statement->execute(["user_id"=>$user_id]);

    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    $nested = [];
    foreach ($results as $row) {
        $nested[] = array_values($row);
    }
    http_response_code(201);
    echo json_encode(["orders"=> $nested, "dummy" => $products]);
}
catch (PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

?>