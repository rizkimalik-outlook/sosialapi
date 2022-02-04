<?php
ini_set("include_path", '/home/k5075995/php:' . ini_get("include_path")  );
session_start();
date_default_timezone_set('Asia/Jakarta');
DEFINE('APP_PATH', dirname(__FILE__) . '/');

include_once 'base/autoload.php';

$module = new Modules_Init;
$module->run();