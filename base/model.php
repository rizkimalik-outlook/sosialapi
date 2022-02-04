<?php
/**
 * 
 * @version 0.3
 * 
 */ 
class Base_Model extends Base_Master {
	
	protected $dsn;
	protected $conn;
	protected $config;
	
	/**
	public function __construct(){		
		//load config
		$this->config = Libs_Registry::get('config');
		if($this->config===false){
			$this->config = Config_Loader::getInstance();
		}
	}
	*/
	
	public function check_connection_database(){
		$database_connection = $this->config->get('database_connection');

		return $database_connection;
	}

	public function setDSN($dsn){
		$config = $this->config->get('dsn');
		$this->dsn = $config[$dsn];
	}
        
	
	public function loadConnection($dsn){
		$this->setDSN($dsn);
		$this->conn = new mysqli(
			$this->dsn['hostname'],
			$this->dsn['username'],
			$this->dsn['password'],
			$this->dsn['database'],
			$this->dsn['port']
		);
		
		if ($this->conn->connect_error) {
			die('Connect Error (' . $this->conn->connect_errno . ') ' . $this->conn->connect_error);
		}
		$this->conn->set_charset("utf8");
	}
	
	protected function _fetchAll($result) {
		$data = array();
		while( $row = $result->fetch_assoc() ){
			$data[] = $row;
		}
		return $data;
	}
/*
	untuk debug.. production gunakan lainnya
*/	
	protected function query($sql,$status=0){
		if($status==0){
			return $this->_run($sql);
		}else{ 
			return $this->_query($sql);
		}
	}
	protected function _query($sql){
		$start = microtime(1);
		$query = $this->conn->query($sql);
		$end   = substr(microtime(1) - $start, 0, 8);
 		if(!$query){
			$this->logger->write('error','[QUERY MYSQL:'.$sql.'][ERROR:'.$this->conn->error.']');
		}else{ 
                    //if($this->conn->affected_rows>0)
			//$this->logger->write('debug', '[QUERY MYSQL:'.$sql.'][TIME:'.$end.'][AFFECTED:'.$this->conn->affected_rows.']');
		}
		
		return $query;
	}
/*
	untuk production
*/
	protected function _run($sql){
		$result = $this->conn->query($sql);
		if($result){
			return $result;
		}else{ 
			$this->logger->write('error','[QUERY:'.$sql.'][error:'.$this->conn->error.']');
			return false;
		}
	}
        
        protected function _getBaseUserToken($token){
            $sql = sprintf("SELECT user_id,token,(UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(created_time)) as lifetime FROM tbl_session WHERE
                token='%s'",
                $token
            );

            $this->logger->write('query','GET USER BY TOKEN ['.$sql.']');
            $result = $this->conn->query($sql);
           
           if($result){
               if($this->conn->affected_rows != 0) {
                   $data = $result->fetch_assoc();
                   return $data;
               }else{
                  return false;
               }
           }else{
               return false;
           }
        }
        
        protected function _getBaseProfile($token){
            $user = $this->_getBaseUserToken($token);
            if(isset($user['user_id'])){
                $sql = sprintf("SELECT * FROM tbl_user WHERE id='%s'",$user['user_id']);
                $result = $this->conn->query($sql);
                $this->logger->write('query','GET USER ['.$sql.']');
                if($result){
                    if($this->conn->affected_rows != 0) {
                        $data = $result->fetch_assoc();
                        return $data;
                    }else{
                        return false;
                    }
                }else{
                        return false;
                }
            }else{
                return false;
            }
	}
	
	protected function resOne($sql, $debug=0){
		 $result=$this->query($sql, $debug);
		 
		if($result){
			$data = $result->fetch_assoc();
			if($debug==1)
				$this->logger->write('debug','[sql:'.$sql.'][data:'.json_encode($data).']');
				
			return $data;
		}else{
			return false;
		}
	}
	
	protected function insertData($table, $data)
	{
		$sql="insert into %s (%s)values(%s);";
		$aFields=$aData=array();
		foreach($data as $nm=>$val){
			$aFields[]="`$nm`";
			$aData[]="'".$this->conn->real_escape_string($val)."'";
		}
		
		$sql=sprintf($sql, 
			$table, implode(",",$aFields), implode(",",$aData)
		);
		
		$result=$this->_run($sql);
		if($result){
			return $this->conn->insert_id;
		}else{
			return false;
		}
	}
	
	protected function countData($table, $field, $where='1', $group='')
	{
		if($group!=''){
			$group="group by $group";
		}
		
		$sql=sprintf("select count(`%s`) c from `%s` where %s %s",
		 $field, $table, $where, $group);
		$query = $this->_run($sql);
		$this->logger->write('debug', '[QUERY:'.$sql.']  [AFFECTED:'.$this->conn->affected_rows.']');
		if($query){
		$data = $this->_fetchAll($query);
		$result=array(
		  'data'=>$data,
		  'total'=>$data[0]['c'],
		  'group_total'=>count($data)
		);
		
			return $result;	
		}else{ 
			return false;
		
		}
	}
/*
	mengembalikan nilai id unik. disarankan memberikan nama tablenya. Hindari penulisan nama id
*/	
	protected function idTable($table='',$step=1,$start=2207){
		if($table==''){
			$table='tbl_my_id';
		}else{ 
			$table.="_id";
		}
		
		if(intval($step)<=0)$step=1;
		$sql="select id from $table";
		$q=$this->_run($sql);
		if($q){
			$row=$this->resOne($sql);
			$id=$row['id'] + $step;
			$sql0="update $table set id=$id";
			$this->_query($sql0);
 
		}else{ 
			$id=$start;
			$sql0="CREATE TABLE IF NOT EXISTS `$table` (`id` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`id`))";
			$this->_query($sql0);
			$sql0="INSERT INTO `$table` (`id`) VALUES($id)";
			$this->_query($sql0);
			
		}
		 
		return $id;
	} 
	
	#$query = array(
	#    'field1'=>'dsadas',
	#    'field2'=>'dasd','dasdas'=>'dasdsa');
	
	# createQuery($query,'tbl_nama','update',array('data'=>'dsadada','sadad'=>'dsad'));
	
	# createQuery($query,'tbl_nama','update',array('freequery'=>"dasdsa d"));
	protected function createQuery($arrays,$table,$action='insert',$where=array()){
	    $q = ''; $num = 0;
	    foreach ($arrays as $key => $value) {
	        $q .= "$key ='$value'".((count($arrays)==$num+1)? '':',');
	        $num++;
	    }
	    if($action=='insert'){
	        return 'INSERT INTO '.$table.' SET '.$q;
	    }elseif($action=='update'){
	        if(count($where)!=0){
	            if(isset($where['freequery'])){
	                return 'UPDATE '.$table.' SET '.$q.' WHERE '.$where['freequery'];
	            }else{
	                $c = ''; $no = 0;
	                foreach ($where as $key => $value) {
	                    $c .= (($no==0) ? '':' AND ');
	                    $c .= "$key ='$value'";
	                 //   $c .= ((count($where)==$no+1) ? ' AND ':' ');
	                    $no++;
	                }
	                return 'UPDATE '.$table.' SET '.$q.' WHERE '.$c;
	            }
	        }else{
	            return 'UPDATE '.$table.' SET '.$q;
	        }
	        
	    }
	        
	}
	
	public function close(){
		$this->conn->close();
	}
}
