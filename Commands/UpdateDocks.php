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

    //TODO check all boats that just got into town and generate a table for merch for them 

    //TODO check all boats that just left town to delete their merch 

    echo json_encode(array("Boats were updated"));
} else {
    echo json_encode(array("Was not a post request"));
}

$connection->close();
