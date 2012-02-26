<?php

require_once dirname(__FILE__).'/../lib/Consts.php';
require_once dirname(__FILE__).'/../lib/DB.php';
require_once dirname(__FILE__).'/../lib/Generator.php';

$fromId = isset($_GET['from']) ? $_GET['from'] : 0;
$pos = isset($_GET['pos']) ? $_GET['pos'] : 0;

$db = new Paintr\DB();
$streamId = $db->getStreamId('everything');
$edge = $db->getRandomEdge($streamId, $fromId, $pos);
$next = $db->getNode($edge['to_id']);

header('Content-type: application/json');

print json_encode($next);

