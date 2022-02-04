<?php
define ('BASE_PATH',    dirname(dirname(__FILE__)));

function autoload($className){
	$path = BASE_PATH . '/' . strtolower(str_replace('_', '/', $className));
    if(file_exists($path . '.php')) {
        require_once($path . '.php');
    }
}

spl_autoload_register('autoload');
