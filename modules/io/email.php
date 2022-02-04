<?php
require_once ("Mail.php");
require_once ("Mail/mime.php");

Class Modules_IO_Email {
	
	private $host;
	private $port;
	private $auth;
	private $username;
	private $password;
	
	private $from;
	private $reciever;
	
	public function __construct($host,$port,$auth,$username,$password){
		$this->host = $host;
		$this->port = $port;
		$this->auth = $auth;
		$this->username	= $username;
		$this->password = $password;
	}
	
	public function setSender($from){
		$this->from = $from;
	}

	public function setReciever($reciever){
		$this->reciever = $reciever;
	}
	
	public function send($to, $subject, $message, $html=0, $from='') {
		$reciever = $to;
		if ($to == ''){
			$reciever = $this->reciever;
		}

		$headers["From"] 	= ($from=='') ? $this->from : $from;
		$headers["To"] 		= $reciever;
		$headers["Subject"] 	= $subject;
		$headers["Content-Type"]= 'text/html; charset=UTF-8';
		$headers["Content-Transfer-Encoding"]= "8bit";
		
		$mime = new Mail_mime;
		if($html == 0){
			$mime->setTXTBody($message);
		}else{
			$mime->setHTMLBody($message);
		}
		
		$mimeparams['text_encoding']="8bit";
		$mimeparams['text_charset']="UTF-8";
		$mimeparams['html_charset']="UTF-8";
		$mimeparams['head_charset']="UTF-8";		
		
		$body = $mime->get($mimeparams);
		$headers = $mime->headers($headers);
		
		// SMTP server name, port, user/passwd
		$smtpinfo["host"] 	= $this->host;
		$smtpinfo["port"] 	= $this->port;
		$smtpinfo["auth"] 	= $this->auth;
		$smtpinfo["username"] 	= $this->username;
		$smtpinfo["password"] 	= $this->password;
		$smtpinfo["debug"] 	= false;
		
		$to = array($reciever);
		
		// Create the mail object using the Mail::factory method
		$mail=& Mail::factory("smtp", $smtpinfo);

		@$mail->send($to, $headers, $body);
	}
}
