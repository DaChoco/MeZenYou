<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([    
        'lifetime' => 120000,
        'path' => '/',
        'domain' => '',             
        'secure' => false,           
        'httponly' => true,        
        'samesite' => 'Strict'      
    ]);
    session_start();
}
?>