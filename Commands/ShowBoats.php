<?php

$connection = require 'config.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $message = '';
    //getting boats not in port.
    $sqlGetBoatsArentInTown = "SELECT * FROM boats WHERE isRunning = 1 AND isInTown = 0;";
    $result = $connection->query($sqlGetBoatsArentInTown);
    while ($row = $result->fetch_assoc()) {
        $boatName = $row["boatName"];
        $weeksLeft = $row["weeksLeft"];
        $message .= "$boatName is $weeksLeft weeks away from port.\n";
    }

    //getting boats that are in port. 
    $sqlGetBoatsInTown = "SELECT * FROM boats WHERE isRunning = 1 AND isInTown = 1;";
    $result = $connection->query($sqlGetBoatsInTown);
    while ($row = $result->fetch_assoc()) {
        $boatName = $row["boatName"];
        $weeksLeft = $row["weeksLeft"];
        $jobsAffected = $row["jobsAffected"];
        $shipmentType = $row["tableToGenerate"];
        $isTier2 = $row["isTier2"];
        $tier2Ability = $row["tier2Ability"];

        $message .= "$boatName is in town for $weeksLeft weeks bringing:\n";

        //getting the shipment items and printing those
        $sqlGetShipment = "SELECT * FROM $shipmentType;";
        $resultLoop = $connection->query($sqlGetShipment);
        while ($good = $resultLoop->fetch_assoc()) {
            $goodName = $good["itemName"];
            $price = $good["price"];
            $quantity = $good["quantity"];
            $message .= "x$quantity $goodName ($price gp)\n";
        }
        $message .= "\n";
        $message .= "While in town the merchants and sailors will help with $jobsAffected and grant players a bonus wage die while working those jobs this week";
        if ($isTier2 == 1) {
            $message .= " and $tier2Ability\n\n";
        } else {
            $message .= ". \n\n";
        }
    }

    echo json_encode(array('message'=>$message));
}

$connection->close();
