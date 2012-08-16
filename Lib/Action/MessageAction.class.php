<?php

class MessageAction extends Action{

    public function ajax_mailbox(){
        $this->display();

    }
    
    public function json_talk_list(){
    	$model = new Model();
    	
    	$user_id = $_SESSION['login_user']['id'];
    	if(isset($_GET['t']) && $_GET['t'] != 0){
    		$query_from = $_SESSION['last_talk_check'];
    		
    		$time_sql = " and t2.create_time>='$query_from'";
    	}
    	else{
    		$time_sql = '';
    	}
    	$_SESSION['last_talk_check'] = date('Y-m-d h:m:s');
    	
    	// this is a three fold sql.
    	// Fold 1: apply constraints, select count data, sort talks and combine other_user.
    	// Fold 2: sort messages for "group by"
    	// Fold 3: create "user_id" and "other_user_id"
    	$sql = "select t2.*,count(*) as record_count, sum(if(is_read=0,1,0)) as unread_count,users.name,users.image
					from(
						select * from (
							select id,content,create_time,1 as is_read,from_user_id as user_id,to_user_id as other_user_id
								from messages union select id,content,create_time,is_read,to_user_id as user_id,
									from_user_id as other_user_id from messages
						) t1 order by create_time desc
					) t2 left join users on users.id=t2.other_user_id where user_id=$user_id $time_sql
					group by other_user_id order by create_time";
    	
    	$messages = $model->query($sql);
    	
    	$this->ajaxReturn($messages, 'json');
    }
    
    public function json_message_list(){
    	$model = new Model();
    	
    	$user_id = $_SESSION['login_user']['id'];
    	$other_user_id = intval($_GET['id']);
    	if(isset($_GET['t']) && $_GET['t'] != 0){
    		$query_from = $_SESSION['last_message_check'];
    		$time_sql = " and create_time>='$query_from'";
    	}
    	else{
    		$time_sql = '';
    	}
    	$_SESSION['last_message_check'] = date('Y-m-d h:m:s');
    	
    	$sql = "select id,content,create_time,if(from_user_id=$user_id,1,0) as is_mine from messages where (from_user_id=$user_id and to_user_id=$other_user_id or from_user_id=$other_user_id and to_user_id=$user_id) $time_sql order by create_time";
    	
    	$messages = $model->query($sql);
    	
    	$sql = "select id,name,image from users where id=$other_user_id";
    	$other_user = $model->query($sql);
    	
    	// set those messages to read(ed).
    	$sql = "update messages set is_read=1 where from_user_id=$other_user_id and to_user_id=$user_id";
    	$model->query($sql);
    	
    	$result = array(
    		'messages' => $messages,
    		'other_user' => $other_user[0],
    		'myself' => array(
    			'name' => $_SESSION['login_user']['name'],
    			'image' => $_SESSION['login_user']['image']	
    		)
    	);
    	
    	$this->ajaxReturn($result, 'json');
    }
    
    public function send_message(){
    	$user_model = M('Users');
    	$message_model = M('Messages');
    	$user_id = 0;
    	$error_string = '';
    	
    	// resolve user id
    	$user_names = explode(',', $_POST['user_name']);
    	$user_ids = array();
    	foreach($user_names as $name){
    		$name = trim($name);
    		if(empty($name)){
    			continue;
    		}
    		$user = $user_model->where(array('name' => $name))->select();
    		if(count($user) == 0){
    			$error_string .=  $name . ', ';
    			continue;
    		}
    		$user_ids[] = $user[0]['id'];
    	} // end of foreach user_names
    	
    	if($error_string != ''){
    		echo L('以下用户不存在: ' . $error_string);
    		return;
    	}
    	$content = $_POST['content'];
		foreach($user_ids as $user_id){
			$message_model->data(array(
				'content' => $content,
				'from_user_id' => $_SESSION['login_user']['id'],
				'to_user_id' => $user_id,
				'create_time' => date('Y-m-d h:m:s')
			))->add();
		} // end of user_ids
		echo 'success';
    } // end of function send_message
    
    public function unread_count(){
    	$message_model = M('Messages');
    	$unread_messages = $message_model->where(array('to_user_id'=>$_SESSION['login_user']['id']))->count();
    	
    	echo $unread_messages;
    
    }
}
?>