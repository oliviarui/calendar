<?php
    header("Content-Type: application/json");

    session_start();
    ini_set("session.cookie_httponly", 1);
    $success = session_destroy();

    // send success value back to the front
    echo json_encode(array("success" => $success));
?>