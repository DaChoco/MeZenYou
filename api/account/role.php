<?php
require_once __DIR__ . "/../utils/cors.php";
header("Content-Type: application/json");
require '../session.php';
$config = require '../config.php';

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["redirect" => "/"]);
 exit();
}
$role = $_SESSION["role"];
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

echo json_encode([
    "role" => $role,
    "orders" => $products
]);