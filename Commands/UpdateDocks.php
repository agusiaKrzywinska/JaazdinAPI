<?php
//validate that the database exists
$connection = require 'config.php';

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
            case "magicItems":
                $goods = generateMagicItems();
                break;
            case "reagents":
                $goods = generateReagents();
                break;
            case "poisonsPotions":
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

    //calling show boats and returning their message as ours. 
    $url = "http://jaazdinapi.mygamesonline.org/Commands/ShowBoats.php";
    $options = [
        'http' => [
            'header' => "Content-type: application/json",
            'method' => 'GET'
        ],
    ];
    $context = stream_context_create($options);
    $contents = file_get_contents($url, false, $context);

    echo $contents;
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

function generateWeaponry()
{
    $goods = [];
    $startingArmors = 2;
    $startingWeapons = 2;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    $metalsInUse = array("NA" => 0);

    for ($i = 0; $i < $startingArmors; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Very Rare']++;
        } elseif ($randNumber == 3) {
            $typesToSpawn["Rare"]++;
        } else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    //generate all armors
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GenerateArmor.php?rarity=$finalRarity";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempArmor = json_decode($contents);
            //finding out which metal it is using
            if (array_key_exists($tempArmor->metal->name, $metalsInUse) == false) {
                $metalsInUse[$tempArmor->metal->name] = rand($tempArmor->metal->price->min, $tempArmor->metal->price->max);
            }
            //convert weapons to shipment items
            $tempGood = array('name' => $tempArmor->metal->name . " " . $tempArmor->name, 'quantity' => 1, 'price' => round(($metalsInUse[$tempArmor->metal->name] * $tempArmor->plates + $tempArmor->price) * 1.33));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }
        }
    }

    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $startingWeapons; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Very Rare']++;
        } elseif ($randNumber == 3) {
            $typesToSpawn["Rare"]++;
        } else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    //generate all weapons
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GenerateWeapon.php?rarity=$finalRarity";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempWeapon = json_decode($contents);
            if (array_key_exists($tempWeapon->metal->name, $metalsInUse) == false) {
                $metalsInUse[$tempWeapon->metal->name] = rand($tempWeapon->metal->price->min, $tempWeapon->metal->price->max);
            }
            //convert weapons to shipment items
            $tempGood = array('name' => $tempWeapon->metal->name . " " . $tempWeapon->name, 'quantity' => 1, 'price' => round(($metalsInUse[$tempWeapon->metal->name] * $tempWeapon->plates + $tempWeapon->price) * 1.33));
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
    $goods = [];
    $totalPets = 5;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $totalPets; $i++) {
        $randNumber = rand(1, 20);
        if ($randNumber >= 19) {
            $typesToSpawn['Legendary']++;
        } elseif ($randNumber >= 16) {
            $typesToSpawn["Very Rare"]++;
        } elseif ($randNumber >= 11) {
            $typesToSpawn["Rare"]++;
        } elseif ($randNumber >= 6) {
            $typesToSpawn["Uncommon"]++;
        } else {
            $typesToSpawn["Common"]++;
        }
    }

    $invalidCombinations = array("Common" => array(), "Uncommon" => array(), "Rare" => array("Ooze"), "Very Rare" => array("Beast", "Ooze", "Dragon"), "Legendary" => array("Beast", "Ooze"));
    //generating all the beast types. 
    $currentPetId = 0;
    $petTypes = [];
    while ($currentPetId < $totalPets) {
        $tempPetType = null;
        $randNumber = rand(1, 20);
        if ($randNumber == 20) {
            $tempPetType = "Dragon";
        } elseif ($randNumber >= 18) {
            $tempPetType = "Ooze";
        } elseif ($randNumber >= 14) {
            $tempPetType = "Aberration";
        } elseif ($randNumber >= 9) {
            $tempPetType = "Monstrosity";
        } else {
            $tempPetType = "Beast";
        }
        //getting the rarity of the type
        $currentRarity = "";
        $temp = 0;
        foreach ($typesToSpawn as $rarity => $amount) {
            for ($i = 0; $i < $amount; $i++) {
                if ($temp == $currentPetId) {
                    $currentRarity = $rarity;
                }
                $temp++;
            }
        }

        //validating that it is a valid combination
        if (in_array($tempPetType, $invalidCombinations["$currentRarity"])) {
            continue;
        }

        $currentPetId++;
        $petTypes[] = $tempPetType;

    }

    //generate all pets
    $currentPetId = 0;
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $finalCreatureType = $petTypes[$currentPetId];
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GeneratePet.php?rarity=$finalRarity&creatureType=$finalCreatureType";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempPet = json_decode($contents);

            //convert weapons to shipment items
            $tempGood = array('name' => $tempPet->name, 'quantity' => 1, 'price' => rand($tempPet->price->min, $tempPet->price->max));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }

            $currentPetId++;
        }
    }
    return $goods;
}

