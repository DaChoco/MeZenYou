<?php
require_once __DIR__ . "/../utils/cors.php";
require __DIR__. "/../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$user_id = $_SESSION['user_id'];
error_log("ID: " . $user_id);

if (
    !isset($data['street'], 
    $data['suburb'], 
    $data['city'], 
    $data['province'], 
    $data['postalcode']
    )) {
    http_response_code(400);
    error_log("Incoming request hit updateaddress.php");
    error_log(print_r($data, true));

    echo json_encode(["error" => "Missing required fields", "status"=>false]);
    exit;
}

$conn = require '../conn.php';

$fulladdress = "{$data['street']}-{$data['suburb']}-{$data['city']}-{$data['province']}-{$data['postalcode']}";

try{
    $statement = $conn->prepare("UPDATE Users SET province = :province, address = :user_address, delivery_instructions =:instructions, phone = :phone WHERE id = :user_id");
    $statement->execute(['user_address'=> $fulladdress, "province"=> $data['province'], "user_id" => $user_id, "instructions"=>$data['delinstructions'], "phone"=> $data['phone']]);

    if ($statement->rowCount() > 0) {
        echo json_encode([
            "status" => true,
            "message" => "Address updated successfully"
        ]);
    } else {
        http_response_code(403);
        echo json_encode([
            "status" => false,
            "message" => "No changes made (same data or user not found)"
        ]);}
}
catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

?>

