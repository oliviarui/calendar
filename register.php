<?php
    // specify type of data being received
    header("Content-Type: application/json");

    // get data from the front
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);

    // access username and password from the data
    $new_user = $json_obj['user'];
    $new_pass = $json_obj['pass'];

    $success = false;

    //filter input, check for empty username and password and special characters in username
    if(!preg_match('/^$/', $new_user) && !preg_match('/^$/', $new_pass) && preg_match('/^[a-zA-Z0-9_.-]*$/', $new_user)) {
        //username and password have passed filter

        //connect to database
        require './database.php';

        // check if the user already exists
        // Use prepared statement
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE username=?");
        // Bind the parameter
        $stmt->bind_param('s', $new_user);
        $stmt->execute();
        // Bind the results
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();

        if($cnt == 0){
            // user does not already exist

            //SECURITY: salt and hash password
            $salted_pass = password_hash($new_pass, PASSWORD_DEFAULT);

            //add user to database
            $stmt = $mysqli->prepare("INSERT INTO users (username, hash_pass) values (?, ?)");
            if(!$stmt){
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }

            $stmt->bind_param('ss', $new_user, $salted_pass);
            if($stmt->execute()) {
                //user and password added to database, update success value
                $success = true;
            }
            $stmt->close();
        }         
    }  

    // send success value back to the front
    echo json_encode(array("success" => $success));
?>