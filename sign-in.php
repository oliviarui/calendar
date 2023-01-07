<?php
    session_start();
    ini_set("session.cookie_httponly", 1);

    // specify type of data being received
    header("Content-Type: application/json");

    // get data from the front
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);

    // access username and password from the data
    $input_user = $json_obj['user'];
    $input_pass = $json_obj['pass'];

    $success = false;

    //filter input, check for empty username and password and special characters in username
    if(!preg_match('/^$/', $input_user) && !preg_match('/^$/', $input_pass) && preg_match('/^[a-zA-Z0-9_.-]*$/', $input_user)) {
        //username and password have passed filter

        //connect to database
        require 'database.php';

        // Use a prepared statement
        $stmt = $mysqli->prepare("SELECT COUNT(*), id, username, hash_pass FROM users WHERE username=?");

        // Bind the parameter
        $stmt->bind_param('s', $input_user);
        if(!$stmt->execute()) {
            $success = 'Username does not exist. Try again or register for an account.';
        }

        // Bind the results
        $stmt->bind_result($cnt, $user_id, $username, $hash_pass);
        $stmt->fetch();
        $stmt->close();
        // compare the submitted password to the actual password hash
        if($cnt == 1 && password_verify($input_pass, $hash_pass)){
            // password is right

            // start session
            session_start();
            // set session username
            $_SESSION['username'] = $username;
            // set session user id
            $_SESSION['user_id'] = $user_id;
            // set user agent
            @$_SESSION['useragent'] = $_SERVER['HTTP_USER_AGENT'];
            // set session token
            $_SESSION['token'] = bin2hex(random_bytes(32));
            $success = true;
        }         
    }  

    // send success value back to the front
    echo json_encode(array("success" => $success));
?>