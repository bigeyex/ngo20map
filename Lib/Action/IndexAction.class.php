<?php
define('OFFSET', 268435456);
define('RADIUS', 85445659.4471); /* $offset / pi() */

class IndexAction extends Action{
    
    public function index(){
        $model = new Model();
        $recommend_ngo = $model->query(
        //            "select * from users where type='ngo' and image is not null order by rand() limit 1");
            "select * from users, media where users.type='ngo' and media.type='image' and exists (select id from events where user_id=users.id and events.id=media.event_id) order by rand() limit 1");
        $recommend_csr = $model->query(
        //            "select * from users where type='csr' and image is not null order by rand() limit 1");
            "select * from users, media where users.type='csr' and media.type='image' and exists (select id from events where user_id=users.id and events.id=media.event_id) order by rand() limit 1");
        $recent_events = $model->query("select id,name from events where is_checked=1 order by create_time desc limit 5");

        $this->assign('recommend_ngo', $recommend_ngo[0]);
        $this->assign('recommend_csr', $recommend_csr[0]);
        $this->assign('recent_events', $recent_events);
        $this->display();
        
    }

    public function map(){
        $model = new Model();
        $type = $_GET['type'];
        if($type == 'exngo'){
            $recommend_org = $model->query(
            "select * from users, media where users.type='ngo' and media.type='image' and exists (select id from events where user_id=users.id and events.id=media.event_id) order by rand() limit 2");
            for($i=0;$i<2;$i++){
                $user[$i] = array(
                    'name' => $recommend_org[$i]['name'], 
                    'href' => U('User/home').'/id/'.$recommend_org[$i]['id'],
                    'image' => $recommend_org[$i]['url'], 
                    'more' => U('Index/map').'/type/exngo',
                    'more_label' => L('更多公益组织>>'),
                    );
            }
            $recommend_event = $model->query(
            "select events.id id,events.name name,(select url from media where media.event_id=events.id and type='image' limit 1) url from events where events.type='ngo' and exists (select * from media where media.event_id=`events`.id and type='image') order by rand() limit 2");
            for($i=0;$i<2;$i++){
                $event[$i] = array(
                    'name' => $recommend_event[$i]['name'], 
                    'image' => $recommend_event[$i]['url'], 
                    'href' => U('Event/view').'/id/'.$recommend_event[$i]['id'],
                    );
            }
        }
        else if($type == 'excsr'){
            $recommend_org = $model->query(
            "select * from users where users.type='csr' order by rand() limit 2");
            for($i=0;$i<2;$i++){
                $user[$i] = array(
                    'name' => $recommend_org[$i]['name'], 
                    'href' => U('User/home').'/id/'.$recommend_org[$i]['id'],
                    'image' => $recommend_org[$i]['image'], 
                    'more' => U('Index/map').'/type/excsr',
                    'more_label' => L('更多社会责任企业'),
                    );
            }
            $recommend_event = $model->query(
            "select events.id id,events.name name,(select url from media where media.event_id=events.id and type='image' limit 1) url from events where (events.type='csr' or events.type='ind') and exists (select * from media where media.event_id=`events`.id and type='image') order by rand() limit 2");
            for($i=0;$i<2;$i++){
                $event[$i] = array(
                    'name' => $recommend_event[$i]['name'], 
                    'image' => $recommend_event[$i]['url'], 
                    'href' => U('Event/view').'/id/'.$recommend_event[$i]['id'],
                    );
            }
        }
        else if($type == 'case'){
            $recommend_event = $model->query(
            "select *,(select url from media where media.event_id=events.id and type='image') url from events where (events.type='case') limit 4");
            for($i=0;$i<2;$i++){
                $user[$i] = array(
                    'name' => $recommend_event[$i]['name'], 
                    'href' => U('Event/view').'/id/'.$recommend_event[$i]['id'],
                    'image' => $recommend_event[$i]['url'], 
                    'more' => U('Index/map').'/type/case',
                    'more_label' => L('更多对接案例'),
                    );
                $event[$i] = array(
                    'name' => $recommend_event[$i+2]['name'], 
                    'image' => $recommend_event[$i+2]['url'], 
                    'href' => U('Event/view').'/id/'.$recommend_event[$i+2]['id'],
                    );
            }

        }

        $this->assign('user_1',$user[0]);
        $this->assign('user_2',$user[1]);
        $this->assign('event_1',$event[0]);
        $this->assign('event_2',$event[1]);

        $this->display();
    }

