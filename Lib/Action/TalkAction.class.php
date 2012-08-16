<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

class TalkAction extends Action {
    public function view() {
        if(!isset($_GET['id']))$_GET['id'] = 0;
        $_SESSION['last_read_talk'] = date("Y-m-d H:i:s");
        $this->display();
    }
    public function ajax_get_message() {
        import('Think.Util.Session');
        $message = M('Messages');
        $r_user_talk = M('R_user_talk');
        $user = M('Users');
        $talk = M('Talks');
        $get_talk_id = $_GET['id'];

        if($get_talk_id == -1){
            $event_model = M('Events');
            $event = $event_model->find($_GET['event_id']);
            $event_user = $user->find($event['user_id']);
            $this->assign('relative_user',array($event_user,$_SESSION['login_user']));
            $this->assign('message_data',array());
            $this->assign('talk_info',array('title'=>$event['name']));
            $this->display('Talk:ajax_get_message');
            return;
        }

        $message_data = $message->where(array('talk_id'=> $get_talk_id))->order('create_time asc')->limit(10)->select();

        $r_user_talk->create();
        $get_relation=$r_user_talk->where('talk_id=$get_talk_id');
        $get_relation->is_read = 1;
        $get_relation->save();
        Session::set('last_read',date("Y-m-d H:i:s"));

        $talk->create();
        $get_current_talk=$talk->where('id=$get_talk_id');
        $get_current_talk->last_touch =Session::get('last_read');
        $get_current_talk->save();

        
        $ret = array();
        $i=0;
        foreach($message_data as $item) {
            $get_user[$i] = $user->find($item['user_id']);
            $ret[$i]['username']=$get_user[$i]['name'];
            $ret[$i] = array_merge($ret[$i], $item);
            $i +=1;
        }
        $this->assign('message_data',$ret);

        $get_talk = $talk->where(array('id'=> $get_talk_id))->find();
        $get_users = $r_user_talk->where(array('talk_id'=> $get_talk_id))->field('user_id')->select();
        $ret2 = array();
        $i=0;
        foreach($get_users as $item) {
            $ret2[$i] = $user->find($item['user_id']);
            $i +=1;
        }
        $this->assign('relative_user',$ret2);
        $this->assign('talk_info',$get_talk);
        $this->display('Talk:ajax_get_message');
    }
    public function ajax_get_new_message() {
        import('Think.Util.Session');
        $message = M('Messages');
        $talk = M('Talks');
        $user = M('Users');
        $r_user_talk = M('R_user_talk');
        $condition['talk_id']=$_GET['talk_id'];
        $condition['user_id']=$_SESSION['login_user']['id'];
        $some_talk_relation = $r_user_talk->where($condition)->find();

        if(!$some_talk_relation['is_read']) {
            $condition2['talk_id']=$condition['talk_id'];
            $condition2['create_time'] = array('gt',Session::get('last_read'));
            $new_message = $message->where($condition2)->select();
            Session::set('last_read',date("Y-m-d H:i:s"));
            $r_user_talk->create();
            $r_user_talk->where(array('talk_id'=>$_GET['talk_id'],
                'user_id'=>$_SESSION['login_user']['id']))->data(array('is_read'=>1))->save();
            Session::set('last_read',date("Y-m-d H:i:s"));

            $talk->create();
            $get_current_talk=$talk->where('id=$get_talk_id');
            $get_current_talk->last_touch =Session::get('last_read');
            $get_current_talk->save();

            $ret = array();
            $i=0;
            foreach($new_message as $item) {
                $get_user[$i] = $user->find($item['user_id']);
                $ret[$i]['username']=$get_user[$i]['name'];
                $ret[$i] = array_merge($ret[$i], $item);
                $i +=1;
            }
            $this->assign('new_message_data',$ret);
            $this->display('Talk:ajax_get_new_messages');
        }
        else
            return null;
        
    }
    public function ajax_post_message() {
        $message = M('Messages');
        $message->create_time = date("Y-m-d H:i:s");
        $message->content = $_POST['content'];
        $message->talk_id = $_POST['talk_id'];
        $message->user_id = $_SESSION['login_user']['id'];
        $message->create_time=date("Y-m-d H:i:s");
        $new_message_id = $message->add();
        
        $talk = M('Talks');
        $talk->create();
        $get_current_talk=$talk->where(array('id'=>$_POST['talk_id']));
        $get_current_talk->is_read = 0;
        $get_current_talk->last_touch = date("Y-m-d H:i:s");
        $get_current_talk->save();

        $new_message = array(
            'username' => $_SESSION['login_user']['name'],
            'content' => $_POST['content'],
            'user_id' => $_SESSION['login_user']['id'],
            'create_time' => date("Y-m-d H:i:s"),
            );

        $relation_model = M('RUserTalk');
        $relation_model->where(array(
            'talk_id' => $_POST['talk_id'],
            'user_id' => array('neq', $_SESSION['login_user']['id']),
        ))->data(array('is_read'=>0))->save();  //把不是本用户的状态都改成未读

        if($new_message_id){
            $this->assign('new_message_data', array($new_message));
            $this->display('Talk:ajax_get_new_messages');
        }
    }
    public function ajax_list_talk() {
        import("ORG.Util.Page");
        $model = new Model();
        $login_user_id = $_SESSION['login_user']['id'];
        $condition = "from talks, r_user_talk where user_id = $login_user_id and talk_id=talks.id and is_deleted=0";
        if(isset($_GET['q'])){
            $query_string = $_GET['q'];
            $condition .= " and (title like '%$query_string%' or users like '%$query_string%')";
        }
        $talk_count = $model->query('select count(*) as count '. $condition);
        $pager = new Page($talk_count[0]['count'],C('TALK_LIST_NUM'));
        $pager_content = $pager->show();
        $result = $model->query('select * '.$condition.' limit '.$pager->firstRow.','.$pager->listRows);

        $this->assign('talk_data', $result);
        $this->assign('page',$pager_content);
        if(isset($_GET['q'])){
            $this->display('Talk:ajax_search_talk');
        }else{
            $this->display('Talk:ajax_list_talk');
        }
    }
    public function ajax_get_new_talk() {
        $model = new Model();
        $login_user_id = $_SESSION['login_user']['id'];
        $condition = "from talks, r_user_talk where user_id = $login_user_id and talk_id=talks.id and is_deleted=0 and talks.last_touch>'".
                    $_SESSION['last_read_talk']."'";
        $result = $model->query('select * '.$condition);
        $_SESSION['last_read_talk'] = date("Y-m-d H:i:s");
        $this->assign('talk_data', $result);
        $this->display('Talk:ajax_get_new_talk');
    }
    public function ajax_delete_talk() {
        $r_user_talk = M('R_user_talk');
        $talk_model = M('Talks');
        $r_user_talk->where(array(
            'user_id' => $_SESSION['login_user']['id'],
            'talk_id' => $_GET['talk_id'],
        ))->data(array(
            'is_deleted' => 1
        ))->save();
        
        $talk = $talk_model->find($_GET['talk_id']);
        $member_list = explode(',', $talk['users']);
        $member_list_result = array();
        $current_user = $_SESSION['login_user']['name'];
        foreach($member_list as $member){
            if($member != $current_user){
                $member_list_result[] = $member;
            }
        }
        $talk_model->data(array(
            'id' => $talk['id'],
            'users' => implode(',', $member_list_result),
        ))->save();
    }
    public function ajax_add_talk_member(){
        $talk_model = M('Talks');
        $r_user_talk_model = M('RUserTalk');
        $user_model = M('Users');
        
        $talk_to_add = $talk_model->find($_POST['talk_id']);
        $member_id_list = array();
        $member_list = array();
        $member_name_list = explode(',', $talk_to_add['users']);
        
        $members = explode(',',$_POST['member']);
        foreach($members as $member){
            if($member != ''){
                $member_user_data = $user_model->find($member);
                if(!in_array($member_user_data['name'], $member_name_list)){
                    $member_name_list[] = $member_user_data['name'];
                    $member_id_list[] = $member;
                    $member_list[] = $member_user_data;
                }
            }
        }
        $talk_model->data(array(
            'id' => $_POST['talk_id'],
            'names'=> implode(',', $member_name_list),
        ))->save();
        foreach($member_id_list as $member_id){
            $r_user_talk_model->add(array(
                'talk_id' => $_POST['talk_id'],
                'user_id' => $member_id,
                'is_read' => 0,
            ));
        }
        $this->assign('member_list', $member_list);
        $this->display('Talk:ajax_get_new_member');

    }
    public function ajax_new_talk(){
        $talk_model = M('Talks');
        $r_user_talk_model = M('RUserTalk');
        $user_model = M('Users');
        if(isset($_POST['event_id'])){
            $event_model = M('Events');
            $event = $event_model->find($_POST['event_id']);
            $members = array($event['user_id']);
            $_POST['title'] = $event['name'];
            $event_id=$event['id'];
        }
        else{
            $members = explode(',',$_POST['member']);
            $event_id=0;
        }
        $talk_model->create();
        $talk_model->create_time = date("Y-m-d H:i:s");
        $talk_model->last_touch = date("Y-m-d H:i:s");
        $member_id_list = array();
        $member_name_list = array();
        foreach($members as $member){
            if($member != ''){
                $member_user_data = $user_model->find($member);
                $member_name_list[] = $member_user_data['name'];
                $member_id_list[] = $member;
            }
        }
        $member_name_list[] = $_SESSION['login_user']['name'];
        $member_id_list[] = $_SESSION['login_user']['id'];
        $talk_model->users = implode(',', $member_name_list);
        $talk_id = $talk_model->add();
        foreach($member_id_list as $member_id){
            $r_user_talk_model->add(array(
                'talk_id' => $talk_id,
                'event_id' => $event_id,
                'user_id' => $member_id,
                'is_read' => 0,
            ));
        }
        $this->assign('talk_data', array(array(
            'talk_id' => $talk_id,
            'title' => $_POST['title'],
            'users' => $talk_model->users,
            'is_read' => 0,
        )));
        $this->display('Talk:ajax_get_new_talk');
    }
}
?>
