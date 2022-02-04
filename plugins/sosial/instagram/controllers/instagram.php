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
	
	private $config_appid 			= '494647064870700';
	private $config_app_secret 		= '32c958e1394a7cb946cf27553a486e9a';
	private $call_back_url_login	= 'https://mendawai.com/sosialapi/sosial/instagram/instagram/loginlink';

	public function __construct() {
		parent::__construct();
		$this->logger->write('debug', 'HTTP REQUEST: '.print_r($_REQUEST,1));
	}
	
	private function fb(){
	

		$fb = new \Facebook\Facebook([
			'app_id' => $this->config_appid,
			'app_secret' => $this->config_app_secret,
			'default_graph_version' => 'v10.0',
		]);

	  return $fb;
	}
	
	public function loginlink(){
		$ig = $this->fb();
		//$callback = $this->input->post('redirect');
		$permissions = ['email','pages_show_list','instagram_basic','instagram_manage_comments']; 
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
				$jsonToken = json_encode($token);
				$tokenValue = str_replace('"', '', $jsonToken); 

				try {
					// Returns a `FacebookFacebookResponse` object
					$response = $ig->get(
					  '/me/accounts',$tokenValue
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
				$jsonWrite = json_encode($graphNode);
				
				// todo Save Token To File
				$nama_file = 'instagram_'.date_timestamp_get(date_create()).'.txt';
				$handle = fopen('public/log_token/instagram/'.$nama_file, 'w');
				fwrite($handle, $jsonWrite);
				fclose($handle);
				
				foreach($graphNode as $data){
					$igaccount = $this->getigaccount($data['access_token'],$data['id']);
					if($igaccount['status'] == false){
						echo "<script type='text/javascript'>
								alert('".$igaccount['msg']."');
							</script>";
					echo "<script type='text/javascript'>location.replace('".$url_login."');</script>";
					}

					$params = '{Raw:"",Data1:"'.$igaccount['instagramid'].'",Data2:"'.$igaccount['username'].'",Data3:"'.$data['id'].'",Data4:"'.$data['name'].'",Data5:"'.$data['access_token'].'",Data6:"'.$tokenValue.'",Data7:"",Data8:"",Data9:"",Data10:""}';
					$params_final = str_replace(' ', '%20', $params);
					$url = 'https://invision.ddns.net:30008/ApiBounty2/Service1.svc/insert_EG?value='.$params_final;
					
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
					if (curl_errno($ch)) {
						echo 'Error:' . curl_error($ch);
					}
					curl_close($ch);
					echo "<script type='text/javascript'>
						alert('".$data['name']." : Success Get Token.');
					</script>";
				}
				echo "<script type='text/javascript'>location.replace('https://instagram.com');</script>";

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
		  
		  if(!isset($graphNode['instagram_business_account'])){
			  $dataig['status'] = false;
			  $dataig['msg'] = 'the page cannot have instagram account';
		  }else{
			  $dataig = $this->getdetailuserig($graphNode['instagram_business_account']['id'],$token);
			  $dataig['status'] = true;
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
		  $data['profile_picture_url'] 	= isset($graphNode['profile_picture_url'])? $graphNode['profile_picture_url']:'';
		  $data['biography'] 			= isset($graphNode['biography'])? $graphNode['biography']:'';

		  return $data ;
	}
	
	public function getprofileinstagram(){
		  $ig = $this->fb();
			
		  $token = $this->input->post('pagetoken');
		  $instgramid = $this->input->post('instagramid'); 	
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$instgramid.'?fields=name,username,profile_picture_url',
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

		  $this->_success($data);
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
			$response[$i]['caption'] =  isset($dt['caption']) ?$dt['caption'] : '';
			$response[$i]['media_type'] =  $dt['media_type'];
			$response[$i]['media_url'] =  $dt['media_url'];
			$response[$i]['timestamp'] =  date('Y-m-d h:i:s',strtotime($dt['timestamp']));
				
			$i++;
		  } 
		    
			
		  $this->_success($response);
	}
	
	
	public function getstories(){
		$ig = $this->fb();

		$this->_mandatory( array('pagetoken','instagramid'));
		$token = $this->input->post('pagetoken');
		$instgramid = $this->input->post('instagramid'); 

		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$instgramid.'/stories?fields=caption,thumbnail_url,media_type,media_url,timestamp', 
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
	
	
	/* public function getcommentdetail(){
		$ig = $this->fb();
		$this->_mandatory( array('pagetoken'));
		$token = $this->input->post('pagetoken');
		$commentid = $this->input->post('commentid'); 

		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$commentid.'/?fields=username,media,user,text,like_count,timestamp,replies{like_count,text,id,username,timestamp}', 
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
	
			 $i = 0;
			 $response = array();

			$response['commentid'] 	 =  $graphNode['id'];
			$response['mediaid'] 	 =  $graphNode['media']['id'];
			$response['mediadetail'] =  $this->getmediadetail($graphNode['media']['id'],$token);
			$response['username'] 	 =  $graphNode['username'];
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
	} */

	public function getcommentdetail(){
		$ig = $this->fb();

		$this->_mandatory( array('pagetoken'));
		$token = $this->input->post('pagetoken');
		$commentid = $this->input->post('commentid'); 

		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   // '/'.$commentid.'/?fields=username,user,media,text,like_count,timestamp,replies{like_count,text,id,username,timestamp}', 
			   '/'.$commentid.'/?fields=username,user,media,text,like_count,timestamp', 
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
			$response['mediadetail'] =  $this->getmediadetail($graphNode['media']['id'],$token);
			$response['text'] 	 	 =  $graphNode['text'];
			$response['like_count']  =  $graphNode['like_count'];
			$response['timestamp'] =  date('Y-m-d h:i:s',strtotime($graphNode['timestamp']));
		    
			
		  $this->_success($response);
	}

	public function getcommentdetail_reply(){
		$ig = $this->fb();

		$this->_mandatory( array('pagetoken'));
		$token = $this->input->post('pagetoken');
		$commentid = $this->input->post('commentid'); 

		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$commentid.'/?fields=replies', 
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

	public function getcheck_commentreply(){
		$ig = $this->fb();
		$res = array();	

		$this->_mandatory( array('pagetoken'));
		$token = $this->input->post('pagetoken');
		$id = $this->input->post('id');

		
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$id.'/?fields=replies', 
			  $token
			);

		} 
		catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			// $this->_error($e->getMessage());
			$this->_success('IGFeed_Reply');
			
		} 
		catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->_error($e->getMessage());
		}		

		// $graphNode = $response->getGraphNode()->asArray();	
		$this->_success('IGFeed_Comment');
	}
	
	private function getmediadetail($mediaid,$token){
		$ig = $this->fb();
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $ig->get(
			   '/'.$mediaid.'?fields=id,media_type,media_url,thumbnail_url,caption,permalink,owner{id,username},timestamp,username', 
			  $token
			  
			);
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			return $e->getMessage();
		  } catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			return $e->getMessage();
		  }
		  $graphNode = $response->getGraphNode()->asArray();
		  return $graphNode;
		  
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
	
	
  
	public function publish_photo(){
		$ig = $this->fb();
		$this->_mandatory( array('pagetoken','instagramid','image_url'));
		$token = $this->input->post('pagetoken');
		$instagramid = $this->input->post('instagramid'); 
		$image_url = $this->input->post('image_url');
		$caption = $this->input->post('caption'); 	
		  try {
			// Returns a `FacebookFacebookResponse` object			  
			$response = $ig->post(
			  '/'.$instagramid.'/media',
			  array('image_url' => $image_url,'caption'=>$caption),
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


	
	
}
