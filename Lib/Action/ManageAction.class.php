<?php

class ManageAction extends Action{
    
    public function reviews(){
        $reviews = M('Reviews');
        import("ORG.Util.Page");

            //定义每页显示数
            $listRows = C('SEARCHRESULT_LISTROWS');
            $review_count = $reviews->count();
            $Page = new Page($review_count,C('SEARCHRESULT_LISTROWS'));

            $review_result = $reviews->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $show2 = $Page->show();
        $this->assign('review_result',$review_result);
        $this->assign('review_count',$review_count);
        $this->assign('page2',$show2);
        $this->display();
    }

    public function change_type2(){
        $this->must_admin();

        $reviews = M('Reviews');

        if($_GET['type'])
            $data['is_checked']='0';
        else
            $data['is_checked']='1';
        $reviews->where(array('id'=>$_GET['id']))->save($data);
        setflash('ok','','您已成功修改所选感想状态');
        $this->redirect('reviews');
    }
    
    public function del_review(){
        $this->must_admin();
        $reviews = M('Reviews');
        
        $ids=split(",",$_GET['ids']);
        $reviews->where(array('id'=>array('in',$ids)))->delete();
        setflash('ok','','您已成功删除所选评论');
        $this->redirect('reviews');
    }
}
?>
