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
            //adding all the goods into the table that was created. 
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

//todo generate weaponry
function generateWeaponry()
{
    return null;
}

//todo generate pets. 
function generatePets()
{
    return null;
}

function generateMeals()
{
    $goods = [];
    $startingCommon = 4;
    $startingUncommon = 4;
    $startingRare = 4;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $startingCommon; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 4) {
            $typesToSpawn['Uncommon']++; 
        }
        else {
            $typesToSpawn["Common"]++;
        }
    }

    for ($i = 0; $i < $startingUncommon; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 4) {
            $typesToSpawn['Rare']++; 
        }
        else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    for ($i = 0; $i < $startingRare; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 4) {
            $typesToSpawn['Very Rare']++; 
        }
        else {
            $typesToSpawn["Rare"]++;
        }
    }

    //generate all meals
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GenerateMeal.php?rarity=$finalRarity";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempMeal = json_decode($contents);
            //convert metals to shipment items
            $tempGood = array('name' => $tempMeal->name, 'quantity' => 1, 'price' => rand($tempMeal->price->min, $tempMeal->price->max));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }
        }
    }

    return $goods;
}

function generatePoisonsPotions()
{
    $goods = [];
    $startingUncommon = 3;
    $startingRare = 2;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $startingUncommon; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 4) {
            $typesToSpawn['Rare']++; 
        }
        else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    for ($i = 0; $i < $startingRare; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 4) {
            $typesToSpawn['Very Rare']++; 
        }
        else {
            $typesToSpawn["Rare"]++;
        }
    }

    //generate all poisons
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GeneratePoison.php?rarity=$finalRarity";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempPoison = json_decode($contents);
            //convert metals to shipment items
            $tempGood = array('name' => $tempPoison->name, 'quantity' => 1, 'price' => rand($tempPoison->price->min, $tempPoison->price->max));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }
        }
    }

    $startingUncommon = 2;
    $startingRare = 2;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $startingUncommon; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 4) {
            $typesToSpawn['Rare']++; 
        }
        else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    for ($i = 0; $i < $startingRare; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 4) {
            $typesToSpawn['Very Rare']++; 
        }
        else {
            $typesToSpawn["Rare"]++;
        }
    }

    //generate all potions
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GeneratePotion.php?rarity=$finalRarity";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempPotion = json_decode($contents);
            //convert metals to shipment items
            $tempGood = array('name' => $tempPotion->name, 'quantity' => 1, 'price' => rand($tempPotion->price->min, $tempPotion->price->max));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }
        }
    }

    return $goods;
}

//todo generate magic items
function generateMagicItems()
{
    return null;
}

function generateSeeds()
{
    $goods = [];
    $startingCommon = 2;
    $startingUncommon = 2;
    $startingRare = 2;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $startingCommon; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 3) {
            $typesToSpawn['Uncommon']++; 
        }
        elseif($randNumber == 4) {
            $typesToSpawn['Rare']++; 
        }
        else {
            $typesToSpawn["Common"]++;
        }
    }

    for ($i = 0; $i < $startingUncommon; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 3) {
            $typesToSpawn['Rare']++; 
        }
        elseif($randNumber == 4) {
            $typesToSpawn['Very Rare']++; 
        }
        else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    for ($i = 0; $i < $startingRare; $i++)
    {
        $randNumber = rand(1, 4);
        if($randNumber == 4) {
            $typesToSpawn['Legendary']++; 
        }elseif($randNumber == 3) {
            $typesToSpawn['Very Rare']++; 
        }
        else {
            $typesToSpawn["Rare"]++;
        }
    }

    //generate all seeds
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GenerateSeeds.php?rarity=$finalRarity";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempSeed = json_decode($contents);
            //convert metals to shipment items
            $tempGood = array('name' => $tempSeed->name, 'quantity' => 1, 'price' => rand($tempSeed->price->min, $tempSeed->price->max));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }
        }
    }

    return $goods;
}

//todo generate reagents
function generateReagents()
{
    return null;
}