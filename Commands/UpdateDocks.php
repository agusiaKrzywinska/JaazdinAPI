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
    while ($row = $result->fetch_assoc()) {
        $type = $row["tableToGenerate"];
        switch ($type) {
            case "metals":
                $goods = generateMetals();
                break;
            case "pets":
                $goods = generatePets();
                break;
            case "meals":
                $goods = generateMeals();
                break;
            case "weaponry":
                $goods = generateWeaponry();
                break;
            case "magic items":
                $goods = generateMagicItems();
                break;
            case "reagents":
                $goods = generateReagents();
                break;
            case "poisons potions":
                $goods = generatePoisonsPotions();
                break;
            case "plants":
                $goods = generateSeeds();
                break;
        }        
        $sqlCreateTable = "CREATE TABLE $type (
            id int NOT NULL AUTO_INCREMENT,
            itemName varchar(40),
            price int,
            quantity int,
            PRIMARY KEY (id)
        );";
        $resultLoop = $connection->query($sqlCreateTable);
    }

    //getting all boats that have just left town.
    $sqlGetBoatsThatLeftTown = "SELECT * FROM boats WHERE isRunning = 1 AND isInTown = 0 AND (
        (isTier2 = 0 AND weeksLeft = waitTime) OR
        (isTier2 = 1 AND weeksLeft = waitTime - 1));";

    $result = $connection->query($sqlGetBoatsThatLeftTown);
    while ($row = $result->fetch_assoc()) {
        $type = $row["tableToGenerate"];
        $sqlDropTable = "DROP TABLE $type;";
        $resultLoop = $connection->query($sqlDropTable);
    }

    echo json_encode(array("Boats were updated"));
}

$connection->close();

function generateMetals()
{
    return null;
}

function generatePets()
{
    return null;
}

function generateMeals()
{
    return null;
}

function generatePoisonsPotions()
{
    return null;
}

function generateReagents()
{
    return null;
}

function generateMagicItems()
{
    return null;
}

function generateWeaponry()
{
    return null;
}

function generateSeeds()
{
    return null;
}