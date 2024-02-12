<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rarity = null;
    if (isset($_GET['rarity'])) {
        $rarity = $_GET['rarity'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $rarity = $parameters->rarity;
    }

    $json_url = "../Inventories/metals.json";

    // find all valid metals
    $validMetals = [];
    $metals = json_decode(file_get_contents($json_url), true);
    foreach ($metals["metals"] as $metal) {
        if ($metal['rarity'] == $rarity) {
            $validMetals[] = $metal;
        }
    }

    // pick a random element from the array and return that metal 
    $metalChosen = $validMetals[rand(0, count($validMetals) - 1)];
    return $metalChosen;
}