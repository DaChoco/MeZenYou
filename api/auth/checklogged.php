<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "message" => "Already logged in",
        "redirect" => "/",
        "logged" => True
    ]);
    exit;
}
 else {
    echo json_encode([
        "message" => "Not logged in",
        "logged" => false
    ]);
 }

?>