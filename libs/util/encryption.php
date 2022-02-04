<?php
/**
 *  
 *  
 * 
 * @author 
 **/

Class Libs_Util_Encryption
{
    public static function encrypt_msisdn($text, $salt_key) {
        if (Libs_Registry::get('_is_base64'))
        {
            return base64_encode($text);
        }
        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypttext = mcrypt_encrypt(MCRYPT_3DES, $salt_key, $text, MCRYPT_MODE_ECB, $iv);
        $txt = self::modified_base64_encode($encrypttext);
        return $txt;
    }

    public static function decrypt_msisdn($text, $salt_key) {
        if (Libs_Registry::get('_is_base64'))
        {
            return base64_decode($text);
        }
        $decrypttext_base64 = self::modified_base64_decode($text);
        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_3DES, $salt_key, $decrypttext_base64, MCRYPT_MODE_ECB, $iv);
        return $decrypttext;
    }

    public static function encrypt_msisdn_oasis($text, $oasis_salt_key) {
	    $text = trim($text);
            $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $encrypttext = mcrypt_encrypt(MCRYPT_3DES, $oasis_salt_key, $text, MCRYPT_MODE_ECB, $iv);
            $txt = self::modified_base64_encode($encrypttext);
            return trim($txt);
    }

    public static function decrypt_msisdn_oasis($text, $oasis_salt_key) {
	    $text = trim($text);
            $decrypttext_base64 = self::modified_base64_decode($text);
            $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $decrypttext = mcrypt_decrypt(MCRYPT_3DES, $oasis_salt_key, $decrypttext_base64, MCRYPT_MODE_ECB, $iv);
            return trim($decrypttext);
    }
    
	public static function encrypt_msisdn_sms($text, $sms_salt_key) {
	    $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    $encrypt = mcrypt_encrypt(MCRYPT_3DES, substr($sms_salt_key,0,20), trim($text), MCRYPT_MODE_ECB, $iv);
	    $msisdn = self::modified_base64_encode($encrypt);
	    return trim($msisdn);
	}

	public static function decrypt_msisdn_sms($text, $sms_salt_key){
		$decrypt_base64 = self::modified_base64_decode(trim($text));
		$iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypt = mcrypt_decrypt(MCRYPT_3DES, substr($sms_salt_key,0,20), $decrypt_base64, MCRYPT_MODE_ECB, $iv);
		return trim($decrypt);
	}    

    public static function modified_base64_encode($txt) {
        $search = array("+", "/");
        $replace = array("-", "_");
        //$txt = str_replace($search, $replace, base64_encode($txt));
	$encoded = base64_encode($txt);
  	$txt = str_replace('+','-', $encoded);
	$txt = str_replace('/','_', $txt);
        return $txt;
    }

    public static function modified_base64_decode($txt) {
        $search = array("-", "_");
        $replace = array("+", "/");
        //$txt = base64_decode(str_replace($search, $replace, $txt));
	$txt = str_replace('-','+', $txt);
	$txt = base64_decode( str_replace('_','/', $txt) );
        return $txt;
    }
    
	public static function PhoneCheck($number) {
		$pattern = '/^6283[1238][0-9]{6,8}$/';
		return preg_match($pattern, $number);
	}
}
