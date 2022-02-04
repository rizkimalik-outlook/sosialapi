<?php
Class Base_View extends Base_Master {
	
	protected $format = 'json'; // html, json, xml 
	protected $theme;
	protected $render;
	
	public function __construct($format='', $option='') {
		parent::__construct();
		if($format=='') $format = $this->format;
		$this->set_render($format, $option);		
	}
	
	public function set_render($render, $option='') {
		$this->render = new Base_View_Render($render, $option);
	}
	
	public function get_render() {
		return $this->render;
	}
	
	public function assign($key, $value) {
		$this->render->assign($key, $value);
	}
	
	public function isCached($template='') {
		if($template!='' && isset($this->theme) && $this->theme!='') $template = $this->theme .'/'. $template;
		return $this->render->isCached($template);
	}
	
	public function clearCache($template='') {
		if($template!='' && isset($this->theme) && $this->theme!='') $template = $this->theme .'/'. $template;
		return $this->render->clearCache($template);
	}
	
	public function setTheme($theme){
		$this->theme = $theme;
	}
	
	public function display($template='') {
		if($template!='' && isset($this->theme) && $this->theme!='') $template = $this->theme .'/'. $template;
		$output = $this->render->output($template);
		$this->logger->write('debug', 'OUTPUT: ' . $output);
		die($output);
	}
}
