#!/usr/bin/php
<?php
error_reporting(E_ALL & ~E_NOTICE);
set_time_limit(0);
ini_set('memory_limit', '-1');
$starttime = microtime(1);
/**
 * Plugin API Handler 
 *  
 **/

require (dirname(__FILE__) . '/../opf.php');
$l = '';
if(isset($_SERVER['argv'])){
	foreach( $_SERVER['argv'] as $v => $a ){
		if($v === 0) continue;
		$l .= '_' . $a;
	}
}
if(!isset($_SERVER['argv'][2])){
	die("CANOT FIND FLUSHER TYPE: vhub, pcrf, standard\n");	
}
$class = $_SERVER['argv'][2];
if($class == 'standard'){
	$class = "pcrf";
}
$limit = isset($_SERVER['argv'][3]) ? $_SERVER['argv'][3] : -1;
$class = 'Module_API_FLusher_' . ucfirst($class);

if(!class_exists( $class )){
	die("CLASS CAN'T BE FOUND: $class\n");
}

Libs_Util_Cli::setFilename('cflusher_staging_' . $l);
if( Libs_Util_Cli::isAlive() ){
	Libs_Util_Cli::msg('locked!');
}
Libs_Util_Cli::lock();
$o = new $class();
$o->process($limit);
echo("\nTime: " . sprintf('%.2f', ($end = microtime(true) - $starttime)) . " second, " . sprintf('%.2f',$end/60) . " minute\n");
Libs_Util_Cli::removeLock();
