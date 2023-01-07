<?php
    session_start();
    ini_set("session.cookie_httponly", 1);

    // specify type of data being received
    header("Content-Type: application/json");

    // get data from the front
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);

    // access data from client
    $event_id = (int)$json_obj['event'];

    $success = false;
    $is_shared = false;

    //filter input, check for empty username and password and special characters in username
    if(!preg_match('/^\d$/', $event_id)) {
        //username and password have passed filter
    
        //connect to database
        require 'database.php';

        // Use a prepared statement
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM event_permissions where event_id=?");

        // bind parameter
        $stmt->bind_param('i', $event_id);
        if($stmt->execute()) {
            $success = true;
        }

        // Bind the results
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();

        if($cnt > 1) {
            $is_shared = true;
        }
    }  

    // send success value back to the front
    echo json_encode(array("success" => $success, "shared" => $is_shared));
?>