<?php

class Fubar {
    
    const MAXDEPTH = 3;
    
    function parse($x=0, $y=0, $pos=0, $depth=0) {
        $n = pow(2, $depth);
        print "[$x, $y] $pos @$depth {$n}x{$n}\n";

        print "  ";
        for ($xx=0; $xx<$n; $xx++) {
            print "$xx";
        }
        print "\n";
        
        for ($yy=0; $yy<$n; $yy++) {
            print "$yy ";
            for ($xx=0; $xx<$n; $xx++) {
                if ($xx==$x && $yy==$y) {
                    print "$pos";
                } else {
                    print "-";
                }
            }
            print "\n";
        }
        
        print "\n\n";
        
        if ($depth >= self::MAXDEPTH) {
            // nowhere else to go...
            return;
        }
        
        $depth++;
        $x *= 2;
        $y *= 2;
        
        // top-left
        $this->parse($x, $y, 1, $depth);
        // top-right
        $this->parse($x+1, $y, 2, $depth);
        // bottom-right
        $this->parse($x+1, $y+1, 3, $depth);
        // bottom-left
        $this->parse($x, $y+1, 4, $depth);
    }
    
}

$fffuuu = new Fubar();
$fffuuu->parse();