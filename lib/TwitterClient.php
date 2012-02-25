<?php

namespace Paintr;

class TwitterClient {

    const API_KEY = 'dNGyKMmV8azMUm9QsWMoag';
    const URI = 'http://search.twitter.com/search.json';

    protected $term = null;
    protected $sinceId = null;

    function __construct($term) {
        $this->term = $term;
    }

    function getStatuses() {
        $params = $this->getParams();
        $rawjson = file_get_contents(self::URI . "?" . $params);
        $results = json_decode($rawjson);

        $this->sinceId = $results->max_id_str;

        return $results;
    }

    function getParams() {
        $params = array(
            'q' => $this->term,
            'result_type' => 'recent',
            'rpp' => 100
        );

        if (isset($this->sinceId)) {
            $params['since_id'] = $this->since_id;
        }

        return http_build_query($params);
    }


}

