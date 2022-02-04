<?php
class Base_Logging_Activity extends Base_Logging {
	
	private static $instance;
	private $objDriver;
	
	// storage
	private $storage = '';
	private $enable  = true;
	
	private function __construct() {
		$config = Config_Loader::getInstance();
		$driver = 'Libs_Logging_Driver_' . $config->get('logActivityDriver');
		$this->objDriver = new $driver(array(
			'logLevel' 		=> 'debug',
			'logPath' 		=> $config->get('logActivityPath'),
			'logPrefix'		=> $config->get('logActivityPrefix'),
			'logTimeFormat' => $config->get('logActivityTimeFormat'),
			'logLineFormat' => $config->get('logActivityLineFormat')
		));
		
		$this->enable = $config->get('logActivityActive');
	}
	
	public static function getInstance(){
		if (!self::$instance)
			self::$instance = new self ();
	
		return self::$instance;
	}
	
	public function log($clientcode, $appid, $class, $function, $parameter, $respcode, $respdesc){
		if( $this->enable==true ) {
			$this->objDriver->write('info', implode('|', array(
				$clientcode, $appid, $class, $function, $respcode, $respdesc, $parameter
			)));
		}
	}
}
