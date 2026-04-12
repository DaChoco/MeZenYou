<?php
require_once __DIR__ . "/../../utils/cors.php";
require '../../session.php';
header("Content-Type: application/json");

$conn = require __DIR__ .'/../../conn.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    error_log("JSON ERROR: WRONG REQUEST");
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['pid'])){
    http_response_code(400);
    error_log("JSON ERROR: USER DIDNT SELECT PRODUCT");
    echo json_encode(["error" => "Method not allowed"]);
    exit;

}
if ($_SESSION['role'] !== "seller" || $_SESSION['role'] !== "ADMIN" || $_SESSION['role'] !== "MODERATOR" || !$_SESSION['user_id']){
    http_response_code(403);
    echo json_encode([
        "message" => "INVALID CREDENTIALS",
        "logged" => false,
        "redirect" => "/"
    ]);
    
}

$user_id = $_SESSION['user_id'];
$pid = $data['pid'];

try{
    $statement = $conn->prepare("DELETE FROM Products WHERE id = :pid AND seller_id = :user_id");
    $statement->execute(['pid'=> $pid, "user_id"=> $user_id]);
     echo json_encode([
        "message" => "You have successfully taken down this listing, thank you",
        "success" => true,

    ]);

} catch (Exception $e){
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);
}