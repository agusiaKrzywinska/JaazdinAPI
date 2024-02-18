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

    //add tier bonus if any
    if ($tier > 3) {
        $roll += ($tier - 3) * 10;
    }

    //add boat bonus if its in town and applied profession
    $sqlGetBoatsInTown = "SELECT * FROM boats WHERE isRunning = 1 AND isInTown = 1;";
    $result = $connection->query($sqlGetBoatsInTown);
    while ($row = $result->fetch_assoc()) {
        $jobsAffected = explode(' ', $row["jobsAffected"]);
        $isTier2 = $row["isTier2"];
        for ($i = 0; $i < count($jobsAffected); $i++) {
            if ($jobsAffected[$i] == $job) {
                $roll += $isTier2 == 1 ? 15 : 10;
            }
        }
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
    echo json_encode(array('message' => "($roll) $message"));
}

$connection->close();