    public function login(){
        $this->display();
    }

    public function login_action(){
        import('@.Util.Authority');
        if(Authority::authorize($_POST['email'], $_POST['password'])){
                $user_model = M('Users');
                $user_id = $_SESSION['login_user']['id'];
                $user_model->where(array('id'=>$_SESSION['login_user']['id']))->data(array('last_login'=>date('Y-m-d h:i:s')))->save();
                $user_model->query("update users set login_count=login_count+1 where id=$user_id");
                $_SESSION['login_user']['last_login']=date('Y-m-d h:i:s');
                //处理自动登录
                if(!empty($_POST['remember'])){
                	$login_email = $_SESSION['login_user']['email'];
                	$login_key = md5($login_email . rand(0,10000) .time() . SALT_KEY);
                	$login_token = md5($login_key . SALT_KEY . $_SESSION['login_user']['password']);
                	setcookie("ngo20_login_email", $login_email, time()+3600*24*14);
                	setcookie("ngo20_login_key", $login_key, time()+3600*24*14);
                	setcookie("ngo20_login_token", $login_token, time()+3600*24*14);
                }
                
                //根据不同用户类型决定发布的东西叫什么
                switch($_SESSION['login_user']['type']){
                	case 'ngo':
                		$_SESSION['login_user']['event_label'] = L('公益项目');
                		break;
                	case 'csr':
                		$_SESSION['login_user']['event_label'] = L('企业社会责任项目');
                		break;
                	case 'ind':
                		$_SESSION['login_user']['event_label'] = L('对接案例');
                		break;
                }

                if(isset($_SESSION['last_page'])){
                    redirect($_SESSION['last_page']);
                }
                else{
                    $this->redirect('User/home/');
                }
        }
        else{
                setflash('error','',L('用户名或密码不正确'));
                $this->display('Index:index');
        }
    }
    public function logout(){
        unset($_SESSION['login_user']);
        unset($_SESSION['last_page']);
        unset($_SESSION['last_page']);
        unset($_SESSION['api']);
        setcookie("ngo20_login_email", "", time()-3600);
        setcookie("ngo20_login_key", "", time()-3600);
        setcookie("ngo20_login_token", "", time()-3600);
        $this->redirect('Index/index');
    }

    public function ajax_detail(){
        $user_model = M('Users');
        $event_model = M('Events');
        $media_model = M('Media');

        $id = $_GET['id'];
        $type = $_GET['type'];

        if($type == 'users'){
            $user = $user_model->find($id);
            $user_events = $event_model->where(array('user_id'=>$id))->order('create_time desc')->select();
            //get image for each event
            for($i=0;$i<count($user_events);$i++){
                $event_id = $user_events[$i]['id'];
                $image = $media_model->where(
                    array(
                        'event_id' => $event_id,
                        'type' => 'image',
                        )
                    )->find();
                if($image){
                    $user_events[$i]['image'] = $image['url'];
                }
            }
            $this->assign('user',$user);
            $this->assign('user_events',$user_events);
        }
        else{
            $event = $event_model->find($id);
            $this->assign('event',$event);
        }
        $this->assign('type',$type);

        $this->display();
    }

    //生成验证码
    public function verify_code(){
    	import("ORG.Util.Image");
    	Image::buildImageVerify(6, 3);
    }
    
    public function subscribe(){
        $this->display();
    }

