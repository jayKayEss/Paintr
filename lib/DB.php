<?php

namespace Paintr;

class DB {

    const USER = "paintr";
    const PASS = "stardustfairiespixies";

    const GET_STREAM = "SELECT id FROM stream WHERE name=?";
    const NEW_STREAM = "INSERT INTO stream (name) VALUES (?)";

    const GET_EDGES = "SELECT * FROM edge WHERE stream_id=? AND color_from=? AND pos=?";
    const GET_EDGES_NO_POS = "SELECT * FROM edge WHERE stream_id=? AND color_from=?";
    const STORE_EDGE = <<<EOF
INSERT INTO edge (stream_id, color_from, pos, data) VALUES (?, ?, ?, ?)
ON DUPLICATE KEY UPDATE data = CONCAT_WS(',', data, ?)
EOF;

    protected $dbh;
    protected $edge_kitty;
    protected $node_cache;

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

    function addEdge($streamId, $colorFrom, $colorTo, $pos) {
        if (!isset($this->edge_kitty["$streamId:$colorFrom:$pos"])) {
            $this->edge_kitty["$streamId:$colorFrom:$pos"] = "";
        }

        $this->edge_kitty["$streamId:$colorFrom:$pos"] .= "$colorTo,";
    }
    
    function storeEdges() {
        $query = $this->dbh->prepare(self::STORE_EDGE);
        
        foreach ($this->edge_kitty as $key => $data) {
            list ($stream_id, $color_from, $pos) = explode(':', $key);
            $data = rtrim($data, ',');
            $query->execute(array($stream_id, $color_from, $pos, $data, $data));
        }
        
        $this->edge_kitty = array();
    }

    function getRandomEdge($streamId, $colorFrom, $pos=0) {
        // error_log("GET RAND EDGE [$streamId] [$colorFrom] [$pos]");
        if (empty($colorFrom)) {
            $colorFrom = '';
        }
        
        if ($pos > 0) {
            $query = $this->dbh->prepare(self::GET_EDGES);
            $query->execute(array($streamId, $colorFrom, $pos));
        } else {
            $query = $this->dbh->prepare(self::GET_EDGES_NO_POS);
            $query->execute(array($streamId, $colorFrom));
        }

        $alledges = array();

        while ($rec = $query->fetch(\PDO::FETCH_ASSOC)) {
            $edges = explode(',', $rec['data']);
            $alledges = array_merge($alledges, $edges);
        }

        if (!empty($alledges)) {
            $idx = mt_rand(0, count($alledges)-1);
            return $alledges[$idx];
        } else {
            return null;
        }
    }

}
