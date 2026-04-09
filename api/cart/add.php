<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');
$conn = require __DIR__ . "/../conn.php";

$productID = $_GET['pid'];
$userID = $_SESSION['user_id'];
$qty = (int) $_GET['qty'];

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

    $sql = "
    INSERT INTO CartItems (cart_id, product_id, quantity)
    VALUES (:cart, :product, :q)
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
    ";

    $conn->prepare($sql)->execute(["cart" => $cartID, "product" => $productID, "q" => $qty]);

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
        "cart_id" => $cartID
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "error: " . $e->getMessage()]);
} finally {
    exit;
}
