<?php

namespace Paintr;

class Parser {

    const COLORCHUNK = 4;
    const MAXDEPTH = 7;

    protected $db;
    protected $streamId;
    protected $image;

    function __construct($db, $streamName) {
        $this->db = $db;
        $this->streamId = $db->getStreamId($streamName);
    }

    function loadFile($filename) {
        if (strpos($filename, '.jpg') || strpos($filename, '.jpeg')) {
            return imagecreatefromjpeg($filename);
        } else {
            return imagecreatefrompng($filename);
        }
    }

    function process($filename) {
        $image = $this->loadFile($filename);
        $w = imagesx($image) - 1;
        $h = imagesy($image) - 1;
        
        $this->parse($image, 0, 0, $w, $h);
    }
    
    function parse($image, $x, $y, $w, $h, $from=null, $pos=0, $depth=1) {
        $scaling = pow(self::MAXDEPTH - $depth + 1, 1.8);
        $color = $this->getAvgColor($image, $x, $y, $w, $h, $scaling);
        // $color = $this->getAvgColor($image, $x, $y, $w, $h, 1);
        $this->addEdge($from, $color, $pos);
        
        $hw = intval($w/2);
        $hh = intval($h/2);
        
        if ($depth >= self::MAXDEPTH || ($hw <= 1 || $hh <= 1)) {
            // nowhere else to go...
            return;
        }
        
        // top-left
        $this->parse($image, $x, $y, $hw, $hh, $color, 1, $depth+1);
        // top-right
        $this->parse($image, $x+$hw, $y, $hw, $hh, $color, 2, $depth+1);
        // bottom-right
        $this->parse($image, $x+$hw, $y+$hh, $hw, $hh, $color, 3, $depth+1);
        // bottom-left
        $this->parse($image, $x, $y+$hh, $hw, $hh, $color, 4, $depth+1);
    }

    function addEdge($from, $to, $pos) {
        if (!empty($from)) {
            $fromId = $this->db->getNodeId($from);
        } else {
            $fromId = 0;
        }
        $toId = $this->db->getNodeId($to);
        $this->db->addEdge($this->streamId, $fromId, $toId, $pos);
    }

    function getAvgColor($image, $x, $y, $w, $h, $scaling) {
        $imageTmp = imagecreatetruecolor(1, 1);
        imagecopyresampled($imageTmp, $image, 0, 0, $x, $y, 1, 1, $w, $h);

        $index = imagecolorat($imageTmp, 0, 0);
        $colors = imagecolorsforindex($imageTmp, $index);
        
        $rgb = sprintf(
            "#%02x%02x%02x",
            intval($colors['red'] / $scaling) * $scaling,
            intval($colors['green'] / $scaling) * $scaling,
            intval($colors['blue'] / $scaling) * $scaling
        );
        
        error_log("[$x, $y][$w, $h] $rgb @$scaling");
        return $rgb;
    }

}
