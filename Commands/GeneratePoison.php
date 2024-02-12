<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rarity = null;
    if (isset($_GET['rarity'])) {
        $rarity = $_GET['rarity'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $rarity = $parameters->rarity;
    }

    $json_url = "../Inventories/poisons.json";
    
    // find all valid poisons
    $validPoison = [];
    $poisons = json_decode(file_get_contents($json_url), true);
    foreach ($poisons["poisons"] as $poison) {
        if ($poison['rarity'] == $rarity) {
            $validPoison[] = $poison;
        }
    }

    //pick a random element from the array and return that poison 
    $chosenPoison = $validPoison[rand(0, count($validPoison) - 1)];
    return $chosenPoison;
}