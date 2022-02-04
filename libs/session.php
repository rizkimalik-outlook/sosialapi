<?php
if(session_id() === '') session_start();
class Libs_Session {
	
	public static function clear($key='') {
		if($key == ''){
			unset($_SESSION['sosialapi']);
		}
		else{
			unset($_SESSION['sosialapi'][$key]);
		}
	}

	public static function set($key, $value) {		
		$_SESSION['sosialapi'][$key] = serialize($value);
	}
	
	public static function get($key){
		return isset($_SESSION['sosialapi'][$key]) ? unserialize($_SESSION['sosialapi'][$key]) : false;
	}
}
