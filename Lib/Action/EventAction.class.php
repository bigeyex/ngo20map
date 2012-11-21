<?php

class EventAction extends Action{

    public function view(){
        $events = M("Events");
        $event_id = $_GET['id'];
        $get_event = array();
        $event_data = $events->where(array("id"=>$_GET['id']))->find();
        
        if(!$event_data['enabled']){
        	setflash('error','',L('对不起！您查看的事件不存在或者已删除！'));
        	return false;
        }
        
        if(!$event_data['is_checked']){
            setflash('notice','',L('该事件尚未通过审核，只有本组织能查看。'));
            if($event_data['user_id'] != $_SESSION['login_user']['id'] && !$_SESSION['login_user']['is_admin']){
                setflash('error','',L('该事件尚未通过审核，只有本组织能查看。'));
                $this->redirect('Index/index');
            }
        }
        
        if(isset($_SESSION['login_user']) && ($_SESSION['login_user']['is_admin'] || $event_data['user_id'] == $_SESSION['login_user']['id'])){
        	$is_editable = true;
        }
        else{
        	$is_editable = false;
        }
        
        //收藏状态
        if(isset($_SESSION['login_user']) && $event_data['user_id'] != $_SESSION['login_user']['id']){
        	$my_id = $_SESSION['login_user']['id'];
        	$follow_model = M('Follow');
        	$follow_status = $follow_model->where(array('from' => $my_id, 'to' => $_GET['id'], 'type'=>'event'))->count();
        	$this->assign('follow_status', $follow_status);
        }
        
        $event_data['host'] = $this->build_view_string($event_data['host']);
        
        //处理标签的显示
        $db_model = new Model();
        $tags = $db_model->query("select name from tags where id in (select tag_id from tagmap where event_id=$event_id)");
        $this->assign('tags', $tags);

        $users = M('Users');
        $puser = array();
        $user_id = $event_data['user_id'];
        $puser = $users->find($user_id);
        $event_labels = array();
        $event_labels[]=split(" ",$event_data['label']);

        $related_link_model = M('RelatedLinks');
        $related_link = $related_link_model->where(array("event_id"=>$event_id))->select();
        
        $media_model = M('Media');
        $related_image = $media_model->where(array("event_id"=>$event_id,"type"=>'image'))->select();
        $related_video = $media_model->where(array("event_id"=>$event_id,"type"=>'video'))->select();

		//获取标题图片的url地址
		$model = new Model();
		$cover_images = $model->query("select * from media where type='image' and event_id=$event_id order by url2 desc limit 1");
		$cover_image_url = 'Img/default-event-image.jpg';
		if(count($cover_images) > 0){
			$cover_image_url = 'Uploadedthumb/thumbm_' . $cover_images[0]['url'];
			
		}
		
		//处理事件的url地址
		if(!preg_match('/^https?:/', $event_data['url'])){
			$event_data['url'] = 'http://' . $event_data['url'];
		}
        
        $listRows = C('EVENTVIEW_DETAILLIST_NUM');
        $other_events = $events->where(array("user_id" =>$event_data['user_id'],'id' =>array('neq',$event_data['id'])))->order('edit_time desc')->limit($listRows)->select();
        $this->assign('puser',$puser);
        $this->assign('event_labels',$event_labels);
        $this->assign('event_data',$event_data);
        $this->assign('is_editable', $is_editable);
        $this->assign('related_link', $related_link);
        $this->assign('related_image', $related_image);
        $this->assign('related_video', $related_video);
        $this->assign('cover_image_url', $cover_image_url);
        $this->assign('other_events',$other_events);
        $this->display();
    }

