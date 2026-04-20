<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try{
    $conn = require __DIR__ . "/../conn.php";

    $statement = $conn->prepare("SELECT province , Count(*) as clients FROM users GROUP BY Province");
    $statement->execute();
    $provinces = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $conn->prepare("SELECT category, SUM(price_at_purchase) AS total_sales, Count(*) as clients FROM orderitems
    INNER JOIN products ON orderitems.product_id = products.id GROUP BY category");
    $statement->execute();
    $categories = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => true, "data_province" => $provinces, "data_category" => $categories]);

}
catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "Database error: "]);
} catch (Exception $e){
    http_response_code(500);
    error_log("INTERNAL SERVER ERROR: " . $e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);

}
