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

    // Decode the JSON data into an associative array
    $validSeeds = [];
    $seeds = json_decode(file_get_contents($json_url), true);
    foreach ($seeds["seeds"] as $seed) {
        if ($seed['rarity'] == $rarity) {
            $validSeeds[] = $seed;
        }
    }

    //pick a random element from the array and return that metal 
    $choosenSeed = $validSeeds[rand(0, count($validSeeds) - 1)];
    echo json_encode($choosenSeed);
    return $choosenSeed;
}