<?php
/**
 * 
 *
 */
Class Config_Manager {
	
	private $driver; 
	
	public function __construct($driver, $config) {
		$key = 'CONFIG_' . strtoupper($driver);
		$onRegistry = false;
		
		// check if available on registry
		$this->driver = Libs_Registry::get($key);
		if( $this->driver !== false ) $onRegistry = true; 
		
		if($onRegistry === false){
			switch($driver){
				case 'mysql':
					$this->driver = new Config_Driver_Mysql($config);
					break;
				default:
					$this->driver = new Config_Driver_File($config);
					break;
			}
			// set to registry, so others can use it
			Libs_Registry::set($key, $this->driver);
		}
	}
	
	public function getData() {
		return $this->driver->getData();
	}
}
