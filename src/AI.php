<?php

use \Curl\Curl;

class AI
{
    private $_input, $_prompt, $_taskString, $_uimIds, $_User;

    public function setPrompt($prompt)
    {
        $this->_prompt = $prompt;
    }

    public function setUser($User)
    {
        $this->_User = $User;
    }

    public function getUser()
    {
        return $this->_User;
    }

    public function setUIMIds($ids)
    {
        $this->_uimIds = $ids;
    }

    public function getUIMIds()
    {
        return $this->_uimIds;
    }

    public function setTaskString($string)
    {
        $this->_taskString = $string;
    }

    public function getTaskString()
    {
        return $this->_taskString;
    }

    public function getPrompt()
    {
        return $this->_prompt;
    }

    public function hasPrompt()
    {
        if ($this->getPrompt() == '') return false;
        return true;
    }

    public function getInput()
    {
        return $this->_input;
    }

    public function setInput($input)
    {
        $this->_input = $input;
    }

    public function setTaskStringAndUIMIdsFromTasksAndReturnTaskString()
    {
        $tasks = $this->getInput();

        $taskString = '';
        $uimIds = [];

        foreach ($tasks as $task) {
            $uimId = $task['id'];
            $task = strtolower(trim($task['it']));
            $uimIds[$task] = $uimId;
            $taskString .= $task . ', ';
        }

        if ($taskString == '') return false;

        $this->setUIMIds($uimIds);
        $this->setTaskString($taskString);

        return $this->getTaskString();
    }

    public function smartSort()
    {
        $items = $this->setTaskStringAndUIMIdsFromTasksAndReturnTaskString();
        $uimIds = $this->getGroceryListItemUIDs($items);

        $P = new DoItPriority;
        $P->setUserId($this->getUser()->getId());

        $priority = 0;

        foreach ($uimIds as $id) {
            $P->setUIMId($id);
            $P->setPriority($priority);

            $P->dbUpdatePriorityForUIMAndUser();

            $priority++;
        }
    }

    public function getGroceryListItemUIDs($items)
    {
        $rules = "Reorder the following list in the most efficient way.  Only provide the ordered list. Your response list should be comma separated.";

        $prompt = "$rules Here is the list: ";
        $prompt .= $items;

        $this->setPrompt($prompt);

        $response = $this->getResponseFromOpenAi();

        /*
        $response = "Oranges, Grapes, Chicken, Chicken Stock, Beans, Spaghetti, Rice";
        
        echo "prompt: " . $prompt."\n\n";
        echo "\n\nResponse" . $response;
        */

        $items = explode(',', $response);

        $uimIds = [];
        $currentUIMIds = $this->getUIMIds();

        foreach ($items as $item) {
            $name = strtolower(trim($item));
            $uimIds[] = $currentUIMIds[$name];
        }

        if (count($uimIds) > 0) return $uimIds;
        return false;
    }

    public function getResponseFromOpenAi()
    {
        if ($this->hasPrompt()) {
            $openAIKey = $_SERVER['OPENAI_API_KEY'];

            $url = "https://api.openai.com/v1/chat/completions";
            $maxTokens = 1500;

            $curl = new Curl();
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
            $curl->setopt(CURLOPT_TIMEOUT, 60);
            
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setHeader('Authorization', 'Bearer ' . $openAIKey);

            $msgs = [['role' => 'user', 'content' => $this->getPrompt()]];

            $curl->post($url, [
                'model' => 'gpt-3.5-turbo',
                'messages' => $msgs,
                'temperature' =>  1,
                'max_tokens' => $maxTokens,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0
            ]);

            if ($curl->error) {
                echo 'Error: ' . $curl->errorMessage . "\n";
                $curl->diagnose();
            } else { // returns only the text from the first choice - there may be many choices...
                return trim($curl->response->choices[0]->message->content);
            }
        } else return false;
    }

    public function howto($howto)
    {
        if ($howto == "identify-your-values") {
            $this->howtoIdentifyYourValues();
        }
    }

    public function howtoIdentifyYourValues()
    {
        $values = $this->getInput();
        $enjoy = $values['enjoy'];
        $motvate = $values['motivate'];
        $care = $values['care'];
        $stand = $values['stand'];
        $remember = $values['remember'];

        $prompt = "Tell me some outside the box goals to accomplish based on the following preferences - they can include peripheral interests related to the following as well:";
        $prompt .= "\n\nI love to $enjoy";
        $prompt .= "\nI am motivated by $motvate";
        $prompt .= "\nI care about $care";
        $prompt .= "\nI stand for $stand";
        $prompt .= "\nI want to be $remember";
        $prompt .= "\n\n";

        $this->setPrompt($prompt);
        $response = $this->getResponseFromOpenAi();
        $response = nl2br($response);
        
        $output = "<h4>Here are some goals you can set based on your answers:</h4>";
        $output .= $response;
        echo $output;
    }

    public function testItinerary(){
        $prompt = "i love running, eating local food, and drinking local beer.";
        $prompt .= "Find some US cities that fill these preferenecs and give me a 5 day itinerary for 1 city based on them. After outputting the itinerary, in a new line under the text 'Places of Interest', list the places of interest in a comma separeated format.d" ;

        $this->setPrompt($prompt);
        $response = $this->getResponseFromOpenAi();
        $formattedresponse1 = nl2br($response);
        
        $output = "<h4>Test Itinerary:</h4>";
        $output .= $formattedresponse1;
        echo $output;
    }
}
