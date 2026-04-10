<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');
$conn = require __DIR__ . "/../conn.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    error_log("PDO ERROR: WRONG REQUEST");
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//VALIDATION STAGE----------------------------------------------------
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    error_log("missing fields ");
    echo json_encode(["error" => "Missing required fields", "success"=> false]);
    exit;
}

$productID = $data['pid'];
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not authenticated"]);
    exit;
}
$userID = $_SESSION['user_id'];
$qty = (int) $data['qty'];

if (!$productID || !$userID || $qty <= 0) {
    http_response_code(403);
    echo json_encode(["error" => "MISSING ONE OR MORE OF THE REQUIRED FIELDS"]);
    exit;
}
try {
    //DOES AVAILABLE STOCK ALLOW THIS?
    $statement = $conn->prepare("SELECT id, stock FROM products WHERE id = :id");
    $statement->execute(["id" => $productID]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(["error" => "PRODUCT NOT FOUND"]);
        exit;
    }
    $stockavailable = $result['stock'];

    if ($stockavailable < $qty) {
        http_response_code(401);
        echo json_encode(["error" => "STOCK EXCEEDS THE AMOUNT OF QUANTITY AVAILABLE"]);
        exit;
    }
    //DOES THE USER EVEN HAVE A CART
    $statement = $conn->prepare("SELECT id FROM Carts WHERE user_id = :userid");
    $statement->execute(["userid" => $userID]);
    $cart = $statement->fetch(PDO::FETCH_ASSOC);


    //INSERT CART OR EXTRACT THE CART ID
    if (!$cart) {
        $conn->prepare("INSERT INTO Carts (user_id) VALUES (:userid)")->execute(["userid" => $userID]);
        $cartID = $conn->lastInsertId();
    } else {
        $cartID = $cart['id'];
    }

    $checkStatement = $conn->prepare("
    SELECT id, quantity 
    FROM CartItems 
    WHERE cart_id = :cart AND product_id = :product
    ");
    $checkStatement->execute([
    "cart" => $cartID,
    "product" => $productID
    ]);

    $existingItem = $checkStatement->fetch(PDO::FETCH_ASSOC);

    $sql = "INSERT INTO CartItems (cart_id, product_id, quantity)
    VALUES (:cart, :product, :q) ON DUPLICATE KEY UPDATE quantity = quantity + :q
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(["cart" => $cartID, "product" => $productID, "q" => $qty]);

    $affected = $stmt->rowCount();

    if ($affected === 0) {
        $check = $conn->prepare("
        SELECT id FROM CartItems 
        WHERE cart_id = :cart AND product_id = :product
    ");
        $check->execute([
            "cart" => $cartID,
            "product" => $productID
        ]);

        if (!$check->fetch()) {
            throw new Exception("Insert failed unexpectedly");
        }
    }

    echo json_encode([
        "message" => "Item added to cart",
        "cart_id" => $cartID,
        "success"=> true
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "error: " . $e->getMessage(), "success"=>false]);
} finally {
    exit;
}
