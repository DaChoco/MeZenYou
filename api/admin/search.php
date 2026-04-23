<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ALLOWED = ["ADMIN", "MODERATOR"];

if (!in_array($_SESSION['role'], $ALLOWED)){
    http_response_code(403);
    echo json_encode(["error" => "Method not allowed", 'redirect' => '/']);
    exit;

}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['txt'])){
     http_response_code(400);
    echo json_encode(["error" => "MISSING DATA"]);
    exit;
}

$USER_INPUT = $data['txt'];


try{
    $conn = require __DIR__ . "/../conn.php";

    $statement = $conn->prepare("SELECT id as rID, username, icon, updated_at, user_role AS role, created_at FROM Users WHERE username LIKE :username OR email LIKE :email LIMIT 10");
    $statement->execute(["username" => "%$USER_INPUT%", "email"=> "%$USER_INPUT%"]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["status" => true, "user"=>$result]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);
} catch (Exception $e){
    http_response_code(500);
    error_log("INTERNAL SERVER ERROR: " . $e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);

}