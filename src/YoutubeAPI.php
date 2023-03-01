<?php 

class YoutubeAPI
{
    public $_query, $_channelId, $_resultsPerPage, $_key, $_order, $_type, $_pageToken;

    public function __construct($key = '', $query = '', $channelId = '', $resultsPerPage = '')
    {
        $this->_key = $key;
        $this->_query = $query;
        $this->_channelId = $channelId;
        $this->_resultsPerPage = $resultsPerPage;
    }

    public function getYoutubeJson()
    {
        $url = "https://youtube.googleapis.com/youtube/v3/search?part=snippet&channelId=" . $this->_channelId . "&maxResults=" . $this->_resultsPerPage . "&key=" . $this->_key
            . "&order=viewCount"
            . "&type=video";

        if ($this->_query != "") $url .= "&q=" . $this->_query;
        if ($this->_pageToken != '') $url .= "&pageToken=" . $this->_pageToken;

        $c = file_get_contents($url);

        if ($c === false) {
            $error = error_get_last();
            echo "Error fetching content: " . $error['message'];
        } else {
            return $c;
        }
    }
}