<?php

$db = new mysqli($config['db_host'],$config['db_user'], $config['db_pass'], $config['db_name']);

// We connect to our database, and then if we get an error in the connection, we print the error, else, we don't do anything.

if($db->connect_errno){

    printf("Connection Failed: %s\n", $db->connect_error);
    exit();

}else{
        // We are connected ! 
}

?>