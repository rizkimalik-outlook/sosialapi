<?php
/**
 * @author zenal
 *
 */
class Libs_Util_Cli {
	/**
	 * @var array
	 */
	protected $argv = array ();
	/**
	 * @var array
	 */
	protected $params = array ();
	/**
	 * @var array
	 */
	protected $filter = null;
	/**
	 * @var string
	 */
	protected $prefix = '@';
	/**
	 * @var string
	 */
	protected static $filename = null;
	/**
	 *
	 *
	 * Database Connection
	 * 
	 * @var array
	 */
	private static $pdo = array();
	/**
	 *
	 *
	 * Enter description here ...
	 * 
	 * @var array
	 */
	public static $email = array ();
	/**
	 *
	 *
	 * Mongo database
	 * 
	 * @var Mongo
	 */
	private static $mongo = null;
	/**
	 *
	 *
	 * Collection from mongodb
	 * 
	 * @var MongoCollection
	 */
	private static $collection = null;
	/**
	 *
	 *
	 * File to lock
	 * 
	 * @var string
	 */
	const LOG_FILE = 'file';
	const LOG_OUTPUT = 'output';
	const LOG_NONE = null;
	/**
	 * @var unknown_type
	 */
	static $debugType = self::LOG_NONE;
	/**
	 * @var unknown_type
	 */
	static $cfg = array (
			'mysql' => array (),
	);
	/**
	 *
	 *
	 * Enter description here ...
	 * 
	 * @var boolean
	 */
	static $debug = true;
	/**
	 *
	 * @since Sep 7, 2011
	 * @param string $driver        	
	 * @return PDO | MongoDb
	 */
	public static function db($driver, array $option = null) {
		switch (strtolower ( $driver )) {
			case 'mysql' :
			case 'pdo' :
				if (null === $option)
					return self::pdo ();
				$args = array (
						'server' => null,
						'var' => null 
				);
				foreach ( $option as $op => $x ) {
					if (array_key_exists ( $op, $args )) {
						$args [$op] = $x;
					}
				}
				foreach ( $args as $p => $a ) {
					if (empty ( $a )) {
						unset ( $args [$p] );
					}
				}
				return call_user_func_array ( array (
						__CLASS__,
						'pdo' 
				), $args );
				break;
		}
		return null;
	}
	/**
	 * Close All Connection
	 * 
	 * @since Sep 7, 2011
	 */
	public static function close() {
		self::$pdo = array();
	}
	// * mysql connection * /
	/**
	 *
	 * @since Sep 7, 2011
	 * @return PDO
	 */
	private static function pdo($server = 'default', $var = 'db') {
		if (!isset(self::$pdo[$server]) || (isset(self::$pdo[$server]) && !self::$pdo[$server] instanceof PDO)) {
			if(!empty(self::$cfg['mysql'])){
				self::msg ( 'Config database can not be empty ' );
				return;
			}
			$cfg = self::$cfg['db'];
			$dsn = 'mysql:dbname=' . $cfg[$server]['database'] . ';host=' . $cfg[$server] ['hostname'];
			if(isset(self::$cfg['port'])){
				$dsn .= ";port=" . $cfg['port'];
			}
			$user = $cfg [$server] ['username'];
			$password = $cfg [$server] ['password'];
			try {
				self::$pdo[$server] = new PDO ( $dsn, $user, $password );
				self::dump ( "Try Connect Mysql" );
			} catch ( PDOException $e ) {
				self::msg ( 'Connection failed: ' . $e->getMessage () );
			}
		} else {
			self::dump ( "Get Mysql Object Static" );
		}
		return self::$pdo[$server];
	}
	/**
	 * @param array $argv
	 * @param array $filter
	 * @param unknown_type $prefix
	 */
	function __construct(array $argv, array $filter = null, $prefix = '@', $filename = null) {
		$this->argv = $argv;
		$this->filter = $filter;
		$this->prefix = $prefix;
		self::$filename = $filename;
		$this->_initArgs ();
	}
	/**
	 * @return string $filename
	 */
	public static function getFilename() {
		return self::$filename;
	}
	/**
	 * @param string $filename
	 */
	public static function setFilename($filename) {
		self::$filename = $filename;
	}
	/**
	 *
	 * @return the $argv
	 */
	public function getArgv() {
		return $this->argv;
	}
	/**
	 *
	 * @return the $params
	 */
	public function getParams() {
		return $this->params;
	}
	/**
	 *
	 * @return array $filter
	 */
	public function getFilter() {
		return $this->filter;
	}
	
	/**
	 *
	 * @return string $prefix
	 */
	public function getPrefix() {
		return $this->prefix;
	}
	/**
	 *
	 * @param array $params        	
	 * @return Libs_Util_Cli
	 */
	public function setParams(array $params) {
		$this->params = $params;
		return $this;
	}
	
