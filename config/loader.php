<?php
/**
 * 
 *
 */
class Config_Loader {
	
	private static $instance;
	
	private $configData 	= array();
	private $configExt  	= array();
	private $configPlugin	= array();
	private $extendedLoaded = false;
	private $pluginLoaded 	= false;
	
	private function __construct() {
		// load basic configuration
		$this->configData = get_object_vars(new Config_Basic);
	}
	
	public static function getInstance() {
		if (!self::$instance)
            self::$instance = new self ();

        return self::$instance;
	}
	
	public function get($key='') {
		if( $key=='' ) return $this->configData;
		if( !isset($this->configData[$key]) ) return '';
		if($key == 'domain') return str_replace('{domain}', $_SERVER['SERVER_NAME'], $this->configData[$key]);
		return $this->configData[$key];
	}
	
	public function app($key='') {
		if($this->extendedLoaded === false){
			$this->_loadExtended();
		}
		if( $key=='' ) return $this->configExt;
		if( !isset($this->configExt[$key]) ) return '';
		return $this->configExt[$key];
	}
	
	public function plugin($key='') {
		if($this->pluginLoaded === false){
			$this->_loadPlugin();
		}
		if( $key=='' ) return $this->configPlugin;
		if( !isset($this->configPlugin[$key]) ) return '';
		return $this->configPlugin[$key];
	}
	
	private function _loadExtended() {
		// load extended configuration
		$manager = new Config_Manager($this->configData['configDriver'], $this->configData);
		$this->configExt = $manager->getData();
	}
	
	private function _loadPlugin() {
		// load plugin configuration
		$manager = new Config_Plugin($this->configData['configDriver'], $this->configData);
		$this->configExt = $manager->getData();
	}
}
