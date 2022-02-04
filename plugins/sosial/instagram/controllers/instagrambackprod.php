<?php
require_once './libs/facebook/src/Facebook/autoload.php';
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\GraphUser;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;

class Controllers_Sosial_Instagram_Instagram extends Modules_Plugin_Base {
	
	private $config_appid 			= '1524851714486953';
	private $config_app_secret 		= '1adbf1ef8697fe9cb290adcc608b9094';
	private $call_back_url_login	= 'https://cs.icephone.id/sosialapi/sosial/instagram/instagram/loginlink';

	public function __construct() {
		parent::__construct();
		$this->logger->write('debug', 'HTTP REQUEST: '.print_r($_REQUEST,1));
	}
	
	private function fb(){
	

		$fb = new \Facebook\Facebook([
			'app_id' => $this->config_appid,
			'app_secret' => $this->config_app_secret,
			'default_graph_version' => 'v3.3',
		]);

	  return $fb;
	}
	
	public function loginlink(){
		$ig = $this->fb();
		//$callback = $this->input->post('redirect');
		$permissions = ['email','manage_pages','pages_show_list','instagram_basic','instagram_manage_comments']; 
		$url_login = $this->call_back_url_login;
		$helper = $ig->getRedirectLoginHelper($url_login);
		$loginUrl = $helper->getLoginUrl($url_login,$permissions);
		$link = "<a href='$loginUrl'>Link</a>";
		
		if(!isset($_GET['code'])){
			// echo $link;
			header('Location: '.$loginUrl);
		}
		
		try {
			$accessToken = $helper->getAccessToken();
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			$this->_error($e->getMessage());
		  }

		  if (isset($accessToken)) {
			$client = $ig->getOAuth2Client();
			
			try {
			  	$accessToken = $client->getLongLivedAccessToken($accessToken);
			  	$token = (string) $accessToken;
			  	// $this->_success($token);

			  	//token value
				$json = json_encode($token);
				$tokenVal = str_replace('"', '', $json); 
				// echo $json;

				try {
					// Returns a `FacebookFacebookResponse` object
					$response = $ig->get(
					  '/me/accounts',$tokenVal
					);
				} catch(\Facebook\Exceptions\FacebookResponseException $e) {
					// When Graph returns an error
					$this->_error($e->getMessage());
				} catch(\Facebook\Exceptions\FacebookSDKException $e) {
					// When validation fails or other local issues
					$this->_error($e->getMessage());
				}
				$graphNode = $response->getGraphEdge()->asArray();
				// $this->_success($graphNode);
				$i = 0;
				$response = array();
				foreach($graphNode as $dt){
						$response[$i]['access_token'] =  $dt['access_token'];
						$response[$i]['page_name'] =  $dt['name'];
						$response[$i]['page_id'] =  $dt['id'];
						$response[$i]['instagram'] =  $this->getigaccount($dt['access_token'],$dt['id']);
						$i++;
				}
				  
				// $this->_success($response);
				// print_r ($response);
				$jsonNode = json_encode($response[0]);
				$data = json_decode($jsonNode);
				$instgram_id = $data->instagram->instagramid;
				$user_name = $data->instagram->username;
				$page_id = $data->page_id;
				$page_name = $data->page_name;
				$access_token = $data->access_token;
				$user_token = $tokenVal;
				
				$params = '{Raw:"",Data1:"'.$instgram_id.'",Data2:"'.$user_name.'",Data3:"'.$page_id.'",Data4:"'.$page_name.'",Data5:"'.$access_token.'",Data6:"'.$user_token.'",Data7:"",Data8:"",Data9:"",Data10:""}';
				$params_final = str_replace(' ', '%20', $params);
				$url = 'https://10.1.25.69:8013/ApiBounty/Service1.svc/insert_EG?value='.$params_final;
				// echo $params;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POST, 0);

				$headers = array();
				$headers[] = 'Content-Type: application/json';
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$result = curl_exec($ch);
				curl_close($ch);
				// echo $result;
				echo "Subcribe Instagram Success";
				echo "<script type='text/javascript'>alert('Subcribe Success');</script>";

			} catch(\Facebook\Exceptions\FacebookResponseException $e) {
			  	$this->_error($e->getMessage());
			}
	

		}
		
	}
	
