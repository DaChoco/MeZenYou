<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([     
        'path' => '/',
        'domain' => '',             
        'secure' => false,           
        'httponly' => true,        
        'samesite' => 'Lax'      
    ]);
    session_start();
}
?>