   public function follow(){
    	$follow_model = M('Follow');
    	
    	$target_id = $_POST['id'];
    	$my_id = $_SESSION['login_user']['id'];
    	
    	$follow_record = $follow_model->where(array('from' => $my_id, 'to' => $target_id, 'type'=>'event'))->select();
    	if(count($follow_record) == 0){  // not followed yet, follow and return 1.
    		$follow_model->data(array('from' => $my_id, 'to' => $target_id, 'type'=>'event'))->add();
    		echo 1;
    	}else{	//already followed, cancel it and return 0.
    		$follow_model->where(array('from' => $my_id, 'to' => $target_id, 'type'=>'event'))->delete();
    		echo 0;
    	}
    }
    
    public function delete_follow(){
        $id=$_GET['id'];
        $follow_model = D('Follows');
        $save_result=$follow_model->where(array('event_id'=>$id,"user_id"=>$_SESSION['login_user']['id']))->delete();
        if($save_result){
                setflash('ok','',L('取消成功。'));
                $this->redirect('Event/view/id/'.$id);
            }
        else{
            setflash('error','',L('取消失败。'));
            $this->redirect('Event/view/id/'.$id);
        }
    }
    public function add(){
        $get_type=$_GET['type'];
        
        //确定默认的事件类型
        $event_types = C('EVENT_TYPES');
        $user_type = $_SESSION['login_user']['type'];
        if(isset($_GET['type']) && $_SESSION['login_user']['is_admin']){
        	$event_type = $_GET['type'];
        }
        else{
        	$event_type = empty($user_type) ? 'ind' : $user_type ;
        }
        
        $event_type_label = $event_types[$event_type];	//事件类型的文字描述

        if($_SESSION['login_user']['is_admin']){
            $host = '';
        }
        else{
            $host = $_SESSION['login_user']['name'];
        }
        
        //获取主办方资料。
        $event = array(
        	'host' => $host
        );
        
        $this->assign('target_url', 'insert');
        $this->assign('create_resource_type',$get_type);	//这里处理从url制定创建的是资源。新版本中已不用此功能
        $this->assign('event_type_label', $event_type_label);
        $this->assign('event_type', $event_type);
        $this->assign('field_title', $this->build_field_title($event_type));
        $this->assign('event',$event);
        $this->display();
    }

    public function insert(){
        $event_model = D('Events');
        $user_model = D('Users');
        $create_result = $event_model->create();

        if(!$create_result){
            $this->assign('event', $_POST);
            $this->assign('target_url', 'insert');
            setflash('error','',L('所填信息不完整'));
            $this->display('add');
            return;
        }

        $event_model->item_field = implode(' ',$_POST['event_field']);
        $event_model->res_tags = implode(' ',$_POST['event_type']);
        if($_POST['begin_time']){
            $event_model->begin_time = $_POST['begin_time'];
        }
        else $event_model->begin_time = '';
        if($_POST['end_time']){
            $event_model->end_time = $_POST['end_time'];
        }
        else $event_model->end_time = '';//开始结束时为空判断
        
        
        //获取创建人信息
        if($_POST['creator']){
        	$user = $user_model->where(array('name' => $_POST['creator']))->find();
        	if(!$user){
	        	$this->assign('event', $_POST);
	            $this->assign('target_url', 'insert');
	            setflash('error','',L('填写的创建人无效'));
	            $this->display('add');
	            return;
        	}
        	$event_model->user_id = $user['name'];
        }
        else{
            $event_model->user_id = $_SESSION['login_user']['id'];
        }

        $event_model->host = $this->build_db_string($_POST['host']);
        $event_model->create_time = date('Y-m-d H:i:s');
        $event_model->edit_time = date('Y-m-d H:i:s');
        if($_SESSION['login_user']['is_admin'] || $_SESSION['login_user']['is_vip'] ){
        	$event_model->is_checked = 1;
        }
        else{
        	$event_model->is_checked = 0;
        }

        if(!check_model()){
            setflash('error','',L('您提交的内容中可能有不合适的地方，请重新编辑'));
            $this->assign('target_url', 'insert');
            $this->display('add');
            return;
        }
        $this_id = $event_model->add();
        
        //处理标签
        $tag_model = M('Tags');
        $tagmap_model = M('tagmap');
        $tag_names = $this->build_csv_item_array($_POST['tags']);
        foreach($tag_names as $tag){
        	$existing_tag = $tag_model->where(array('name'=>$tag))->find();
        	if(!$existing_tag){
        		$tag_id = $tag_model->add(array('name' => $tag));
        	}
        	else{
        		$tag_id = $existing_tag['id'];
        	}
        	$tagmap_model->add(array('tag_id'=>$tag_id, 'event_id'=>$this_id));
        }
       
        setflash('ok','','事件已成功添加');
        
        $this->redirect('Event/view/id/'. $this_id);
    }

