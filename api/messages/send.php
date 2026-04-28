<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
require_once __DIR__ . "/../utils/aws.php";
require_once __DIR__ . "/../utils/AWSCLIENTS.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$senderID = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
if (!$senderID || !$role) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$recieverID = $input['rID'] ?? null;
$txt = $input['message'] ?? null;
//My Icon 
$senderIcon = $input['icon'];

if (!$recieverID|| !$txt || !$senderIcon) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

if ($recieverID === $senderID){
    http_response_code(400);
    echo json_encode(["error" => "WARNING: CANNOT SEND MESSAGE TO YOURSELF"]);
    exit;

}

try {
    $conn = require __DIR__ . "/../conn.php";
    $statement = $conn->prepare('SELECT id, icon FROM users WHERE id = :rID');
    $statement->execute([":rID"=> $recieverID]);

    $result = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$result['id']){
        http_response_code(401);
        echo json_encode(["error" => "INVALID MESSAGE REQUEST"]);
        exit;

    }
    //their icon
    $icon = $result['icon'];

    
    $dynamoDB = createDynamoClient($ACCESS);
    $aws = new AWSservice(null, $dynamoDB);
    
    $result = $aws->sendmessage($senderID, $recieverID, $txt, $senderIcon, $icon, $role);
    
    if ($result['success']) {
        http_response_code(201);
        echo json_encode($result);
    } else {
        http_response_code(500);
        echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
}
