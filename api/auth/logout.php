<?php 
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";

$_SESSION = [];


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
http_response_code(201);
echo json_encode(["message" => "Logged out", "success" => true]);

?>