<?php
Class Modules_Language_Parser {
	
	private $lang;
	private $arrLang;
	private $enableLanguage = array('id','en');
	
	public function __construct($lang='id'){
		$this->setLanguage($lang);
	}
	
	public function setLanguage($lang){
		if( !in_array($lang, $this->enableLanguage) ){
			die('invalid language request');
		}
		
		if($this->lang !== $lang){
			$this->arrLang = array();
			$this->lang = strtolower($lang);
			$class = 'Language_'.$this->lang;
			$obj = new $class;
			$this->arrLang = $obj->language;
		}
	}
	
	public function getLanguage(){
		return $this->lang;
	}
	
	public function get($vars){
		if( isset($this->arrLang[$vars]) ){
			return $this->arrLang[$vars];
		}
		else{
			return false;
		}
	}
	
	public function loadExternal($obj){
		if( isset($obj->language) ){
			$tmp = array_merge($this->arrLang, $obj->language);
			$this->arrLang = array();
			$this->arrLang = $tmp;
			unset($tmp);
		}
	}
}