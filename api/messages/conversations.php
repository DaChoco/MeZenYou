<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
require_once __DIR__ . "/../utils/aws.php";
require_once __DIR__ . "/../utils/AWSCLIENTS.php";
$ACCESS = require __DIR__ . "/../config.php";
header('Content-Type: application/json');

$userID = (string) $_SESSION['user_id'] ?? null;

if (!$userID) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

try {
    $conn = require __DIR__ . "/../conn.php";
    $statement = $conn->prepare('SELECT updated_at as current FROM users WHERE id = :uID');
    $statement->execute(["uID" => $userID]);

    //will be used cache busting. Current is the time this user last updated their profile.
    $current = $statement->fetch(PDO::FETCH_ASSOC)['current'];

    $dynamoDB = createDynamoClient($ACCESS);
    $AWS = new AWSservice(null, $dynamoDB);

    $result = $AWS->getConversations($userID);

    $otherIDs = array_column($result, 'otherID');
    $otherIDs = array_unique($otherIDs);

    $placeholders = implode(',', array_fill(0, count($otherIDs), '?'));

    $statement = $conn->prepare("SELECT id, username FROM users WHERE id IN ($placeholders)");
    $statement->execute($otherIDs);

    $userRows = $statement->fetchAll(PDO::FETCH_ASSOC);

    $userMap = [];
    foreach ($userRows as $user) {
        $userMap[$user['id']] = $user['username'];
    }

    foreach ($result as &$conv) {
        $conv['username'] = $userMap[$conv['otherID']] ?? null;
    }
    unset($conv); // good practice after reference


    echo json_encode(["conversations" => $result, "current" => $current, "status" => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