function generateMeals()
{
    $goods = [];
    $startingCommon = 4;
    $startingUncommon = 4;
    $startingRare = 4;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $startingCommon; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Uncommon']++;
        } else {
            $typesToSpawn["Common"]++;
        }
    }

    for ($i = 0; $i < $startingUncommon; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Rare']++;
        } else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    for ($i = 0; $i < $startingRare; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Very Rare']++;
        } else {
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

    for ($i = 0; $i < $startingUncommon; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Rare']++;
        } else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    for ($i = 0; $i < $startingRare; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Very Rare']++;
        } else {
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

    for ($i = 0; $i < $startingUncommon; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Rare']++;
        } else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    for ($i = 0; $i < $startingRare; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Very Rare']++;
        } else {
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

function generateMagicItems()
{
    $goods = [];
    $startingA = 5;
    $startingB = 3;
    $typesToSpawn = array("A" => 0, "B" => 0, "C" => 0, "D" => 0, );

    for ($i = 0; $i < $startingA; $i++) {
        $randNumber = rand(1, 6);
        if ($randNumber == 6) {
            $typesToSpawn['C']++;
        } elseif ($randNumber == 5) {
            $typesToSpawn["B"]++;
        } else {
            $typesToSpawn["A"]++;
        }
    }

    for ($i = 0; $i < $startingB; $i++) {
        $randNumber = rand(1, 6);
        if ($randNumber == 6) {
            $typesToSpawn['D']++;
        } elseif ($randNumber == 5) {
            $typesToSpawn["C"]++;
        } else {
            $typesToSpawn["B"]++;
        }
    }

    //generate all magic items
    foreach ($typesToSpawn as $table => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GenerateMagicItem.php?table=$table";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempMagicItem = json_decode($contents);
            //convert magic items to shipment items
            $tempGood = array('name' => $tempMagicItem->name, 'quantity' => 1, 'price' => rand($tempMagicItem->price->min, $tempMagicItem->price->max));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }
        }
    }

    return $goods;
}

function generateSeeds()
{
    $goods = [];
    $startingCommon = 2;
    $startingUncommon = 2;
    $startingRare = 2;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $startingCommon; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 3) {
            $typesToSpawn['Uncommon']++;
        } elseif ($randNumber == 4) {
            $typesToSpawn['Rare']++;
        } else {
            $typesToSpawn["Common"]++;
        }
    }

    for ($i = 0; $i < $startingUncommon; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 3) {
            $typesToSpawn['Rare']++;
        } elseif ($randNumber == 4) {
            $typesToSpawn['Very Rare']++;
        } else {
            $typesToSpawn["Uncommon"]++;
        }
    }

    for ($i = 0; $i < $startingRare; $i++) {
        $randNumber = rand(1, 4);
        if ($randNumber == 4) {
            $typesToSpawn['Legendary']++;
        } elseif ($randNumber == 3) {
            $typesToSpawn['Very Rare']++;
        } else {
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

function generateReagents()
{
    $goods = [];
    $totalReagents = 4;
    $typesToSpawn = array("Common" => 0, "Uncommon" => 0, "Rare" => 0, "Very Rare" => 0, "Legendary" => 0);

    for ($i = 0; $i < $totalReagents; $i++) {
        $randNumber = rand(1, 20);
        if ($randNumber == 20) {
            $typesToSpawn['Legendary']++;
        } elseif ($randNumber >= 16) {
            $typesToSpawn["Very Rare"]++;
        } elseif ($randNumber >= 13) {
            $typesToSpawn["Rare"]++;
        } elseif ($randNumber >= 3) {
            $typesToSpawn["Uncommon"]++;
        } else {
            $typesToSpawn["Common"]++;
        }
    }

    //generating all the beast types. 
    $currentPetId = 0;
    $petTypes = [];
    for ($i = 0; $i < $totalReagents; $i++) {
        $tempPetType = null;
        $randNumber = rand(1, 12);
        switch ($randNumber) {
            case 1:
                $tempPetType = "Aberration";
                break;
            case 2:
                $tempPetType = "Celestial";
                break;
            case 3:
                $tempPetType = "Construct";
                break;
            case 4:
                $tempPetType = "Dragon";
                break;
            case 5:
                $tempPetType = "Elemental";
                break;
            case 6:
                $tempPetType = "Fey";
                break;
            case 7:
                $tempPetType = "Fiend";
                break;
            case 8:
                $tempPetType = "Giant";
                break;
            case 9:
                $tempPetType = "Monstrosity";
                break;
            case 10:
                $tempPetType = "Ooze";
                break;
            case 11:
                $tempPetType = "Plant";
                break;
            case 12:
                $tempPetType = "Undead";
                break;
        }
        $petTypes[] = $tempPetType;

    }

    //generate all reagents
    $currentPetId = 0;
    foreach ($typesToSpawn as $rarity => $amount) {
        for ($i = 0; $i < $amount; $i++) {
            $finalRarity = str_replace(' ', '%20', $rarity);
            $finalCreatureType = $petTypes[$currentPetId];
            $url = "http://jaazdinapi.mygamesonline.org/Commands/GenerateReagent.php?rarity=$finalRarity&creatureType=$finalCreatureType";
            $options = [
                'http' => [
                    'header' => "Content-type: application/json",
                    'method' => 'GET'
                ],
            ];
            $context = stream_context_create($options);
            $contents = file_get_contents($url, false, $context);
            $tempReagent = json_decode($contents);

            //convert weapons to shipment items
            $tempGood = array('name' => $tempReagent->name, 'quantity' => 1, 'price' => rand($tempReagent->price->min, $tempReagent->price->max));
            if (array_key_exists($tempGood['name'], $goods)) {
                $goods[$tempGood['name']]['quantity']++;
            } else {
                $goods[$tempGood['name']] = $tempGood;
            }
            $currentPetId++;
        }
    }
    return $goods;
}