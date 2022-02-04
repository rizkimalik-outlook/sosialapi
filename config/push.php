<?php
class Config_Push {
	const SMS = 0;
	const PUSH = 1;
	const SMS_PUSH = 2;
	/**
	 * @var boolean
	 */
	protected $pushAll = self::SMS_PUSH;
	/**
	 *
	 * @var stdClass
	 */
	public $cfgBlackbarry = null;
	/**
	 * @var stdClass
	 */
	public $cfgAndroid = null;
	/**
	 * @var stdClass
	 */
	public $cfgIphone = null;
	/**
	 * @var array
	 */
	public $cfg = array(
				//'host' => '10.70.5.4',
				'host' => '10.70.5.153',
				'port' => 11300,
				'timeout' => null,
				'tube_prefix' => 'mt_',
				'smsc' => array(
					'username' => 'odpsmpp_usr',
					'password' => 'odpsmpp_pwd',
					'smsc' => 'smsc_hcpt',
					'dlr-mask' => '0',
				),
				'tube_prefix_pcrf' => 'pcrf_',
	);
	/**
	 * @var unknown_type
	 */
	public $logFile = "/var/log/push/push_";

	public $smsInboxApi = "http://10.70.5.26:22013/cgi-bin/sendsms";
	/**
	 * @return boolean $pushAll
	 */
	public function getPushAll() {
		return $this->pushAll;
	}

	/**
	 * @param boolean $pushAll
	 */
	public function setPushAll($pushAll) {
		$this->pushAll = $pushAll;
	}

	/**
	 * Constructor 
	 */
	function __construct() {
		/**
		 * Android config
		 */
		$this->cfgAndroid = new stdClass ();
		$this->cfgAndroid->url = "";
		$this->cfgAndroid->username = '';
		$this->cfgAndroid->password = '';
		$this->cfgAndroid->source = '';
		$this->cfgAndroid->urlGcm = "";
		$this->cfgAndroid->apiKey = '';
		$this->cfgAndroid->senderID = '';
		$this->cfgAndroid->authCode = null;
		$this->cfgAndroid->authUrl = "https://www.google.com/accounts/ClientLogin";
		$this->cfgAndroid->service = "";
		$this->cfgAndroid->regid = "";
		/**
		 * Blackbarry
		 */
		$this->cfgBlackbarry = new stdClass();
		$this->cfgBlackbarry->url = "https://cp265.pushapi.na.blackberry.com/mss/PD_pushRequest";
		$this->cfgBlackbarry->appid = "";
		$this->cfgBlackbarry->password = "";
        

	}
}
