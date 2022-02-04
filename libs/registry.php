<?php
class Libs_Registry {
	private static $storage = array();

	public static function remove($key) {
        if (isset(self::$storage[$key]))
            unset(self::$storage[$key]);
	}

	public static function set($key, $value) {
		self::$storage[$key] = $value;
	}
	
	public static function get($key){
		if( isset(self::$storage[$key]) ) {
			return self::$storage[$key]; 
		}
		else{
			return false;
		}
	}
}
