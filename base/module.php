<?php
class Base_Module extends Base_Master {
	
	public function __construct(){
		parent::__construct();
	}
	public function httpcode($code){
		switch ($code) {
                    case 100: return array('code'=>$code,'message'=>'Continue'); break;
                    case 101: return array('code'=>$code,'message'=>'Switching Protocols'); break;
                    case 200: return array('code'=>$code,'message'=>'OK'); break;
                    case 201: return array('code'=>$code,'message'=>'Created'); break;
                    case 202: return array('code'=>$code,'message'=>'Accepted'); break;
                    case 203: return array('code'=>$code,'message'=>'Non-Authoritative Information'); break;
                    case 204: return array('code'=>$code,'message'=>'No Content'); break;
                    case 205: return array('code'=>$code,'message'=>'Reset Content'); break;
                    case 206: return array('code'=>$code,'message'=>'Partial Content'); break;
                    case 300: return array('code'=>$code,'message'=>'Multiple Choices'); break;
                    case 301: return array('code'=>$code,'message'=>'Moved Permanently'); break;
                    case 302: return array('code'=>$code,'message'=>'Moved Temporarily'); break;
                    case 303: return array('code'=>$code,'message'=>'See Other'); break;
                    case 304: return array('code'=>$code,'message'=>'Not Modified'); break;
                    case 305: return array('code'=>$code,'message'=>'Use Proxy'); break;
                    case 400: return array('code'=>$code,'message'=>'Bad Request'); break;
                    case 401: return array('code'=>$code,'message'=>'Unauthorized'); break;
                    case 402: return array('code'=>$code,'message'=>'Payment Required'); break;
                    case 403: return array('code'=>$code,'message'=>'Forbidden'); break;
                    case 404: return array('code'=>$code,'message'=>'Not Found'); break;
                    case 405: return array('code'=>$code,'message'=>'Method Not Allowed'); break;
                    case 406: return array('code'=>$code,'message'=>'Not Acceptable'); break;
                    case 407: return array('code'=>$code,'message'=>'Proxy Authentication Required'); break;
                    case 408: return array('code'=>$code,'message'=>'Request Time-out'); break;
                    case 409: return array('code'=>$code,'message'=>'Conflict'); break;
                    case 410: return array('code'=>$code,'message'=>'Gone'); break;
                    case 411: return array('code'=>$code,'message'=>'Length Required'); break;
                    case 412: return array('code'=>$code,'message'=>'Precondition Failed'); break;
                    case 413: return array('code'=>$code,'message'=>'Request Entity Too Large'); break;
                    case 414: return array('code'=>$code,'message'=>'Request-URI Too Large'); break;
                    case 415: return array('code'=>$code,'message'=>'Unsupported Media Type'); break;
                    case 500: return array('code'=>$code,'message'=>'Internal Server Error'); break;
                    case 501: return array('code'=>$code,'message'=>'Not Implemented'); break;
                    case 502: return array('code'=>$code,'message'=>'Bad Gateway'); break;
                    case 503: return array('code'=>$code,'message'=>'Service Unavailable'); break;
                    case 504: return array('code'=>$code,'message'=>'Gateway Time-out'); break;
                    case 505: return array('code'=>$code,'message'=>'HTTP Version not supported'); break;
                    default:
                        return array('code'=>5000,'message'=>'Unknown http status code "' . htmlentities($code) . '"');
                    break;
                }
	}
}
