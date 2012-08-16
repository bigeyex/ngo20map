<?php

class ListenAction extends Action{

	public function index(){
		$q = $_GET['q'];
		
		$this->assign('q', $q);
		$this->display();
	}
	
	public function sina(){
		import("@.Util.SinaOpenApi");
		$openapi = new SinaOpenApi('3210519828');
		$openapi->setUser('bigeyex@163.com', 'ngo20@ustc');

		$content = $openapi->request('statuses/search', array(q => $_GET['q']), 'GET');
		header('Content-Type:text/html;charset=UTF-8');
		echo "<ul>";
		foreach($content as $weibo){
			echo '<li><a href="'. $weibo['user']['url'] .'">' . $weibo['user']['name'] . '</a> : ' . mb_substr($weibo['text'],0,100) . "</li>";
		}
		echo "</ul>";
	}
	
	public function tencent(){
		import("@.Util.Tencent.Weibo");
	
		$_SESSION[OpenSDK_Tencent_Weibo::ACCESS_TOKEN]="b3925ee2c6444560812e7a9ca2ce1dc0";
		$_SESSION[OpenSDK_Tencent_Weibo::OAUTH_TOKEN_SECRET]="ddc13be9996c5e446d645719cf5b3f25";
		
		echo OpenSDK_Tencent_Weibo::init("801099234", "be55c3a5c7391dd4dcd2bb83df7eaacf");
		
		$api_name = 'search/t';
		$params=array(
						'format'=>'json',
						'keyword' => $_GET['q'],
						);
		$call_result = OpenSDK_Tencent_Weibo::call($api_name,$params);
		
		header('Content-Type:text/html;charset=UTF-8');
		$i = 1;
		echo "<ul>";
		foreach($call_result['data']['info'] as $t){
			echo '<li><a href="http://t.qq.com/'. $t['name'] .'">' . $t['nick'] . '</a> : ' . mb_substr($t['origtext'],0,100) . "</li>";	
			if($i >= 10) break;
			$i++;
		}
		echo "</ul>";
	}
	
}
?>