<?php

class ManageAction extends Action{
    
    public function events(){
        $event_model = M('Events');
        $user_id = $_SESSION['login_user']['id'];

        //build archives
        $sql = 'select distinct year(create_time) yr from events where create_time is not null and user_id='.$user_id;
        $archive_list = $event_model->query($sql);

        $display_mode = $_GET['show'];
        $sql = ' from events where user_id='.$user_id;
        if ($display_mode == 'published'){
            // what does published mean?
            $sql .= ' and enabled=1';
        }
        else if ($display_mode == 'draft'){
            $sql .= ' and enabled=2';
        }
        else if ($display_mode == 'deleted'){
            $sql .= ' and enabled=0';
        }
        else if (is_numeric($display_mode)){    //archive mode
            $sql .= ' and enabled=0 and year(create_time)='.$display_mode;
        }
        else{
            $sql .= ' and enabled=1';
            $display_mode = 'all';
        }

        $result_count = $event_model->query('select count(*) cnt' . $sql);
        $result_count = $result_count[0]['cnt'];

        import("ORG.Util.Page");
        $rows_per_page = C('SEARCH_RESULT_PER_PAGE');
        $pager = new Page($result_count, $rows_per_page);
        //page config
        $pager->setConfig('prev', '<');
        $pager->setConfig('next', '>');
        $pager->setConfig('theme', '%upPage%%linkPage%%downPage%');
        $pager_content = $pager->show();

        $user_events = $event_model->query('select *' . $sql . ' order by create_time desc limit '.$pager->firstRow.','.$pager->listRows);
        $media_model = M('Media');
        for($i=0;$i<count($user_events);$i++){
            $media = $media_model->where(array('event_id'=>$user_events[$i]['id']))->find();
            $user_events[$i]['event_image'] = $media['url'];
            $user_events[$i]['description'] = strip_tags($user_events[$i]['description']);
        }
        
        $this->assign('display_mode',$display_mode);
        $this->assign('result_count',$result_count);
        $this->assign('user_events',$user_events);
        $this->assign('pager_content',$pager_content);
        $this->assign('archive_list',$archive_list);
        $this->display();
    }

    public function move_events(){
        if(isset($_GET['id'])){
            $ids = array($_GET['id']);
            $action = $_GET['action'];
        }
        else{
            $ids = $_POST['ids'];
            $action = $_POST['action'];
        }

        $event_model = M('Events');
        $event_model->where(array('id'=>array('in', $ids)));
        switch($action){
            case 'delete':
                $event_model->save(array('enabled'=>0));
            case 'publish':
                $event_model->save(array('enabled'=>1));
            case 'draft':
                $event_model->save(array('enabled'=>2));
            case 'destroy':
                $event_model->delete();
        }
        $this->redirect('events');
    }

}
?>
