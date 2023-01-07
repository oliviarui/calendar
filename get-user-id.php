<?php
    session_start();
    ini_set("session.cookie_httponly", 1);

    // specify type of data being received
    header("Content-Type: application/json");

    // get data from the front
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);

    // access username and password from the data
    $share_username = $json_obj['user'];
    $csrf_token = $json_obj['token'];

    $success = false;

    // check csrf token
    if (!hash_equals($_SESSION['token'], $csrf_token)) {
        // token is not matching
        echo json_encode(array(
            "error" => "Request forgery detected: get-user-id."
        ));
        exit;
    }
    // token matches

    if(!preg_match('/^$/', $share_username)) {
        // user name is not empty
        // connect to database
        require './database.php';

        // Use a prepared statement
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");

        // Bind the parameter
        $stmt->bind_param('s', $share_username);
        if($stmt->execute()) {
            // got the username
            $success = true;
        }

        // Bind the results
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

        // send user id back to the front
        echo json_encode(array("success" => $success, "shareUserID" => htmlentities($user_id)));
    }
?>