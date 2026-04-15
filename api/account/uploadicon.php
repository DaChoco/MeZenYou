<?php
require_once __DIR__ . "/../utils/cors.php";
require '../session.php';
header("Content-Type: application/json");
require __DIR__ . "/../utils/aws.php";
require __DIR__ . "/../utils/AWSCLIENTS.php";
$conn = require '../conn.php';
$ACCESS = require __DIR__ . "/../config.php";
$user_id = $_SESSION['user_id'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_FILES['image'])) {
    http_response_code(400);
    error_log("MISSING IMAGE");
    echo json_encode(["error" => "Image is required"]);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== 0) {
    http_response_code(400);
    error_log("FILE ERROR");
    echo json_encode(["error" => "File upload error"]);
    exit;
}

$allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
$realType = mime_content_type($file['tmp_name']);

if (!in_array($realType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid file type"]);
    exit;
}

try{
    $conn->beginTransaction();

    $statement = $conn->prepare("UPDATE Users SET icon = :iconurl WHERE id = :id");

    $s3 = createS3Client($ACCESS);
    $aws = new AWSservice($s3, null);

    $result =$aws->uploadUserIcon($user_id, $file['tmp_name']);

    if (empty($result) || $result === null){
        throw new Exception("Error with AWS S3");
    }
    $statement->execute(["iconurl"=> $result, "id"=>$user_id]);

    $conn->commit();

    http_response_code(201);
    echo json_encode(["status" => true, "message" => "Thank you, your icon is uploaded, it will reflect on your profile.", "imgurl" => $result]);
}
catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(["message" => "INTERNAL SERVER ERROR", "status" => false]);
}

