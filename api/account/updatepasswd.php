<?php
require_once __DIR__ . "/../utils/cors.php";
require __DIR__. "/../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data['password']) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$new_password = $data['password'];
$password_hash = password_hash($new_password, PASSWORD_BCRYPT);
$user_id = $_SESSION['user_id'];
error_log("ID: " . $user_id);

try{
    $conn = require '../conn.php';
    $current_time = (int) time();
    $statement = $conn->prepare("UPDATE Users SET password_hash = :hashed, updated_at = $current_time WHERE id = :user_id");
    $statement->execute([":hashed"=> $password_hash, ":user_id" =>$user_id]);

    if ($statement->rowCount() > 0) {
        echo json_encode([
            "status" => true,
            "message" => "Password updated successfully"
        ]);
    } else {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "No changes occured"
        ]);}
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
