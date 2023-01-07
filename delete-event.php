<?php
    session_start();
    ini_set("session.cookie_httponly", 1);

    // specify type of data being received
    header("Content-Type: application/json");

    // get data from the front
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);

    // access event id
    $event_id = (int)$json_obj['inputEventID'];
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

    // make sure that the event being edited does belong to the current user 
    if(preg_match('/^\d*$/', $event_id)) {
        //connect to database
        require './database.php';

        // get all event info
        $stmt = $mysqli->prepare("SELECT user FROM event_permissions WHERE event_id=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $abuse_of_func = true;
        while ($stmt->fetch()) {
            if((int)$user_id == (int)$_SESSION['user_id']) {
                $abuse_of_func = false;
            }
        }            
        $stmt->close();
        if($abuse_of_func) {
            echo json_encode(array(
                "error" => "Abuse of functionality detected"
            ));
            exit;
        }
    }

    if(preg_match('/^\d*$/', $event_id)) {
        //connect to database
        require './database.php';
        // delete event from event permissions table
        $stmt = $mysqli->prepare("DELETE FROM event_permissions WHERE event_id=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        // delete event with the given id
        $stmt->bind_param('i', $event_id);
        if($stmt->execute()) {
            $success = true;
        }
        $stmt->close();

        // delete event from events table
        $stmt = $mysqli->prepare("DELETE FROM events WHERE id=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        // delete event with the given id
        $stmt->bind_param('i', $event_id);
        if($stmt->execute()) {
            $success = true;
        }
        $stmt->close();
    }
    echo json_encode(array("success" => $success));
?>