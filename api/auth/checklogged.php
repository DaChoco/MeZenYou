<?php
require_once "../session.php";

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "message" => "Already logged in",
        "redirect" => "/",
        "logged" => True
    ]);
    exit;
}

?>