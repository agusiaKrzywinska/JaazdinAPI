<?php
//validate that the database exists
$connection = require 'config.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //update boats to have 1 week pass. 
    $sqlUpdateBoat = "UPDATE boats SET weeksLeft = weeksLeft - 1 WHERE isRunning = 1;";
    $result = $connection->query($sqlUpdateBoat);

    //update if they are in port or not based on if they reached 0. 
    $sqlUpdateBoat = "UPDATE boats
SET weeksLeft = CASE
                    WHEN isTier2 = 1 THEN CASE
                                                WHEN isInTown = 1 THEN waitTime - 1
                                                ELSE timeInTown + 1
                                            END
                    ELSE CASE
                            WHEN isInTown = 1 THEN waitTime
                            ELSE timeInTown
                         END
                END,
    isInTown = CASE
                    WHEN isInTown = 0 THEN 1
                    ELSE 0
                END
WHERE isRunning = 1 AND weeksLeft <= 0;";

    $result = $connection->query($sqlUpdateBoat);

    //getting all boats in town for the first time.
    $sqlGetBoatsInTownFirstWeek = "SELECT * FROM boats WHERE isRunning = 1 AND isInTown = 1 AND (
        (isTier2 = 0 AND weeksLeft = timeInTown) OR
        (isTier2 = 1 AND weeksLeft = timeInTown + 1));";

    $result = $connection->query($sqlGetBoatsInTownFirstWeek);
    //TODO generate all inventories for boats in town

    //getting all boats that have just left town.
    $sqlGetBoatsThatLeftTown = "SELECT * FROM boats WHERE isRunning = 1 AND isInTown = 0 AND (
        (isTier2 = 0 AND weeksLeft = waitTime) OR
        (isTier2 = 1 AND weeksLeft = waitTime - 1));";

    $result = $connection->query($sqlGetBoatsThatLeftTown);
    //TODO remove all the merch tables which were created for boats in town. 

    echo json_encode(array("Boats were updated"));
} else {
    echo json_encode(array("Was not a post request"));
}

$connection->close();
