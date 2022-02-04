<?php
require_once './libs/abraham/twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;



class Controllers_Sosial_Twitter_Twitter extends Modules_Plugin_Base {
	
	private $CONSUMER_KEY       = 'uGf2WnAgtlyfvCrgNDJoop50Y';
	private $CONSUMER_SECRET    = 'E5EmNSw9uMliXwXrj4swrIzhbFdEWJEZ6aytAC7sM6MQe1P7Do';
	private $OAUTH_CALLBACK     ='https://mendawai.com/sosialapi/sosial/twitter/twitter/getoauthtoken';
	private $environments       = 'mendawai';
	
	public function __construct() {
		parent::__construct();
		$this->logger->write('debug', 'HTTP REQUEST: '.print_r($_REQUEST,1));
	}

	

	public function GetOauthToken(){

		$request_token = [];
		$request_token['oauth_token'] = $_SESSION['oauth_token'];
		$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

		if (isset($_REQUEST['oauth_token'])) {
			
			$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);
			$access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);
			//$this->_success($access_token);
			$jsonNode = json_encode($access_token);
			
			// todo Save Token To File
			$nama_file = 'twitter_'.date_timestamp_get(date_create()).'.txt';
			$handle = fopen('public/log_token/twitter/'.$nama_file, 'w');
			
			
			//insert token ke DB
			$data = json_decode($jsonNode);
			$oauthtoken = $data->oauth_token;
			$tokensecret = $data->oauth_token_secret;
			$userid = $data->user_id;
			$screenname = $data->screen_name;
			
			$params = '{Raw:"",Data1:"'.$screenname.'",Data2:"'.$userid.'",Data3:"'.$oauthtoken.'",Data4:"'.$tokensecret.'",Data5:" ",Data6:"",Data7:"",Data8:"",Data9:"",Data10:""}';
			$params_final = str_replace(' ', '%20', $params);
			$url = 'https://invision.ddns.net:30008/ApiBounty2/Service1.svc/insert_tango?value='.$params_final;

			fwrite($handle, $jsonNode."\n\n param final :".$params_final);
			fclose($handle);
			
