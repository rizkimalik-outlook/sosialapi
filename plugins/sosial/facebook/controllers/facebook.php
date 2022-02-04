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

class Controllers_Sosial_Facebook_Facebook extends Modules_Plugin_Base {
	
	private $config_appid 			= '494647064870700';
	private $config_app_secret 		= '32c958e1394a7cb946cf27553a486e9a';
	private $call_back_url_login	= 'https://mendawai.com/sosialapi/sosial/facebook/facebook/loginlink';

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
		$fb = $this->fb();
		//$callback = $this->input->post('redirect');
		$permissions = ['pages_messaging_subscriptions','pages_messaging','pages_show_list','pages_manage_engagement','pages_manage_metadata','email','pages_read_engagement','pages_manage_posts']; 
		$url_login = $this->call_back_url_login;
		$helper = $fb->getRedirectLoginHelper($url_login);
		$loginUrl = $helper->getLoginUrl($url_login,$permissions);
		// $link = "<a href='$loginUrl'>Link</a>";
		
		if(!isset($_GET['code'])){
			//echo $link;
			header('Location: '.$loginUrl);
		}
		
		try {
			$accessToken = $helper->getAccessToken();
		} 
		catch(\Facebook\Exceptions\FacebookResponseException $e) {
			$this->_error($e->getMessage());
		}

