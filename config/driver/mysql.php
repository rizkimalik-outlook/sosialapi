<?php
class Config_Driver_Mysql {
	
	private $oMysql;
	
	public function __construct($config) {
		$dsnName= 'mysql';

		$dbData = new Libs_Db_Data;
		$dbData->hostname 	= $config['dsn'][$dsnName]['hostname'];
		$dbData->username 	= $config['dsn'][$dsnName]['username'];
		$dbData->password 	= $config['dsn'][$dsnName]['password'];
		$dbData->database 	= $config['dsn'][$dsnName]['database'];
		$dbData->port 	  	= $config['dsn'][$dsnName]['port'];
		$dbData->option	  	= $config['dsn'][$dsnName]['option'];
		$dbData->persistent	= $config['dsn'][$dsnName]['persistent'];
		$dbData->usecache  	= $config['dsn'][$dsnName]['usecache'];
		$dbData->cachedriver= $config['dsn'][$dsnName]['cachedriver'];
		$dbData->cacheparam = $config['dsn'][$dsnName]['cacheparam'];
		$dbData->charset	= $config['dsn'][$dsnName]['charset'];
		
		$dbManager = new Libs_Db_Manager($config['dsn'][$dsnName]['driver'], $dbData);
		$this->oMysql = $dbManager->getConnection();
	}
	
	public function getData(){
		$result = array();
		$data = $this->oMysql->fetch("SELECT * FROM smsgw_sso_configuration");
		if( $data != false ){
			foreach($data as $row){
				$result[$row['param']] = $row['value'];
			}	
			return $result;	
		}
		return $result;
	}
}