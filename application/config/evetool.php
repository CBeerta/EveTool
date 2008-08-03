<?php
/**
 * Where to store the cached images from EVE-O
 **/
if ($_SERVER['HTTP_HOST'] == 'anaea.fra.beerta.de')
{
    $config['image_cache_path'] = '/var/www/files/cache/';
    $config['image_cache_url'] = 'http://anaea.fra.beerta.de/files/cache/';
}
else
{
    $config['image_cache_path'] = '/var/www/evetool/files/cache/';
    $config['image_cache_url'] = 'http://aello.beerta.net/files/cache/';
}


?>
