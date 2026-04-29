<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');
$conn = require __DIR__ . "/../conn.php";
#THIS ROUTE BRINGS YOUR DATA FOR SIGNED IN USERS

$userID = $_SESSION['user_id'];
if (!$userID) {
    http_response_code(403);
    echo json_encode(["error" => "USER NOT SIGNED IN"]);
    exit;
}
try{
    $sql = "SELECT email, address, phone FROM Users WHERE id = :id";

    $statement = $conn->prepare($sql);
    $statement->execute([":id" => $userID]);

    $results = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$results) {
        http_response_code(200);
        echo json_encode(["message" => "user has no data", "success" => false]);
        exit;
    }
    

    echo json_encode(["message" => "User has a cart", "success" => true, "username" => $_SESSION["username"],
    "email" => $results['email'],
    "address" => $results['address'],
    "phone" => $results['phone']]);
}
catch (PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}