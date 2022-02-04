<?php
class Base_Logging {
	
	private static $instance;
	private $objDriver;
	
	// storage
	private $storage = '';
	private $enable  = true;
	
	private function __construct() {
		$config = Config_Loader::getInstance();
		$driver = 'Libs_Logging_Driver_' . $config->get('logDriver');
		$this->objDriver = new $driver(array(
			'logLevel' 		=> $config->get('logLevel'),
                        'logSegmented' 		=> $config->get('logSegmented'),
			'logPath' 		=> $config->get('logPath'),
			'logPrefix'		=> $config->get('logPrefix'),
			'logTimeFormat'         => $config->get('logTimeFormat'),
			'logLineFormat'         => $config->get('logLineFormat'),
			'logAllowLevel'         => $config->get('logAllowLevel'),
			'logDuration'		=> $config->get('logDuration')
		));
		
		$this->enable = $config->get('logActive');
	}
	
	public static function getInstance(){
		if (!self::$instance)
            self::$instance = new self ();

        return self::$instance;
	}
	
	public function write($level, $message){
		if( $this->enable==true ) {
			if($level == 'summary'){
				$this->_store($message);
			}else{
				$this->objDriver->write($level, $message);
			}
		}
	}
	
	private function _store($message){
		$this->storage .= $message . "\t";
	}
	
	public function __destruct(){
		if($this->storage != '') $this->objDriver->write('summary', $this->storage);
	}
}
