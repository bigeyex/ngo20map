<?php
define('OFFSET', 268435456);
define('RADIUS', 85445659.4471); /* $offset / pi() */

class IndexAction extends Action{
    
    public function index(){
        $db_model = new Model();
        
        $random_users = $db_model->query("select * from users where is_checked=1 and type!='ind' order by rand() limit 0,3");
        $recent_events = $db_model->query("select * from events where is_checked=1 order by id desc limit 0,5");
        $hot_tags = $db_model->query("select count(event_id) score,name from tagmap,tags where tagmap.tag_id=tags.id group by tag_id order by score desc limit 0,20");
        $record_count = M('Events')->where("enabled=1 and is_checked=1")->count() + M('Users')->where("type != 'ind' and enabled=1 and is_checked=1")->count();

        //为绘制信息增加折线图准备数据
        $line_chart_data = array();
        for($i=12;$i>=0;$i--){
        	$label = date('Y-n',strtotime("-$i Month",time()));
        	$line_chart_data[$label] = 0;
        }
        $event_and_user_counts = $db_model->query("select YEAR(create_time) year, MONTH(create_time) month, count(*) count from (select create_time from events e1 union all select create_time from users u1 where type!='ind') t where t.create_time != 0 and t.create_time is not null group by MONTH(create_time) order by create_time desc");
        foreach($event_and_user_counts as $count){
        	$line_chart_data[$count['year'].'-'.$count['month']] = $count['count'];
        }
        
        $this->assign('line_chart_data', $line_chart_data);
        $this->assign('random_users', $random_users);
        $this->assign('recent_events', $recent_events);
        $this->assign('hot_tags', $hot_tags);
        $this->assign('record_count', $record_count);
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
                    $this->redirect('User/recommend/');
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

    public function ajax_cluster(){
        //获取传输参数
        $table = $_POST['model'];
        $zoom = $_POST['zoom'] ? $_POST['zoom'] : '5';
        $data = '';

        $model = new Model();
        $user_model = M('Users');
        

        $search_condition = $this->build_map_where_clause(array(
        	'model' => $_POST['model'],
        	'type' => $_POST['type'],
        	'progress' => $_POST['progress'],
        	'res_tag' => $_POST['res_tag'],
        	'res_tag2' => $_POST['res_tag2'],
        	'field' => $_POST['field'],
        	'key' => $_POST['key'],
        ));

    	$search_condition .= " and is_checked=1";
        if($table=='events'){
            $sql ="select events.id id,events.longitude longitude,events.latitude latitude,host,'events' model from events where enabled=1 and ".$search_condition;
        }else if($table == 'all'){
        	$user_filter_type = "";
        	$event_filter_type = "";
        	$item_type_filter = "";
        	if(!empty($_POST['field'])){
        		$event_filter_type = "and item_field like '%".$_POST['field']."%'";
        		$user_filter_type = "and work_field like '%".$_POST['field']."%'";
        	}
        	$user_filter_key = "";
        	$event_filter_key = "";
        	if(!empty($_POST['key'])){
        		$event_filter_key = "and (name like '%". $_POST['key'] ."%' or description like '%". $_POST['key'] ."%')";
        		$user_filter_key = "and (name like '%". $_POST['key'] ."%' or introduction like '%". $_POST['key'] ."%')";
        	}
        	if($_POST['type'] == 'ngo'){
        		$item_type_filter = "and type='ngo'";
        	}
        	else if($_POST['type'] == 'csr'){
        		$item_type_filter = "and (type='csr' or type='ind')";
        	}
        	$sql ="(select id,longitude,latitude,'events' model from events where enabled=1 $event_filter_type $event_filter_key $item_type_filter and is_checked=1) union (select id,longitude,latitude,'users' model from users where enabled=1 $user_filter_type $user_filter_key $item_type_filter and type!='ind' and is_checked=1)";
        }else{
            
            $sql ="select id,longitude,latitude,'users' model from users where enabled=1 and ".$search_condition;
        }

        $result = $model->query($sql);
        $this->ajaxReturn($this->cluster($result, 40, $zoom),'',1,'json');
    }

    public function ajax_detail(){
        import("ORG.Util.Page");
        $page = $_POST['page'];
        $_GET['p'] = $page;	//这里是为了使thinkphp的分页器发挥作用。
        //定义每页显示数目
        $listRows = C('SEARCH_DETAILLIST_NUM');
        $page_start = $listRows*($page-1);

        $model = new Model();
        $user_model = M('Users');
        $order = " order by ".$_POST['model'].".id desc limit $page_start,$listRows";
        

        $condition = $this->build_map_where_clause(array(
        	'model' => $_POST['model'],
        	'type' => $_POST['type'],
        	'progress' => $_POST['progress'],
        	'res_tag' => $_POST['res_tag'],
        	'res_tag2' => $_POST['res_tag2'],
        	'field' => $_POST['field'],
        	'key' => $_POST['key'],
        ));
        
        $model_type = $_POST['model'];
        //地图上圆点代表的经纬度范围
        if(!empty($_POST['cluster_id'])){	//如果有一个为0的话就取全部信息，而非特定区域。
	        $condition .= " and $model_type.id in (".$_SESSION['cluster_ids'][$_POST['cluster_id']][$model_type].")";
		}
		
		//下面排除了唯一不需要已审核的情况：用户查看自己的事件
        if(!isset($_SESSION['login_user']) || !isset($owner_id) || $_POST['type'] == 'followed' || $owner_id != $_SESSION['login_user']['id']){
        	$condition .= " and $model_type.is_checked=1";
        }
        $condition .= " and $model_type.enabled=1";
		
        if($model_type=='events'){
            $sql = "select events.id event_id,events.name event_name,events.place place,item_field,progress,events.begin_time begin_time, events.end_time end_time,host,user_id,longitude,latitude,'events' model from events where ".$condition.$order;
        }else if($model_type=='all'){
	        $user_filter_type = "";
        	$event_filter_type = "";
        	$location_filter = "";
        	$item_type_filter="";
        	$user_filter_key = "";
        	$event_filter_key = "";
        	if(!empty($_POST['field'])){
        		$event_filter_type = "and item_field like '%".$_POST['field']."%'";
        		$user_filter_type = "and work_field like '%".$_POST['field']."%'";
        	}
        	if(!empty($_POST['cluster_id'])){	//retrive id list from session by cluster_id
		        $user_location_filter .= " and id in (".$_SESSION['cluster_ids'][$_POST['cluster_id']]['users'].")";
                $event_location_filter .= " and id in (".$_SESSION['cluster_ids'][$_POST['cluster_id']]['events'].")";
			}
        	if($_POST['type'] == 'ngo'){
        		$item_type_filter = "and (type='ngo' or type='fund')";
        	}
        	else if($_POST['type'] == 'csr'){
        		$item_type_filter = "and (type='csr' or type='ind')";
        	}
        	if(!empty($_POST['key'])){
        		$event_filter_key = "and (name like '%". $_POST['key'] ."%' or description like '%". $_POST['key'] ."%')";
        		$user_filter_key = "and (name like '%". $_POST['key'] ."%' or introduction like '%". $_POST['key'] ."%')";
        	}
        	$sql = "(select id,name,create_time,'users' model,longitude,latitude from users where enabled=1 and is_checked=1 $user_filter_type $user_location_filter $user_filter_key $item_type_filter and type!='ind') union (select id,name,create_time,'events' model,longitude,latitude from events where enabled=1 and is_checked=1 $event_filter_type $event_location_filter $event_filter_key $item_type_filter) order by create_time desc limit $page_start,$listRows";
        }else{
            $sql = "select id,name,work_field,place,longitude,latitude,'users' model from users where ".$condition.$order;
        }
        //首先查询结果的数量
        if($model_type=='all'){
        	$result_count_array = $model->query("select (select count(*) from users where enabled=1 and is_checked=1 $user_filter_type $user_location_filter $user_filter_key $item_type_filter and type!='ind') + (select count(*) from events where enabled=1 and is_checked=1 $event_filter_type $event_location_filter $event_filter_key $item_type_filter) result_count");
        }
        else{
        	$result_count_array = $model->query("select count(*) result_count from $model_type where ".$condition);
        }
        $result_count = $result_count_array[0]['result_count'];
        if($result_count > 0){
	        $result = $model->query($sql);
	        
	        //如果是事件的话，要产生一些额外属性，如进度
	        $is_admin = $_SESSION['login_user']['is_admin'];
	        $current_user_id = $_SESSION['login_user']['id'];
	        if($model_type=='events'){
	        	$today = date();
	        	for($i=0;$i<count($result);$i++){
	        		//计算事件进度
	        		if(!empty($result[$i]['begin_time']) && $result[$i]['begin_time'] > $today){
	        			$result[$i]['progress_text'] = '筹备中';
	        		}
	        		else if(!empty($result[$i]['begin_time']) && !empty($result[$i]['end_time']) 
	        				&& $result[$i]['begin_time'] < $today && $result[$i]['end_time'] > $today){
	        			$result[$i]['progress_text'] = '进行中';
	        		}
	        		else{
	        			$result[$i]['progress_text'] = '已完成';
	        		}
	        		
	        		//计算是否有编辑权限
	        		if(!empty($current_user_id) && ($is_admin || $result[$i]['user_id']==$current_user_id)){
	        			$result[$i]['editable'] = true;
	        		}
	        		else{
	        			$result[$i]['editable'] = false;
	        		}
	        	}
	        }
	
	        $Page = new Page($result_count,$listRows);
	        $show = $Page->show();
        }// end if result count > 0
        else{	// no result
        	$show = '';
        	$result = array();
        }
        $this->assign('page',$show);
        $this->assign('type',$_POST['type']);
        $this->assign('result',$result);
        $this->display();
    }
    
    //地图部分的组件
    public function _map(){
    	$this->display('Index:_map');
    }
    
    //生成验证码
    public function verify_code(){
    	import("ORG.Util.Image");
    	Image::buildImageVerify(6, 3);
    }
    
    private function build_map_where_clause($param){
    	//param 是地图参数，有model(用户还是事件), type(标注点类型), progress(进度限制), res_tag(资源标签), field(领域)
    	//本部分不负责分页部分
    	$sql_where = array();
    	$model = $param['model'];
    	//首先是标注点类型
    	$sql_where[] = "$model.type = '".$param['type']."'";
    	
    	//进度限制
    	if(!empty($param['progress']) && $model == 'events'){
    		$today = date('Y-m-d');
    		switch($param['progress']){
    			case 'planning':
    				$sql_where[] = "events.progress=0 and events.begin_time<'$today'";
    				break;
    			case 'running':
    				$sql_where[] = "events.progress=0 and events.begin_time<'$today' and events.end_time>'$today'";
    				break;
    			case 'finished':
    				$sql_where[] = "events.progress=0 and events.end_time<'$today'";
    				break;
    			case 'delayed':
    				$sql_where[] = "events.progress=1";
    				break;
    			case 'failed':	//尚未考虑拖延一定时间自动失败
    				$sql_where[] = "events.progress=2";
    				break;
    			case 'daily':
    				$sql_where[] = "events.progress=3";
    				break;
    		}
    	}
    	
    	//资源标签限制
    	if(!empty($param['res_tag']) && $model == 'events'){
    		$sql_where[] = "res_tags like '%". $param['res_tag'] ."%'";
    	}
    	if(!empty($param['res_tag2']) && $model == 'events'){
    		$sql_where[] = "res_tags like '%". $param['res_tag2'] ."%'";
    	}
    	
    	//领域限制
    	if(!empty($param['field'])){
    		if($model == 'events'){
    			$sql_where[] = "item_field like '%". $param['field'] ."%'";
    		}
    		else{
    			$sql_where[] = "work_field like '%". $param['field'] ."%'";
    		}
    	}
    	
    	if(!empty($param['key'])){
	    	if($model == 'events'){
    			$sql_where[] = "(name like '%". $param['key'] ."%' or description like '%". $param['key'] ."%')";
    		}
    		else{
    			$sql_where[] = "(name like '%". $param['key'] ."%' or introduction like '%". $param['key'] ."%')";
    		}
    	}
    	
    	$where_clause = implode(" and ", $sql_where);
    	return $where_clause;
    }

    //将经度转换成x
    private function lonToX($lon) {
        return round(OFFSET + RADIUS * $lon * pi() / 180);
    }

    //将纬度转换成y
    private function latToY($lat) {
        return round(OFFSET - RADIUS *
                    log((1 + sin($lat * pi() / 180)) /
                    (1 - sin($lat * pi() / 180))) / 2);
    }

    //计算地图上两个点的像素距离
    private function pixelDistance($lat1, $lon1, $lat2, $lon2, $zoom) {
        $x1 = $this->lonToX($lon1);
        $y1 = $this->latToY($lat1);

        $x2 = $this->lonToX($lon2);
        $y2 = $this->latToY($lat2);

        return sqrt(pow(($x1-$x2),2) + pow(($y1-$y2),2)) >> (22 - $zoom*0.7);
    }

    //将地图上的点加入一个点群
    private function joincluster(&$cluster, $marker){
        if(isset($cluster['minlat'])){  //如果已经初始化，修改经纬度范围
			$cluster['num']++;
            if($marker['latitude'] < $cluster['minlat'])
                $cluster['minlat'] = $marker['latitude'];
            if($marker['latitude'] > $cluster['maxlat'])
                $cluster['maxlat'] = $marker['latitude'];
            if($marker['longitude'] < $cluster['minlon'])
                $cluster['minlon'] = $marker['longitude'];
            if($marker['longitude'] > $cluster['maxlon'])
                $cluster['maxlon'] = $marker['longitude'];
        }
        else{   //..没有初始化，作为第一个marker加入
            $cluster['minlat'] = $marker['latitude'];	//最小纬度
            $cluster['maxlat'] = $marker['latitude'];	//最大纬度
            $cluster['minlon'] = $marker['longitude'];
            $cluster['maxlon'] = $marker['longitude'];
            $cluster['num'] = 1;						//标注点的数量
            $cluster['cluster_id'] = $marker['id'];
        }
    }

    //点群聚类算法
    public  function cluster($markers, $distance, $zoom) {
        $clustered = array();
        /* clear session storing for ajax_detail */
        unset($_SESSION['cluster_ids']);
        $_SESSION['cluster_ids'] = array();
        $cluster_id = 1;
        /* Loop until all markers have been compared. */
        while (count($markers)) {
            $marker  = array_pop($markers);
            /* push the initial marker */
            $cluster = array(
                'num'=>1, 
                'cluster_id'=>$cluster_id, 
                'latitude'=>$marker['latitude'], 
                'longitude'=>$marker['longitude']
                );
            /* id_list will be stored in $_SESSION['cluster_ids'][cluster_id],
                used to fetch ajax_detail. */
            $id_list['users'] = 0;
            $id_list['events'] = 0;
            $id_list[$marker['model']] = $marker['id'];
            /* Compare against all markers which are left. */
            foreach ($markers as $key => $target) {
                $pixels = $this->pixelDistance($marker['latitude'], $marker['longitude'],
                                        $target['latitude'], $target['longitude'],
                                        $zoom);
                /* If two markers are closer than given distance remove */
                /* target marker from array and add it to cluster.      */
                if ($distance > $pixels) {
                    unset($markers[$key]);
                    //$cluster[] = $target;
                    //$this->joincluster($cluster, $target);
                    $cluster['latitude'] += $target['latitude'];
                    $cluster['longitude'] += $target['longitude'];
                    $cluster['num']++;
                    $id_list[$target['model']] .= "," . $target['id'];
                }
            }

            $cluster['latitude'] = $cluster['latitude'] / $cluster['num'];
            $cluster['longitude'] = $cluster['longitude'] / $cluster['num'];
            $_SESSION['cluster_ids'][$cluster['cluster_id']] = $id_list;

            $clustered[] = $cluster;
            $cluster_id++;
        }
        
        return $clustered;
    }

    public function subscribe(){
        $this->display();
    }

}
?>