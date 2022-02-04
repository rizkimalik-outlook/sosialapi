<?php
/**
 * base class response
 */
$basepath = dirname(__FILE__);
require_once $basepath . '/response/response_json.php';
require_once $basepath . '/response/response_xml.php';
require_once $basepath . '/response/response_httpget.php';

class Libs_Response {
	
	private $type;
	private $xmlroot;
	private $language = 'en';
	
	public function __construct($type='xml', $xmlroot='<response/>'){
		$this->set_type($type);
		$this->set_xml_root($xmlroot);
	}
	
	public function set_type($type) {
		if($type != 'xml' && $type != 'json' && $type!= 'httpget') die('CLASS RESPONSE: ERROR INVALID RENDER TYPE');
		$this->type = $type;
	}
	
	public function set_xml_root($xmlroot) {
		$this->xmlroot = $xmlroot;
	}
	
	public function render($data) {
		if( !is_array($data) ) die('CLASS RESPONSE: ERROR INVALID RENDER DATA');
		$class = 'response_' . $this->type;
		$o = new $class;
		if($this->type == 'xml') $o->set_xml_root($this->xmlroot);
		$return = $o->render($data);
		
		//$logging= Logging::getInstance();
		//$logging->write('debug',"RESPONSE: $return");
		//$logging->write('summary',"RESPONSE_API:$return");
		 
		return $return;
	}
	
	public function setLanguage($lang){
		$this->language = strtolower($lang);
	}
	
	public function code($code, $data=''){
		$file = dirname(__FILE__) . '/../language/' . $this->language . '.php';
		
		if( !file_exists($file) ) {
			die( $this->render(array('code'=>'209', 'message'=>'internal error')) );
		}
		
		require_once $file;
		
		$resp = array(
			'code'		=> "$code", 
			'message' 	=> isset($lang[$code])?$lang[$code]:$lang[0]
		);
		
		if($data != '') $resp['data'] = $data;
		
		return $this->render($resp);
	}	
}
