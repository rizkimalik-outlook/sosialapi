<?php
class Config_Application {

	public $defaultController 	= 'home';
	
	public $smartyDebug			= false;
	public $smartyForceCompile	= true;
	public $smartyCache			= false;
	public $smartyCacheLifetime	= 300; //in second
	
	public $saltCookies			= 'iTjuStRanDomWoRd';

	// email
	public $emailHost			= '';
	public $emailPort			= '465';
	public $emailAuth			= true;
	public $emailUsername		= '';
	public $emailPassword		= '';
	public $emailGlobalSender	= '';
	
}
