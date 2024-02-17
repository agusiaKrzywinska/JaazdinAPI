<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rarity = null;
    $type = null;
    if (isset($_GET['rarity'])) {
        $rarity = $_GET['rarity'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $rarity = $parameters->rarity;
    }

    if (isset($_GET['creatureType'])) {
        $type = $_GET['creatureType'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $type = $parameters->creatureType;
    }

    //convert rarity to cr range. 
    $crRarityTable = array("Common" => array(0, 1), "Uncommon" => array(2, 5), "Rare" => array(6, 8), "Very Rare" => array(9, 11), "Legendary" => array(12, 13));

    $json_url = "../Inventories/pets.json";

    // find all valid metals
    $validPets = [];
    $pets = json_decode(file_get_contents($json_url), true);
    foreach ($pets["pets"] as $pet) {
        //check for proper rarity && creature type
        if ($pet['cr'] >= $crRarityTable["$rarity"][0] && $pet['cr'] <= $crRarityTable["$rarity"][1] && $pet['type'] == $type) {
            $validPets[] = $pet;
        }
    }

    // pick a random element from the array and return that metal 
    $chosenPet = $validPets[rand(0, count($validPets) - 1)];
    echo json_encode($chosenPet);
}