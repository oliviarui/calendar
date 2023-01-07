<?php
    header("Content-Type: application/json");

    session_start();
    ini_set("session.cookie_httponly", 1);
    $logged_in = false;
    if(isset($_SESSION['username'])) {   
        $previous_ua = @$_SESSION['useragent'];
        $current_ua = $_SERVER['HTTP_USER_AGENT'];
    
        if(isset($_SESSION['useragent']) && $previous_ua !== $current_ua){
            die("Session hijack detected");
        }else{
            $_SESSION['useragent'] = $current_ua;
        }

        $logged_in = true;
    }

    echo json_encode(array("loggedIn" => $logged_in, "username" => htmlentities($_SESSION['username']), "token" => htmlentities($_SESSION['token'])));
?>