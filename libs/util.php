<?php
Class Libs_Util {
	
	/**
	 * normalize msisdn
	 */
	public static function normalizeMsisdn($msisdn, $country_code='62'){
		if(substr($msisdn,0,1) == 0){
			return $country_code . substr($msisdn, 1, strlen($msisdn)); 
		}
		if(substr($msisdn,0,1) == 8){
			return $country_code . substr($msisdn, 0, strlen($msisdn));
		}
		if(substr($msisdn,0,1) == '+'){
			return substr($msisdn, 1, strlen($msisdn)); 
		}
		return $msisdn;
	}
	
	///////////////////////
	
	private static function modified_base64_encode($txt) {
		$search = array("+", "/");
		$replace = array("-", "_");
		$txt = str_replace($search, $replace, base64_encode($txt));
		return $txt;
	}
	
	private static function modified_base64_decode($txt) {
		$search = array("-", "_");
		$replace = array("+", "/");
		$txt = base64_decode(str_replace($search, $replace, $txt));
		return $txt;
	}
	
	public static function encrypt($saltKey, $string) {
	    $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    $encrypt = mcrypt_encrypt(MCRYPT_3DES, substr($saltKey,0,20), trim($string), MCRYPT_MODE_ECB, $iv);
	    $msisdn = self::modified_base64_encode($encrypt);
	    return trim($msisdn);
	}

	public static function decrypt($saltKey, $string){
		$decrypt_base64 = self::modified_base64_decode(trim($string));
		$iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypt = mcrypt_decrypt(MCRYPT_3DES, substr($saltKey,0,20), $decrypt_base64, MCRYPT_MODE_ECB, $iv);
		return trim($decrypt);
	}
	
	public static function bytesToSize($number, $precision = 2) {  
		if ($number) {
			$number = doubleval($number);
			if ($number/(float)1024000000 >= 1) {
				$txt = number_format(((float)$number/(float)1024000000), $precision)  ." GB";				
			} else if ($number/(float)1024000 >= 1) {
				$txt = number_format(($number/(float)1024000), $precision)  ." MB";
			} else if ($number/1024 >= 1) {
				$txt = number_format(($number/1024), $precision)  ." KB";
			} else {
				$txt = number_format($number, $precision) . " Byte";
			}
		} else $txt=0;

		return $txt;
	}

	public static function creditcardValidation($number) {
		// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
		$number = preg_replace('/\D/', '', $number);

		// Set the string length and parity
		$number_length = strlen($number);
		$parity            = $number_length % 2;

		// Loop through each digit and do the maths
		$total = 0;

		for ($i = 0; $i < $number_length; $i++) {
				$digit = $number[$i];

				// Multiply alternate digits by two
				if ($i % 2 == $parity) {
						$digit*=2;

						// If the sum is two digits, add them together (in effect)
						if ($digit > 9) {
								$digit-=9;
						}
				}

				// Total up the digits
				$total+=$digit;
		}
		if($total == 0) return false;
		// If the total mod 10 equals 0, the number is valid
		return ($total % 10 == 0) ? TRUE : FALSE;
	}
	
	
	public static function getImages($path, $filename) {
		// supported format
		$format = array('jpg', 'jpeg', 'png', 'gif', 'tif', 'bmp');
		
		$temp 	= explode('.', $filename);
		unset($temp[count($temp)-1]);
		$filename = implode('.', $temp);
		
		foreach($format as $ext) {			
			if( file_exists($path.$filename.'.'.$ext) ){
				return $filename.'.'.$ext;
			}
		}
		return false;
	}
}