	public function getpagetokenandaccountig(){
		$ig = $this->fb();
 
		$this->_mandatory( array('usertoken'));
		$token = $this->input->post('usertoken');
		 
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			  '/me/accounts',$token
			  
			);
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		  $graphNode = $response->getGraphEdge()->asArray();
		  $i = 0;
		  $response = array();
		  foreach($graphNode as $dt){
				$response[$i]['access_token'] =  $dt['access_token'];
				$response[$i]['page_name'] =  $dt['name'];
				$response[$i]['page_id'] =  $dt['id'];
				$response[$i]['instagram'] =  $this->getigaccount($dt['access_token'],$dt['id']);
				$i++;
		  }
		  
		  $this->_success($response);
	}
		
	private function getigaccount($token,$pageid){
		  
		  $ig = $this->fb();
		
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$pageid.'?fields=instagram_business_account',
			  $token
			  
			);
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		  $graphNode = $response->getGraphNode()->asArray();
		  
		  if(!$graphNode['instagram_business_account']){
			  $dataig = 'the page cannot have instagram account';
		  }else{
			  $dataig = $this->getdetailuserig($graphNode['instagram_business_account']['id'],$token);
		  }

		  return $dataig;
	}
	
	public function getinstagramaccount(){		
		$ig = $this->fb();

		$this->_mandatory( array('pagetoken','pageid'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid'); 
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$pageid.'?fields=instagram_business_account',
			  $token
			  
			);
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		  $graphNode = $response->getGraphNode()->asArray();
		  //print_r($graphNode);die;
		  if(!$graphNode['instagram_business_account']){
			  $dataig = 'the page cannot have instagram account';
		  }else{
			  $dataig = $this->getdetailuserig($graphNode['instagram_business_account']['id'],$token);
		  }
		  
		  
		    
			
		  $this->_success($dataig);
	}
	
	private function getdetailuserig($id,$token){
		  $ig = $this->fb();

		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$id.'?fields=biography,name,username,profile_picture_url',
			  $token
			  
			);
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		  $graphNode = $response->getGraphNode()->asArray();
		  
		  $data['instagramid'] 			= $graphNode['id'];
		  $data['username'] 			= $graphNode['username'];
		  $data['name'] 				= $graphNode['name'];
		  $data['profile_picture_url'] 	= $graphNode['profile_picture_url'];
		  $data['biography'] 			= $graphNode['biography'];

		  return $data ;
	}
	
	public function getmedia(){
		$ig = $this->fb();

		$this->_mandatory( array('pagetoken','instagramid'));
		$token = $this->input->post('pagetoken');
		$instgramid = $this->input->post('instagramid'); 

		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$instgramid.'/media?fields=caption,thumbnail_url,media_type,media_url,timestamp', 
			  $token
			  
			);
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		  $graphNode = $response->getGraphEdge()->asArray();
		  
		  $i = 0;
		  $response = array();

		  foreach($graphNode as $dt){
			$response[$i]['mediaid'] =  $dt['id'];
			$response[$i]['caption'] =  $dt['caption'];
			$response[$i]['media_type'] =  $dt['media_type'];
			$response[$i]['media_url'] =  $dt['media_url'];
			$response[$i]['timestamp'] =  date('Y-m-d h:i:s',strtotime($dt['timestamp']));
				
			$i++;
		  } 
		    
			
		  $this->_success($response);
	}
	
	public function commentmedia(){

		$ig = $this->fb();
		$this->_mandatory( array('pagetoken'));
		$token = $this->input->post('pagetoken');
		$mediaid = $this->input->post('mediaid'); 
		$message = $this->input->post('message'); 

		  try {
			// Returns a `FacebookFacebookResponse` object			  
			$response = $ig->post(
			  '/'.$mediaid.'/comments?',
			  array('message' => $message),
			  $token  
			  
			);

		   } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		  $graphNode = $response->getGraphNode()->asArray();
		  $this->_success($graphNode);
	}
	
	public function replay(){
		
		$ig = $this->fb();
		$this->_mandatory( array('pagetoken','id','message'));
		$token = $this->input->post('pagetoken');
		$id = $this->input->post('id'); 
		$message = $this->input->post('message'); 
		  try {
			// Returns a `FacebookFacebookResponse` object			  
			$response = $ig->post(
			  '/'.$id.'/replies?',
			  array('message' => $message),
			  $token  
			  
			);

		   } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		  $graphNode = $response->getGraphNode()->asArray();
		  $this->_success($graphNode);
	}
	
	public function getcommentdetail(){
		$ig = $this->fb();

		$this->_mandatory( array('pagetoken'));
		$token = $this->input->post('pagetoken');
		$commentid = $this->input->post('commentid'); 

		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$commentid.'/?fields=username,user,media,text,like_count,timestamp,replies{like_count,text,id,username,timestamp}', 
			  $token
			  
			);

		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		 
		  $graphNode = $response->getGraphNode()->asArray();
			//print_r($graphNode);die;
			 $response = array();

			$response['commentid'] 	 =  $graphNode['id'];
			$response['username'] 	 =  $graphNode['username'];
			$response['mediaid'] 	 =  $graphNode['media']['id'];
			$response['text'] 	 	 =  $graphNode['text'];
			$response['like_count']  =  $graphNode['like_count'];
			$response['timestamp'] =  date('Y-m-d h:i:s',strtotime($graphNode['timestamp']));
			$response['replies'] = array();
				if(isset($graphNode['replies'])){
					$c = 0;
					foreach($graphNode['replies'] as $replies){
						$response['replies'][$c]['repliesid'] = $replies['id'];
						$response['replies'][$c]['username'] = $replies['username'];
						$response['replies'][$c]['text'] = $replies['text'];
						$response['replies'][$c]['like_count'] = $replies['like_count'];
						$response['replies'][$c]['timestamp'] = date('Y-m-d h:i:s',strtotime($replies['timestamp']));
						
						$c++;
					}
				}
		
			  
		  
		    
			
		  $this->_success($response);
	}
	
	public function getcommentandreplay(){
		$ig = $this->fb();

		$this->_mandatory( array('pagetoken'));
		$token = $this->input->post('pagetoken');
		$mediaid = $this->input->post('mediaid'); 

		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$mediaid.'/comments?fields=username,user,text,like_count,timestamp,replies{like_count,text,id,username,timestamp}', 
			  $token
			  
			);
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->_error($e->getMessage());
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		  }
		  $graphNode = $response->getGraphEdge()->asArray();
		 
		 $i = 0;
		 $response = array();

		  foreach($graphNode as $dt){
 
			$response[$i]['commentid'] 	 =  $dt['id'];
			$response[$i]['username'] 	 =  $dt['username'];
			$response[$i]['text'] 	 	 =  $dt['text'];
			$response[$i]['like_count']  =  $dt['like_count'];
			$response[$i]['timestamp'] =  date('Y-m-d h:i:s',strtotime($dt['timestamp']));
			$response[$i]['replies'] = array();
				if(isset($dt['replies'])){
					$c = 0;
					foreach($dt['replies'] as $replies){
						$response[$i]['replies'][$c]['repliesid'] = $replies['id'];
						$response[$i]['replies'][$c]['username'] = $replies['username'];
						$response[$i]['replies'][$c]['text'] = $replies['text'];
						$response[$i]['replies'][$c]['like_count'] = $replies['like_count'];
						$response[$i]['replies'][$c]['timestamp'] = date('Y-m-d h:i:s',strtotime($replies['timestamp']));
						
						$c++;
					}
				}
			$i++;
		  }
		  
		    
			
		  $this->_success($response);
	}


	
	
	
	
	
	
	
	
}