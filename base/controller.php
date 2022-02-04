<?php
class Base_Controller extends Base_Master {

	public $input;
	public $view;
	public $lang = 'en';
	public $dicti;

	public $sso;
	public $msisdn;

	public function __construct($option='') {
		parent::__construct();

		// initialize base input class
		$this->input = new Base_Input;

		// initialize base view class
		$this->view  = new Base_View('smarty', array(
			'forceCompile'	=> $this->config->app('smartyForceCompile'),
			'debug'			=> $this->config->app('smartyDebug'),
			'cache'			=> $this->config->app('smartyCache'),
			'lifetime'		=> $this->config->app('smartyCacheLifetime')
		));
		// build url change language
		$_SERVER['REQUEST_URI'] = str_replace(array('&lang=id','&lang=en'),'',$_SERVER['REQUEST_URI']);
		if(	strpos($_SERVER['REQUEST_URI'],'?')!==false ){
			$urlLang = $_SERVER['REQUEST_URI'] . '&lang=';
		}
		else{
			$urlLang = $_SERVER['REQUEST_URI'] . '?lang=';
		}
		$this->view->assign('url4lang',	 $urlLang);
	}

	protected function _setLanguage($language){
		$_SESSION['language'] = $this->lang = $language;

		$file = dirname(__FILE__) . '/../language/' . $this->lang . '.php';

		if( !file_exists($file) ) {
			die( $this->_response(209,'internal error') );
		}

		require_once $file;

		$this->dicti = $lang;
	}

	public function respCode($code, $data='') {
		$this->_response($code, isset($this->dicti[$code])?$this->dicti[$code]:$this->dicti[0], $data);
		exit;
	}

	private function _response($code, $message, $data='') {
		$this->view->assign('code', "$code");
		$this->view->assign('message', $message);
		if($data != '') $this->view->assign('data', $data);
		$this->view->display();
	}

	protected function checkMandatory($vars, $return=false) {
		if(is_array($vars)){
			foreach($vars as $var){
				$result = $this->checkMandatory($var, $return);
				if($result === false && $return == true){
					return false;
				}
			}
		}
		else{
			if( !isset($_REQUEST[$vars]) ){
				$this->logger->write('debug', 'Missing required parameter: ' . $vars);
				if($return == false) {
					$this->respCode('201');
					exit;
				}
				else{
					return false;
				}
			}
			else{
				if($_REQUEST[$vars] == ''){
					$this->logger->write('debug', 'Required parameter is empty: ' . $vars);
					if($return == false) {
						$this->respCode('201');
						exit;
					}
					else{
						return false;
					}
				}
			}
		}

		if($return == true) return true;
	}



	protected function redirect($url){
		header('location:' . $url); exit;
	}

}

