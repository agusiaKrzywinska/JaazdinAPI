<?php
//validate that the database exists
$connection = require 'config.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

//todo make this update all docks to have proper progress tracked. 
