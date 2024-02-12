<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $parameters = json_decode(file_get_contents("php://input"));
    $metalRarity = $parameters->rarity;

    $json_url = "../Inventories/armors.json";

    // pick a random armor
    $armors = json_decode(file_get_contents($json_url), true);
    $chosenArmor = $armors["armors"][rand(0, count($armors["armors"]) - 1)];

    //pick a random metal based on the rarity. 
    $chosenArmor['metal'] = require 'GenerateMetal.php';

    return $chosenArmor;
}