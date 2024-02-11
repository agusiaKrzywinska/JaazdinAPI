<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $parameters = json_decode(file_get_contents("php://input"));
    $metalRarity = $parameters->rarity;

    $json_url = "../Inventories/weapons.json";

    // Decode the JSON data into an associative array
    $armors = json_decode(file_get_contents($json_url), true);

    //pick a random element from the array and return that metal 
    $weaponChoosen = $armors["weapons"][rand(0, count($armors["weapons"]) - 1)];

    //pick a random metal based on the rarity. 
    $weaponChoosen['metal'] = require 'GenerateMetal.php';

    echo json_encode($weaponChoosen);
    return $weaponChoosen;
}