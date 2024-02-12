<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rarity = null;
    if (isset($_GET['rarity'])) {
        $rarity = $_GET['rarity'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $rarity = $parameters->rarity;
    }

    $json_url = "../Inventories/seeds.json";

    // find all valid seeds
    $validSeeds = [];
    $seeds = json_decode(file_get_contents($json_url), true);
    foreach ($seeds["seeds"] as $seed) {
        if ($seed['rarity'] == $rarity) {
            $validSeeds[] = $seed;
        }
    }

    // pick a random element from the array and return that seed 
    $chosenSeed = $validSeeds[rand(0, count($validSeeds) - 1)];
    return $chosenSeed;
}