    public function tile(){
        $scalex = array(0,301.421310,150.710655,75.355327,37.677664,18.838832,9.419416,4.709708,2.354854,1.177427,0.588714,0.294357,0.147179,0.07359,0.036795,0.018397,0.009199,0.0046,0.0023,0.001149);
        $scaley = array(0,138.558225,88.011798,50.105148,26.953469,13.990668,7.125178,3.594854,1.805441,0.904715,0.452855,0.226552,0.113307,0.056661,0.028332,0.014166,0.007084,0.003542,0.001771,0.000885);
        $zoom = $_GET['zoom'];
        $tilex = $_GET['x'];
        $tiley = $_GET['y'];
        $icon_size = 8;
        $model = new Model();

        //caculate range
        $minlon = (256 * $tilex - $icon_size) / pow(2, $zoom-18);
        $maxlon = (256 * ($tilex+1) + $icon_size) / pow(2, $zoom-18);
        $minlat = (256 * $tiley - $icon_size) / pow(2, $zoom-18);
        $maxlat = (256 * ($tiley+1) + $icon_size) / pow(2, $zoom-18);
        $minrange = convertMC2LL($minlon, $minlat);
        $maxrange = convertMC2LL($maxlon, $maxlat);
        $minlon = $minrange[0];$minlat = $minrange[1];
        $maxlon = $maxrange[0];$maxlat = $maxrange[1];

        $cond = "longitude>$minlon and longitude<$maxlon and latitude>$minlat and latitude<$maxlat";
        $user_fields="id, longitude, latitude, type, 'users' model, create_time";
        $event_fields="id, longitude, latitude, type, 'events' model, create_time";
        if(!empty($_GET['model'])){
            if($_GET['model'] == 'users'){
                $event_fields = '';
            }
            else if($_GET['model'] == 'events'){
                $user_fields = '';
            }
        }
        $sql = $this->query_map(array(
            'user_fields' => $user_fields,
            'event_fields' => $event_fields,
            'type' => $_GET['type'],
            'field' => $_GET['field'],
            'progress' => $_GET['progress'],
            'res_tags' => $_GET['res_tags'],
            'where' => $cond,
            'order' => "type='case',type='ngo',  create_time asc",
            ));
        
        $data = $model->query($sql);
        header("content-type:image/png");  
        $img=imagecreatetruecolor(256,256);  
        //$bgcolor=ImageColorAllocate($img,0,0,0);  
        //$bgcolor = imagecolorallocatealpha($Img, 0, 0, 255, 10);
        $red=ImageColorAllocate($img,255,0,0);  
        //$bgcolortrans=ImageColorTransparent($img,$bgcolor); 
        $transparent = imagecolorallocatealpha( $img, 0, 0, 0, 127 ); 
        imagefill( $img, 0, 0, $transparent ); 
        imagealphablending($img, true); 

        $base_path = dirname(__FILE__)."/../../";

        $greenCircle = imagecreatefrompng($base_path."Public/Img/icons/green-circle.png");
        imagealphablending($greenCircle, true); 
        $yellowCircle = imagecreatefrompng($base_path."Public/Img/icons/yellow-circle.png");
        imagealphablending($yellowCircle, true); 
        $redCircle = imagecreatefrompng($base_path."Public/Img/icons/red-circle.png");
        imagealphablending($redCircle, true); 
        foreach($data as $d){
            $mc = convertLL2MC($d['longitude'], $d['latitude']);
            $x = abs($mc[0] * pow(2, $zoom-18)) - (256*($tilex));
            $y = abs($mc[1] * pow(2, $zoom-18)) - (256*($tiley));
            switch ($d['type']) {
                case 'ngo':
                    imagecopy($img, $greenCircle, $x-5, 256-$y-5, 0, 0, 10, 10);
                    break;
                case 'csr':
                case 'ind':
                    imagecopy($img, $yellowCircle, $x-5, 256-$y-5, 0, 0, 10, 10);
                    break;
                case 'case':
                    imagecopy($img, $redCircle, $x-5, 256-$y-5, 0, 0, 10, 10);
                    break;
            }
            
        }
        imagesavealpha($img,true); 
        if(empty($_GET['key'])){
        //Imagepng($img, "Runtime/Cache/tile-$zoom-$tilex-$tiley.png");
        }
        Imagepng($img);  
        ImageDestroy($img);  
    }

    public function viewport_detail(){
        $model = new Model();

        //$cond = 'longitude>'.$_GET['minlon'].' and longitude<'.$_GET['maxlon'].' and latitude>'.$_GET['minlat'].' and latitude<'.$_GET['maxlat'];
        $user_fields="id, name, longitude, latitude, type, 'users' model, 0 begin_time, 0 end_time, 0 res_tags, create_time, work_field field";
        $event_fields="id, name, longitude, latitude, type, 'events' model, begin_time, end_time, res_tags, create_time, item_field field";
        if(!empty($_GET['model'])){
            if($_GET['model'] == 'users'){
                $event_fields = '';
            }
            else if($_GET['model'] == 'events'){
                $user_fields = '';
            }
        }
        $sql = $this->query_map(array(
            'user_fields' => $user_fields,
            'event_fields' => $event_fields,
            'type' => $_GET['type'],
            'field' => $_GET['field'],
            'progress' => $_GET['progress'],
            'res_tags' => $_GET['res_tags'],
            //'where' => $cond,
            'order' => 'create_time desc',
            ));
        
        $data = $model->query($sql);
        $this->ajaxReturn($data,1,1);
    }