			$ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, $url);
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
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
			// echo $result;
			
			//direct subcribe
			$params = array();
			$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauthtoken, $tokensecret);
			$statues = $connection->post("account_activity/all/$this->environments/subscriptions",$params);

			if(isset($statues->errors)){
				// $this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
				echo "<script type='text/javascript'>alert('".$statues->errors['0']->message."');</script>";
			}else{  
				// $this->_success($statues);
				echo "<script type='text/javascript'>alert('".$screenname." subscribe success')</script>";
				echo "<script type='text/javascript'>location.replace('https://twitter.com');</script>";
			}
			// location.replace('https://twitter.com');
			
			die;
		}

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET);
		$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $this->OAUTH_CALLBACK));

		$_SESSION['oauth_token'] = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
		
		$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
		header("Location: $url");
		exit();
	
	}

	public function show(){
		$this->_mandatory( array('oauth_token','oauth_token_secret','mention_id'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$mention_id = $this->input->post('mention_id');
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'id'  => $mention_id,
			'include_entities' => FALSE,
			'trim_user'=> false,
		  );

		$statues = $connection->get("statuses/show",$params);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{
			$this->_success($statues);
		}
	}

	public function posttweet(){
		
		$this->_mandatory( array('oauth_token','oauth_token_secret','message'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$message = $this->input->post('message');
		$media_ids = $this->input->post('media_ids');
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'status'  => $message,
			'trim_user' => TRUE,
			'include_entities'=> FALSE,
			'media_ids' => $media_ids
		  );

		$statues = $connection->post("statuses/update",$params);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{
			$response = array();
			$response['mention_id'] 					=  $statues->id_str;
			$response['mention'] 						=  $statues->text;
			$response['source'] 						=  $statues->source;
			$response['in_reply_to_status_id'] 			=  $statues->in_reply_to_status_id;
			$response['in_replay_from_tweet'] 			=  $this->showstatus($statues->in_reply_to_status_id,$oauth_token,$oauth_token_secret);       
			$response['created_at'] 					=  date('Y-m-d h:i:s',strtotime($statues->created_at));
					
		
			
			$this->_success($response);
		}	
				 
	}
	
	public function gettimeline(){
		$this->_mandatory( array('oauth_token','oauth_token_secret','limit'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$limit = $this->input->post('limit');
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'count'  => $limit,
		  );

		$statues = $connection->get("statuses/user_timeline",$params);
		//print_r($statues);die;
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{
			$i = 0;
			$response = array();
			foreach($statues as $data){
				//$data->created_at->setTimezone( new \DateTimeZone( 'Asia/Jakarta' ) );
				$response[$i]['id'] 							=  $data->id;
				$response[$i]['text'] 						=  $data->text;
				$response[$i]['via'] 							=  $data->source;    
				$response[$i]['created_at'] 					=  date('Y-m-d h:i:s',strtotime($data->created_at));
					
				$i++;
			}
			
			$this->_success($response);
		}
				 
	}

	public function getmention(){
		$this->_mandatory( array('oauth_token','oauth_token_secret','limit'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$limit = $this->input->post('limit');
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'count'  => $limit,
			'include_entities' => FALSE,
			'trim_user'=> false,
		  );

		$statues = $connection->get("statuses/mentions_timeline",$params);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{
			$i = 0;
			$response = array();
			foreach($statues as $data){
				//$data->created_at->setTimezone( new \DateTimeZone( 'Asia/Jakarta' ) );
				$response[$i]['mention_id'] 					=  $data->id;
				$response[$i]['mention'] 						=  $data->text;
				$response[$i]['source'] 							=  $data->source;
				$response[$i]['in_reply_to_status_id'] 			=  $data->in_reply_to_status_id;
				$response[$i]['in_replay_from_tweet'] 			=  $this->showstatus($data->in_reply_to_status_id,$oauth_token,$oauth_token_secret);
				$response[$i]['from_user'] 						=  $data->user;        
				$response[$i]['created_at'] 					=  date('Y-m-d h:i:s',strtotime($data->created_at));
					
				$i++;
			}
			
			$this->_success($response);
		}

				 
	}

	private function showstatus($id_replay,$oauth_token,$oauth_token_secret){	
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
		$params = array(
			'id'  => $id_replay,
			'include_entities' => FALSE,
			'trim_user'=> false,
		  );

		$statues = $connection->get("statuses/show",$params);
		
		return $statues->text;
	}

	public function replaytweet(){
		$this->_mandatory( array('oauth_token','oauth_token_secret','mention_id','message'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$mention_id = $this->input->post('mention_id');
		$message = $this->input->post('message');
		$media_ids = $this->input->post('media_ids');

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'status'  => $message,
			'in_reply_to_status_id' => $mention_id,
			'auto_populate_reply_metadata' => TRUE,
			'media_ids' => $media_ids
		  );

		$statues = $connection->post("statuses/update",$params);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{
				$response = array();
				$response['mention_id'] 					=  $statues->id;
				$response['mention'] 						=  $statues->text;
				$response['source'] 						=  $statues->source;
				$response['in_reply_to_status_id'] 			=  $statues->in_reply_to_status_id;
				$response['in_replay_from_tweet'] 			=  $this->showstatus($statues->in_reply_to_status_id,$oauth_token,$oauth_token_secret);       
				$response['created_at'] 					=  date('Y-m-d h:i:s',strtotime($statues->created_at));
					
		
			
			$this->_success($response);
		}
				 
	}

	private function getuser($id,$oauth_token,$oauth_token_secret){
		
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
		$params = array(
			'user_id'  => $id,
		  );

		$statues = $connection->get("users/show",$params);
		if(isset($statues->errors)){
			return '-';
		}else{
			$response = array();
			$response['id'] 					=  $statues->id;
			$response['name'] 					=  $statues->name;
			$response['screen_name'] 			=  $statues->screen_name;
			$response['poto_profile'] 			=  $statues->profile_image_url_https;
			
			return $response;	
		}
				 
	}

	private function checkme($oauth_token,$oauth_token_secret){
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
		$response = $connection->get("account/verify_credentials");

		return $response->id;
	}

	public function getdirectmessage(){
		
		$this->_mandatory( array('oauth_token','oauth_token_secret'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'count' => 50,
		  );

		$statues = $connection->get("direct_messages/events/list",$params);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$i = 0;
			$response = array();
			foreach($statues->events as $data){

				// if($data->message_create->sender_id == $this->checkme($oauth_token,$oauth_token_secret)){
				// 	continue;
				// }
				
				$response[$i]['type'] 						=  $data->type;
				$response[$i]['id'] 						=  $data->id;
				$response[$i]['message_data'] 				=  $data->message_create->message_data;
				$response[$i]['from_id'] 					=  $data->message_create->sender_id;
				$response[$i]['from_detail'] 				=  $this->getuser($data->message_create->sender_id,$oauth_token,$oauth_token_secret);    
				$response[$i]['created_timestamp'] 			=  $data->created_timestamp;
					
				$i++;
			}

			$this->_success($response);
		}

				 
	}


	public function getdirectmessageshow(){
		
		$this->_mandatory( array('oauth_token','oauth_token_secret','id'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$id = $this->input->post('id');

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'id' => $id,
		  );

		$statues = $connection->get("direct_messages/events/show",$params);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$response = array();
			$response['type'] 						=  $statues->event->type;
			$response['id'] 						=  $statues->event->id;
			$response['message_data'] 				=  $statues->event->message_create->message_data;
			$response['from_id'] 					=  $statues->event->message_create->sender_id;
			$response['from_detail'] 				=  $this->getuser($statues->event->message_create->sender_id,$oauth_token,$oauth_token_secret);    
			$response['created_timestamp'] 			=  $statues->event->created_timestamp;

			$this->_success($response);
		}
		
				 
	}


	public function postdirectmessage(){
		
		$this->_mandatory( array('oauth_token','oauth_token_secret','from_id','message','type'));
		
		if($this->input->post('type') == 'media'){
			$this->_mandatory( array('media_ids'));
		}
		
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$from_id = $this->input->post('from_id');
		$message = $this->input->post('message');
		$media_ids = $this->input->post('media_ids');
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
		$type = $this->input->post('type');
		if($type == 'media' ){
			$params = [
			'event' => [
				'type' => 'message_create',
				'message_create' => [
					'target' => [
						'recipient_id' => $from_id
					],
					'message_data' => [
						'text' => $message,
						'attachment' => [
							'type' => 'media',
							'media'=> ['id' => $media_ids ]
						]
						
					]
				]
			]
			];
		}else{
			$params = [
			'event' => [
				'type' => 'message_create',
				'message_create' => [
					'target' => [
						'recipient_id' => $from_id
					],
					'message_data' => [
						'text' => $message,
					]
				]
			]
		];
		}
		

		$statues = $connection->post("direct_messages/events/new",$params,true);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$response = array();
			$response['type'] 						=  $statues->event->type;
			$response['id'] 						=  $statues->event->id;
			$response['message_data'] 				=  $statues->event->message_create->message_data;
			$response['from_id'] 					=  $statues->event->message_create->sender_id;
			$response['from_detail'] 				=  $this->getuser($statues->event->message_create->sender_id,$oauth_token,$oauth_token_secret);    
			$response['created_timestamp'] 			=  $statues->event->created_timestamp;

			$this->_success($response);
		}
				 
	}

	public function deletedirectmessage(){
		
		$this->_mandatory( array('oauth_token','oauth_token_secret','id'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$id  = $this->input->post('id');

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'id' => $id,
		  );

	
		$statues = $connection->delete("direct_messages/events/destroy",$params);

		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$this->_success($statues);
		}
				 
	}

	public function webhooksregistercallbacks(){
		if(isset($_REQUEST['crc_token'])) {
		  $signature = hash_hmac('sha256', $_REQUEST['crc_token'], $this->CONSUMER_SECRET, true);
		  $response['response_token'] = 'sha256='.base64_encode($signature);
		  print json_encode($response);
		} else {   

		  	$eventJSON = file_get_contents('php://input');
			$data 	= json_decode($eventJSON);

			/*twitter-DM*/			
			if($data->direct_message_events[0]->type == 'message_create'){
				$nama_file = $data->direct_message_events[0]->created_timestamp.'.json';
				$nama_folder = $data->direct_message_events[0]->message_create->sender_id;
				$path = "public/twitternotif/direct_message/".$nama_folder;
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				$mode = (!file_exists($path.'/'.$nama_file)) ? 'w':'a';
				$handle = fopen($path.'/'.$nama_file.'',$mode);
				
				//custom params
				$userid = $data->direct_message_events[0]->message_create->target->recipient_id;
				$senderid = $data->direct_message_events[0]->message_create->sender_id;
				$timestamp = $data->direct_message_events[0]->created_timestamp;
				$text = $data->direct_message_events[0]->message_create->message_data->text;
				$attach = $data->direct_message_events[0]->message_create->message_data->attachment->media->media_url_https;
				$attachment = str_replace('&', '|', $attach);
				//detail sender
				$name = $data->users->$senderid->name;
				$screen_name = $data->users->$senderid->screen_name;
				$follower = $data->users->$senderid->followers_count;
				$friends = $data->users->$senderid->friends_count;
				$status = $data->users->$senderid->statuses_count;
				$url_profile = $data->users->$senderid->profile_image_url_https;
				
				$params = "{Raw:'',Data1:'".$senderid."',Data2:'".$timestamp."',Data3:'".$screen_name."',Data4:'".$text."',Data5:'".$userid."',Data6:'".$attachment."',Data7:'".$follower."',Data8:'".$friends."',Data9:'".$status."',Data10:''}";
				$params_1 = str_replace("#", "%23", $params);
				$params_2 = str_replace("\n", "<br>", $params_1);
				$params_final = str_replace(" ", "%20", $params_2);
				// $url = 'https://10.1.25.69:8013/ApiBounty/Service1.svc/send_Hunter?value='.$params_final;
				$url = 'https://invision.ddns.net:30008/ApiBounty2/Service1.svc/send_Hunter?value='.$params_final;

				fwrite($handle,"\r\n".$eventJSON."\r\n params_final :".$params_final);
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
				echo $result;
				
			/*untuk mention*/	
			}else{
				$nama_file = $data->tweet_create_events[0]->timestamp_ms.'.json';
				$nama_folder = $data->tweet_create_events[0]->user->id;
				$path = "public/twitternotif/mention/".$nama_folder;
				if (!is_dir($path)) {
					mkdir($path, 0777, true);
				}
				$mode = (!file_exists($path.'/'.$nama_file)) ? 'w':'a';
				$handle = fopen($path.'/'.$nama_file.'',$mode);

				$page_id = $data->for_user_id;
				$mention_id = $data->tweet_create_events[0]->id_str;
				// $status_id = $data->tweet_create_events[0]->in_reply_to_status_id_str;
				$status_id_str = $data->tweet_create_events[0]->in_reply_to_status_id_str;
				if ($status_id_str === null){
					$status_id = $data->tweet_create_events[0]->id_str;
				}
				else{
					$status_id = $data->tweet_create_events[0]->in_reply_to_status_id_str;
				}

				$timestamp = $data->tweet_create_events[0]->timestamp_ms;
				$sender_id = $data->tweet_create_events[0]->user->id;
				$screen_name = $data->tweet_create_events[0]->user->screen_name;
				$text = $data->tweet_create_events[0]->text;
				$attach = $data->tweet_create_events[0]->user->profile_image_url_https;
				$attachment = str_replace('&', '|', $attach);
				$media_val = $data->tweet_create_events[0]->entities->media[0]->media_url_https;
				$media = str_replace('&', '|', $media_val);
				
				$params = "{Raw:'',Data1:'".$sender_id."',Data2:'".$timestamp."',Data3:'".$screen_name."',Data4:'".$text."',Data5:'".$page_id."',Data6:'".$attachment."',Data7:'".$status_id."',Data8:'".$media."',Data9:'".$mention_id."',Data10:'TWMention'}";
				$params_1 = str_replace("#", "%23", $params);
				$params_2 = str_replace("\n", "<br>", $params_1);
				$params_final = str_replace(" ", "%20", $params_2);
				// $url = 'https://10.1.25.69:8013/ApiBounty/Service1.svc/send_Spirit?value='.$params_final;
				$url = 'https://invision.ddns.net:30008/ApiBounty2/Service1.svc/send_Spirit?value='.$params_final;
				
				fwrite($handle,"\r\n".$eventJSON."\r\n params_final :".$params_final);
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
		
		$this->_mandatory( array('oauth_token','oauth_token_secret'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array(
			'url' => 'https://mendawai.com/sosialapi/sosial/twitter/twitter/webhooksregistercallbacks'
		  );

		
		$statues = $connection->post("account_activity/all/$this->environments/webhooks",$params);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$this->_success($statues);
		}
				 
	}
	
	public function webhooksunregistercallback(){
	
		
		$this->_mandatory( array('oauth_token','oauth_token_secret','webhooks_id'));
		$oauth_token 		= $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$webhooks_id 		= $this->input->post('webhooks_id');
		//$id  = $this->input->post('id');

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array();

		
		$statues = $connection->delete("account_activity/all/$this->environments/webhooks/$webhooks_id",$params);

		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$this->_success($statues);
		}
				 
	}
	
	public function webhooksregistercallbackcheck(){
		
		$this->_mandatory( array('oauth_token','oauth_token_secret'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array();

	
		$statues = $connection->get("account_activity/all/webhooks",$params);

		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$this->_success($statues);
		}
				 
	}
	
	
	public function webhookssubscribe(){
		
		
		$this->_mandatory( array('oauth_token','oauth_token_secret'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');

		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array();

		
		$statues = $connection->post("account_activity/all/$this->environments/subscriptions",$params);

		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$this->_success($statues);
		}
				 
	}
	
	public function webhooksunsubscribe(){
		
		
		$this->_mandatory( array('oauth_token','oauth_token_secret'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');


		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);

		$params = array();

		
		$statues = $connection->delete("account_activity/all/$this->environments/subscriptions",$params);

		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{  
			$this->_success($statues);
		}
				 
	}
	
	
	public function uploadmedia(){
		
		$this->_mandatory( array('oauth_token','oauth_token_secret','url'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$url = $this->input->post('url');
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
		$params = array(
			'media'  => $url,
		  );

		$statues = $connection->upload("media/upload",$params);
		if(isset($statues->errors)){
			$this->_error($statues->errors['0']->code.' - '.$statues->errors['0']->message);
		}else{
			$response = array();
			$response['media_id'] 					=  $statues->media_id;
			$response['media_id_string']			=  $statues->media_id_string;
			$response['size'] 						=  $statues->size;
			$response['expires_after_secs'] 		=  $statues->expires_after_secs;
			$response['data'] 						=  $statues->image;
					
		
			
			$this->_success($response);
		}	
				 
	}
	
	public function retrievimage(){	
		
		$this->_mandatory( array('oauth_token','oauth_token_secret','url','extension'));
		$oauth_token = $this->input->post('oauth_token');
		$oauth_token_secret = $this->input->post('oauth_token_secret');
		$url = $this->input->post('url');
		$extension = $this->input->post('extension');
		$connection = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $oauth_token, $oauth_token_secret);


		$statues = $connection->getRetrievImage($url);
		
		if($statues != ''){
			
			$url = 'https://mendawai.com/sosialapi/public/file/';
			$nama_file = rand(1000,10000).strtotime(date('Ymd h:i:s'));
			$mode = $extension;
			$path = 'public/file/'.$nama_file.'.'.$mode;
			file_put_contents($path, $statues);
			
			$response = array();
			$response['url'] 						=  $url.$nama_file.'.'.$mode;
			$this->_success($response);
		}else{
			$this->_error('error file');
		}
		/*$im = imagecreatefromstring($statues);
		if ($im !== false) {
			header('Content-Type: image/png');
			imagepng($im);
			imagedestroy($im);
		}*/
	}

	


}
