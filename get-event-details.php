<?php
    session_start();
    ini_set("session.cookie_httponly", 1);

     // specify type of data being received
     header("Content-Type: application/json");

     // get data from the front
     $json_str = file_get_contents('php://input');
     $json_obj = json_decode($json_str, true);
 
     // access event id
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

     //check that event id format is correct
     if(preg_match('/^\d*$/', $event_id)) {
        //connect to database
        require './database.php';

        // get all event info
        $stmt = $mysqli->prepare("SELECT event_name, start_d, start_time, descript, importance FROM events WHERE id=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        //only get events that match the current date and the inputted user
        $stmt->bind_param('i', $event_id);
        $stmt->execute();
        $stmt->bind_result($event_name, $event_start_date, $event_start_time, $event_descript, $event_import);
        while($stmt->fetch()) {
            $success = true;
            $event_details = array(htmlentities($event_name), htmlentities($event_start_date), htmlentities($event_start_time), htmlentities($event_descript), htmlentities($event_import));
        }
        $stmt->close();
    }
    if($success) {
        echo json_encode(array("success" => $success, "eventDetails" => $event_details));
    } else {
        echo json_encode(array("success" => $success));
    }
    
 
?>