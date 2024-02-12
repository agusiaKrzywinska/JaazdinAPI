<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rarity = null;
    if (isset($_GET['rarity'])) {
        $rarity = $_GET['rarity'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $rarity = $parameters->rarity;
    }

    $json_url = "../Inventories/potions.json";

    // find all valid potions
    $validPotions = [];
    $potions = json_decode(file_get_contents($json_url), true);
    foreach ($potions["potions"] as $potion) {
        if ($potion['rarity'] == $rarity) {
            $validPotions[] = $potion;
        }
    }

    // pick a random element from the array and return that potion 
    $chosenPotion = $validPotions[rand(0, count($validPotions) - 1)];
    echo json_encode($chosenPotion);
}