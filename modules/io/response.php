<?php
class Modules_IO_Response {
	
	public function success($data=''){
		return $this->response(200,'success',$data);
	}

	public function error($data=''){
		return $this->response(200,'error',$data);
	}
	
	public function failed($code, $message){
		return $this->response($code,$message);
	}
	
	private function response($code,$message,$data=''){
		$response = array(
			'code'		=> $code,
			'message'	=> $message
		);
		if($data != '') $response['data'] = $data;
		header('Content-Type: application/json');
		return json_encode($response);
	}
}
