<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
if (!$data['clientid']){
    http_response_code(405);
    echo json_encode(["error" => "MISSING USER TO UPDATE"]);
    exit;

}
$SQL = "";
$params = [];
if (isset($data['status'])){
    $SQL = "UPDATE Users SET user_status = :status WHERE id = :id";
    $params = [":status" => $data['status'], ":id"=> $data['clientid']];
}
else if (isset($data['role'])){
    $SQL = "UPDATE Users SET user_role = :role WHERE id = :id";
    $params = [":role" => $data['role'], ":id"=> $data['clientid']];

}
else{
    http_response_code(405);
    echo json_encode(["error" => "NEITHER IS SET"]);
}

try{
    $conn = require __DIR__ . "/../conn.php";

    $statement = $conn->prepare($SQL);
    $statement->execute($params);
    

    echo json_encode(["status" => true, "message" => "Successfully updated role"]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["message" => "INTERNAL SERVER ERROR"]);
} catch (Exception $e){
    http_response_code(500);
    error_log("INTERNAL SERVER ERROR: " . $e->getMessage());
    echo json_encode(["message" => "INTERNAL SERVER ERROR"]);

}