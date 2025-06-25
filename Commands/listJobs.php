<?php
$files = glob("../d100Tables/*.json");
$jobs = array_map(function($file) {
    return basename($file, ".json");
}, $files);
header('Content-Type: application/json');
echo json_encode($jobs);