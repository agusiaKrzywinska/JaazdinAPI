<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $parameters = json_decode(file_get_contents("php://input"));
    $metalRarity = $parameters->rarity;

    $json_url = "../Inventories/weapons.json";

    // pick a random weapon
    $weapons = json_decode(file_get_contents($json_url), true);
    $weaponChosen = $weapons["weapons"][rand(0, count($weapons["weapons"]) - 1)];

    // pick a random metal based on the rarity. 
    $weaponChosen['metal'] = require 'GenerateMetal.php';

    echo json_encode($weaponChosen);
    return $weaponChosen;
}