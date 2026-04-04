<?php
require_once __DIR__ . "./utils/cors.php";
// Example: Dummy Output
$products = [
    [
        "name" => "SHY Vol. 8",
        "price" => 280,
        "category" => "Comics/Manga",
        "location" => "Cape Town",
        "image" => "/images/SHYVol8.webp",
        "rating" => "★★★★☆",
        "id" => 1
    ],
    [
        "name" => "Love Bullet Vol. 2",
        "price" => 310,
        "category" => "Comics/Manga",
        "location" => "Durban",
        "image" => "/images/37c5f1ca-d930-432c-9e4e-0c632f954b85.png",
        "rating" => "★★★★★",
        "id" => 2
    ],
    [
        "name" => "Iphone 17",
        "price" => 19999,
        "category" => "Electronics",
        "location" => "Cape Town",
        "image" => "https://m.media-amazon.com/images/I/61X5FknDWuL._AC_SL1500_.jpg",
        "rating" => "★★★★★",
        "id" => 3
    ]
];

$id = $_GET['id'] ?? null;

$selectedProduct = null;
for ($i = 0; $i < count($products); $i++) {
    if ($id == $products[$i]['id']) {
        $selectedProduct = $products[$i];
        break;
    }
}

if (!$selectedProduct) {
    echo "Product not found";
    exit;
}
http_response_code(201);
echo json_encode(["product"=> $selectedProduct]);
?>