<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['table'])) {
        $table = $_GET['table'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $table = $parameters->table;
    }

    $json_url = "../Inventories/magicItems.json";

    // pick a random armor
    $magicItems = json_decode(file_get_contents($json_url), true);
    $chosenMagicItem = null;
    $tableRoll = rand(0, 100);
    foreach ($magicItems["magicItems"] as $item) {

        if ($item['table'] == $table && $tableRoll >= $item['roll']['min'] && $tableRoll <= $item['roll']['max']) {
            $chosenMagicItem = $item;
            break;
        }
    }

    echo json_encode($chosenMagicItem);
}