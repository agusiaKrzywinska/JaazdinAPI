<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $parameters = json_decode(file_get_contents("php://input"));
    $rarity = $parameters->rarity;

    $json_url = "metals.json";

    // Decode the JSON data into an associative array
    $validMetals = [];
    $metals = json_decode(file_get_contents($json_url), true);
    foreach ($metals["metals"] as $metal) {
        if($metal->rarity == $rarity) 
        {
            $validMetals[] = $metal;
        }
    }

    //pick a random element from the array and return that metal 
    $metalChoosen = $validMetals[rand(0, count($validMetals) -1)];
    echo json_encode($metalChoosen);
}