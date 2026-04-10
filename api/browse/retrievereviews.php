<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
require_once __DIR__ ."/../utils/aws.php";
require_once __DIR__ ."/../utils/secretsaws.php";
$ACCESS = require __DIR__ ."/../config.php";
header('Content-Type: application/json');

$dynamoDB = createDynamoClient($ACCESS);

$aws = new AWSservice(null, $dynamoDB);

$pID = $_GET['pid'];

try{
    $data = $aws->retrieveProductReviews($pID);
    if ($data != false){
         echo json_encode(["items" => $data['reviews'], "avg"=>$data['avg']]);
    }
    else{
        echo json_encode(["items" => [], "avg"=>0, "message"=>"No Reviews have released yet."]);
    }
   

    
}
catch(Exception $e){
    http_response_code(500);
    echo json_encode(["error" => "error: " . $e->getMessage()]);

}
finally{
    exit;
}

