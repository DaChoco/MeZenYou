<?php
require_once __DIR__ . "/../../utils/cors.php";
require '../../session.php';
header("Content-Type: application/json");
require __DIR__ . "/../../utils/aws.php";
require __DIR__ . "/../../utils/AWSCLIENTS.php";
$ACCESS = require __DIR__ . "/../../config.php";
$conn = require __DIR__ .'/../../conn.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    error_log("JSON ERROR: WRONG REQUEST");
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['pid'])){
    http_response_code(400);
    error_log("JSON ERROR: USER DIDNT SELECT PRODUCT");
    echo json_encode(["error" => "Method not allowed"]);
    exit;

}
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ["seller", "ADMIN", "MODERATOR"]))
    {
    http_response_code(403);
    echo json_encode([
        "message" => "INVALID CREDENTIALS",
        "logged" => false,
        "redirect" => "/",
    ]);
    exit;
    
}

$user_id = $_SESSION['user_id'];
$pid = $data['pid'];
$active = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);


try{
    if ($active === null) {
    throw new Exception("Invalid active value");
    }
    
    $conn->beginTransaction();
    $statement = $conn->prepare("UPDATE Products SET is_active = :active WHERE id = :pid AND seller_id = :user_id");
    $statement->execute([':active' => (int) $active,':pid'=> $pid, ":user_id"=> $user_id]);

    $conn->commit();
     echo json_encode([
        "message" => "You have successfully taken down this listing, thank you",
        "success" => true,

    ]);

} catch (Exception $e){
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    error_log($e->getMessage() );
    error_log((string) $data['active']);
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);
}