	/**
	 *
	 * @since Sep 8, 2011
	 * @param array $filter        	
	 */
	protected function _initArgs() {
		$len = strlen ( $this->getPrefix () );
		for($i = 0; $i < count ( $this->argv ); $i ++) {
			if (substr ( $this->argv [$i], 0, $len ) == $this->getPrefix ()) {
				$value = null;
				if (isset ( $argv [$i + 1] )) {
					$value = $this->argv [$i + 1];
				}
				if (substr ( $value, 0, $len ) == $this->getPrefix ()) {
					$value = null;
				}
				$key = substr ( $argv [$i], $len );
				if (null !== $this->getFilter () && ! in_array ( $key, $this->getFilter () )) {
					continue;
				}
				$this->params [$key] = $value;
			}
		}
	}
	/**
	 *
	 * @since Sep 9, 2011
	 * @param array $helper_message        	
	 * @param string $prefix        	
	 */
	static function msgHelp(array $helper_message, $prefix) {
		$separator = "\t-> ";
		$msg = "";
		$ln = 12;
		$sample = array ();
		foreach ( $helper_message as $h => $d ) {
			if ($h != 'help')
				$sample [] = "$prefix$h value";
			if (is_string ( $d )) {
				$msg .= $prefix . $h . $separator . $d . "\n";
			} else if (is_array ( $d )) {
				$msg .= $prefix . $h;
				foreach ( $d as $k => $m ) {
					if (0 == $k) {
						$msg .= " -> ";
					} else {
						$msg .= $separator . "";
					}
					$msg .= $m . "\n";
				}
			}
		}
		$sample = "\tUse: php " . $_SERVER ['PHP_SELF'] . " [" . join ( ' | ', $sample ) . "]";
		$msg = "\n$msg\n$sample";
		return $msg;
	}
	/**
	 *
	 * @since Sep 7, 2011
	 */
	static function lock($exptime = 31536000000) {
		if (! self::isCli() ) {
			self::msg ( "Fatal error, use command line to execute!" );
		}
		file_put_contents ( self::lockFilename (), getmypid () . ":" . (time () + $exptime) );
	}
	/**
	 *
	 * @since Sep 16, 2011
	 * @return boolean
	 */
	static function isCli() {
		return defined ( 'STDIN' ) ? true : false;
	}
	/**
	 * Check if cli is alive
	 * @return boolean
	 */
	static function isAlive() {
		$expire = 0;
		$pid = null;
		if (! file_exists ( self::lockFilename () )) {
			return false;
		}
		$content = explode ( ":", file_get_contents ( self::lockFilename () ) );
		if (count ( $content ) > 0) {
			if (isset ( $content [0] ))
				$pid = ( int ) $content [0];
			if (isset ( $content [1] ))
				$expire = ( int ) $content [1];
		}
		if (null === $pid) {
			if (! $expire)
				return false;
			
			if (time () > $expire) {
				self::removeLock ();
			}
		} else {
			if (posix_getsid ( $pid ) === false) {
				// li_removeLock();
				return false;
			}
			return true;
		}
		return true;
	}
	/**
	 *
	 * @since Sep 7, 2011
	 */
	static function removeLock() {
		if (file_exists ( self::lockFilename () ))
			unlink ( self::lockFilename () );
	}
	/**
	 *
	 * @since Sep 7, 2011
	 * @return string
	 */
	static function lockFilename() {
		return sys_get_temp_dir () . '/' . self::$filename . '.lock';
	}
	/**
	 * Cli file log name
	 * 
	 * @see {@link self::dump()}
	 * @since Oct 27, 2011
	 */
	static function logFilename() {
		return sys_get_temp_dir () . '/' . self::$filename . '-' . date ( 'Ymd' ) . '.log';
	}
	
	/**
	 * Function send message on STDOUT and you can send email
	 * 
	 * @since Nov 14, 2011
	 * @param string $message        	
	 * @param boolean $delete        	
	 * @param boolean $subjectMail        	
	 * @param boolean $time        	
	 * @param boolean $die        	
	 */
	static function msg($message = '', $delete = false, $subjectMail = false, $time = false, $die = true) {
		if ($delete) {
			self::removeLock ();
		}
		if ($subjectMail && count ( self::$email ) > 0) {
			foreach ( self::$email as $mail ) {
				@mail ( $mail, $subjectMail, $message );
			}
		}
		if ($die)
			die ( ($time ? date ( "Y-m-d H:i:s" ) : "") . ' ' . $message . "\n" );
	}
	/**
	 * Don't use echo() **Echo writes to the STDOUT of your current session.
	 *
	 * If you logout, that will cause fatal errors and the daemon to die
	 * 
	 * @since Sep 7, 2011
	 * @param unknown_type $message        	
	 */
	static function dump($message = '-', $logType = null) {
		if (! self::$debug)
			return;
		if ($logType === null) {
			$logType = self::$debugType;
		}
		$message = (date ( "Y-m-d H:i:s" ) . ' ' . $message . "\n");
		switch ($logType) {
			case self::LOG_FILE :
				error_log ( $message, 3, self::logFilename () );
				break;
			// ase self::LOG_OUTPUT:echo $message;break;
		}
	}
	/**
	 *
	 * @since Nov 14, 2011
	 */
	static function sleep() {
		usleep ( 100 );
		return;
	}
	/**
	 *
	 * @since Sep 7, 2011
	 * @param mixed $value        	
	 */
	static function quote($value, $default = null) {
		if (! $value && null !== $default) {
			$value = $default;
		}
		if (is_object ( $value ) && method_exists ( $value, '__toString' )) {
			return rtrim ( ltrim ( $value->__toString (), '"' ), '"' );
		} else if (self::realInt ( $value )) {
			return $value;
		} elseif (is_float ( $value )) {
			return sprintf ( '%F', $value );
		}
		return /*'"' . */addcslashes ( $value, "\000\n\r\\'\"\032" )/* . '"'*/;
	}
	/**
	 *
	 * @since Sep 7, 2011
	 * @param int $int        	
	 */
	static function realInt($int) {
		// First check if it's a numeric value as either a string or number
		if (is_numeric ( $int ) === TRUE) {
			// It's a number, but it has to be an integer
			if (( int ) $int == $int) {
				return TRUE;
				// It's a number, but not an integer, so we fail
			} else {
				return FALSE;
			}
			// Not a number
		} else {
			return FALSE;
		}
	}
}
