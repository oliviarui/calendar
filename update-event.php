<?php
    session_start();
    ini_set("session.cookie_httponly", 1);
    
    // specify type of data being received
    header("Content-Type: application/json");

    // get data from the front
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);

    // access username and password from the data
    $event_id = (int)$json_obj['event'];
    $event_title = $json_obj['title'];
    $event_date = $json_obj['date'];
    $event_time = $json_obj['time'];
    $event_descript = $json_obj['descript'];
    $event_import = $json_obj['import'];
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

    //filter input, check for empty input fields
    if(!preg_match('/^$/', $event_title) && !preg_match('/^$/', $event_date) && !preg_match('/^$/', $event_time)) {

        //connect to database
        require './database.php';

        //update event in database
        $stmt = $mysqli->prepare("UPDATE events SET event_name=?, start_d=?, start_time=?, descript=?, importance=? WHERE id=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('sssssi', $event_title, $event_date, $event_time, $event_descript, $event_import, $event_id);
        if($stmt->execute()) {
            //event updated
            $success = true;
        }
        $stmt->close();
    }  

    // send success value back to the front
    echo json_encode(array("success" => $success));

?>