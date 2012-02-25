<?php

require_once dirname(__FILE__).'/../lib/Consts.php';
require_once dirname(__FILE__).'/../lib/DB.php';
require_once dirname(__FILE__).'/../lib/Parser.php';
require_once dirname(__FILE__).'/../lib/Generator.php';
require_once dirname(__FILE__).'/../lib/TwitterClient.php';

$naptime = 120;

$db = new Tootr\DB();
$clients = array();

while(1) {
    foreach (Tootr\Consts::$STREAMS as $stream) {

        if (isset($clients[$stream])) {
            $client = $clients[$stream];
        } else {
            $client = new Tootr\TwitterClient($stream);
        }

        $parser = new Tootr\Parser($db, $stream);

        $results = $client->getStatuses();

        foreach ($results->results as $status) {
            if ($status->iso_language_code != 'en') {
                continue;
            }

            error_log($status->text);
            $parser->parse($status->text);
        }
    }
    sleep($naptime);
}

