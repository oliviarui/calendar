<?php
     //connect to group database
     $mysqli = new mysqli('localhost', 'wustl_inst', 'wustl_pass', 'mod5');

     if($mysqli->connect_errno) {
         printf("Connection Failed: %s\n", $mysqli->connect_error);
         exit;
     }
?>