<?php
class libs_http_request
{
	public static function get($url, $getfields, $timeout = 10) 
    {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url . '?' . $getfields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public static function post($url, $postfields, $timeout = 10) 
    {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public static function postBody($url, $body, $timeout = 10) 
    {
		return self::post($url, $body, $timeout);
	}

	public function getRealIpAddr()
    {
	    if (!empty($_SERVER['HTTP_CLIENT_IP'])){
	    	$ip = $_SERVER['HTTP_CLIENT_IP'];
	    }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
	    	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    }else{
	    	$ip = $_SERVER['REMOTE_ADDR'];
	    }
	    return $ip;
	}
}
