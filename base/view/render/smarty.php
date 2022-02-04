<?php
Class Base_View_Render_Smarty implements Base_View_Render_Interface {
	
	private $smarty;
	
	public function __construct($option) {
		
		require_once dirname(__FILE__) . '/../../../libs/smarty/Smarty.class.php';
		
		$this->smarty = new Smarty;
				
		$this->smarty->force_compile 	= isset($option['forceCompile']) ? $option['forceCompile'] : false;
		$this->smarty->debugging 		= isset($option['debug']) ? $option['debug'] : false;
		$this->smarty->caching 			= isset($option['cache']) ? $option['cache'] : true;
		$this->smarty->cache_lifetime 	= isset($option['lifetime']) ? $option['lifetime'] : 60;
		$this->smarty->compile_check 	= false;
		
		$this->smarty->setCompileDir( dirname(__FILE__).'/../../../templates_c/' );
		$this->smarty->setCacheDir( dirname(__FILE__).'/../../../templates_c/' );
		$this->smarty->setTemplateDir( dirname(__FILE__).'/../../../views/' );
	}
	
	public function assign($key, $val){
		$this->smarty->assign($key, $val);
	}
	
	public function isCached($template){
		return $this->smarty->isCached($template);
	}
	
	public function clearCache($template='') {
		if( $template!='' ){
			return $this->smarty->clearCache($template);
		}
		else{
			return $this->smarty->clearAllCache();
		}
	}
	
	public function output($template) {
		$this->smarty->display($template);
	}
}
