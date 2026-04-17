<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//this route is an extreme last resort, once this is done they are unrecoverable essentially

$ALLOWED = ["ADMIN", "MODERATOR"];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

if (!in_array($_SESSION['role'], $ALLOWED)){
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed", 'redirect' => '/']);
    exit;

}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data['deleted_id']){
    http_response_code(405);
    echo json_encode(["error" => "MISSING USER TO DELETE"]);
    exit;

}
$deletedID = $data['deleted_id'];
try{
    $conn = require __DIR__ . "/../conn.php";
    $conn->beginTransaction();
    //START THE PROCESS TO DELETE THE USER. WE CANT COMPLETELY WIPE THEM'
    //BUT WE CAN ANONYMIZE THEM AND ERASE AS MUCH NON CRITICAL DATA AS POSSIBLE
    $statement = $conn->prepare("DELETE FROM carts WHERE user_id = :id");
    $statement->execute(['id'=> $deletedID]);
    
    //WIPE ALL THEIR PRODUCTS
    $statement = $conn->prepare("UPDATE Products SET is_active = FALSE WHERE seller_id = :id");
    $statement->execute(['id'=> $deletedID]);

    //ANONYMIZE DATA
    $statement = $conn->prepare("UPDATE Users SET 
    email = :newEMAIL,
    username = NULL,
    city = NULL,
    province = NULL,
    address = NULL,
    phone = NULL,
    delivery_instructions = NULL,
    password_hash = NULL,
    user_status = 'DELETED'
    WHERE id = :id");
    $statement->execute(["newEMAIL"=> "deleted_$deletedID@example.com", "id" => $deletedID]);

    $conn->commit();
    echo json_encode(["status" => true, "message" => "User has been deleted, thank you so much for your time, sorry it had to end this way."]);
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR", "status" => false]);
} catch (Exception $e){
    $conn->rollBack();
    http_response_code(500);
    error_log("INTERNAL SERVER ERROR: " . $e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR", "status" => false]);

}