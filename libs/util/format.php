<?php
/**
 *  
 *  
 * 
 * @author 
 **/

Class Libs_Util_Format
{
	public static function load_view($plugin_name,$file_read,$data='')
	{
		$_componentFile = BASE_PATH.'/plugins/'.$plugin_name.'/view/'.$file_read.'.php';
		$data_file = file_get_contents($_componentFile);
		foreach($data as $key => $val) {
			$data_file = str_replace('{'.$key.'}',$val,$data_file);
		}
		return $data_file; 
	}

	// Formatting Functions
	public static function volume($number) {
		if ($number) {
			$number = doubleval($number);

			if ($number/1024000000 >= 1) {
				$txt = number_format(($number/1024000000), 2)  ." GB";
			} else if ($number/1024000 >= 1) {
				$txt = number_format(($number/1024000), 2)  ." MB";
			} else if ($number/1024 >= 1) {
				$txt = number_format(($number/1024), 2)  ." KB";
			} else {
				$txt = number_format($number, 2) . " Byte";
			}
		} else $txt=0;
		
		return $txt;
	}

	public static function tanggal($text) {
		$text = date('d-M-Y H:i', strtotime($text));
		return $text;
	}

    public static function uang($text,$lang='id') {

        $dec_point = ($lang == 'en') ? ',' : '.';
        $thousand_step = ($lang == 'en') ? ',' : '.';

        $text = number_format(doubleval($text),0,$dec_point,$thousand_step);

        return ($lang=='id'?'Rp ':'IDR ') .$text;
    }
	
	public static function currency($text,$lang)
	{
		if($lang=='id')	
			return 'Rp '.number_format($text,0,',','.');
		else
			return 'IDR '.number_format($text,0,'.',',');
	}

	// Other Functions
	public static function makeSeed() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 10000000);
	}
	
	public static function _genTxid() {
		return mt_rand(00,99) . substr(str_replace('.','',microtime(1)),-8);
	}
	
    // trx id format signature
    public static function genTrxId($msisdn=''){

        $micro = explode(' ', microtime());
        $time  = end($micro);
        
        // 02 for package
		$result = str_pad(substr('02',0,2), 2, '0', STR_PAD_LEFT);
		$result.= str_pad(rand(0, 99) . rand(0,99), 4, 0, STR_PAD_LEFT);
		$result.= $time;
		$result.= substr($msisdn, -2);
		return $result;
	}	

	public static function normalizeMsisdn($msisdn){
		if(substr($msisdn,0,1) == '0'){
			return '62' . substr($msisdn, 1, strlen($msisdn)); 
		}
		if(substr($msisdn,0,1) == '08'){
			return '62' . substr($msisdn, 0, strlen($msisdn));
		}
		if(substr($msisdn,0,1) == '+'){
			return substr($msisdn, 1, strlen($msisdn)); 
		}
		return $msisdn;
	}
	
	public function replace_achor($text,$replacement='')
	{
		$return = preg_replace("/href=(['\"]).*a>/i",$replacement, $text);
		$return = str_replace('<a','',$return);
		return $return;
	}

    public static function _number_format($number, $lang, $prefix = false)
    {
        $dec_point = ($lang == 'en') ? ',' : '.';
        $thousand_step = ($lang == 'en') ? ',' : '.';
        if ($prefix)
        {
            $prefix = ($lang == 'en') ? 'IDR ' : 'Rp ';
        }
        return $prefix.number_format($number, 0, $dec_point, $thousand_step);
    }

    public static function date_format_id($format,$date){
        return date($format, strtotime(preg_replace('/(\d{1,2})\/(\d{1,2})\/(19|20)(\d{2}) (\d{2}):(\d{2}):(\d{2})/', '\2/\1/\3\4 \5:\6:\7', $date)));
    }

	public static function isValidMsisdn($msisdn){
		if(!is_numeric($msisdn)){
			return false;
		}
		if(!preg_match('/^6289/', $msisdn)){
			return false;
		}
		return true;
	}
}
 
