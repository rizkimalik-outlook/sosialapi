<?php
Class Base_Master {
	
	protected $config;
    protected $logger;
    protected $activity; // logging activity
    
    public function __construct(){
    	$this->config 	= Config_Loader::getInstance();
        $this->logger 	= Base_Logging::getInstance();
        $this->activity	= Base_Logging_Activity::getInstance();
    }
}
