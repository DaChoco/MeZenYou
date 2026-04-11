<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
require_once __DIR__ ."/../utils/aws.php";
require_once __DIR__ ."/../utils/AWSCLIENTS.php";
$ACCESS = require_once __DIR__ ."/../config.php";
header('Content-Type: application/json');

try{
    $dynamoDB = createDynamoClient($ACCESS);

    $aws = new AWSservice(null, $dynamoDB);

    $data = $aws->retrieveAllProductReviews();
    if ($data != false){
         echo json_encode(["items" => $data]);
    }
    else{
        echo json_encode(["items" => [], "message"=>"No Reviews have released yet."]);
    }
   

    
}
catch(Exception $e){
    http_response_code(500);
    error_log(print_r($ACCESS, true));
    error_log(print_r($dynamoDB, true));
    echo json_encode(["error" => "error: " . $e->getMessage()]);

}
finally{
    exit;
}

