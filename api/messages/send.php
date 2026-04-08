<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
require_once __DIR__ . "/../utils/aws.php";
require_once __DIR__ . "/../utils/secretsaws.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$senderID = $_SESSION['user_id'] ?? null;
if (!$senderID) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$recieverID = $input['recipientID'] ?? null;
$txt = $input['message'] ?? null;

if (!$recipientID || !$messageText) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $dynamoDB = createDynamoClient($ACCESS);
    $aws = new AWSservice(null, $dynamoDB);
    
    $result = $aws->sendmessage($senderID, $recieverID, $txt);
    
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
} finally {
    exit;
}
