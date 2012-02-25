<?php

namespace Paintr;

require_once dirname(__FILE__).'/../lib/Consts.php';
require_once dirname(__FILE__).'/../lib/DB.php';
require_once dirname(__FILE__).'/../lib/Parser.php';
require_once dirname(__FILE__).'/../lib/Generator.php';

$db = new DB();

$filename = $argv[1];

$parser = new Parser($db, 'everything');
$parser->process($filename);
