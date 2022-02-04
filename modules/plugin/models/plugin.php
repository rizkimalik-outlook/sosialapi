<?php
class Models_Plugin extends Base_Model {
	
	public function __construct(){
		parent::__construct();

		if($this->check_connection_database()){
			$this->loadConnection('default');
		}
		
	}
	
	public function isExists($plugin){
		$sql = sprintf("SELECT plugin_id FROM tbl_core_plugin WHERE plugin_name='%s' AND plugin_status=1 LIMIT 1", 
			$this->conn->real_escape_string(strtoupper($plugin))
		);
		$result = $this->conn->query($sql);
		
		if($this->conn->affected_rows != 0){
			return true;
		}
		else{
			return false;
		}
	}
}
