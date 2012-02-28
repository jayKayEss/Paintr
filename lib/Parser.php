<?php

namespace Paintr;

class Parser {

    const COLORCHUNK = 4;
    const MAXDEPTH = 7;

    protected $db;
    protected $streamId;
    protected $image;
    protected $resampled;

    function __construct($db, $streamName) {
        $this->db = $db;
        $this->streamId = $db->getStreamId($streamName);
    }

    function loadFile($filename) {
        if (strpos($filename, '.jpg') || strpos($filename, '.jpeg')) {
            $this->image = imagecreatefromjpeg($filename);
        } else {
            $this->image = imagecreatefrompng($filename);
        }
    }

    function process($filename) {
        $this->loadFile($filename);
        $this->precacheImages();
        $this->parse();
        $this->db->storeEdges();
    }
    
    function parse($x=0, $y=0, $from=null, $pos=0, $depth=0) {
        $scaling = pow(self::MAXDEPTH - $depth + 1, 1.2);
        $color = $this->getAvgColor($depth, $x, $y, $scaling);
        $this->addEdge($from, $color, $pos);
        
        if ($depth >= self::MAXDEPTH) {
            // nowhere else to go...
            return;
        }
        
        $depth++;
        $x *= 2;
        $y *= 2;
        
        // top-left
        $this->parse($x, $y, $color, 1, $depth);
        // top-right
        $this->parse($x+1, $y, $color, 2, $depth);
        // bottom-right
        $this->parse($x+1, $y+1, $color, 3, $depth);
        // bottom-left
        $this->parse($x, $y+1, $color, 4, $depth);
    }

    function addEdge($from, $to, $pos) {
        if (empty($from)) {
            $from = '';
        }
        $this->db->addEdge($this->streamId, $from, $to, $pos);
    }

    function getAvgColor($depth, $x, $y, $scaling) {
        $imageTmp = $this->resampled[$depth];

        $index = imagecolorat($imageTmp, $x, $y);
        $colors = imagecolorsforindex($imageTmp, $index);
        
        $rgb = sprintf(
            "#%02x%02x%02x",
            intval($colors['red'] / $scaling) * $scaling,
            intval($colors['green'] / $scaling) * $scaling,
            intval($colors['blue'] / $scaling) * $scaling
        );
        
        // error_log("$depth [$x, $y] $rgb @$scaling");
        return $rgb;
    }

    function precacheImages() {
        $w = imagesx($this->image);
        $h = imagesy($this->image);
        
        for ($depth=0; $depth<=self::MAXDEPTH; $depth++) {
            $dim = pow(2, $depth);
            $resampled = imagecreatetruecolor($dim, $dim);
            imagecopyresampled(
                $resampled, $this->image, 
                0, 0, 0, 0, 
                $dim, $dim, $w, $h
            );
            $this->resampled[$depth] = $resampled;
        }
    }
}
