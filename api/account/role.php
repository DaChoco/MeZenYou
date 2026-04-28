<?php
require_once __DIR__ . "/../utils/cors.php";
require '../session.php';
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["redirect" => "/"]);
    exit;
}
$role = $_SESSION["role"];
$id = $_SESSION["user_id"];

try{
$conn = require __DIR__. "/../conn.php";
$statement = $conn->prepare("SELECT address, username, phone, delivery_instructions, icon, updated_at FROM Users WHERE id = :id AND user_status = 'ACTIVE'");
$statement->execute([":id"=> $id]);

$result = $statement->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "role" => $role,
    "user" => $id,
    "username" => $result['username'],
    "address" => $result["address"],
    "phone" => $result["phone"],
    "icon" => $result["icon"],
    "timestamp" => $result["updated_at"]
]);

}
catch (PDOException $e){
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "ERROR"]);

}

