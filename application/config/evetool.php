<?php
/**
 * Where to store the cached images from EVE-O
 **/


/* Local Storage where the image Downloader puts it's files to
 * Make Sure this is in your Document_root, and accessable as "/files/cache/"
 */
$config['image_cache_path'] = '/var/www/evetool/files/cache/';

/**
 * FIXME: This option is completely Useless, none of the urls generated use this option currently.
$config['image_cache_url'] = '/files/cache/';
*/


$config['killmail_directory'] = '/var/www/killmails/';




?>