<?php

class ApiAction extends Action{

	public function qq_login(){
		$code = $_GET['code'];
		$openid = $_GET['openid'];
		$openkey = $_GET['openkey'];
		$redirect_uri = "http://" . $_SERVER['HTTP_HOST'] . __APP__ . "/Api/qq_login";

		$client_id = C('QQ_APPKEY');
		$client_secret = C('QQ_APPSECRET');
		$access_token = '';
		$expires_in = '';

		$request_uri = "https://open.t.qq.com/cgi-bin/oauth2/access_token?client_id=$client_id&client_secret=$client_secret&redirect_uri=$redirect_uri&grant_type=authorization_code&code=$code";
		if($result = file_get_contents($request_uri)){
			//parse param from qq response
			foreach(explode('&', $result) as $block){
				$param = explode('=', $block);
				if($param[0] == "access_token") $access_token = $param[1];
				if($param[0] == "expires_in") $expires_in = $param[1];
			}

			//save param to session
			$api = array();
			$api['api_vendor'] = 'qq';
			$api['api_id'] = $openid;
			$api['api_openkey'] = $openkey;
			$api['api_token'] = $access_token;
			$_SESSION['api'] = $api;

			//check if new user
			$user_model = M('Users');
			$user = $user_model->where(array('api_vendor'=>'qq', 'api_id'=>$openid))->find();
			if($user){
				$_SESSION['login_user'] = $user;
				$user_model->query("update users set last_login=now() where id=".$user['id']);
				$user_model->query("update users set login_count=login_count+1 where id=".$user['id']);
				$this->redirect('User/recommend');
			}
			else{
				$this->redirect('User/pre_register');
			}
		}
	}

	public function weibo_login(){
		$code = $_GET['code'];
		$redirect_uri = "http://" . $_SERVER['HTTP_HOST'] . __APP__ . "/Api/weibo_login";

		$client_id = C('WEIBO_APPKEY');
		$client_secret = C('WEIBO_APPSECRET');
		$access_token = '';
		$expires_in = '';
		$api_id = '';

		$request_uri = "https://api.weibo.com/oauth2/access_token";
		$post_params = array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri' => $redirect_uri,
				'grant_type' => 'authorization_code',
				'code' => $code,
			);
		if($result = $this->http_post($request_uri, $post_params)){
			//parse param from weibo response
			$token = json_decode($result, true);
			$access_token = $token['access_token'];
			$expires_in = $token['expires_in'];

			//get weibo id
			$request_uri = "https://api.weibo.com/2/account/get_uid.json?access_token=$access_token";
			if($result = file_get_contents($request_uri)){
				$param = json_decode($result, true);
				$api_id = $param['uid'];

				//save param to session
				$api = array();
				$api['api_vendor'] = 'weibo';
				$api['api_id'] = $api_id;
				$api['api_token'] = $access_token;
				$_SESSION['api'] = $api;

				//check if new user
				$user_model = M('Users');
				$user = $user_model->where(array('api_vendor'=>'weibo', 'api_id'=>$api_id))->find();
				if($user){
					$_SESSION['login_user'] = $user;
					$user_model->query("update users set last_login=now() where id=".$user['id']);
					$user_model->query("update users set login_count=login_count+1 where id=".$user['id']);
					$this->redirect('User/recommend');
				}
				else{
					$this->redirect('User/pre_register');
				}
			}	//get weibo id
			else die('get weibo id failed');
		}	//get access token
		else die ('get access token failed');
	}

	private function http_post($request_uri, $data){
		$url = $request_uri;
		$ch = curl_init($url);
		 
		$body = http_build_query($data);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
}
?>