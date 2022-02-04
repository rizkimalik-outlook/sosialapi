<?php
class Config_Driver_File {
	
	public function __construct($config) { }
	
	public function getData(){
		return get_object_vars(new Config_Application);
	}
}