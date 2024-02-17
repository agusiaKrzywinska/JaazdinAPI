<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['rarity'])) {
        $metalRarity = $_GET['rarity'];
    } else {
        $parameters = json_decode(file_get_contents("php://input"));
        $metalRarity = $parameters->rarity;
    }
    $metalRarity = str_replace(' ', '%20', $metalRarity);

    $json_url = "../Inventories/weapons.json";

    // pick a random weapon
    $weapons = json_decode(file_get_contents($json_url), true);
    $weaponChosen = $weapons["weapons"][rand(0, count($weapons["weapons"]) - 1)];

    // pick a random metal based on the rarity. 
    $url = "http://jaazdinapi.mygamesonline.org/Commands/GenerateMetal.php?rarity=$metalRarity";
    $options = [
        'http' => [
            'header' => "Content-type: application/json",
            'method' => 'GET'
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $weaponChosen['metal'] = json_decode($result);

    echo json_encode($weaponChosen);
}