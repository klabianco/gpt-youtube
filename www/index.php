<?php
require __DIR__ . '/../../vendor/autoload.php';

$http_origin = $_SERVER['HTTP_ORIGIN'];

$allowed_domains = array(
  'https://plantrip.io',
  'http://localitineraries.o9p.net',
  'https://webpage.o9p.net',
  'http://localwebpage.o9p.net'
);

if (in_array($http_origin, $allowed_domains))
{  
    header("Access-Control-Allow-Origin: $http_origin");
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

$request = explode("/", $_SERVER['REQUEST_URI']);

require_once __DIR__ . '/../src/AI.php';
require_once __DIR__ . '/../src/YoutubeAPI.php';

if ($request[1] == "api") {
    $AI = new AI();
    $response['valid'] = false;

    if ($request[2] == "youtube") {
        $Youtube = new YoutubeAPI($_ENV['YOUTUBE_API_KEY']);
        $Youtube->_videoId = $_POST['youtubeId'];

        $method = $request[3];

        if ($Youtube->_videoId == '') {
            $response['message'] = "No Video Id Provided";
        } else {
            if ($method == "getWhiteBlackPlayerNamesFromDescription") {
                $response['valid'] = true;

                $description = $Youtube->getDescriptionTextFromVideoId();

                $prompt = "tell me who the white player is and who the black player is based on the following text.  In general, the first player is white and the second player is black.  give me their names in this format:  [white player first name, last name] - [black player first name, last name].  Do not include any other info in the response except their names in that format.  The names should not contain commas.
    ###\n";
                $prompt .= $description;

                $AI->setPrompt($prompt);
                $response['message'] = $AI->getResponseFromOpenAi();
            } else if ($method == "getOpeningFromDescription") {
                $response['valid'] = true;
                $Youtube->_method = "videos";
                $description = $Youtube->getDescriptionTextFromVideoId();

                $prompt = 'in chess, given this series of moves in the description below tell me what is the opening is called. Only give me the opening name witn no other text.   Format it like "Opening Name" without the quotations.
            ###\n';
                $prompt .= $description;

                $AI->setPrompt($prompt);
                $response['message'] = $AI->getResponseFromOpenAi();
            } else {
                $response['message'] = "Invalid Method";
            }
        }
    } else if ($request[2] == "travel") {
        $method = $request[3];

        if ($method == "getItinerary") {
            $prompt = $_POST['prompt'];

            $response['valid'] = true;

            $AI->setPrompt($prompt);
            $response['message'] = $AI->getResponseFromOpenAi();
        }
    } else if ($request[2] == "webpage") {
        $prompt = $_POST['prompt'];

        $AI->setPrompt($prompt);
        $response = $AI->getResponseFromOpenAi();

        echo $response;

        exit();
    }

    echo json_encode($response);
}
