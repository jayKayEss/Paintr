<?php

namespace Paintr;

class Generator {

    protected $db;
    protected $streamId;

    function __construct($db, $streamName) {
        $this->db = $db;
        $this->streamId = $db->getStreamId($streamName);
    }

    function generate($chars=140) {
        $buff = '';
        $retries = 0;

        while (1) {
            $phrase = $this->generatePhrase();

            if (strlen($buff)+strlen($phrase)+1 < $chars) {
                $buff .= ' ' . $phrase;
                $retries = 0;
            } else {
                if (++$retries > 3) {
                    break;
                }
            }
        }

        return trim($buff);
    }

    function generatePhrase() {
        $buff = '';
        $lastNodeId = 0;
        $bailout = 0;

        while (1) {
            if (++$bailout > 100) {
                break;
            }

            $edge = $this->db->getRandomEdge($this->streamId, $lastNodeId);
            
            if ($edge) {
                $node = $this->db->getNode($edge['to_id']);

                $buff .= ' '.$node['term'];
                $lastNodeId = $edge['to_id'];
            } else {
                break;
            }
        }

        return trim($buff);
    }

}

