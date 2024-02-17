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

    $json_url = "../Inventories/reagents.json";

    // find all valid reagents
    $validReagents = [];
    $reagents = json_decode(file_get_contents($json_url), true);
    foreach ($reagents["reagents"] as $reagent) {
        //check for proper rarity
        if ($reagent['rarity'] == $rarity && $reagent['type'] == $type) {
            $validReagents[] = $reagent;
        }
    }

    // pick a random element from the array and return that metal 
    $chosenReagent = $validReagents[rand(0, count($validReagents) - 1)];
    echo json_encode($chosenReagent);
}
