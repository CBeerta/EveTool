Requirements:
=============
    - PHP 5.3.x  with Curl, SimpleXML
    - MySQL 5.x

Installation Guide:
===================

    - Download and install the Eve Tyrannis Database and configure application/config/database.php accordingly
    - Drop the Contents into your Webserver, set the base_url in application/config/config.php
    - Create 'application/config/characters.ini' (example characters.example.ini)
    - Download the Images Dump from CCP and unzip into 'files/itemdb' (cd files/itemdb ; wget http://content.eveonline.com/data/Tyrannis_1.0.4-imgs.7z ; 7z x  Tyrannis_1.0.4-imgs.7z ; rm *.7z)
    - Make sure your webserver uses the .htaccess (easiest is to point the 404 site to index.php)

Extra Info:
===========

To gain some Performance there's 2 things one can do:

    1. Install Memcached and activate the PHP memcached extension. (Configure memecache host in application/config/config.php)
    2. Setup a Cronjob once a minute to keep the EveApi XML Cache "warm":
           # php -q cli.php cron_update



