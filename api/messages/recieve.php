<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
require_once __DIR__ . "/../utils/aws.php";
require_once __DIR__ . "/../utils/AWSCLIENTS.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');

$userID = $_SESSION['user_id'] ?? null;
if (!$userID) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$recieverID = $_GET['recieverID'] ?? null;
$limit = (int)($_GET['limit'] ?? 50);

if (!$recieverID) {
    http_response_code(400);
    echo json_encode(["error" => "Missing recieverID parameter"]);
    exit;
}

try {
    $dynamoDB = createDynamoClient($ACCESS);
    $aws = new AWSservice(null, $dynamoDB);
    
    $messages = $aws->getChatMessages($userID, $recieverID, $limit);
    
    if ($messages['success'] === false) {
        echo json_encode(["messages" => [], "error" => "Could not retrieve messages"]);
        exit;
    } else {
        // Mark this conversation as read
        $conversationID = $userID < $recieverID ? "$userID#$recieverID" : "$recieverID#$userID";
        //$aws->markMessagesAsRead($conversationID, $userID);
        
        http_response_code(200);
        echo json_encode(["messages" => $messages]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
?>