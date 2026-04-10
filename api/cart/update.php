<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";

header('Content-Type: application/json');

$conn = require __DIR__ . "/../conn.php";

// POST AND SUCH
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// AUTH AND SUCH
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

$userid = $_SESSION['user_id'];

$productid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
$targetQuantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : -1;

if ($productid <= 0 || $targetQuantity < 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

try {
    $conn->beginTransaction();

    // GET END USER CART
    $cartStmt = $conn->prepare("
        SELECT id FROM Carts WHERE user_id = :userid
    ");
    $cartStmt->execute(["userid" => $userid]);
    $cart = $cartStmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        throw new Exception("Cart not found");
    }

    $cartId = $cart['id'];

    $itemStmt = $conn->prepare("
        SELECT quantity FROM CartItems 
        WHERE cart_id = :cart AND product_id = :pid
        FOR UPDATE
    ");
    $itemStmt->execute([
        "cart" => $cartId,
        "pid" => $productid
    ]);

    $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        //IF THERES NO ITEM JUST END THIS.
        http_response_code(404);
        echo json_encode(["error" => "Item not found in cart"]);
        $conn->rollBack();
        exit;
    }
    
    $stockStmt = $conn->prepare("
        SELECT stock FROM Products WHERE id = :pid
    ");
    $stockStmt->execute(["pid" => $productid]);
    $product = $stockStmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Product not found");
    }
    $stock = (int)$product['stock'];

    $targetQuantity = min($targetQuantity, $stock);

    if ($targetQuantity === 0) {
        // DELETE
        $deleteStmt = $conn->prepare("
            DELETE FROM CartItems 
            WHERE cart_id = :cart AND product_id = :pid
        ");
        $deleteStmt->execute([
            "cart" => $cartId,
            "pid" => $productid
        ]);

    } else {
        $updateStmt = $conn->prepare("
            UPDATE CartItems 
            SET quantity = :quantity
            WHERE cart_id = :cart AND product_id = :pid
        ");
        $updateStmt->execute([
            "quantity" => $targetQuantity,
            "cart" => $cartId,
            "pid" => $productid
        ]);
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "product_id" => $productid,
        "quantity" => $targetQuantity
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(500);
    error_log("API ERROR: " . $e->getMessage());

    echo json_encode([
        "success" => false,
        "error" => "Internal server error"
    ]);
} finally {
    exit;
}