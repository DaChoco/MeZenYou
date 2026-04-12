<?php
require_once __DIR__ . "/../utils/cors.php";
require '../session.php';
header("Content-Type: application/json");

$conn = require '../conn.php';

if ($_SESSION['role'] !== "seller" || $_SESSION['role'] !== "ADMIN" || $_SESSION['role'] !== "MODERATOR"){
    http_response_code(403);
    echo json_encode([
        "message" => "Not logged in",
        "logged" => false,
        "redirect" => "/"
    ]);
    
}

try{

} catch (Exception $e){
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}