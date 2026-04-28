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

//THE REST---------------------------------------
$conn = require '../conn.php';

try {
    $stmt = $conn->prepare("SELECT id, username, password_hash, user_role, user_status FROM Users WHERE email = :email");
    $stmt->execute([":email" => $email]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        // No user found
        error_log("User not found");
        echo json_encode(["error" => "INVALID CREDENTIALS"]);
        exit;
    }

    if ($result['user_status'] === 'DELETED' || $result['user_status'] === 'BANNED') {
    session_unset();
    session_destroy();
    http_response_code(403);
    echo json_encode(["error" => "Account has been deleted"]);
    exit;
}

    $password_hash = $result['password_hash'];
    $user_id = $result['id'];
    $user_role = $result['user_role'];
    $username = $result['username'];

    if (password_verify($password, $password_hash)) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $user_role;
    $_SESSION['username'] = $username;

    http_response_code(201);
    echo json_encode(["message" => "Login successful", "redirect" => "/"]);
    } else {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);
}
