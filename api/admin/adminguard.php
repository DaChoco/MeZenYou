<?php
require_once __DIR__ . "/../utils/cors.php";
require_once __DIR__ ."/../session.php";

if ($_SESSION['role'] === "ADMIN" || $_SESSION['role'] === "MODERATOR") {
    echo json_encode([
        "message" => "Already logged in",
        "logged" => True
    ]);
    exit;
}
//note for later, moderator should lack access to some routes that the admin can do. For example, dont let the moderator delete the admin.
 else {
    http_response_code(403);
    echo json_encode([
        "message" => "Not logged in",
        "logged" => false,
        "redirect" => "/"
    ]);
 }
