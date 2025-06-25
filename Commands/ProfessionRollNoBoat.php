<?php

$connection = require 'config.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $roll = null;
    $tier = null;
    $job = null;
    if (isset($_GET['roll'])) {
        $roll = $_GET['roll'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $roll = $parameters->roll;
    }

    if (isset($_GET['tier'])) {
        $tier = $_GET['tier'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $tier = $parameters->tier;
    }

    if (isset($_GET['job'])) {
        $job = $_GET['job'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $job = $parameters->job;
    }
    
    $startingRoll = $roll;

    //add tier bonus if any
    if ($tier > 3) {
        $roll += (min($tier, 7) - 3) * 10;
    }

    //find bonus from json
    $json_url = "../d100Tables/$job.json";
    $bonuses = json_decode(file_get_contents($json_url), true);
    $message = "";
    foreach ($bonuses as $bonus) {
        //check for proper rarity && creature type
        if ($roll >= $bonus['roll']['min'] && $roll <= $bonus['roll']['max']) {
            $message = $bonus['bonus'];
            break;
        }
    }
    //return bonus message. 
    echo json_encode(array('message' => "$job (base $startingRoll, @tier $tier, final Result: $roll) $message"));
}

$connection->close();