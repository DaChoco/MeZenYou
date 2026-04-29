<?php
require_once __DIR__ . "/./utils/cors.php";
require 'session.php';
header('Content-Type: application/json');
// Example: Dummy Output
$id = $_GET['id'] ?? null;
$conn = require 'conn.php';

$user = "";
$buyerID = "";
if (!isset($_SESSION['email'])){
    $user = "GUEST";
}
else {
    $user = $_SESSION['email'];
}

if (!isset($_SESSION['user_id'])){
    $buyerID = null;
}
else{
    $buyerID = $_SESSION['user_id'];
}


try{
    $stmt = $conn->prepare("SELECT 
    Products.id, Users.id as userID, product_name, author, price, category, descriptiontxt, location, stock, image, username 
    FROM Products INNER JOIN Users ON Products.seller_id = Users.id WHERE Products.id = :id");
    $stmt->execute([":id"=>$id]);
    

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT city FROM Users WHERE id = :id");
    $stmt->execute([":id"=>$id]);
    $city = $stmt->fetch(PDO::FETCH_ASSOC)['city'];

    http_response_code(200);
    echo json_encode(["product"=> $result, "user"=> ["city"=> $city, "email"=>$user]]);
}
catch (PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);

}
catch (Exception $e){
    http_response_code(500);
    echo json_encode(["error" => "INTERNAL SERVER ERROR" .$e->getMessage()]);

}

?>