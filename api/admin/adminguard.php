<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";

if ($_SESSION['role'] === "ADMIN") {
    echo json_encode([
        "message" => "Already logged in",
        "logged" => True
    ]);
    exit;
}
 else {
    http_response_code(403);
    echo json_encode([
        "message" => "Not logged in",
        "logged" => false,
        "redirect" => "/"
    ]);
 }
