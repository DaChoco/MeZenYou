<?php
require_once __DIR__ . "/../utils/cors.php";
require_once __DIR__ ."/../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 0);
error_reporting(E_ALL);

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
if (!isset($data['id'], $data['status'])){
    http_response_code(405);
    echo json_encode(["error" => "MISSING ORDER TO UPDATE"]);
    exit;

}

try{
    $conn = require __DIR__ . "/../conn.php";

    $statement = $conn->prepare("UPDATE Orders SET order_status = :status WHERE id = :id");
    $statement->execute([":status"=> $data['status'], ":id" => $data['id']]);
    

    echo json_encode(["status" => true, "message" => "Successfully updated order"]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["message" => "INTERNAL SERVER ERROR"]);
} catch (Exception $e){
    http_response_code(500);
    error_log("INTERNAL SERVER ERROR: " . $e->getMessage());
    echo json_encode(["message" => "INTERNAL SERVER ERROR"]);

}