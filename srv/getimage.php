<?php

require_once dirname(__FILE__).'/../lib/Consts.php';
require_once dirname(__FILE__).'/../lib/DB.php';
require_once dirname(__FILE__).'/../lib/Generator.php';

$max = isset($_GET['max']) ? intval($_GET['max']) : 10;
$width = isset($_GET['width']) ? intval($_GET['width']) : 400;
$height = isset($_GET['height']) ? intval($_GET['height']) : 400;
$from = isset($_GET['from']) ? intval($_GET['from']) : 0;
$pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

$db = new Paintr\DB();
$streamId = $db->getStreamId('everything');

$ret = array();

render(1, 0, 0, $width, $height, $from, $pos);

function render($i=1, $x=0, $y=0, $w=1, $h=1, $from=0, $pos=0) {
    global $ret, $max, $db, $streamId;
    
    if ($i > $max) {
        return;
    }
    
    $edge = $db->getRandomEdge($streamId, $from, $pos);
    // $edge = $db->getRandomEdge($streamId, $from);

    if (empty($edge)) {
        return;
    }

    $next = $db->getNode($edge['to_id']);
        
    $color = $next['term'];
    $toId = $next['id'];
    
    $ret[] = array(
        intval($x), intval($y), 
        intval($w), intval($h),
        $color, $i,
        intval($edge['pos']), intval($edge['to_id'])
    );
    
    $hw = $w/2;
    $hh = $h/2;
    
    // top-left
    render($i+1, $x, $y, $hw, $hh, $toId, 1);
    // top-right
    render($i+1, $x+$hw, $y, $hw, $hh, $toId, 2);
    // bottom-right
    render($i+1, $x+$hw, $y+$hh, $hw, $hh, $toId,3 );
    // bottom-left
    render($i+1, $x, $y+$hh, $hw, $hh, $toId, 4);
}


header('Content-type: application/json');

if ($from) {
    array_shift($ret);
}

print json_encode($ret);

