<?php
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

require_once __DIR__ . '/../src/AI.php';
require_once __DIR__ . '/../src/YoutubeAPI.php';

$AI = new AI();
$Youtube = new YoutubeAPI($_ENV['YOUTUBE_API_KEY']);
$Youtube->_videoId = $_GET['youtubeId'];

if ($Youtube->_videoId == '') {
    $response['valid'] = false;
    $response['message'] = "No Video Id Provided";
} else {
    $response['valid'] = true;

    $Youtube->_method = "videos";
    $json = $Youtube->getYoutubeJson();

    $details = json_decode($json);

    $description = $details->items[0]->snippet->description;

    $prompt = "tell me who the white player is and who the black player is based on the following text.  In general, the first player is white and the second player is black.  give me their names in this format:  [white player first name, last name] - [black player first name, last name].  Do not include any other info in the response except their names in that format.
###\n";
    $prompt .= $description;

    $AI->setPrompt($prompt);
    $response['message'] = $AI->getResponseFromOpenAi();
}

echo json_encode($response);