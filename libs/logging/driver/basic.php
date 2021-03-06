<?php
if(!defined('LOG_SUMMARY'))	define('LOG_SUMMARY',  0);     /* Summary */
if(!defined('LOG_EMERG')) 	define('LOG_EMERG',    1);     /* System is unusable */
if(!defined('LOG_ALERT')) 	define('LOG_ALERT',    2);     /* Immediate action required */
if(!defined('LOG_CRIT'))  	define('LOG_CRIT',     3);     /* Critical conditions */
if(!defined('LOG_ERR'))   	define('LOG_ERR',      4);     /* Error conditions */
if(!defined('LOG_WARNING'))     define('LOG_WARNING',  5);     /* Warning conditions */
if(!defined('LOG_NOTICE')) 	define('LOG_NOTICE',   6);     /* Normal but significant */
if(!defined('LOG_INFO')) 	define('LOG_INFO',     7);     /* Informational */
if(!defined('LOG_DEBUG')) 	define('LOG_DEBUG',    8);     /* Debug-level messages */
if(!defined('LOG_PROCESS'))     define('LOG_PROCESS',  9);     /* Process flow load */
if(!defined('LOG_INPUT'))       define('LOG_INPUT',   10);     /* Input additional */
if(!defined('LOG_QUERY'))       define('LOG_QUERY',   11);     /* Query progress*/

class Libs_Logging_Driver_Basic {

	protected $threadId;
	protected $time;
	
	protected $logLevel 	 = 'query';
        protected $logSegmented	 = true;
	protected $logPrefix  	 = '';
	protected $logDuration   = 'daily';
	protected $logPath  	 = '/var/log/cmsent/';
	protected $logTimeFormat = 'Y-m-d H:i:s';
	protected $logLineFormat = '{datetime} {exectime} {threadId} {level} {file} {class} {function} {message}';
	protected $timeDigit     = 8; //0.000006
        protected $logAllowLevel = array(''); //{'debug','info','...'}
	
	public function __construct($option){
		if( isset($option['logLevel']) ) 	$this->logLevel 	= $option['logLevel'];
                if( isset($option['logSegmented']) ) 	$this->logSegmented 	= $option['logSegmented'];
		if( isset($option['logPath']) )  	$this->logPath  	= $option['logPath'];
		if( isset($option['logPrefix']) )  	$this->logPrefix	= $option['logPrefix'];
		if( isset($option['logTimeFormat']) ) 	$this->logTimeFormat	= $option['logTimeFormat'];
		if( isset($option['logLineFormat']) ) 	$this->logLineFormat	= $option['logLineFormat'];
                if( isset($option['logAllowLevel']) ) 	$this->logAllowLevel    = $option['logAllowLevel'];
		if( isset($option['logDuration']) )     $this->logDuration      = $option['logDuration'];
		$this->threadId	= substr(microtime(1),0,3) . rand(100,999);
		$this->time		= microtime();
	}
	
	protected function getRunTime(){
		return substr( ( microtime() - $this->time ), 0, $this->timeDigit );
	}
	
	protected function stringToPriority($level){
		switch(strtolower($level)){
			case 'summary': 	return LOG_SUMMARY; break;
			case 'emergency': 	return LOG_EMERG; break;
			case 'alert': 		return LOG_ALERT; break;
			case 'critical': 	return LOG_CRIT; break;
			case 'error': 		return LOG_ERR; break;
			case 'warning': 	return LOG_WARNING; break;
			case 'notice': 		return LOG_NOTICE; break;
			case 'info': 		return LOG_INFO; break;
			case 'debug': 		return LOG_DEBUG; break;
			case 'process':		return LOG_PROCESS; break;
			case 'input':		return LOG_INPUT; break;
                        case 'query':		return LOG_QUERY; break;
			default: 		return LOG_DEBUG; break;			
		}
	}
	
	protected function genFilename($level=""){
            $duration = date("Ymd");
            if($this->logDuration=='hourly')
                $duration = date("Ymd_H");

            if($this->logSegmented==true && $level!=""){
                return $this->logPrefix .'_'.$level.'_' . $duration;
            }else{
                return $this->logPrefix . '_' . $duration;
            }
	}
	
	public function write($strLevel='debug', $message){
		$level 		= $this->stringToPriority($strLevel);
		$logLevel 	= $this->stringToPriority($this->logLevel);
		$filename	= $this->genFilename($strLevel);
		$dateFormat	= date($this->logTimeFormat);
		
		$debug 		= debug_backtrace();
		$file		= $debug[0]['file'];
		$class		= $debug[1]['class'];
		$function	= $debug[1]['function'];
		
		if($strLevel != 'summary'){
			$message = preg_replace(array('(\s+)','(\t+)'), ' ', $message);
		}
		
		$content = str_replace(
			array('{datetime}', '{exectime}', '{threadId}', '{level}', '{file}', '{class}', '{function}', '{message}'),
			array('['.$dateFormat.']', $this->getRunTime(), $this->threadId, '['.$strLevel.']', $file, $class, $function, $message. "\n"),
			$this->logLineFormat			
		);
		
            if($level <= $logLevel){
                $allowing = explode(',',$this->logAllowLevel);
                if (in_array($strLevel,$allowing)){
                    return (boolean) file_put_contents($this->logPath . $filename, $content, FILE_APPEND);
                }
            }
	}
        private function read_size($file){
        if(filesize($file)<=500){
            return true;
        }else{
            return false;
        }   
    
    }
}
