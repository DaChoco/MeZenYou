<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
require_once __DIR__ ."/../utils/aws.php";
require_once __DIR__ ."/../utils/AWSCLIENTS.php";
$ACCESS = require_once __DIR__ ."/../config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$dynamoDB = createDynamoClient($ACCESS);

$aws = new AWSservice(null, $dynamoDB);

$pID = $_GET['pid'];
$uID = $_SESSION['user_id'];
$username = $_SESSION['username'];

if (!isset($uID, $pID)){
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;

}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['comment'], $data['rating'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try{
    $result = $aws->uploadProductReview($uID, $pID, $data['comment'], $data['rating'], $username);

    if ($result === true){
        http_response_code(201);
        echo json_encode(["success"=> $result, "message" => "Review uploaded successefully"]);
    }
    else{
        http_response_code(501);
        echo json_encode(["success"=> $result, "message" => "Review failed to upload. Please Try again later"]);

    }
    
}
catch(Exception $e){
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);

}
finally{
    exit;
}