    function ajax_hotspots(){
        $model = new Model();
        $sql = "(select id, name, longitude, latitude, type, 'users' model, create_time from users where type != 'ind' and is_checked=1) union (select id, name, longitude, latitude, type, 'events' model, create_time from events where is_checked=1) order by type!='case',type!='ngo',create_time desc";
        $this->ajaxReturn($model->query($sql),1,1);

    }

    private function query_map($param){
    //生成地图查询语句。
    //return (string)sql
    //param 是地图参数，有model(用户还是事件), type(标注点类型), progress(进度限制), res_tags(资源标签), field(领域)
    //user_fields & event_fields eg. "id, name"
    //order eg. "id desc"
    //本部分不负责分页部分
    // auto is_checked = 1 and enabled = 1
        $users_fields = $events_fields = "*";

        $users_where = array();
        $events_where = array();

        if(!empty($param['where'])){
            $users_where[] = $events_where[] = $param['where'];
        }

        $users_where[] = "type != 'ind'";
        if(!empty($param['type'])){
            if($param['type'] == 'excsr'){
                //special case #1: ind events belongs to csr
                $users_where[] = "type='csr'";
                $events_where[] = "(type='csr' or type='ind')";
            }
            else if($param['type'] == 'csr'){
                $users_where[] = $events_where[] = "type='csr'";
            }
            else if($param['type'] == 'exngo'){
                //special case #2: fund belongs to extended ngo concept
                $users_where[] = $events_where[] = "(type='ngo' or type='fund')";
            }
            else{
                $users_where[] = $events_where[] = "type='".$param['type']."'";
            }
        }

        //领域限制
        if(!empty($param['field'])){
                $events_where[] = "item_field like '%". $param['field'] ."%'";
                $users_where[] = "work_field like '%". $param['field'] ."%'";
        }

        //资源标签限制
        if(!empty($param['res_tags'])){
            $events_where[] = "res_tags like '%". $param['res_tag'] ."%'";
        }
        if(!empty($param['res_tags2'])){
            $events_where[] = "res_tags like '%". $param['res_tag2'] ."%'";
        }

        if(!empty($param['key'])){
                $events_where[] = "(name like '%". $param['key'] ."%' or description like '%". $param['key'] ."%')";
                $users_where[] = "(name like '%". $param['key'] ."%' or introduction like '%". $param['key'] ."%')";
        }
        
        //进度限制
        if(!empty($param['progress'])){
            $today = date('Y-m-d');
            switch($param['progress']){
                case 'planning':
                    $events_where[] = "events.begin_time>'$today'";
                    break;
                case 'running':
                    $events_where[] = "events.begin_time<'$today' and events.end_time>'$today'";
                    break;
                case 'finished':
                    $events_where[] = "events.end_time<'$today'";
                    break;
                case 'delayed':
                    $events_where[] = "events.progress=1";
                    break;
                case 'failed':  //尚未考虑拖延一定时间自动失败
                    $events_where[] = "events.progress=2";
                    break;
                case 'daily':
                    $events_where[] = "events.progress=3";
                    break;
            }
        }
        
        $events_where[] = $users_where[] = 'is_checked=1';
        $events_where[] = $users_where[] = 'enabled=1';
        
        $order = "";
        if(!empty($param['order'])){
            $order = ' order by '.$param['order'];
        }

        $sql_list = array();
        if(!empty($param['user_fields'])){
            $sql_list[] = '(select '.$param['user_fields'].' from users where '.implode(" and ", $users_where).')';
        }
        if(!empty($param['event_fields'])){
            $sql_list[] = '(select '.$param['event_fields'].' from events where '.implode(" and ", $events_where).')';
        }
        $sql = implode(' union ', $sql_list) . $order;
        return $sql;
    }

}
?>