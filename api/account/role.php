<?php
require_once __DIR__ . "/../utils/cors.php";
require '../session.php';
header("Content-Type: application/json");
$config = require '../config.php';

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["redirect" => "/"]);
    exit();
}
$role = $_SESSION["role"];
$id = $_SESSION["user_id"];

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
$conn = require __DIR__. "/../conn.php";
$statement = $conn->prepare("SELECT address, phone, delivery_instructions, icon FROM Users WHERE id = :id");
$statement->execute(["id"=> $id]);

$result = $statement->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "role" => $role,
    "user" => $id,
    "address" => $result["address"],
    "phone" => $result["phone"],
    "icon" => $result["icon"],
    "orders" => $products
]);

}
catch (PDOException $e){
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);

}

