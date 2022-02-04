<?php
class Modules_IO_Parser {
	
	public function __construct() {
		$this->_sanitize_globals();
	}
	
	/**
	* Sanitize Globals
	*
	* This function does the following:
	*
	* Unsets $_GET data (if query strings are not enabled)
	*
	* Unsets all globals if register_globals is enabled
	*
	* Standardizes newline characters to \n
	*
	* @access	private
	* @return	void
	*/
	private function _sanitize_globals()
	{
		// It would be "wrong" to unset any of these GLOBALS.
		$protected = array('_SERVER', '_GET', '_POST', '_FILES', '_REQUEST',
							'_SESSION', '_ENV', 'GLOBALS', 'HTTP_RAW_POST_DATA',
							'system_folder', 'application_folder', 'BM', 'EXT',
							'CFG', 'URI', 'RTR', 'OUT', 'IN');

		// Unset globals for securiy.
		// This is effectively the same as register_globals = off
		foreach (array($_GET, $_POST, $_COOKIE) as $global)
		{
			if ( ! is_array($global))
			{
				if ( ! in_array($global, $protected))
				{
					global $$global;
					$$global = NULL;
				}
			}
			else
			{
				foreach ($global as $key => $val)
				{
					if ( ! in_array($key, $protected))
					{
						global $$key;
						$$key = NULL;
					}
				}
			}
		}

		// Clean $_GET Data
		if (is_array($_GET) AND count($_GET) > 0)
		{
			foreach ($_GET as $key => $val)
			{
				$_GET[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
		}
		

		// Clean $_POST Data
		if (is_array($_POST) AND count($_POST) > 0)
		{
			foreach ($_POST as $key => $val)
			{
				$_POST[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
		}

		// Clean $_COOKIE Data
		if (is_array($_COOKIE) AND count($_COOKIE) > 0)
		{
			// Also get rid of specially treated cookies that might be set by a server
			// or silly application, that are of no use to a CI application anyway
			// but that when present will trip our 'Disallowed Key Characters' alarm
			// http://www.ietf.org/rfc/rfc2109.txt
			// note that the key names below are single quoted strings, and are not PHP variables
			unset($_COOKIE['$Version']);
			unset($_COOKIE['$Path']);
			unset($_COOKIE['$Domain']);

			foreach ($_COOKIE as $key => $val)
			{
				$_COOKIE[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
		}

		// Sanitize PHP_SELF
		$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);
	}
	
	/**
	* Clean Input Data
	*
	* This is a helper function. It escapes data and
	* standardizes newline characters to \n
	*
	* @access	private
	* @param	string
	* @return	string
	*/
	private function _clean_input_data($str)
	{
		if (is_array($str))
		{
			$new_array = array();
			foreach ($str as $key => $val)
			{
				$new_array[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
			return $new_array;
		}

		return $str;
	}


	/**
	* Clean Keys
	*
	* This is a helper function. To prevent malicious users
	* from trying to exploit keys we make sure that keys are
	* only named with alpha-numeric text and a few other items.
	*
	* @access	private
	* @param	string
	* @return	string
	*/
	private function _clean_input_keys($str)
	{
 		if ( ! preg_match("/^[a-z0-9:_\\/-]+$/i", $str))
 		{
 			exit('Disallowed Key Characters.');
 		}
		
		return $str;
	}
	
	/**
	 * Fetch from array
	 *
	 * This is a helper function to retrieve values from global arrays
	 *
	 * @access	private
	 * @param	array
	 * @param	string
	 * @return	string
	 */
	private function _fetch_from_array(&$array, $index = '') {
		if ( ! isset($array[$index])) {
			return FALSE;
		}

		return $array[$index];
	}
	
        private function filterdata($data) {
            $data = trim(htmlentities(strip_tags($data)));

            if (get_magic_quotes_gpc())
                    $data = stripslashes($data);

            //$data = mysql_real_escape_string($data);

            return $data;
        }
	/**
	* Fetch an item from the GET array
	*
	* @access	public
	* @param	string
	* @return	string
	*/
	function get($index = NULL) {
		// Check if a field has been provided
		if ($index === NULL AND ! empty($_GET))
		{
			$get = array();

			// loop through the full _GET array
			foreach (array_keys($_GET) as $key)
			{
				$get[$key] = $this->_fetch_from_array($_GET, $key);
			}
			return $get;
		}

		return $this->_fetch_from_array($_GET, $index);
	}


	/**
	* Fetch an item from the POST array
	*
	* @access	public
	* @param	string
	* @return	string
	*/
	public function post($index = NULL)
	{
		// Check if a field has been provided
		if ($index === NULL AND ! empty($_POST))
		{
			$post = array();

			// Loop through the full _POST array and return it
			foreach (array_keys($_POST) as $key)
			{
				$post[$key] = $this->_fetch_from_array($_POST, $key);
			}
			return $post;
		}

		return $this->_fetch_from_array($_POST, $index);
	}


	/**
	* Fetch an item from either the GET array or the POST
	*
	* @access	public
	* @param	string	The index key
	* @return	string
	*/
	function get_post($index = '')
	{
		if ( ! isset($_POST[$index]) )
		{
			return $this->get($index);
		}
		else
		{
			return $this->post($index);
		}
	}


	/**
	* Fetch an item from the COOKIE array
	*
	* @access	public
	* @param	string
	* @return	string
	*/
	function cookie($index = '')
	{
		return $this->_fetch_from_array($_COOKIE, $index);
	}

	
	/**
	* Fetch an item from the SERVER array
	*
	* @access	public
	* @param	string
	* @return	string
	*/
	function server($index = '')
	{
		return $this->_fetch_from_array($_SERVER, $index);
	}


	/**
	* Fetch the IP Address
	*
	* @access	public
	* @return	string
	*/
	public function ip_address()
	{
		if (config_item('proxy_ips') != '' && $this->server('HTTP_X_FORWARDED_FOR') && $this->server('REMOTE_ADDR'))
		{
			$proxies = preg_split('/[\s,]/', config_item('proxy_ips'), -1, PREG_SPLIT_NO_EMPTY);
			$proxies = is_array($proxies) ? $proxies : array($proxies);

			$ip_address = in_array($_SERVER['REMOTE_ADDR'], $proxies) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('REMOTE_ADDR') AND $this->server('HTTP_CLIENT_IP'))
		{
			$ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('REMOTE_ADDR'))
		{
			$ip_address = $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('HTTP_CLIENT_IP'))
		{
			$ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('HTTP_X_FORWARDED_FOR'))
		{
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($ip_address === FALSE)
		{
			$ip_address = '0.0.0.0';
			return $ip_address;
		}

		if (strpos($ip_address, ',') !== FALSE)
		{
			$x = explode(',', $ip_address);
			$ip_address = trim(end($x));
		}

		if ( ! $this->valid_ip($ip_address))
		{
			$ip_address = '0.0.0.0';
		}

		return $ip_address;
	}


	/**
	* Validate IP Address
	*
	* Updated version suggested by Geert De Deckere
	*
	* @access	public
	* @param	string
	* @return	string
	*/
	public function valid_ip($ip)
	{
		$ip_segments = explode('.', $ip);

		// Always 4 segments needed
		if (count($ip_segments) != 4)
		{
			return FALSE;
		}
		// IP can not start with 0
		if ($ip_segments[0][0] == '0')
		{
			return FALSE;
		}
		// Check each segment
		foreach ($ip_segments as $segment)
		{
			// IP segments must be digits and can not be
			// longer than 3 digits or greater then 255
			if ($segment == '' OR preg_match("/[^0-9]/", $segment) OR $segment > 255 OR strlen($segment) > 3)
			{
				return FALSE;
			}
		}

		return TRUE;
	}


	/**
	* User Agent
	*
	* @access	public
	* @return	string
	*/
	public function user_agent() {
		$this->user_agent = ( ! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];

		return $this->user_agent;
	}
	
	/**
	 * Request Headers
	 *
	 * In Apache, you can simply call apache_request_headers(), however for
	 * people running other webservers the function is undefined.
	 *
	 * @return array
	 */
	public function request_headers()
	{
		// Look at Apache go!
		if (function_exists('apache_request_headers'))
		{
			$headers = apache_request_headers();
		}
		else
		{
			$headers['Content-Type'] = (isset($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');

			foreach ($_SERVER as $key => $val)
			{
				if (strncmp($key, 'HTTP_', 5) === 0)
				{
					$headers[substr($key, 5)] = $this->_fetch_from_array($_SERVER, $key);
				}
			}
		}

		// take SOME_HEADER and turn it into Some-Header
		foreach ($headers as $key => $val)
		{
			$key = str_replace('_', ' ', strtolower($key));
			$key = str_replace(' ', '-', ucwords($key));

			$this->headers[$key] = $val;
		}

		return $this->headers;
	}


	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 *
	 * @param 	string		array key for $this->headers
	 * @return 	mixed		FALSE on failure, string on success
	 */
	public function get_request_header($index) {
		if (empty($this->headers))
		{
			$this->request_headers();
		}

		if ( ! isset($this->headers[$index]))
		{
			return FALSE;
		}

		return $this->headers[$index];
	}


	/**
	 * Is ajax Request?
	 *
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
	 *
	 * @return 	boolean
	 */
	public function is_ajax_request() {
		return ($this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
	}


	/**
	 * Is cli Request?
	 *
	 * Test to see if a request was made from the command line
	 *
	 * @return 	boolean
	 */
	public function is_cli_request() {
		return (php_sapi_name() == 'cli') or defined('STDIN');
	}
	
	/**
	 * 
	 * @param unknown_type $username
	 */
	public function isValidUsername($username){
		return (bool) preg_match("/^[a-z0-9_-]{3,64}$/", $username);
	}
	
	/**
	 * normalize msisdn
	 */
	public function normalizeMsisdn($msisdn){
		if(substr($msisdn,0,1) == 0){
			return '62' . substr($msisdn, 1, strlen($msisdn)); 
		}
		if(substr($msisdn,0,1) == 8){
			return '62' . substr($msisdn, 0, strlen($msisdn));
		}
		if(substr($msisdn,0,1) == '+'){
			return substr($msisdn, 1, strlen($msisdn)); 
		}
		return $msisdn;
	}
	
	public function modified_base64_encode($txt) {
		$search = array("+", "/");
		$replace = array("-", "_");
		$txt = str_replace($search, $replace, base64_encode($txt));
		return $txt;
	}
	
	public function modified_base64_decode($txt) {
		$search = array("-", "_");
		$replace = array("+", "/");
		$txt = base64_decode(str_replace($search, $replace, $txt));
		return $txt;
	}
	
	public function encrypt($saltKey, $string) {
	    $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    $encrypt = mcrypt_encrypt(MCRYPT_3DES, substr($saltKey,0,20), trim($string), MCRYPT_MODE_ECB, $iv);
	    $msisdn = $this->modified_base64_encode($encrypt);
	    return trim($msisdn);
	}

	public function decrypt($saltKey, $string){
		$decrypt_base64 = $this->modified_base64_decode(trim($string));
		$iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypt = mcrypt_decrypt(MCRYPT_3DES, substr($saltKey,0,20), $decrypt_base64, MCRYPT_MODE_ECB, $iv);
		return trim($decrypt);
	}
	
	public function decryptRequest() {
		if(isset($_POST['data']) && $_POST['data']!=''){
			$arrData = json_decode($_POST['data'], 1);
			$_POST 	 = array_merge($_POST,$arrData);
                        $_REQUEST= array_merge($_REQUEST,$arrData);
			unset($_POST['data']); unset($_REQUEST['data']);
		}
	}
}