    public function save(){
        $event_model = D('Events');
        $create_result = $event_model->create();
        $this_id = $_POST['id'];

        //权限检查
        if(!$_SESSION['login_user']['is_admin']){
            $current_user_count = $event_model->where(array('id'=>$_POST['id'],
                'user_id'=>$_SESSION['login_user']['id']));
            if($current_user_count == 0){//说明这个事件不是当前用户建的
                $this->assign('event', $_POST);
                $this->assign('target_url', 'save');
                setflash('error','',L('权限不足，无法编辑事件'));
                $this->display('add');
                return;
            }
        }
        if(!$create_result){
            $this->assign('event', $_POST);
            $this->assign('target_url', 'save');
            setflash('error','',L('所填信息不完整'));
            $this->display('new');
            return;
        }

        $event_model->item_field = implode(' ',$_POST['event_field']);
        $event_model->res_tags = implode(' ',$_POST['event_type']);
        if($_POST['begin_time']){
            $event_model->begin_time = $_POST['begin_time'];
        }
        else $event_model->begin_time = '';
        if($_POST['end_time']){
            $event_model->end_time = $_POST['end_time'];
        }
        else $event_model->end_time = '';//开始结束时为空判断
        
        $event_model->edit_time = date('Y-m-d');
        if(!$_SESSION['login_user']['is_admin'] && !$_SESSION['login_user']['is_vip'] ){
            $event_model->is_checked = 0;
        }
        if($_POST['creator'])
            $event_model->user_id = $_POST['creator'];

        //处理标签
        $tag_model = M('Tags');
        $tagmap_model = M('tagmap');
        $tag_names = $this->build_csv_item_array($_POST['tags']);
        $tagmap_model->where(array('event_id'=>$this_id))->delete();
        foreach($tag_names as $tag){
        	$existing_tag = $tag_model->where(array('name'=>$tag))->find();
        	if(!$existing_tag){
        		$tag_id = $tag_model->add(array('name' => $tag));
        	}
        	else{
        		$tag_id = $existing_tag['id'];
        	}
        	$tagmap_model->add(array('tag_id'=>$tag_id, 'event_id'=>$this_id));
        }

        if(!check_model()){
            setflash('error','',L('您提交的内容中可能有不合适的地方，请重新编辑'));
            $this->assign('event', $_POST);
            $this->assign('target_url', 'save');
            $this->display('add');
            return;
        }

        $event_model->save();
        
        setflash('ok','','事件已成功保存');

        $this->redirect('Event/view/id/'. $_POST['id']);
    }

