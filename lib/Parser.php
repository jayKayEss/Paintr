<?php

namespace Paintr;

class Parser {

    const CHUNKSIZE = 32;

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
    
    function parse($image, $x, $y, $w, $h, $from=null) {
        $color = $this->getAvgColor($image, $x, $y, $w, $h);
        $this->addEdge($from, $color);

        
        $hw = intval($w/2);
        $hh = intval($h/2);
        
        if ($hw <= 10 && $hh <= 10) {
            // nowhere else to go...
            return;
        }
        
        // top-left
        $this->parse($image, $x, $y, $hw, $hh, $color);
        // top-right
        $this->parse($image, $x+$hw, $y, $hw, $hh, $color);
        // bottom-right
        $this->parse($image, $x+$hw, $y+$hh, $hw, $hh, $color);
        // bottom-left
        $this->parse($image, $x, $y+$hh, $hw, $hh, $color);
    }

    function addEdge($from, $to) {
        if (!empty($from)) {
            $fromId = $this->db->getNodeId($from);
        } else {
            $fromId = 0;
        }
        $toId = $this->db->getNodeId($to);
        $this->db->addEdge($this->streamId, $fromId, $toId);
    }

    function getAvgColor($image, $x, $y, $w, $h) {
        $imageTmp = imagecreatetruecolor(1, 1);
        imagecopyresampled($imageTmp, $image, 0, 0, $x, $y, 1, 1, $w, $h);

        $index = imagecolorat($imageTmp, 0, 0);
        $colors = imagecolorsforindex($imageTmp, $index);
        
        $rgb = sprintf(
            "#%02x%02x%02x",
            $colors['red'],
            $colors['green'],
            $colors['blue']
        );
        
        error_log("[$x, $y][$w, $h] $rgb");
        return $rgb;
    }

}
