<?php
Class Base_View_Render {
	
	private $driver;
	private $option;
	
	public function __construct($render, $option) {
		switch( strtoupper($render) ){
			case 'JSON':
				$this->driver = new Base_View_Render_Json($option);
				break;
				
			case 'SMARTY':
				$this->driver = new Base_View_Render_Smarty($option);
				break;
		}
	}
	
	public function assign($key, $val){
		$this->driver->assign($key, $val);
	}
	
	public function isCached($template){
		return $this->driver->isCached($template);
	}
	
	public function clearCache($template) {
		return $this->driver->clearCache($template);
	}
	
	public function output($template) {
		return $this->driver->output($template);
	}
}
