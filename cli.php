#!/usr/bin/env php 
<?php

/* we don't need to be limited by...normal limitations */
set_time_limit(0);
ini_set('memory_limit', '256M');

/* make sure this isn't being called by a web browser */
if (isset($_SERVER['REMOTE_ADDR'])) die('Permission denied.');

/* set some constants */
define('CMD', 1);

function cli_usage()
{
	print "Usage: {$argv[0]} cli <command>\n";
	print "\n";
	print "Valid Commands are:\n";
	print "\tcron_update		Update XML Cache, best called from cron once a minute\n";
	exit;
}

if (count($argv) < 2)
{
	cli_usage();
}

/* manually set the URI path based on command line arguments... */
$argv[0] = 'cli';
$_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'] = '/' . implode('/', $argv) . '/';

/* call up the framework */
include(dirname(__FILE__).'/index.php');

?>
