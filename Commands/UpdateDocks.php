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

    while ($row = $result->fetch_assoc()) {
        $type = $row["tableToGenerate"];

        $sqlCreateTable = "CREATE TABLE $type (
            id int NOT NULL AUTO_INCREMENT,
            itemName varchar(40),
            price int,
            quantity int,
            PRIMARY KEY (id)
        );";
        $resultLoop = $connection->query($sqlCreateTable);

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

        foreach ($goods as $name => $data) {
            //TODO add all the goods into the table that was created. 
            $price = $data["price"];
            $quantity = $data["quantity"];
            $sqlCreateTable = "INSERT INTO $type (`itemName`, `price`, `quantity`) VALUES('$name',$price,$quantity);";
            $resultLoop = $connection->query($sqlCreateTable);
        }
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
    $goods = [];
    //calculating the metals to generate. 
    $startingUncommon = 5;
    $startingRare = 3;
    $typesToSpawn = array("Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $startingUncommon; $i++) {
        $randNumber = rand(1, 6);
        if ($randNumber == 5) {
            $typesToSpawn["Rare"]++;
        } else if ($randNumber == 6) {
            $typesToSpawn["Very Rare"]++;
        } else {
            $typesToSpawn["Uncommon"]++;
        }
    }
    for ($i = 0; $i < $startingRare; $i++) {
        $randNumber = rand(1, 6);
        if ($randNumber == 5) {
            $typesToSpawn["Very Rare"]++;
        } else if ($randNumber == 6) {
            $typesToSpawn["Legendary"]++;
        } else {
            $typesToSpawn["Rare"]++;
        }
    }
    //generate all metals
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GenerateMetal.php?rarity=$finalRarity";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempMetal = json_decode($contents);
            //convert metals to shipment items
            $tempGood = array('name' => $tempMetal->name, 'quantity' => 1, 'price' => rand($tempMetal->price->min, $tempMetal->price->max));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }
        }
    }
    return $goods;
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