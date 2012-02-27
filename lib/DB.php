<?php

namespace Paintr;

class DB {

    const USER = "paintr";
    const PASS = "stardustfairiespixies";

    const GET_STREAM = "SELECT id FROM stream WHERE name=?";
    const NEW_STREAM = "INSERT INTO stream (name) VALUES (?)";

    const GET_NODE = "SELECT id FROM node WHERE term=?";
    const GET_NODE_BY_ID = "SELECT * FROM node WHERE id=?";
    const NEW_NODE = "INSERT INTO node (term) VALUES (?)";

    const NEW_EDGE = <<<'EOL'
INSERT INTO edge (stream_id, from_id, to_id, pos, rank) VALUES (?, ?, ?, ?, 1) 
ON DUPLICATE KEY UPDATE rank=rank+1
EOL;

    const GET_EDGES = "SELECT * FROM edge WHERE stream_id=? AND from_id=? AND pos=?";
    const GET_EDGES_NO_POS = "SELECT * FROM edge WHERE stream_id=? AND from_id=?";

    protected $dbh;

    function __construct() {
       $this->dbh = new \PDO('mysql:host=127.0.0.1;dbname=paintr', self::USER, self::PASS); 
    }

    function getStreamId($name) {
        $query = $this->dbh->prepare(self::GET_STREAM);
        $query->execute(array($name));

        if ($rec = $query->fetch(\PDO::FETCH_ASSOC)) {
            return $rec['id'];
        }

        $query = $this->dbh->prepare(self::NEW_STREAM);
        $query->execute(array($name));

        return $this->dbh->lastInsertId();
    }

    function getNodeId($term) {
        $query = $this->dbh->prepare(self::GET_NODE);
        $query->execute(array($term));

        if ($rec = $query->fetch(\PDO::FETCH_ASSOC)) {
            return $rec['id'];
        }

        $query = $this->dbh->prepare(self::NEW_NODE);
        $query->execute(array($term));

        return $this->dbh->lastInsertId();
    }

    function getNode($id) {
        $query = $this->dbh->prepare(self::GET_NODE_BY_ID);
        $query->execute(array($id));

        if ($rec = $query->fetch(\PDO::FETCH_ASSOC)) {
            return $rec;
        }
    }

    function addEdge($streamId, $fromId, $toId, $pos) {
        $query = $this->dbh->prepare(self::NEW_EDGE);
        $query->execute(array($streamId, $fromId, $toId, $pos));
    }

    function getRandomEdge($streamId, $fromId, $pos=0) {
        
        if ($pos > 0) {
            $query = $this->dbh->prepare(self::GET_EDGES);
            $query->execute(array($streamId, $fromId, $pos));
        } else {
            $query = $this->dbh->prepare(self::GET_EDGES_NO_POS);
            $query->execute(array($streamId, $fromId));
        }

        $picked = null;
        $count = 0;

        while ($rec = $query->fetch(\PDO::FETCH_ASSOC)) {
            $max = $count + $rec['rank'];
            $rand = rand(1, $max);

            // error_log("RAND $max $rand");

            if ($rand > $count && $rand <= $max) {
                $picked = $rec;
            }

            $count = $max;
        }

        if (!$picked) {
            $picked = $rec;
        }

        return $picked;
    }

}
