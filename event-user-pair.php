<?php
    session_start();
    ini_set("session.cookie_httponly", 1);

    // specify type of data being received
    header("Content-Type: application/json");

    // get data from the front
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);

    // access username and password from the data
    $user_id = $_SESSION['user_id'];
    if(preg_match('/^\d+$/', $json_obj['user'])) {
        // this is for sharing an event, if the json data includes a user input
        $user_id = $json_obj['user'];
    } 
    $event_id = (int)$json_obj['event'];
    $csrf_token = $json_obj['token'];

    $success = false;

    // check csrf token
    if (!hash_equals($_SESSION['token'], $csrf_token)) {
        // token is not matching
        echo json_encode(array(
            "error" => "Request forgery detected."
        ));
        exit;
    }
    // token matches

    //filter input, check for empty input fields
    if(preg_match('/^\d+$/', $user_id) && preg_match('/^\d+$/', $event_id)) {
        //connect to database
        require './database.php';

        // put event and id pair into permissions table
        $stmt = $mysqli->prepare("INSERT INTO event_permissions (event_id, user) values (?, ?)");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $success = $user_id.' '.$event_id;
        $stmt->bind_param('ii', $event_id, $user_id);
        if($stmt->execute()) {
            //event user pair added to permissions table
            $success = true;
        }
        $stmt->close();
    }  

    // send success value back to the front
    echo json_encode(array("success" => $success));
?>