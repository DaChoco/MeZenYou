<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
require_once __DIR__ . "/../utils/aws.php";
require_once __DIR__ . "/../utils/AWSCLIENTS.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');

$userID1 = $_SESSION['user_id'] ?? null;
$userID2 = $_GET['rid'] ?? null;
$role = $_SESSION['role'] ?? null;


if (!$userID1 || !$role) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if (!$userID2) {
    http_response_code(401);
    echo json_encode(["error" => "No CHAT RECIPIENT"]);
    exit;

}

try {
    $conn = require __DIR__ . "/../conn.php";
    $statement = $conn->prepare('SELECT id, username FROM users WHERE id IN (:uID1, :uID2)');
    $statement->execute(["uID1" => $userID1, "uID2" => $userID2]);

    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(401);
        echo json_encode(["error" => "INVALID MESSAGE REQUEST"]);
        exit;

    }

    $userRows = $statement->fetchAll(PDO::FETCH_ASSOC);

    $userMap = [];
    foreach ($userRows as $user) {
        $userMap[$user['id']] = $user['username'];
    }


    $dynamoDB = createDynamoClient($ACCESS);
    $AWS = new AWSservice(null, $dynamoDB);

    $messages = $AWS->getChatMessages($userID1, $userID2);
    
    foreach ($messages as &$msg) {
        $senderID = $msg['senderID'] ?? "";
        $msg['username'] = $userMap[$senderID] ?? "UNKNOWN";
    }
    unset($msg);

    echo json_encode(["messages" => $messages, "status" => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
