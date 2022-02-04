<?php
Class Base_View_Render_Json implements Base_View_Render_Interface {
	
	private $param;
	
	public function __construct($option) {	}
	
	public function assign($key, $val){
		$this->param[$key] = $val;	
	}
	
	public function isCached($template=''){
		return false;
	}
	
	public function output($template='') {
		return json_encode($this->param);
	}
}
