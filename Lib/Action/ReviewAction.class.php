<?php

class ReviewAction extends Action{

    public function ajax_view(){
        $review_model = D('Reviews');
        $review_data = $review_model->relation(true)->find($_GET['id']);
        $this->assign('review_data', $review_data);
        $this->display();
    }
    public function json_view(){
        $review_model = D('Reviews');
        $review_data = $review_model->find($_GET['id']);
        $this->ajaxReturn($review_data,'',1);
    }

    //this function is deprecated. see ajax_review_list()
    public function view(){
        $reviews = M('Reviews');
        $reviewrows=C('EVENTVIEW_DETAILLIST_NUM');
        $get_reviews = $reviews->where(array("event_id"=>$_GET['event_id']))->order('create_time desc')->limit($reviewrows)->select();
        $users = M('Users');
        $userdata = $users->find($get_reviews[0]['user_id']);
        $get_reviews[0]['username']=$userdata['name'];
        $this->assign('get_reviews',$get_reviews);
        $get_user = $_SESSION['login_user']['id'];
        $this->assign('get_user',$get_user);
        $this->display();
    }

    public function ajax_review_list(){
        import("ORG.Util.Page");
        $review_model = D('Reviews');
        $condition = array(
            'event_id' => $_GET['id'],
            'is_checked' => 1
        );
        $review_count = $review_model->where($condition)->count();
        $pager = new Page($review_count, C('EVENTVIEW_REVIEW_NUM'));
        $pager_content = $pager->show();
        $review_result = $review_model->where($condition)->order('id desc')->
                relation(true)->limit($pager->firstRow.','.$pager->listRows)->select();
        $this->assign('review_result', $review_result);
        $this->assign('pager_content', $pager_content);
        $this->display('Review:ajax_review_list');
    }

    public function insert(){
        $review_model = M('Reviews');
        $review_model->create();
        $review_model->user_id = $_SESSION['login_user']['id'];
        $review_model->event_id = $_POST['event_id'];
        $review_model->owner_id = $_POST['owner_id'];
        $review_model->title = $_POST['title'];
        $review_model->content = $_POST['content'];
        $review_model->is_checked = 1;
        $review_model->create_time = date('Y-m-d H:m:s');
        $review_model->add();
        setflash('ok',L('评论已发表'),L('评论已发表'));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function save(){
        $review_model = D('Reviews');
        $target_review = $review_model->relation(true)->find($_POST['id']);
        if(!($_SESSION['login_user']['is_admin'] ||
                $target_review['user_id'] != $_SESSION['login_user']['id'] ||
                    $target_review['event']['user_id'] != $_SESSION['login_user']['id'])){
            die('权限不足');
            return;
        }
        $review_model->create();
        $review_model->id = $_POST['id'];
        $review_model->title = $_POST['title'];
        $review_model->content = $_POST['content'];
        $review_model->is_checked = 1;
        $review_model->create_time = date('Y-m-d');
        $review_model->save();

    }

    public function delete(){
        $review_model = D('Reviews');
        $review_data = $review_model->find($_GET['id']);
        if(!$_SESSION['login_user']['is_admin'] &&
                $review_data['user_id'] != $_SESSION['login_user']['id'] &&
                    $review_data['owner_id'] != $_SESSION['login_user']['id']){
            die('权限不足');
            return;
        }
        $review_model->where(array('id'=>$_GET['id']))->delete();
        setflash('ok',L('评论已删除'),L('评论已删除'));
        redirect($_SERVER['HTTP_REFERER']);
    }
}
?>