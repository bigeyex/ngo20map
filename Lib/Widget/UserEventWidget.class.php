<?php
//显示该用户发布的事件
class UserEventWidget extends EduWidget{

    public function render($data){
        $event_model = M('Events');
        $login_user_id = $_SESSION['login_user']['id'];
        if(!isset($data['user_id'])){
        	$data['user_id'] = $login_user_id;
        }
        $user_events = $event_model->where(array('user_id' => $data['user_id'], 'enabled'=>1))->select();
        $is_admin = $_SESSION['login_user']['is_admin'];
        
        //把标签(res_tags, item_field)断成数组,以适应标签的样式
        for($i=0;$i<count($user_events);$i++){
        	$user_events[$i]['item_field_array'] = explode(' ', $user_events[$i]['item_field']);
        	$user_events[$i]['res_tags_array'] = explode(' ', $user_events[$i]['res_tags']);
        	$user_events[$i]['host'] = implode(' ', explode(',', $user_events[$i]['host']));
        	if($is_admin || $user_events[$i]['user_id'] == $login_user_id){
        		$user_events[$i]['editable'] = true;
        	}
        	else{
        		$user_events[$i]['editable'] = false;
        	}
        }
        
        $tpl_data = array();
        $tpl_data['user_events'] = $user_events;
        $content = $this->renderFile('UserEvent',$tpl_data);
        return $content;
    }

}
?>
