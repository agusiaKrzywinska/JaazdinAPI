<?php

$connection = require 'config.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $message = array();
    //getting boats not in port.
    $sqlGetBoatsArentInTown = "SELECT boatName, weeksLeft FROM boats WHERE isRunning = 1 AND isInTown = 0 ORDER BY weeksLeft ASC;";
    $result = $connection->query($sqlGetBoatsArentInTown);

    $boatsNotInTown = array();
    while ($row = $result->fetch_assoc()) {
        $boatsNotInTown[] = array("boatName" => $row["boatName"], "weeksLeft" => $row["weeksLeft"]);
    }


    $boatsInTown = array();
    //getting boats that are in port. 
    $sqlGetBoatsInTown = "SELECT * FROM boats WHERE isRunning = 1 AND isInTown = 1 ORDER BY weeksLeft ASC;";
    $result = $connection->query($sqlGetBoatsInTown);
    while ($row = $result->fetch_assoc()) {
        $shipmentType = $row["tableToGenerate"];
        if ($shipmentType != "N/A") {
            //getting the shipment items and printing those
            $sqlGetShipment = "SELECT * FROM $shipmentType;";
            $resultLoop = $connection->query($sqlGetShipment);

            $goods = array();
            while ($good = $resultLoop->fetch_assoc()) {
                $goodName = $good["itemName"];
                $price = $good["price"];
                $quantity = $good["quantity"];
                $goods[] = array("name" => $goodName, "price" => $price, "quantity" => $quantity);
            }
        }

        $tier2 = $row["isTier2"] == 1 ? $row["tier2Ability"] : "";

        $boatsInTown[] = array("boatName" => $row["boatName"], "weeksLeft" => $row["weeksLeft"], "jobs" => $row["jobsAffected"], "shipment" => $goods, "tier2Ability" => $tier2);
    }

    $message = array("boatsNotInTown" => $boatsNotInTown, "boatsInTown" => $boatsInTown);

    echo json_encode($message);
}

$connection->close();
