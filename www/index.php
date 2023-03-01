<?php
require __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../src/AI.php';
require_once __DIR__ . '/../src/YoutubeAPI.php';

$AI = new AI();
$Youtube = new YoutubeAPI();

$output = "gm";

echo json_encode($output);