<?php
define('OFFSET', 268435456);
define('RADIUS', 85445659.4471); /* $offset / pi() */
class UserAction extends Action {
    public function pre_register(){
        $this->display();
    }

    public function newUser() {
        if(!isset($_GET['type'])){
            $this->redirect('pre_register');
        }

    	if(!empty($_GET['type'])){
    		$type = $_GET['type'];
    	}
    	else{
    		$type = 'ind';
    	}
        $type_string = C('USER_TYPE');
           
        //显示当提交失败重定向到此页时，用户已经填好的信息
        $last_form_data = null;
        if(isset($_SESSION['last_form'])){
        	$last_form_data = $_SESSION['last_form'];
        	unset($_SESSION['last_form']);
        }
        if(is_array($last_form_data['work_field'])){
            $last_form_data['work_field'] = implode(',', $last_form_data['work_field']);
        }
        
        $this->assign('type',$type);
        $this->assign('target_url','insert');
        $this->assign('type_string', $type_string);
        $this->assign('field_title', $this->build_field_title($type));
        $this->assign('user', $last_form_data);
        $this->display();
    }

    public function save_avatar() {
        import('ORG.Net.UploadFile');
        import('ORG.Util.Image');
        $user_model = M('Users');
        $upload = new UploadFile();
        $upload->maxSize = 3145728;
        $upload->savePath = './Public/Uploaded/';
        $upload->thumb = true;
        $upload->thumbPath = './Public/Uploadedthumb/';
        $upload->thumbPrefix="thumbs_,thumbm_,thumb_";
        $upload->thumbMaxWidth = "50,150,200";
        $upload->thumbMaxHeight = "50,150,200";
        $upload->saveRule = 'uniqid';
        if(!$upload->upload()) {
            print_r($upload->getErrorMsg());
            die(L('图片上传失败'));
        }
        else {
            $info = $upload->getUploadFileInfo();
        }
        $user_model->id = $_SESSION['login_user']['id'];
        $user_model->image = $info[0]["savename"];
        $user_model->save();
        setflash('ok',L('成功修改用户头像'),L('成功修改用户头像'));
        $_SESSION['login_user']['photo'] = $info[0]["savename"];
        $this->redirect('avatar');
    }

    public function commitedit() {
        import('@.Util.Authority');

        $user=M('Users');
        $user->create();

        //以下为头像上传代码
        import('ORG.Net.UploadFile');
        import('ORG.Util.Image');
        $upload = new UploadFile();
        $upload->maxSize = 3145728;
        $upload->savePath = './Public/Uploaded/';
        $upload->thumb = true;
        $upload->thumbPath = './Public/Uploadedthumb/';
        $upload->thumbPrefix="thumbs_,thumbm_,thumb_";
        $upload->thumbMaxWidth = "50,150,200";
        $upload->thumbMaxHeight = "50,150,200";
        $upload->saveRule = 'uniqid';
        if($upload->upload()) {
            $info = $upload->getUploadFileInfo();
            if($info[0]["savename"])
                $user->image = $info[0]["savename"];
        }
        
        //如果不是管理员或者没有传id，则改自己的资料
        if(!isset($_POST['id']) || !$_SESSION['login_user']['is_admin']){
       		$user->id = $_SESSION['login_user']['id'];
        }
        else{
        	$user->id = $_POST['id'];
        }
        
        if(!$_SESSION['login_user']['is_admin']){
        	$user->is_admin = 0;
        }

        $user->work_field = implode(' ',$_POST['work_field']);
        if(!check_model()) {
            setflash('error','',L('您提交的内容中可能有不合适的地方，请重新编辑'));
            $this->display('newUser');
        }else {
            $user->save();
            $result = $user->where(array('id' => $_SESSION['login_user']['id']))->find();
            if($result){
                $_SESSION['login_user'] = $result;
                setflash('ok','',L('您的个人信息已修改成功！'));
            }
            else{
                setflash('error','',L('信息更新失败'));
            }
            $this->redirect('home');//写好User/home后定位到该目标
        }

        $this->redirect('home');//写好User/home后定位到该目标
    }

    public function edit() {
        if($_SESSION['login_user']['is_admin'] && isset($_GET['id'])){
            $id = $_GET['id'];
        }else{
            $id = $_SESSION['login_user']['id'];
        } 
        $user_model = M('Users');
        $user_data = $user_model->find($id);
        $type = $user_data['type'];
        $type_string = C('USER_TYPE');
        
        $this->assign('type', $type);
        $this->assign('user', $user_data);
        $this->assign('type_string', $type_string);
        $this->assign('field_title', $this->build_field_title($type));
        $this->assign('target_url', 'commitedit');
        $this->display('newUser');
    }

