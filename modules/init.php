<?php
class Modules_Init extends Base_Module {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function run() {
		$input 	= new Modules_IO_Parser;
		$queryString = $input->get('uri');
		if( $queryString != false ) {
			$tmp = explode('/', $queryString);
		}
		else{
			$tmp = explode('/', $this->config->app('defaultController'));
		}
		
		$input->decryptRequest();
		
		$project	= isset($tmp[0]) ? $tmp[0] : die(json_encode($this->httpcode(400)));
		$plugin		= isset($tmp[1]) ? $tmp[1] : die(json_encode($this->httpcode(400)));
		$controller	= isset($tmp[2]) ? $tmp[2] : die(json_encode($this->httpcode(400)));
		$function	= isset($tmp[3]) ? $tmp[3] : 'index';
		$pluginNamespace = $project.'.'.$plugin.'.'.$controller.'.'.$function;
		
		// check if plugin exists
		$pluginInit = new Modules_Plugin_Init;
		$result = $pluginInit->isExists($pluginNamespace);
		
		if($result == false){
			http_response_code(503);
			header('Content-Type: application/json');
			echo json_encode($this->httpcode(503));
			die;
		} 
		
		$pluginInit->run($pluginNamespace);
	}
}
