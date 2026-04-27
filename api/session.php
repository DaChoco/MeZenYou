<?php
if (session_status() === PHP_SESSION_NONE) {
    //259200 SECONDS IS 3 DAYS
    ini_set('session.gc_maxlifetime', 259200);
    session_set_cookie_params([
        'lifetime' => 259200,            
        'path' => '/',
        'domain' => '',             
        'secure' => false,           
        'httponly' => true,        
        'samesite' => 'Lax'      
    ]);
    session_start();
}

?>