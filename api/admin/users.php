<?php
require_once __DIR__ . "/../utils/cors.php";
require_once "../session.php";
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ALLOWED = ["ADMIN", "MODERATOR"];

if (!in_array($_SESSION['role'], $ALLOWED)){
    http_response_code(403);
    echo json_encode(["error" => "Method not allowed", 'redirect' => '/']);
    exit;

}

$page = isset($_GET['pg']) && $_GET['pg'] !== '' ? (string)$_GET['pg']: null;

if ($page === null || $page < 1){
        $page = 0;
    }
    else{
        --$page;
    }

    
    $offset = max(0, (int)$page) * 10;

try{
    $conn = require __DIR__ . "/../conn.php";

    $statement = $conn->prepare("SELECT id, username, icon, email, user_role AS role, user_status AS status, created_at FROM Users LIMIT 10 OFFSET $offset");
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => true, "users" => $results]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
    echo json_encode(["error" => "Database error: "]);
} catch (Exception $e){
    http_response_code(500);
    error_log("INTERNAL SERVER ERROR: " . $e->getMessage());
    echo json_encode(["error" => "INTERNAL SERVER ERROR"]);

}