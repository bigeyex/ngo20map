<?php
class ReviewViewWidget extends EduWidget{

    public function render($data){
		C("VAR_PAGE", "cp");
		$review_model = D('Reviews');
		import("ORG.Util.Page"); 
		if(isset($_SESSION['login_user'])){
			$my_id = $_SESSION['login_user']['id'];
		}
		else{
			$my_id = 0;
		}
		$is_admin = $_SESSION['login_user']['is_admin'];
		$show_post_button = true;
		if(isset($data['event_id'])){	//读取的是事件评论
			$review_model->where(array('event_id'=>$data['event_id'], 'is_checked'=>1));
			$event_id = $data['event_id'];
			$event_model = M('Events');
			$related_event = $event_model->find($event_id);
			$owner_id = $related_event['user_id'];
		}
		else if(isset($data['user_id'])){
			$review_model->where(array('owner_id'=>$data['user_id'], 'event_id'=>0, 'is_checked'=>1));
			$event_id = 0;
			$owner_id = $data['user_id'];
		}
		else{	//用户自己的管理界面
			$review_model->where(array('owner_id'=>$my_id))->order('id desc');
			$show_post_button = false;
			$event_id = 0;
			$owner_id = $my_id;
		}	
		
		import("ORG.Util.Page"); 
		$page = new Page($count, C('REVIEWS_PER_PAGE'));
		$page_show = $page->show();
		$review_model->limit($page->firstRow.','.$page->listRows);
		$reviews = $review_model->relation(true)->select();
		
        $tpl_data = array('reviews'=>$reviews, 'my_id'=>$my_id, 'show_post_button'=>$show_post_button, 'floor'=>$page->firstRow+1, 'event_id'=>$event_id, 'owner_id'=>$owner_id, 'is_admin'=>$is_admin);
        $content = $this->renderFile('ReviewView',$tpl_data);
        return $content;
    }

}
?>