    public function edit(){
        $events = D("Events");
        $user_model = M("Users");
        $event_types = C('EVENT_TYPES');
        $get_event = array();
        $edit_data = $events->relation(TRUE)->where(array("id"=>$_GET['id']))->find();
        //把时间的显示从mysql数据库格式变为年-月-日，以供日期选择器操作
        $edit_data['begin_time'] = date('Y-m-d',strtotime($edit_data['begin_time']));
        $edit_data['end_time'] = date('Y-m-d',strtotime($edit_data['end_time']));

		//处理主办人信息
		$edit_data['host'] = $this->build_view_string($edit_data['host']);

		//处理标签的显示
        $db_model = new Model();
        $event_id = $_GET['id'];
        $tags = $db_model->query("select name from tags where id in (select tag_id from tagmap where event_id=$event_id)");
        $tag_names = array();
        foreach($tags as $tag){
        	$tag_names[] = $tag['name']; 
        }
        $this->assign('tags', implode(',', $tag_names));
        
        //确定默认的事件类型
        $event_default_type = array(
        	'ngo' => false,
        	'csr' => false,
        	'fund' => false,
        );
        $event_default_type[$edit_data['type']] = true;
        
        //获取创建者信息
        $creator = $user_model->find($edit_data['user_id']);
        $creator_name = $creator['name'];

        $this->assign('tag_ids',$edit_data['tag_id']);
        $this->assign('related_links',$related_links);
        $this->assign('images',$images);
        $this->assign('videos',$videos);
        $this->assign('event',$edit_data);
        $this->assign('field_title', $this->build_field_title($edit_data['type']));
        $this->assign('event_type', $edit_data['type']);
        $this->assign('event_type_label', $event_types[$edit_data['type']]);
        $this->assign('creator_name',$creator_name);
        $this->assign('target_url', 'save');
        $this->assign('event_default_type', $event_default_type);
        $this->display('add');
    }

     public function suggest(){		//为自动完成提供数据
            if($_GET['type']=='tag'){
                $table = M('Tags');
                $map['name']=array('like','%'.$_GET['q'].'%');
            }else{
                $table = M('Users');
                $map['_string'] = "name like '%".$_GET['q']."%' or english_name like '%".$_GET['q']."%'";
            }

            $list = $table->where($map)->select();
            foreach($list as $item){
                    echo $item['name']."\n";
            }
    }
    
    public function add_link(){
    	$link_model = M('RelatedLinks');
    	$link_model->create();
    	if(!stripos($link_model->url,'http://')){
    		$link_model->url = 'http://' . $link_model->url;
    	}
    	$result = $link_model->add();
    	if($result){
    		echo $result;
    	}
    }
    
    public function delete_media(){
    	if($_GET['type'] == 'link'){
    		$media_model = M('RelatedLinks');
    	}
    	else{
    		$media_model = M('Media');
    	}
    	$event_model = M('Events');
    	
    	$media = $media_model->find($_GET['id']);
    	if(!$media){
    		echo 'no media';
    		return;
    	}
    	$event = $event_model->find($media['event_id']);
    	
    	//检查权限
    	if(!$_SESSION['login_user']['is_admin'] && $_SESSION['login_user']['id'] != $event['user_id']){
    		echo 'no auth';
    		return;
    	}
    	$media_model->where(array('id' => $_GET['id']))->delete();
        echo 'ok';
    	//$this->redirect('Event/view/id/'.$media['event_id']);
    }
    
    public function add_video(){
    	$media_model = M('Media');
    	$media_model->create();
    	$result = $media_model->add();
    	if($result){
    		echo $result;
    	}
    }

    public function upload_image(){
        import('ORG.Net.UploadFile');
        import('ORG.Util.Image');
        $upload = new UploadFile();
        $upload->maxSize = 3145728;
        $upload->savePath = './Public/Uploaded/';
        $upload->thumb = true;
        $upload->thumbPath = './Public/Uploadedthumb/';
        $upload->thumbPrefix="thumbs_,thumbm_,thumb_,thumbl_";
        $upload->allowExts=array('jpg','jpeg','png','gif');
        $upload->thumbMaxWidth = "50,150,200,600";
        $upload->thumbMaxHeight = "50,150,200,600";
        $upload->saveRule = 'uniqid';
        if($upload->upload()){
            $info = $upload->getUploadFileInfo();
            echo $info[0]["savename"];
            return;
        }
        else{
            echo $upload->getErrorMsg();
        }
    }

