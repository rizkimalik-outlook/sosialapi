<?php
class Modules_Plugin_Base extends Base_Module {
	
	protected $input;
	protected $response;
	protected $config;
	protected $controller;
	protected $lang;
	protected $email;
	
	protected $pluginPath = ''; // running plugin path
	protected $model;
	protected $libs;
	protected $pluginConfig;
	protected $project;
	protected $pluginName;
	
	// for activity logging perpose
	protected $clientcode 	= 0; 
	protected $appcode	= 0;
	protected $token	= false;
	protected $function 	= '';
	protected $quizid       = 0;
	
	public function __construct() {
            parent::__construct();
            $this->input        = new Modules_IO_Parser;
            $this->response     = new Modules_IO_Response;
            $this->encryption   = new Modules_IO_Encryption;
            $this->config       = Config_Loader::getInstance();
            $this->setController( get_class($this) );
            

            // load language
            $this->lang		= New Modules_Language_Parser;
            $this->_loadLanguage($this->lang->getLanguage());
        }
        

        protected function _success($data='', $return=0) {
		$message = $this->lang->get('c200');
		$this->activity->log(
			$this->clientcode, 
			$this->appcode, 
			get_class($this), 
			$this->function, 
			count($_REQUEST)==0 ? '':json_encode($_REQUEST), 
			'200', 
			$message
		);
		
		$response = $this->response->success($data);
		
		if( $return === 0 ){
			echo $response; exit;
		}
		else{
			return $response;
		}
	}


	protected function _error($data='', $return=0) {
		$message = 'error';
		$this->activity->log(
			$this->clientcode, 
			$this->appcode, 
			get_class($this), 
			$this->function, 
			count($_REQUEST)==0 ? '':json_encode($_REQUEST), 
			'200', 
			$message
		);
		
		$response = $this->response->error($data);

		
		if( $return === 0 ){
			echo $response; exit;
		}
		else{
			return $response;
		}
	}
	
	protected function _failed($code, $return=0) {
		$message = $this->lang->get('c'.$code);	
	
		$response = $this->response->failed($code, $message);
		
		if( $return === 0 ){
			echo $response; exit;
		}
		else{
			return $response;
		}
	}
	
	public function setController($controller) {
		$this->controller = $controller;
	}
	
	public function getContorller() {
		return $this->controller;
	}
        
        protected function _header(){
            
            
        }


        protected function _mandatory($vars, $method='post'){
           
		if( is_array($vars) ) {
			foreach($vars as $var){
				$this->_mandatory($var, $method);
			}
		}else{
			if($method == 'request') { //DEFAULT
				if( !isset($_REQUEST[$vars]) || $_REQUEST[$vars]=='' ){
					$this->_failed(203);
				}
			}
			if($method == 'post') {
				if( !isset($_POST[$vars]) || $_POST[$vars]=='' ){
					$this->_failed(203);
				}
			}
			if($method == 'get') {
				if( !isset($_GET[$vars]) || $_GET[$vars]=='' ){
					$this->_failed(203);
				}
			}
                        if($method == 'encryption') {
				if( !isset($this->paramEncrypt->$vars) || $this->paramEncrypt->$vars=='' ){
                                    if( !isset($_REQUEST[$vars]) || $_REQUEST[$vars]=='' ){
					$this->_failed(203);
                                    }
				}
			}
		}
	}
        
        protected function encryptedData($key=NULL,$method='post'){
            if(isset($this->paramEncrypt->$key)){
                return $this->paramEncrypt->$key;
            }else{
                if($method=='post')
                    return $this->input->post($key);
                else
                    return $this->input->get($key);
            }
        }


        private function _parseLocation(){
		if( $this->pluginPath == '' ){
			$controller = $this->getContorller();
			$o = explode('_', $controller);
			$this->project 		= strtolower($o[1]);
			$this->pluginName	= strtolower($o[2]);
			return APP_PATH . 'plugins/' . $this->project . '/' . $this->pluginName . '/' ;
		}
		else{
			return $this->pluginPath;
		}
	}
	
	private function _getModelLocation(){
		return $this->_parseLocation() . 'models/';
	}
	
	private function _getConfigLocation(){
		return $this->_parseLocation() . 'config/';
	}
        
        private function _getLibsLocation(){
		return $this->_parseLocation() . 'libs/';
	}
	
	private function _getLanguageLocation(){
		//return $this->_parseLocation() . 'language/';
                
                return APP_PATH . 'language/';
                
	}
             
        protected function _loadConfig($pluginConfig) {
            $pluginConfig = strtolower($pluginConfig);
            $file = $this->_getConfigLocation() . $pluginConfig . '.php';
            if( file_exists($file) ){
                    if( isset($this->pluginConfig[$pluginConfig]) ){
                            return $this->pluginConfig[$pluginConfig];
                    }
                    else{
                            require_once $file;
                            $classname = 'Config_'.$this->project.'_'.$this->pluginName.'_'.$pluginConfig;
                            return $this->pluginConfig[$pluginConfig] = new $classname;
                    }
            }
            else{
                    die('Configuration not found');
            }
	}
        
	protected function _loadLibs($libs) {
		$libs = strtolower($libs);
		$file = $this->_getLibsLocation() . $libs . '.php';
		if( file_exists($file) ){
			if( isset($this->libs[$libs]) ){
				return $this->libs[$libs];
			}
			else{
				require_once $file;
				$classname = 'Libs_'.$this->project.'_'.$this->pluginName.'_'.$libs;
				return $this->libs[$libs] = new $classname;
			}
		}
		else{
			die('library not found');
		}
	}
        
	protected function _loadModel($model) {
		$model = strtolower($model);
		$file = $this->_getModelLocation() . $model . '.php';
		if( file_exists($file) ){
			if( isset($this->model[$model]) ){
				return $this->model[$model];
			}
			else{
				require_once $file;
				$classname = 'Models_'.$this->project.'_'.$this->pluginName.'_'.$model;
				return $this->model[$model] = new $classname;
			}
		}
		else{
			die('model not found');
		}
	}
	
	protected function _loadLanguage($lang) {
		$lang = strtolower($lang);
		$file = $this->_getLanguageLocation() . $lang . '.php';
                
		if( file_exists($file) ){
			require_once $file;
			//$classname = 'Language_'.$this->project.'_'.$this->pluginName.'_'.$lang;
                        
                        $classname = 'Language_'.$lang;
                        
			$this->lang->loadExternal(new $classname);
		}
		else{
			die('language not found');
		}
	}
	
	protected function _setLanguage($lang){
		$this->lang->setLanguage($lang);
		$this->_loadLanguage($lang);
	}
	
	protected function _isTokenValid($clientcode, $token, $username='') {
		$model 	= $this->_loadModel('session');
		return $model->isValidToken($clientcode, $token, $username);
	}
	
	public function exec($function){
		$this->function = $function;
		$this->$function();
	}
}
