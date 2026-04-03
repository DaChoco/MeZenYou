<?php
require_once "../session.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$user_id = $_SESSION['user_id'];

if (
    !isset($data['streetaddress'], 
    $data['suburb'], 
    $data['city'], 
    $data['province'], 
    $data['postalcode']
    )) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields", "status"=>false]);
    exit;
}

$conn = require '../conn.php';

$fulladdress = "{$data['streetaddress']}-{$data['suburb']}-{$data['city']}-{$data['province']}-{$data['postalcode']}";

try{
    $statement = $conn->prepare("UPDATE Users SET address = :user_address, delivery_instructions =:instructions WHERE id = :user_id");
    $statement->execute(['user_address'=> $fulladdress, "id" => $user_id, "instructions"=>$data['instructions']]);

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
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

?>

