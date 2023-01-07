<?php
    session_start();
    ini_set("session.cookie_httponly", 1);

     // specify type of data being received
     header("Content-Type: application/json");

     // get data from the front
     $json_str = file_get_contents('php://input');
     $json_obj = json_decode($json_str, true);
 
     // access username and password from the data
     $current_date = $json_obj['date'];
     $input_user = $_SESSION['user_id'];
     $csrf_token = $json_obj['token'];

     $has_events = false;
     $return_data = array();

    // check csrf token
    if (!hash_equals($_SESSION['token'], $csrf_token)) {
        // token is not matching
        echo json_encode(array(
            "error" => "Request forgery detected."
        ));
        exit;
    }
    // token matches

     //check that date format is correct
     if(preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $current_date)) {
        //connect to database
        require './database.php';

        $user_events = array();

        // get all event ids associated with current user and put into an array
        $stmt = $mysqli->prepare("SELECT event_id FROM event_permissions WHERE user=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        //only get events that match the current date and the inputted user
        $stmt->bind_param('i', $input_user);
        $stmt->execute();
        $stmt->bind_result($event_id);
        while($stmt->fetch()) {
            array_push($user_events, $event_id);
        }
        $stmt->close();

        // for each event id get all event info where the id is equal and the start date is equal
        foreach($user_events as $id) {
            // get all event info
            $stmt = $mysqli->prepare("SELECT event_name, start_time, importance FROM events WHERE start_d=? AND id=?");
            if(!$stmt){
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            //only get events that match the current date and the inputted user
            $stmt->bind_param('si', $current_date, $id);
            $stmt->execute();
            $stmt->bind_result($event_name, $event_start_time, $event_import);
            while($stmt->fetch()) {
                $has_events = true;
                $event_info = array(htmlentities($id), htmlentities($event_name), htmlentities($event_start_time), htmlentities($event_import));
                array_push($return_data, $event_info);
            }
            $stmt->close();
        }
    }
    echo json_encode(array("hasEvents" => $has_events, "eventsInfo" => $return_data));

?>