    public function ajax_upload(){
        import('@.Util.qqFileUploader');
        $allowedExtensions = array("jpeg", "png", "gif", "jpg");
        $sizeLimit = 10 * 1024 * 1024;

        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('./Public/Uploaded/');

        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
    }
    
    public function set_cover(){
    	$media_model = M('Media');
    	$event_model = M('Events');
    	$event_id = $_GET['event_id'];
    	$image_id = $_GET['image_id'];
    	
    	$event = $event_model->find($event_id);
    	if(!$_SESSION['login_user']['is_admin'] && $_SESSION['login_user']['id'] != $event['user_id']){
    		echo 'no auth';
    		return;
    	}
    	
    	$media_model->where(array('event_id' => $event_id, 'type' => 'image'))->
    		data(array('url2' => ''))->save();
    	$media_model->where(array('id' => $image_id))->
    		data(array('url2' => 'cover'))->save();
    	
    	setflash('ok','',L('成功设置封面图片'));
        $this->redirect('Event/view/id/'.$event_id);
    }
    
    public function deleteEvent(){
        $event_model = M('Events');
        
        //验证是否有删除事件的权限
        $event = $event_model->find($_GET['id']);
        if(!$_SESSION['login_user']['is_admin'] && $_SESSION['login_user']['id'] != $event['user_id']){
        	error(L('没有删除事件的权限'));
        }
        
        //通过设置enabled来删除事件
        $event['enabled'] = 0;
        $event_model->save($event);
       
       	$flash_string = L('事件已成功删除').' <a href="'.U('Event/recover').'/id/'.$event['id'].'">'.L('撤销删除').'</a>';
        setflash('ok','',$flash_string);
        $this->redirect('User/home/id/'. $_SESSION['login_user']['id']);
    }
    
    //恢复已删除的事件
    public function recover(){
        $event_model = M('Events');
        
        //验证是否有恢复事件的权限
        $event = $event_model->find($_GET['id']);
        if(!$_SESSION['login_user']['is_admin'] && $_SESSION['login_user']['id'] != $event['user_id']){
        	error(L('没有恢复事件的权限'));
        }
        
        //通过设置enabled来删除事件
        $event['enabled'] = 1;
        $event_model->save($event);
       
       	$flash_string = L('删除操作已撤销');
        setflash('ok','',$flash_string);
        $this->redirect('Event/view/id/'. $event['id']);
    }
    
    //将以逗号分割的形式转换成数组
    private function build_csv_item_array($string){
    	$string= str_replace('，', ',', $string);	//替换掉中文逗号
    	$string= str_replace(', ', ',', $string);	//替换掉空格-都好
    	$string= str_replace(' ', ',', $string);	//替换掉空格
    	$item_array = explode(',', $string);
    	$output_array = array();
    	foreach($item_array as $item){
    		$item = trim($item);
    		if(!empty($item)){
    			$output_array[] = $item;
    		}
    	}
    	return $output_array;
    }
    
    //将输入的主办方、标签等字符串进行处理，形成数据库易于查询的，两边都有逗号的形式。
    private function build_db_string($string){
    	return implode(',',$this->build_csv_item_array($string));
    }
    
    //将主办方等数据库中的字符串转为易于显示的形式。
    private function build_view_string($string){
    	$item_array = explode(',', $string);
    	$output_array = array();
    	foreach($item_array as $item){
    		if(!empty($item)){
    			$output_array[] = $item;
    		}
    	}
    	return implode(', ',$output_array);
    }
    
    //根据不同的用户类型决定一些表单标题
    private function build_field_title($type){
        $field_title = array(
        	'event_name' => L('事件名称'),
        	'introduction' => L('简介'),
        	'place' => L('具体地点'),
        );
        if($type != 'ind'){
        	$field_title['event_name'] = L('项目名称');
        	$field_title['introduction'] = L('项目简介');
        	$field_title['place'] = L('执行区域');
        }
        return $field_title;
    }
}
?>
