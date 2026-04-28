<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header("Content-Type: application/json");
require __DIR__ ."/../utils/AWSCLIENTS.php";
require __DIR__ ."/../utils/aws.php";
$ACCESS = require __DIR__ ."/../config.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//this route is an extreme last resort, once this is done they are unrecoverable essentially

$ALLOWED = ["ADMIN", "MODERATOR"];

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['deleted_id'])){
    http_response_code(400);
    echo json_encode(["error" => "MISSING USER TO DELETE"]);
    exit;

}
$deletedID = $data['deleted_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

if (!in_array($_SESSION['role'], $ALLOWED)){
    http_response_code(401);
    echo json_encode(["message" => "NOT AUTHORIZED", 'redirect' => '/']);
    exit;

}

if ($_SESSION['user_id'] == $deletedID) {
    http_response_code(403);
    echo json_encode(["message" => "You cannot delete your own account via admin route"]);
    exit;
}

try{
    $conn = require __DIR__ . "/../conn.php";
    
    //VERIFICATION
    $statement = $conn->prepare("SELECT icon, user_role FROM users WHERE id = :id AND user_status = 'ACTIVE'");
    $statement->execute([":id"=>$deletedID]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);
      if (!$user) {
        http_response_code(401);
        echo json_encode(["message" => "USER NOT FOUND", "status"=> false]);
        exit;
        
    }
    $delete_target_role = $user['user_role'];

    if (in_array($delete_target_role, ['ADMIN', 'MODERATOR']) && $_SESSION['role'] !== "ADMIN") {
        http_response_code(403);
        echo json_encode(["error" => "Insufficient permissions"]);
        exit;
    }
    
    $url = $user['icon'];
    //START THE PROCESS TO DELETE THE USER. WE CANT COMPLETELY WIPE THEM'
    //BUT WE CAN ANONYMIZE THEM AND ERASE AS MUCH NON CRITICAL DATA AS POSSIBLE
    $conn->beginTransaction();
    $statement = $conn->prepare("DELETE FROM carts WHERE user_id = :id");
    $statement->execute([':id'=> $deletedID]);
    
    //WIPE ALL THEIR PRODUCTS
    $statement = $conn->prepare("UPDATE Products SET is_active = FALSE WHERE seller_id = :id");
    $statement->execute([':id'=> $deletedID]);

    #WIPE THEIR ICON FROM AWS

    //ANONYMIZE DATA
    $statement = $conn->prepare("UPDATE Users SET 
    username = NULL,
    city = NULL,
    province = NULL,
    address = NULL,
    phone = NULL,
    delivery_instructions = NULL,
    password_hash = NULL,
    icon = NULL,
    user_status = 'DELETED'
    WHERE id = :id");
    
    $statement->execute([":id" => $deletedID]);

    $conn->commit();
    $aws = new AWSservice(createS3Client($ACCESS), null);
    $s3output = $aws->deleteS3Image($url);
    if ($s3output === false){
        throw new Exception("Something went wrong with the deletion");
    }
    echo json_encode(["status" => true, "message" => "User has been deleted, thank you so much for your time, sorry it had to end this way."]);
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["message" => "INTERNAL SERVER ERROR", "status" => false]);
} catch (Exception $e){
    if ($conn->inTransaction()){
        $conn->rollBack();
    }
    
    http_response_code(500);
    error_log("INTERNAL SERVER ERROR: " . $e->getMessage());
    echo json_encode(["message" => "INTERNAL SERVER ERROR", "status" => false]);

}