<?php
require_once __DIR__ . "/./utils/cors.php";
require 'session.php';
// Example: Dummy Output
$id = $_GET['id'] ?? null;
$conn = require 'conn.php';

$user = "";
if (!isset($_SESSION['email'])){
    $user = "GUEST";
}
else {
    $user = $_SESSION['email'];
}


try{
    $stmt = $conn->prepare("SELECT 
    Products.id, Users.id as userID, product_name, author, price, category, descriptiontxt, location, stock, image, username 
    FROM Products INNER JOIN Users ON Products.seller_id = Users.id WHERE Products.id = :id");
    $stmt->execute(["id"=>$id]);

    

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["product"=> $result, "user"=> $user]);
}
catch (PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);

}

?>