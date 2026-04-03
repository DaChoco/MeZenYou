<?php
require_once "../session.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);
//MAKE SURE THIS IS JSON OR REJECT THE REQUEST------------------------------------
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}
//VALIDATION STAGE----------------------------------------------------
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

$hashed_password = password_hash($password, PASSWORD_BCRYPT);
//THEREST-----------------------------------------------------
$conn = require '../conn.php';

try{
    $statement = $conn->prepare("SELECT id FROM Users WHERE email = :email or password_hash = :password_hash");
    $statement->execute(['email' => $email, 'password_hash'=>$hashed_password]);


    //IF there is more than one item this means already exists
    if ($statement->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(["error" => "Username or email already exists"]);
        exit;
    }

    $statement = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)");
    $statement->execute(['email' => $email, 'password_hash' => $hashed_password]);

    //SUCCESSFUL REGISTRATION ACHIEVED!
    

    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    http_response_code(201);
    echo json_encode([
        "message" => "User registered successfully",
        "user_id" => $conn->lastInsertId()
    ]);

}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}



?>