		if (isset($accessToken)) {
			$client = $fb->getOAuth2Client();
			
			try {
				$accessToken = $client->getLongLivedAccessToken($accessToken);
				$token = (string) $accessToken;
				//$this->_success($token);die();
				
				// todo SAVE TOKEN & SUBCRIBE
				$jsonToken = json_encode($token);
				$tokenValue = str_replace('"', '', $jsonToken); 

				//get Page Token
				try {
					// Returns a `FacebookFacebookResponse` object
					$response = $fb->get(
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
				$jsonNode = json_encode($graphNode);
				

				// todo Save Token To File
				$nama_file = 'facebook_'.date_timestamp_get(date_create()).'.json';
				$handle = fopen('public/log_token/facebook/'.$nama_file, 'w');
				fwrite($handle, $jsonToken.'\n\n'.$jsonNode);
				fclose($handle);
				
				foreach($graphNode as $data){
					$params = '{Raw:"",Data1:"'.$data['name'].'",Data2:"'.$data['id'].'",Data3:"'.$data['access_token'].'",Data4:"",Data5:" ",Data6:"",Data7:"",Data8:"",Data9:"",Data10:""}';
					$params_final = str_replace(' ', '%20', $params);
					$url = 'https://selindo.mendawai.com/ApiBounty2/Service1.svc/insert_mana?value='.$params_final;
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($ch, CURLOPT_POST, 1);

					$headers = array();
					$headers[] = 'Content-Type: application/json';
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$result = curl_exec($ch);
					curl_close($ch);
					// echo $result;
					echo "<script type='text/javascript'>alert('".$data['name']." : Success Get Token.');</script>";
					
					//Subcribe function
					try {
						// Returns a `FacebookFacebookResponse` object
						$response2 = $fb->post(
						  '/'.$data['id'].'/subscribed_apps/',
						  array('subscribed_fields' => 'messages,feed,mention'),
						  $data['access_token']
						);
					} catch(\Facebook\Exceptions\FacebookResponseException $e) {
						// When Graph returns an error
						$this->_error($e->getMessage());
					} catch(\Facebook\Exceptions\FacebookSDKException $e) {
						// When validation fails or other local issues
						$this->_error($e->getMessage());
					}
					$graphNode2 = $response2->getGraphNode()->asArray();
					// $this->_success($graphNode2);
					echo "<script type='text/javascript'>
						alert('".$data['name']." : Subcribe Success.');
					</script>";
				}
				// $this->_success($graphNode);
				echo "<script type='text/javascript'>location.replace('https://facebook.com');</script>";

			} 
			catch(\Facebook\Exceptions\FacebookResponseException $e) {
				$this->_error($e->getMessage());
			}
		}
		
	}
 

	/* public function loginlink(){
		$fb = $this->fb();
		$permissions = ['pages_messaging_subscriptions','pages_messaging','pages_show_list','pages_manage_engagement','pages_manage_metadata','email']; 
		$url_login = $this->call_back_url_login;
		$helper = $fb->getRedirectLoginHelper($url_login);
		$loginUrl = $helper->getLoginUrl($url_login,$permissions);
		$link = "<a href='$loginUrl'>Link</a>";
		
		if(!isset($_GET['code'])){
			echo $link;
		}
		
		try {
			$accessToken = $helper->getAccessToken();
		  } catch(\Facebook\Exceptions\FacebookResponseException $e) {
			$this->_error($e->getMessage());
		  }

		  if (isset($accessToken)) {
			$client = $fb->getOAuth2Client();
			
			try {
			  $accessToken = $client->getLongLivedAccessToken($accessToken);
			  $token = (string) $accessToken;
			  $this->_success($token);
			} catch(\Facebook\Exceptions\FacebookResponseException $e) {
			  $this->_error($e->getMessage());
			}
	

		}
		
	} */

	public function getpagetoken(){
		$fb = $this->fb();

		$this->_mandatory( array('usertoken'));
		$token = $this->input->post('usertoken');
		 
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
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
		  $this->_success($graphNode);
	}


	public function getfeed(){
		$fb = $this->fb();
		$this->_mandatory( array('pagetoken','pageid'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
			  '/'.$pageid.'/feed?fields=message,comments{from,comment_count,message,comments,attachment,created_time{from,message,created_time}},created_time',
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
			$dt['created_time']->setTimezone( new \DateTimeZone( 'Asia/Jakarta' ) );  
			$response[$i]['feedid'] 	 =  $dt['id'];
			$response[$i]['feedpost'] 	 =  $dt['message'];
			$response[$i]['date'] =  $dt['created_time']->format('Y-m-d h:i:s');
			$response[$i]['comment'] = array();
				if(isset($dt['comments'])){
					$c = 0;
					foreach($dt['comments'] as $comments){
						$comments['created_time']->setTimezone( new \DateTimeZone( 'Asia/Jakarta' ) );  
						$response[$i]['comment'][$c]['comment_id'] = $comments['id'];
						$response[$i]['comment'][$c]['from'] = $comments['from']['name'];
						$response[$i]['comment'][$c]['date'] = $comments['created_time']->format('Y-m-d h:i:s');
						$response[$i]['comment'][$c]['message'] = $comments['message'];
						$response[$i]['comment'][$c]['attachment'] = isset($comments['attachment']) ? $comments['attachment'] : '';
						$response[$i]['comment'][$c]['reply_count'] = $comments['comment_count'];
						$response[$i]['comment'][$c]['replay'] = array();
							if(isset($comments['comments'])){
								$r = 0;
								foreach($comments['comments'] as $replay){
									$replay['created_time']->setTimezone( new \DateTimeZone( 'Asia/Jakarta' ) );  
									$response[$i]['comment'][$c]['replay'][$r]['from'] = $replay['from']['name'];
									$response[$i]['comment'][$c]['replay'][$r]['message'] = $replay['message'];
									$response[$i]['comment'][$c]['replay'][$r]['attachment'] = isset($replay['attachment']) ? $replay['attachment'] : '';
									$response[$i]['comment'][$c]['replay'][$r]['date'] = $replay['created_time']->format('Y-m-d h:i:s');
									$r++;
								}
							}
						$c++;
					}
				}
			$i++;
		  }
		  $this->_success($response);
	}
	
	public function feedDetail(){	
		$fb = $this->fb();
		$this->_mandatory( array('pagetoken','pageid'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');
	
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
			  '/'.$pageid.'?fields=from,message,permalink_url,created_time,attachments',
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
	
	
	public function postfeedphoto(){
		$fb = $this->fb();

		$this->_mandatory( array('pagetoken','pageid','url'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');
		$message = $this->input->post('message');
		$url = $this->input->post('url');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
			  '/'.$pageid.'/photos/',
			  array('url' => $url,'message' => $message),
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


	public function postfeed(){
		$fb = $this->fb();

		$this->_mandatory( array('pagetoken','pageid','message'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');
		$message = $this->input->post('message');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
			  '/'.$pageid.'/feed/',
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

	public function commentfeed(){
		$fb = $this->fb();

		$this->_mandatory( array('pagetoken','feedid','message'));
		$token = $this->input->post('pagetoken');
		$feedid = $this->input->post('feedid');
		$message = $this->input->post('message');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
			  '/'.$feedid.'/comments/',
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

	function  unicodeDecode($str)
		{
			$text = json_encode($str);
			$text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
				return '\\';
			}, $text);
			return json_decode($text);
		}
		
	
	public function replaycomment(){
		$fb = $this->fb();


		$this->_mandatory( array('pagetoken','comment_id','message'));
		$token = $this->input->post('pagetoken');
		$comment_id = $this->input->post('comment_id');
		$message = $this->input->post('message');
		//$message = '@[3977069165701618] hai juga 😝';

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
			  '/'.$comment_id.'/comments/',
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


	public function conversations(){
		$fb = $this->fb();
		$this->_mandatory( array('pagetoken','pageid'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
			  '/'.$pageid.'/conversations?fields=senders,message_count,snippet,updated_time',
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
			$dt['updated_time']->setTimezone( new \DateTimeZone( 'Asia/Jakarta' ) );  
			$response[$i]['conversationsid'] 	 =  $dt['id'];
			$response[$i]['conversations_from'] 	 =  $dt['senders']['0']['name'];
			$response[$i]['conversations_count'] 	 =  $dt['message_count'];
			$response[$i]['updated_time'] =  $dt['updated_time']->format('Y-m-d h:i:s');
				
			$i++;
		  }
		  $this->_success($response);
	}
	
	public function Get_Mid_Detail(){
		$fb = $this->fb();
		$this->_mandatory( array('pagetoken','mid'));
		$token = $this->input->post('pagetoken');
		$mid = 'm_'.$this->input->post('mid');
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
			  '/'.$mid.'?fields=created_time,from,message,id,sticker,to',
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

	public function messages_replay(){
		$fb = $this->fb();


		$this->_mandatory( array('pagetoken','pageid','message','sender_id'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');
		$message = $this->input->post('message');
		$sender_id= $this->input->post('sender_id');

	
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
			  '/'.$pageid.'/messages/',
			  array('recipient' => array('id' => $sender_id),'message'=> array('text'=>$message)),
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
	
	public function messages_attahcment(){
		$fb = $this->fb();


		$this->_mandatory( array('pagetoken','pageid','attachment_type','attachment_link','sender_id'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');
		$attachment_type = $this->input->post('attachment_type');
		$attachment_link = $this->input->post('attachment_link');
		$sender_id = $this->input->post('sender_id');

	
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
			  '/'.$pageid.'/messages/',
			  array('recipient' => array('id' => $sender_id),'message'=> array('attachment'=> array('type' => $attachment_type,'payload' => array('url' => $attachment_link,'is_reusable' => false)))),
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


	public function conversationsdetail(){
		$fb = $this->fb();

		$this->_mandatory( array('pagetoken','conversationsid'));
		$token = $this->input->post('pagetoken');
		$conversationsid = $this->input->post('conversationsid');
	
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
			  '/'.$conversationsid.'?fields=messages{message,from,attachments{image_data,mime_type,name,size,video_data,file_url},created_time}',
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
		  foreach($graphNode['messages'] as $dt){
			$dt['created_time']->setTimezone( new \DateTimeZone( 'Asia/Jakarta' ) );  
			$response[$i]['from'] 	 		=  $dt['from']['name'];
			$response[$i]['message'] 	 	=  $dt['message'];
			$response[$i]['attachments']		=  $dt['attachments'];
			$response[$i]['created_time'] 	=  $dt['created_time']->format('Y-m-d h:i:s');
				
			$i++;
		  }

		  /*delete new message*/
	      unlink('public/notiffb/'.$conversationsid.'.json');		  
		  $this->_success($response);
	}


	public function conversationsreplay(){
		$fb = $this->fb();

		$this->_mandatory( array('pagetoken','conversationsid','message'));
		$token = $this->input->post('pagetoken');
		$conversationsid = $this->input->post('conversationsid');
		$message = $this->input->post('message');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
			  '/'.$conversationsid.'/messages/',
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
	

	public function getpageid(){
		$fb = $this->fb();
		$this->_mandatory( array('pagetoken'));
		$token = $this->input->post('pagetoken');
		 
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
			  '/me/',$token
			  
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
	
	
	public function getprofilePicture(){
		$fb = $this->fb();
		$this->_mandatory( array('pagetoken','profileid'));
		$token = $this->input->post('pagetoken');
                $profileid = $this->input->post('profileid');
		 
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
			  '/'.$profileid.'?fields=picture',$token
			  
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

	public function getprofile(){
		$fb = $this->fb();
		$this->_mandatory( array('pagetoken','profileid'));
		$token = $this->input->post('pagetoken');
                $profileid = $this->input->post('profileid');
		 
		  try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->get(
			//   '/'.$profileid ,$token
			  '/'.$profileid.'?fields=name,picture',$token
			  
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
	
	public function getNotification(){
		// $fb = $this->fb();
		if (isset($_GET['hub_verify_token'])) {
			if ($_GET['hub_verify_token'] === 'Rahasialah123_') {
				echo $_GET['hub_challenge'];
				return;
			} else {
				echo 'Invalid Verify Token';
				return;
			}
		}
		
		$input  = file_get_contents('php://input');
		$data 	= json_decode($input);
	
		/*untuk instagram*/
		if($data->object == 'instagram'){
			/*untuk ig feed*/
			if($data->entry[0]->changes[0]->field == 'comments'){
				$nama_file = $data->entry[0]->time.'.json';
				$nama_folder = $data->entry[0]->changes[0]->value->id;
				$path = "public/notifig/feed";
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				$mode = (!file_exists($path.'/'.$nama_file)) ? 'w':'a';
				$handle = fopen($path.'/'.$nama_file.'',$mode);
				
				$instagram_id = $data->entry[0]->id;
				$timestamp = $data->entry[0]->time;
				$comment_id = $data->entry[0]->changes[0]->value->id;
				$text = $data->entry[0]->changes[0]->value->text;
				
				$params = "{Raw:'',Data1:'".$instagram_id."',Data2:'".$timestamp."',Data3:'".$comment_id."',Data4:'".$text."',Data5:'',Data6:'',Data7:'',Data8:'',Data9:'',Data10:''}"; 
				$params_1 = str_replace("#", "%23", $params);
				$params_2 = str_replace("\n", "<br>", $params_1);
				$params_final = str_replace(" ", "%20", $params_2);
				// $url = 'https://10.1.25.69:8013/ApiBounty/Service1.svc/EG_Notif?value='.$params_final;
				$url = 'https://selindo.mendawai.com/ApiBounty2/Service1.svc/EG_Notif?value='.$params_final;

				fwrite($handle,"\r\n".$input."\r\n params_final :".$params_final);
				fclose($handle);
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);

				$headers = array();
				$headers[] = 'Content-Type: application/json';
				$headers[] = 'Header: Dota2';
				$headers[] = 'Content-Length: 0';
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$result = curl_exec($ch);
				curl_close($ch);
			}
		}
		 
		
		/*untuk facebook*/
		else if($data->object == 'page'){
			/*untuk feed*/
			if($data->entry[0]->changes[0]->field == 'feed' && $data->entry[0]->changes[0]->value->item != 'reaction'){
				$nama_file = $data->entry[0]->changes[0]->value->created_time.'.json';
				$nama_folder = $data->entry[0]->changes[0]->value->from->id;
                $pageid_folder = $data->entry[0]->id;
				$path = "public/notiffb/feed/pageid_".$pageid_folder."/".$nama_folder;
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				$mode = (!file_exists($path.'/'.$nama_file)) ? 'w':'a';
				$handle = fopen($path.'/'.$nama_file.'',$mode);
				
				//kirim notif ke WS
				$from_name = $data->entry[0]->changes[0]->value->from->name;
				$from_id = $data->entry[0]->changes[0]->value->from->id;
				$post_url = $data->entry[0]->changes[0]->value->post->permalink_url;
				//$post_id = $data->entry[0]->changes[0]->value->post->id;
				$post_id = $data->entry[0]->changes[0]->value->post_id;
				$item = $data->entry[0]->changes[0]->value->item;
				
				$message = $data->entry[0]->changes[0]->value->message;
				$comment_id = $data->entry[0]->changes[0]->value->comment_id;
				$attach1 = $data->entry[0]->changes[0]->value->photo;
				$attach2 = $data->entry[0]->changes[0]->value->video;
				$attachments = str_replace('&', '|', $attach1.$attach2);
				$created_time = $data->entry[0]->changes[0]->value->created_time;
				$parent_id = $data->entry[0]->changes[0]->value->parent_id;
				$page_id = $data->entry[0]->id;
				
				$params = "{Raw:'',Data1:'".$from_id."',Data2:'".$from_name."',Data3:'".$attachments."',Data4:'".$post_url."',Data5:'".$post_id."',Data6:'".$message."',Data7:'".$comment_id."',Data8:'".$created_time."',Data9:'".$parent_id."',Data10:'".$page_id."',Data11:'".$item."'}"; 
				$params_1 = str_replace("#", "%23", $params);
				$params_2 = str_replace("\n", "<br>", $params_1);
				$params_final = str_replace(" ", "%20", $params_2);
				// $url = 'https://10.1.25.69:8013/ApiBounty/Service1.svc/send_feeder?value='.$params_final;
				$url = 'https://selindo.mendawai.com/ApiBounty2/Service1.svc/send_feeder?value='.$params_final;
				
				fwrite($handle,"\r\n".$input."\r\n params_final :".$params_final);
				fclose($handle);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);

				$headers = array();
				$headers[] = 'Content-Type: application/json';
				$headers[] = 'Header: Dota2';
				$headers[] = 'Content-Length: 0';
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$result = curl_exec($ch);
				curl_close($ch);
				
			/*untuk mention*/
			}else if($data->entry[0]->changes[0]->field == 'mention' && $data->entry[0]->changes[0]->value->item != 'reaction'){
				$nama_file = $data->entry[0]->changes[0]->value->created_time.'.json';
				$nama_folder = $data->entry[0]->changes[0]->value->post_id;
                $pageid_folder = $data->entry[0]->id;

				$path = "public/notiffb/mention/pageid_".$pageid_folder."/".$nama_folder;
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				$mode = (!file_exists($path.'/'.$nama_file)) ? 'w':'a';
				$handle = fopen($path.'/'.$nama_file.'',$mode);
				
				//split data
				$page_id = $data->entry[0]->id;
				$item = $data->entry[0]->changes[0]->value->item;
				$post_id = $data->entry[0]->changes[0]->value->post_id;
				$comment_id = $data->entry[0]->changes[0]->value->comment_id;
				$message = $data->entry[0]->changes[0]->value->message;
				$created_time = $data->entry[0]->changes[0]->value->created_time;
				
				$params = "{Raw:'',Data1:'".$item."',Data2:'".$post_id."',Data3:'".$comment_id."',Data4:'".$message."',Data5:'".$created_time."',Data6:'".$page_id."',Data7:'".explode("_",$post_id)[0]."',Data8:'',Data9:'',Data10:''}"; 
				$params_1 = str_replace("#", "%23", $params);
				$params_2 = str_replace("\n", "<br>", $params_1);
				$params_final = str_replace(" ", "%20", $params_2);
				// $url = 'https://10.1.25.69:8013/ApiBounty/Service1.svc/send_call?value='.$params_final;
				$url = 'https://selindo.mendawai.com/ApiBounty2/Service1.svc/send_call?value='.$params_final;
				
				fwrite($handle,"\r\n".$input."\r\n params_final :".$params_final);
				fclose($handle);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);

				$headers = array();
				$headers[] = 'Content-Type: application/json';
				$headers[] = 'Header: Dota2';
				$headers[] = 'Content-Length: 0';
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$result = curl_exec($ch);
				curl_close($ch);
				
			
			/*untuk messageing */
			}else{
				$nama_file = $data->entry[0]->messaging[0]->timestamp.'.json';
				$nama_folder = $data->entry[0]->messaging[0]->sender->id;
                $pageid_folder = $data->entry[0]->id;

				$path = "public/notiffb/messenger/pageid_".$pageid_folder."/".$nama_folder;
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				$mode = (!file_exists($path.'/'.$nama_file)) ? 'w':'a';
				$handle = fopen($path.'/'.$nama_file.'',$mode);
			
				//kirim notif ke WS
				$pageid = $data->entry[0]->id;
				$senderid = $data->entry[0]->messaging[0]->sender->id;
				$msg = $data->entry[0]->messaging[0]->message->text;
				$mid = $data->entry[0]->messaging[0]->message->mid;
				$datetime = $data->entry[0]->messaging[0]->timestamp;
				$attach = $data->entry[0]->messaging[0]->message->attachments[0]->payload->url;
				$attachments = str_replace('&', '|', $attach);
				
				$params = "{Raw:'',Data1:'".$senderid."',Data2:'".$datetime."',Data3:'',Data4:'".$msg."',Data5:'".$pageid."',Data6:'".$attachments."',Data7:'".$mid."',Data8:'',Data9:'',Data10:''}";
				$params_1 = str_replace("#", "%23", $params);
				$params_2 = str_replace("\n", "<br>", $params_1);
				$params_final = str_replace(" ", "%20", $params_2);
				// $url = 'https://10.1.25.69:8013/ApiBounty/Service1.svc/send_Bounty?value='.$params_final;
				$url = 'https://selindo.mendawai.com/ApiBounty2/Service1.svc/send_Bounty?value='.$params_final;

				fwrite($handle,"\r\n".$input."\r\n params_final :".$params_final);
				fclose($handle);
				
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);

				$headers = array();
				$headers[] = 'Content-Type: application/json';
				$headers[] = 'Header: Dota2';
				$headers[] = 'Content-Length: 0';
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$result = curl_exec($ch);
				curl_close($ch);

	
			}
			
		} 
		/*end data object*/	
		

	}
	
	public function messagewebhooks_subscribe(){
		$fb = $this->fb();

		$this->_mandatory( array('pagetoken','pageid'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
			  '/'.$pageid.'/subscribed_apps/',
			  array('subscribed_fields' => 'messages,feed,mention,comments'),
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
	
	public function messagewebhooks_unsubscribe(){
		$fb = $this->fb();

		$this->_mandatory( array('pagetoken','pageid'));
		$token = $this->input->post('pagetoken');
		$pageid = $this->input->post('pageid');

		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->delete(
			  '/'.$pageid.'/subscribed_apps/',
			  array(),
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
	
	
	/* public function DeleteFeed(){	
		$fb = $this->fb();
		$this->_mandatory( array('pagetoken','feedid'));
		$token = $this->input->post('pagetoken');
		$feedid = $this->input->post('feedid');
	
		try {
		// Returns a `Facebook\FacebookResponse` object
		$response = $fb->delete(
			'/'.$feedid,
			$token
		);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		$this->_error($e->getMessage());
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		$this->_error($e->getMessage());
		}
		$graphNode = $response->getGraphNode();	
		$this->_success($graphNode);
	} */
	
	
	
}
