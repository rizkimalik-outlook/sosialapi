<?php
class Config_Basic {
	
	// config driver
	public $configDriver 	= 'basic';
	public $domain			= 'https://mendawai.com/sosialapi/';
	
	//Folder config
	public $base_folder	= '';
        
	#Security
	public $secretKey	 	= 'sosialapi';
	public $shareKey	 	= '952a2cd77d69c755';
	// database connection
	public $database_connection = FALSE;
	public $dsn = array(
		'default' => array(
			'hostname'	=> '',
			'username'	=> '',
			'password'	=> '',
			'database'	=> '',
			'port'		=> 3306
		)
	);
	
	// logging driver
	public $logDriver 		= 'basic';
	public $logActive		= false;
	public $logWhitelist    = false;
	public $logLevel		= 'query';
	public $logSegmented	= false;
	public $logDuration     = 'hourly';
	public $logPrefix 		= 'api'; // default log prefix filename
	public $logPath			= '';
	public $logTimeFormat   = 'D M d H:i:s Y';
	public $logLineFormat   = '{datetime} {exectime} {threadId} {level} {message}';
	public $logAllowLevel   = 'query,process,debug,info,input,query,error,summary,emergency,alert,critical,warning,notice,cache';
                
	
	// logging activity driver
	public $logActivityDriver 		= 'basic';
	public $logActivityActive		= false;
	public $logActivityWhitelist	= false;
	public $logActivityPrefix 		= 'activity'; // default log prefix filename
	public $logActivityPath			= '';
	public $logActivityTimeFormat	= 'Y-m-d H:i:s';
	public $logActivityLineFormat	= '{datetime}|{message}';
}
