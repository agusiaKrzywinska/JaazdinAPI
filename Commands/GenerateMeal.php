<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rarity = null;
    if (isset($_GET['rarity'])) {
        $rarity = $_GET['rarity'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $rarity = $parameters->rarity;
    }

    $json_url = "../Inventories/meals.json";

    // Decode the JSON data into an associative array
    $validMeals = [];
    $meals = json_decode(file_get_contents($json_url), true);
    foreach ($meals["meals"] as $meal) {
        if ($meal['rarity'] == $rarity) {
            $validMeals[] = $meal;
        }
    }

    //pick a random element from the array and return that metal 
    $mealChoosen = $validMeals[rand(0, count($validMeals) - 1)];
    echo json_encode($mealChoosen);
    return $mealChoosen;
}