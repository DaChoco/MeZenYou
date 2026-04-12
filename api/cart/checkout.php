<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header('Content-Type: application/json');
$conn = require __DIR__ . "/../conn.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

//VALIDATION STAGE----------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['fullname']) || empty($data['payment']) || empty($data['price']) || empty($data['phone']) ){
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

// AUTH AND SUCH
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    // Are they not signed in? Then make a guest account
    $statement = $conn->prepare("
        INSERT INTO Users (email, username, phone, user_role)
        VALUES (:email, :name, :phone, 'guest')
    ");

    $statement->execute([
        "email" => $data['email'],
        "name" => $data['fullname'],
        "phone" => $data['phone']
    ]);

    $user_id = $conn->lastInsertId();
    }


try{ 
    $conn->beginTransaction();
    //STATEMENT #1
    $statement = $conn->prepare("SELECT c.id as cart_id
        FROM Carts c
        WHERE c.user_id = :id
        LIMIT 1
    ");
    
    $statement->execute(["id" => $user_id]);
    $cart = $statement->fetch(PDO::FETCH_ASSOC);
     if (!$cart) {
        throw new Exception("Cart not found");
    }

    $cart_id = $cart['cart_id'];

        //STATEMENT #2
    $statement = $conn->prepare("
        SELECT CartItems.product_id, CartItems.quantity, Products.price, Products.stock, Products.seller_id
        FROM CartItems CartItems
        JOIN Products ON CartItems.product_id = Products.id
        WHERE CartItems.cart_id = :cart_id
        FOR UPDATE
    ");
    $statement->execute(["cart_id" => $cart_id]);
    $items = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (count($items) === 0) {
        throw new Exception("Cart is empty");
    }

    $total_price = 0;

    foreach ($items as $item) {
        if ($item['stock'] < $item['quantity']) {
            throw new Exception("Not enough stock for product ID " . $item['product_id']);
        }

        $total_price += $item['price'] * $item['quantity'];
    }
    //STATEMENT #3
     $statement = $conn->prepare("
        INSERT INTO Orders (buyer_id, total_price, payment, delivery, address)
        VALUES (:buyer_id, :total_price, :payment, :delivery, :addr)
    ");
    $fulladdress = "{$data['street']}-{$data['city']}-{$data['province']}-{$data['postal']}";
    $statement->execute([
        "buyer_id"=> $user_id, 
        "total_price" => $total_price, 
        "payment"=>$data['payment'], 
        "delivery"=>$data['delivery'],
        "addr"=> $fulladdress
        ]);

    $order_id = $conn->lastInsertId();
    //STATEMENT #4
    $statement = $conn->prepare("INSERT INTO OrderItems 
        (order_id, buyer_id, product_id, seller_id, price_at_purchase, quantity)
        VALUES (:orid, :bid, :pid, :seid, :price, :quantity)
    ");

    foreach ($items as $item) {
        $statement->execute([
            "orid" => $order_id,
            "bid" => $user_id,
            "pid" => $item['product_id'],
            "seid" => $item['seller_id'],
            "price" => $item['price'],
            "quantity" => $item['quantity']
        ]);
    }
        //STATEMENT #5
        $statement = $conn->prepare("UPDATE Products
        SET stock = stock - :q
        WHERE id = :pid
    ");

    foreach ($items as $item) {
        $statement->execute([
            "q"=> $item['quantity'],
            "pid" => $item['product_id']
        ]);
    }


    $statement = $conn->prepare("
        DELETE FROM CartItems WHERE cart_id = :id");
    $statement->execute(["id" => $cart_id]);

    $conn->commit();

    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "message" => "Item successfully purchased, you will get an SMS to your phone number",
        "redirect" => "/"]);
}
catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("API ERROR: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "INTERNAL SERVER ERROR",
        "order_id" => null
    ]);
} finally {
    exit;
}
