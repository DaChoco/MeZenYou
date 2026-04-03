<?php
require_once "../session.php";
header('Content-Type: application/json');

// Dummy data 
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

// Filters
$category = $_GET['category'] ?? null;
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (int)$_GET['min'] : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (int)$_GET['max'] : null;

$filtered = array_values(array_filter($products, function ($p) use ($category, $min, $max) {

    if ($category) {
        $cat = strtolower(str_replace(['/', ' '], '', $p['category']));
        if ($cat !== strtolower($category)) return false;
    }

    if ($min !== null && $p['price'] < $min) return false;
    if ($max !== null && $p['price'] > $max) return false;

    return true;
}));


http_response_code(201);
echo json_encode([
    "products" => $filtered,
    "user" => $_SESSION['email'] ?? null
]);