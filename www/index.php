<?php
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . '/../src/AI.php';
require_once __DIR__ . '/../src/YoutubeAPI.php';

$AI = new AI();
$Youtube = new YoutubeAPI($_ENV['YOUTUBE_API_KEY']);
$Youtube->_videoId = "nmu4FE79y_8";
$Youtube->_method = "videos";
$json = $Youtube->getYoutubeJson();

$details = json_decode($json);

$description = $details->items[0]->snippet->description;

$AI->setPrompt("asdfas");
$AI->getResponseFromOpenAi();