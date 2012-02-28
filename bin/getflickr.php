<?php

$numphotos = 13;

$flickr = 'http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=4a1a4fcfe5cc7e2953badcbd76812a02&format=json&nojsoncallback=1&text=%s&sort=relevance&page=%d';

// farm server-id id secret
$photo_url = 'http://farm%s.staticflickr.com/%s/%s_%s.jpg';

$process = realpath(dirname(__FILE__)."/process.php");
$kitty = realpath(dirname(__FILE__)."/../flickr");

function getFlickr($terms, $page) {
    global $flickr, $photo_url, $process, $kitty;
    $url = sprintf($flickr, $terms, $page);
    $raw = file_get_contents($url);
    $res = json_decode($raw);
    
    foreach ($res->photos->photo as $photo) {
        $url = sprintf($photo_url, $photo->farm, $photo->server, $photo->id, $photo->secret);
        $local_name = "$kitty/{$photo->id}.jpg";
        
        if (!file_exists($local_name)) {
            file_put_contents($local_name, file_get_contents($url));
        }

        print("php $process $local_name\n");
        system("php $process $local_name\n");
    }
    
    return $res->photos->pages;
}

$page = 1;
$count = 1;
while (1) {
    $max_pages = getFlickr('american+flag', $page);
    if ($page < $max_pages) {
        $page++;
    } else {
        break;
    }
    if (++$count >= $numphotos) {
        break;
    }
}
