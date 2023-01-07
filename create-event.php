<?php
    session_start();
    ini_set("session.cookie_httponly", 1);
    
    // specify type of data being received
    header("Content-Type: application/json");

    // get data from the front
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);

    // access data
    $user_id = $_SESSION['user_id'];
    $csrf_token = $json_obj['token'];
    $event_title = $json_obj['title'];
    $event_date = $json_obj['date'];
    $event_time = $json_obj['time'];
    $event_descript = $json_obj['descript'];
    $event_import = $json_obj['import'];

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
    if(!preg_match('/^$/', $event_title) && !preg_match('/^$/', $event_date) && !preg_match('/^$/', $event_time)) {
        //make sure user id is an int
        // $user_id = (int)$user_id;

        //connect to database
        require './database.php';

        //add event to events table
        $stmt = $mysqli->prepare("INSERT INTO events (event_name, start_d, start_time, descript, importance) values (?, ?, ?, ?, ?)");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }

        $stmt->bind_param('sssss', $event_title, $event_date, $event_time, $event_descript, $event_import);
        if($stmt->execute()) {
            //event added to events table
            $success = true;
        }
        $stmt->close();

        // get largest (aka most recent) id
        $stmt = $mysqli->prepare("SELECT MAX(id) FROM events");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        if(!$stmt->execute()) {
            //event added to events table
            $success = false;
        }
        $stmt->bind_result($event_id);
        $stmt->fetch();
        $stmt->close();
    }  

    // send success value and event id back to the front
    echo json_encode(array("success" => $success, "eventID" => htmlentities($event_id)));
?>