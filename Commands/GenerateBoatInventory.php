<?php

//validate that the database exists
$connection = require 'config.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //TODO get data passed through about which boat to generate which cargo. 

} else {
    echo "";
}