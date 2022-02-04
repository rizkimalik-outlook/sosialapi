<?php
require_once dirname(__FILE__) . '/models/plugin.php';

class Modules_Plugin_Init {
	
	public function isExists($plugin) {
		$model = new Models_Plugin;
		return true;//$model->isExists($plugin);
	}
	
	public function run($plugin){
		$a = explode('.', $plugin);
		$file = dirname(__FILE__).'/../../plugins/'.$a[0].'/'.$a[1].'/controllers/'.$a[2].'.php';
		if( file_exists($file) ) {
			require_once $file;
			$classname = 'controllers_'.$a[0].'_'.$a[1].'_'.$a[2];
			$p = new $classname;
			$p->exec($a[3]);
		}
		else{
			die('file not found');
		}
	}
}
