<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ALLOWED = ["ADMIN", "MODERATOR"];

if (!in_array($_SESSION['role'], $ALLOWED)) {
    http_response_code(403);
    echo json_encode(["error" => "Method not allowed", 'redirect' => '/']);
    exit;

}

$page = isset($_GET['pg']) && $_GET['pg'] !== '' ? (string) $_GET['pg'] : null;

if ($page === null || $page < 1) {
    $page = 0;
} else {
    --$page;
}


$offset = max(0, (int) $page) * 10;

try {
    $conn = require __DIR__ . "/../conn.php";

    $statement = $conn->prepare("SELECT Orders.id AS id, buyer.username AS buyer, GROUP_CONCAT(DISTINCT seller.username) AS seller, Orders.total_price, Orders.created_at AS date, payment, order_status AS status FROM Orders
    INNER JOIN OrderItems 
        ON OrderItems.order_id = Orders.id
    INNER JOIN Users AS seller 
        ON seller.id = OrderItems.seller_id
    INNER JOIN Users AS buyer 
        ON buyer.id = Orders.buyer_id
    GROUP BY Orders.id LIMIT 10 OFFSET $offset");
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $conn->prepare("SELECT COUNT(DISTINCT Orders.id) AS total_rows FROM Orders  
    INNER JOIN OrderItems  
        ON OrderItems.order_id = Orders.id;");
    $countStmt->execute();

    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['total_rows'];

    $limit = 10;
    $totalPages = ceil($totalRows / $limit);

    echo json_encode(["status" => true, "orders" => $results, "totalpages" => $totalPages, "rows" => $totalRows]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR: DB500"]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("INTERNAL SERVER ERROR: " . $e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);

}