    public function editpass() {
        $user = M('Users');
        $user->create();

        if($_SESSION['login_user']['is_admin']){
            $id = $_POST['id'];
        }else{
            $id = $_SESSION['login_user']['id'];        
	        if(md5($_POST['prepass']) != $_SESSION['login_user']['password']) {
	            setflash('error','',L( '请确认提供了正确的旧登录密码'));
	            redirect($_SERVER['HTTP_REFERER']);
	        }
        }
        $user->id = $id;
        $user->password = md5($_POST['password']);

        $user->save();
        $_SESSION['login_user']['password'] = md5($_POST['password']);
        setflash('ok','',L('用户密码已成功修改'));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function home() {
        $user_model = M('Users');       //获取用户资料
        
        if(isset($_SESSION['login_user'])){
        	$my_id = $_SESSION['login_user']['id'];
        }
        else{
	        $my_id = 0;
        }
        
        if(!isset($_GET['id'])) {
            if($my_id != 0) {
                $_GET['id'] = $_SESSION['login_user']['id'];
            }
            else {
                $this->redirect('Index/index');
            }
        }
        else{
	        if($my_id != 0){
	        	$follow_model = M('Follow');
	        	$follow_status = $follow_model->where(array('from' => $my_id, 'to' => $_GET['id'], 'type'=>'user'))->count();
	        	$this->assign('follow_status', $follow_status);
	        }
        }
        $user_data = $user_model->find($_GET['id']);
        if(!$user_data['longitude']) {
            $user_data['longitude'] = 116.404;
            $user_data['latitude'] = 39.915;
        }
        
        //决定主页上的筛选条件哪条是预先选中的
        $type_select_status = array(
        	'followed_events' => '',
        	'host_events' => '',
        	'own_events' => '',
        );
        if(empty($_GET['type'])){
        	$type_select_status['host_events'] = 'selected';
        }
        else{
        	$type_select_status[$_GET['type'].'_'.$_GET['model']] = 'selected';
        }
		
		$is_self = false;
        if ($_SESSION['login_user']['id']==$_GET['id']){
            $tab_value['followed']=L("我关注的项目");
            $tab_value['whois']=L("我的项目");
            $is_self = true;
        }
        else{
            $tab_value['followed']=L("TA关注的项目");
            $tab_value['whois']=L("TA的项目");
        }
        
        //处理url地址
		if(!preg_match('/^https?:/', $user_data['website'])){
			$user_data['website'] = 'http://' . $user_data['website'];
		}

        //处理公益人用户的专长选项
        if($user_data['type'] == 'ind'){
            $work_fields = C('ORG_FIELDS');
            $user_work_fields = explode(' ', $user_data['work_field']);
            $work_field_str = array();
            $spec_str = array();    //公益人专长
            foreach($user_work_fields as $field){
                if(in_array($field, $work_fields)){
                    $work_field_str[] = $field;
                }
                else{
                    $spec_str[] = $field;
                }
            }

            $user_data['work_field'] = implode(' ', $work_field_str);
            $user_data['spec_str'] = implode(' ', $spec_str);
        }

        //get all medals
        $user_id = $user_data['id'];
        $model = new Model();

        $medals = $model->query("select * from medal where medal.id in (select medal_id from medalmap where user_id=$user_id)");
        $this->assign('medals', $medals);
        
        $this->assign('is_self', $is_self);
        $this->assign('user_data', $user_data);
        $this->assign('tab_value', $tab_value);
        $this->assign('type_select_status', $type_select_status);
        $this->display();
    }
    public function my() {
        $user_model = M('Users');       //获取用户资料
        
        //现在此页面删除
        $this->redirect('recommend');
        
        if(isset($_SESSION['login_user'])){
        	$my_id = $_SESSION['login_user']['id'];
        }
        else{
	        $my_id = 0;
        }
        
        if(!isset($_GET['id'])) {
            if($my_id != 0) {
                $_GET['id'] = $_SESSION['login_user']['id'];
            }
            else {
                $this->redirect('Index/index');
            }
        }
        else{
	        if($my_id != 0){
	        	$follow_model = M('Follow');
	        	$follow_status = $follow_model->where(array('from' => $my_id, 'to' => $_GET['id']))->count();
	        	$this->assign('follow_status', $follow_status);
	        }
        }
        $user_data = $user_model->find($_GET['id']);
        if(!$user_data['longitude']) {
            $user_data['longitude'] = 116.404;
            $user_data['latitude'] = 39.915;
        }
        
        
		
		$is_self = false;
        if ($_SESSION['login_user']['id']==$_GET['id']){
            $tab_value['followed']=L("我关注的项目");
            $tab_value['whois']=L("我的项目");
            $is_self = true;
        }
        else{
            $tab_value['followed']=L("TA关注的项目");
            $tab_value['whois']=L("TA的项目");
        }
        $this->assign('is_self', $is_self);
        $this->assign('user_data', $user_data);
        $this->assign('tab_value', $tab_value);
        $this->display();
    }
    
    public function recommend(){
    	//获取推荐数据
    	$ngo_users = $this->query_recommend("ngo","users",3);
    	$ngo_events = $this->query_recommend("ngo","events",4);
    	
    	$csr_users = $this->query_recommend("csr","users",3);
    	$csr_events = $this->query_recommend("csr","events",4);
    	
    	if($_SESSION['login_user']['type'] == 'ind'){
    		$vol_events = $this->query_recommend("ngo","events",4, "志愿者需求");
    		$this->assign('vol_events', $vol_events);
    	}
    	
    	//读取关注动态
    	$db_model = new Model();
    	$my_id = $_SESSION['login_user']['id'];
    	$follow_stream = $db_model->query("select * from events where is_checked=1 and user_id in (select `to` from follow where `from`=$my_id and type='user')");
    	$follow_users = $db_model->query("select * from users where is_checked=1 and id in (select `to` from follow where `from`=$my_id and type='user')");
    	$follow_events = $db_model->query("select * from events where is_checked=1 and id in (select `to` from follow where `from`=$my_id and type='event')");
    	
    	$this->assign('ngo_users', $ngo_users);
    	$this->assign('ngo_events', $ngo_events);
    	$this->assign('csr_users', $csr_users);
    	$this->assign('csr_events', $csr_events);
    	$this->assign('follow_stream', $follow_stream);
    	$this->assign('follow_users', $follow_users);
    	$this->assign('follow_events', $follow_events);
    	$this->display();
    }
    
    public function recommend_rss(){
    	$event_model = M('Events');
        $province = $_GET['province'];
        $work_field = $_GET['work_field'];

        $where_clause = array('is_checked'=>1, 'enabled'=>1);
        if(!empty($province)){
            $where_clause['province'] = array('like', "%$province%");
        }
        if(!empty($work_field)){
            $where_clause['work_field'] = array('like', "%$work_field%");
        }

        $related_events = $event_model->where($where_clause)->order('create_time desc')->select();

        $this->assign('related_events', $related_events);
        $this->assign('province', $province);
        $this->assign('work_field', $work_field);
        $this->display();
    	//$this->display('','',"application/xml");
    }
    
    public function search(){
    	$user_model = M('Users');
    	$keyword = $_GET['keyword'];
    	$user = $user_model->where(array('name'=>$keyword))->find();
    	if($user){
    		$this->redirect('User/home/id/'.$user['id']);
    	}
    	else{
    		setflash('notice','',L('该组织在系统中尚无记录'));
    		$this->redirect('Index/index/keyword/'.$keyword);
    	}
    }
    
    

    public function check_unique() {
        $user=M('Users');
        $user->create();
        $email = $_GET['q'];
        $u = $user->where(array('email'=>array('eq',$email)))->count();
        if($u!=0) {
            echo L('电子邮箱已被使用');
        }
        else {
            echo 'ok';
        }
    }
    
    public function check_unique_name(){
		$user_model = M('Users');
		if($_GET['name'] == $_GET['q']){
			echo 'ok';
			return;
		}
		$user_count = $user_model->where(array('name' => $_GET['q']))->count();
		if($user_count == 0){
			echo 'ok';
		}
		else{
			echo L('用户名已存在');
		}
	}
	
	public function check_exist(){
		$user_model = M('Users');
		$user_count = $user_model->where(array('name' => $_GET['q']))->count();
		if($user_count != 0){
			echo 'ok';
		}
		else{
			echo L('用户不存在');
		}
	}

    public function insert() {
        $user=M('Users');
        $user->create();
        
        //检查验证码是否一致
        if($_SESSION['verify'] != md5($_POST['verify'])) {
        	setflash('error','',L('验证码不一致'));
        	$_SESSION['last_form'] = $_POST;
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }
        
        import('@.Util.Authority');
        //以下为头像上传代码
        import('ORG.Net.UploadFile');
        import('ORG.Util.Image');
        $upload = new UploadFile();
        $upload->maxSize = 3145728;
        $upload->allowExts=array('jpg','jpeg','png','gif');
        $upload->savePath = './Public/Uploaded/';
        $upload->thumb = true;
        $upload->thumbPath = './Public/Uploadedthumb/';
        $upload->thumbPrefix="thumbs_,thumbm_,thumb_";
        $upload->thumbMaxWidth = "50,150,200";
        $upload->thumbMaxHeight = "50,150,200";
        $upload->saveRule = 'uniqid';
        if(!$upload->upload()) {
            /*$error_msg = $upload->getErrorMsg();
            setflash('error','',L($error_msg));
            $_SESSION['last_form'] = $_POST;
            $this->redirect('newUser');
            */
        }else{
            $info = $upload->getUploadFileInfo();
        }
//      $user->id = $_SESSION['login_user']['id'];
        $user->image = $info[0]["savename"];
//        $user_model->save();
//        setflash('ok','成功修改用户头像','成功修改用户头像');
//        $_SESSION['login_user']['photo'] = $info[0]["savename"];
//        $this->redirect('newUser');

        $user->work_field = implode(' ',$_POST['work_field']);
        $user->type = $_POST['type'];
        $user->password = md5($user->password);
        $user->create_time = date('Y-m-d H:i:s');
        $user->last_login = date('Y-m-d H:i:s');
        
        if(!$_SESSION['login_user']['is_admin']){
        	$user->is_admin = 0;
        }

        if(!check_model()) {
            setflash('error','',L('您提交的内容中可能有不合适的地方，请重新编辑'));
            $_SESSION['last_form'] = $_POST;
            $this->redirect('newUser');
        }else {
            if($_POST['type']=="ind") {
                $user->is_checked = 1;
                $user->add();
                Authority::authorize($_POST['email'], $_POST['password']);
                setflash('ok','', L('帐号已成功创建！'));
            }
            else {
                $user->add();
                Authority::authorize($_POST['email'], $_POST['password']);
                setflash('ok','', L('您的帐号类型为组织类用户，管理员审核后可获得更多权限！'));

            }
            $this->redirect('recommend');//写好User/home后定位到该目标
        }
    }
    
    public function follow(){
    	$follow_model = M('Follow');
    	
    	$target_id = $_POST['id'];
    	$my_id = $_SESSION['login_user']['id'];
    	
    	$follow_record = $follow_model->where(array('from' => $my_id, 'to' => $target_id, 'type'=>'user'))->select();
    	if(count($follow_record) == 0){  // not followed yet, follow and return 1.
    		$follow_model->data(array('from' => $my_id, 'to' => $target_id, 'type'=>'user'))->add();
    		echo 1;
    	}else{	//already followed, cancel it and return 0.
    		$follow_model->where(array('from' => $my_id, 'to' => $target_id, 'type'=>'user'))->delete();
    		echo 0;
    	}
    }
    
    private function build_field_title($type){
        //根据不同的用户类型决定一些表单标题
        $field_title = array(
        	'user_name' => L('姓名'),
        	'introduction' => L('个人简介'),
        	'work_field' => L('关注领域'),
        	'figure' => L('头像'),
        );
        if($type != 'ind'){
        	$field_title['user_name'] = L('机构全称');
        	$field_title['introduction'] = L('机构简介');
        	$field_title['work_field'] = L('关注领域');
        	$field_title['figure'] = L('机构标志');
        }
        return $field_title;
    }
    
    //查找向用户推荐的内容
    //type: 内容分类(ngo, csr)
    //unit: 内容类型(users, events)
    //limit: 搜索的条数
    private function query_recommend($type, $unit, $limit, $user_id=0, $res_tags=0){
    	//定义基本常数
    	$geo_weight = 1;				//地理接近性权重
    	if($res_tags != 0){
			$geo_weight = 20;	//if query volunteer info., care more about location.
		}
    	$category_weight = 200;		//类别权重
    	$date_weight = 0;			//时效性权重
    	
    	//根据类型不同定义一些常量
    	if($unit == 'users'){
    		$sql_category = 'work_field';
    	}
    	else{	//model == 'events'
    		$sql_category = 'item_field';
    	}
    	if($user_id == 0){
	    	$myself = $_SESSION['login_user'];
    	}
    	else{
			$user_model = M('Users');
			$myself = $user_model->find($user_id);
    	}
    	$my_longitude = $myself['longitude'];
    	$my_latitude = $myself['latitude'];
    	$my_categories = explode(' ',$myself['work_field']);
    	
    	$model = new Model();
    	$sql = "select *, $geo_weight*(abs(longitude-$my_longitude)+abs(latitude-$my_latitude))";
		foreach($my_categories as $category){
			$sql .= "-$category_weight*if($sql_category like '%$category%',1,0)";
		}
		
		if($unit == 'events'){
			$sql .= " score from events";
		}
		else{
			$sql .= " score from users";
		}
		
		$sql .= " where type='$type'";
		if($res_tags != 0){
			$sql .= " and res_tags like '%$res_tags%'";
		}
		$sql .= " and enabled=1 and is_checked=1 order by score limit 0,$limit";
    	return $model->query($sql);
    }
    
    function forget_password_action(){
    	$user_model = M('Users');
    	$forget_password_model = M('ForgetPassword');
    	$salt = "^k99ekFd";
    	
    	$user = $user_model->where(array('email'=>$_POST['email'], 'name'=>$_POST['name']))->find();
    	if(empty($user)){
    		setflash('error','', L('输入的电子邮件地址和姓名/组织名称不匹配'));
    		redirect($_SERVER['HTTP_REFERER']);
    	}
    	
    	$user_id = $user['id'];
    	$expDate = date('Y-m-d H:i:s',strtotime("+3 Day",time()));
    	$key = md5($user['name'] . '_' . $user['id'] . rand(0,10000) .$expDate . $salt);
    	$issue_count = $forget_password_model->where(array('user_id'=>$user_id, 'expire_date'=>array('lt', 'now()')))->count();
    	if($issue_count > 0){
    		setflash('error','', L('已经发送过找回密码邮件'));
    		redirect($_SERVER['HTTP_REFERER']);
    	}
    	$forget_password_model->data(array(
    		'user_id' => $user_id,
    		'link' => $key,
    		'expire_date' => $expDate
    	))->add();
    	
    	$uname = $_POST['name'];
    	$passwordLink = 'http://' . $_SERVER['HTTP_HOST'] . __APP__ . "/User/reset_password/key/$key";
    	$to = $user['email'];
		$subject = "ngo20密码找回";
		$message = "您好，$uname：\r\n<br/>";  
        $message .= "请访问下面的链接来重新设置密码:\r\n<br/>";  
        $message .= "-----------------------\r\n";  
        $message .= "<a href=\"$passwordLink\">$passwordLink</a>\r\n<br/>";  
        $message .= "-----------------------\r\n<br/>";  
        $message .= "该链接将在3天后失效。此邮件由系统生成，请不要回复这封邮件。\r\n\r\n<br/>";  
        $message .= "如果您没有申请找回密码，请不要访问上面的链接\r\n\r\n<br/>";  
        $message .= "谢谢,\r\n<br/>";  
        $message .= "-- 公益地图维护团队";  
        $headers = "From: 公益地图 <no-reply@ngo20.org> \n";  
        $headers .= "To-Sender: \n";  
        $headers .= "X-Mailer: PHP\n"; // mailer  
        $headers .= "Reply-To: no-reply@ngo20.org\n"; // Reply address  
        $headers .= "Return-Path: no-reply@ngo20.org\n"; //Return Path for errors  
        $headers .= "Content-Type: text/html; charset=utf-8"; //Enc-type  
		if(!mail($to, $subject, $message, $headers)){
			setflash('error','', L('无法发送找回密码邮件，请联系管理员'));
    		redirect($_SERVER['HTTP_REFERER']);
		}
		else{
			setflash('ok','', L('密码找回邮件已发送，请查看自己的电子邮箱。'));
    		redirect($_SERVER['HTTP_REFERER']);
		}
    }
    
    function reset_password(){
    	$forget_password_model = M('ForgetPassword');
    	$issue_count = $forget_password_model->where(array('link' => $_GET['key'], 'expire_date'=>array('gt', date('Y-m-d H:i:s'))))->count();
    	if($issue_count <= 0){
    		setflash('error','', L('找回密码链接不正确，请访问完整链接地址'));
    		$this->redirect('forget_password');
    	}
    	$this->display();
    }
    
    function reset_password_action(){
    	$user_model = M('Users');
    	$forget_password_model = M('ForgetPassword');
    	$issue = $forget_password_model->where(array('link' => $_POST['key'], 'expire_date'=>array('gt', date('Y-m-d H:i:s'))))->find();
    	$new_password = $_POST['password'];
    	$user_model->where(array('id'=>$issue['user_id']))->data(array('password'=>md5($new_password)))->save();
    	$forget_password_model->where(array('id'=>$issue['id']))->delete();
    	setflash('ok','', L('密码修改成功'));
    	$this->redirect('Index/index');
    }
}
?>