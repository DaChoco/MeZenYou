<?php
require_once __DIR__ . "/../utils/cors.php";
header("Content-Type: application/json");
require_once "../session.php";

//MAKE SURE THIS IS JSON OR REJECT THE REQUEST------------------------------------

ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}
//VALIDATION STAGE----------------------------------------------------
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data['email'] || !$data['password']) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];
$username = $data['username'];

$hashed_password = password_hash($password, PASSWORD_BCRYPT);
//THEREST-----------------------------------------------------
$conn = require '../conn.php';

try{
    $statement = $conn->prepare("SELECT id FROM Users WHERE email = :email");
    $statement->execute(['email' => $email]);


    //IF there is more than one item this means already exists
    if ($statement->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(["error" => "User with this Email already exists"]);
        exit;
    }
    $SQL = "";
    if (isset($data["is_seller"])){
        $SQL = "INSERT INTO users (email, password_hash, username, user_role) VALUES (:email, :password_hash, :username, 'seller')";
    }
    else{
        $SQL = "INSERT INTO users (email, password_hash, username) VALUES (:email, :password_hash, :username)";
    }
    $statement = $conn->prepare($SQL);
    $statement->execute(['email' => $email, 'password_hash' => $hashed_password, 'username'=> $username]);

    //SUCCESSFUL REGISTRATION ACHIEVED!
    
    session_regenerate_id(true);
    $_SESSION['user_id'] = $conn->lastInsertId();
    $_SESSION['email'] = $email;
    $_SESSION['username'] = $data['username'];
    http_response_code(201);
    echo json_encode([
        "message" => "User registered successfully",
        "user_id" => $conn->lastInsertId(),
        "redirect" => "/"
    ]);

}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "INTERNAL SERVER ERROR" ]);
}



?>