#!/usr/local/php/bin/php
<?php
/* make sure this isn't called from a web browser */
if (isset($_SERVER['REMOTE_ADDR'])) {
    die('Permission denied!');
}

/* raise or eliminate limits we would otherwise put on http requests */
set_time_limit(0);
ini_set('memory_limit', '512M');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTP_USER_AGENT'] = 'hx.bb.crond';
$_SERVER['REMOTE_PORT'] = 'index';
$_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'] = $argv[1];

define('CLI_RUNTIME', TRUE);
require dirname(__FILE__) . '/index.php';