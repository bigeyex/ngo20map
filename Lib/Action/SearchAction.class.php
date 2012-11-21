<?php

class SearchAction extends Action{

	public function result(){
		
		if(!isset($_GET['q']) && !isset($_GET['preserve'])){	//第一次进入搜索界面
			$this->display();
			return;
		}
	
		//如果继续上次搜索，从session中读取搜索条件
		if(!empty($_GET['preserve']) && isset($_SESSION['saved_search_condition'])){
			$search_condition = $_SESSION['saved_search_condition'];
			unset($search_condition['p']);
		}
		else{
			$search_condition = array();
		}
		
		//用传入的搜索条件覆盖现有的搜索条件
		//XXX: sql injection prevention relies on PHP settings. see get_magic_quotes_gpc()
		foreach($_GET as $key=>$value){
			$search_condition[$key] = $value;
		}
		
		//保存搜索条件
		$_SESSION['saved_search_condition'] = $search_condition;
		
		$db_model = new Model();
		extract($search_condition);
		//提取标签搜索条件
		if(preg_match('/(?P<query>.*)tag:(?P<tag>.+)/', $q, $matches)){
			$q = trim($matches['query']);
			$tag = $matches['tag'];
			$model = 'events';
		}
		else{
			unset($tag);
		}
		
		preg_match_all('/[^\s|,|，]+/', $q, $matches);
		$keys = $matches[0];
		
		//之所以分成两段，是为了count()做准备.
		$user_part_sql_head = "select id user_id, name user_name, type, aim content, 0 event_id, 0 event_name, work_field field, 0 res_tags, create_time";
		if(count($keys) >= 2){	//需要计算搜索分数
			$user_part_sql_head .= ", 0";
			$weight = 100;
			foreach($keys as $key){
				$user_part_sql_head .= "+$weight*if(name like '%$key%',1,0)";
				$weight -= 1;
				$user_part_sql_head .= "+$weight*if(introduction like '%$key%',1,0)";
				$weight -= 1;
			}
			$user_part_sql_head .= " score";
		}
		$user_part_sql_body = " from users where is_checked=1";
		if(count($keys) >= 1){
			$user_part_sql_body .= " and (0";
			foreach($keys as $key){
				$user_part_sql_body .= " or name like '%$key%' or introduction like '%$key%'";
			}
			$user_part_sql_body .= ")";
		}
		if(isset($work_field) && !empty($work_field)) $user_part_sql_body .= " and work_field like '%$work_field%'";
		
		$event_part_sql_head = "select users.id user_id, users.name user_name, events.type type, description content, events.id event_id, events.name event_name, item_field field, res_tags, events.create_time create_time ";
		if(count($keys) >= 2){	//需要计算搜索分数
			$event_part_sql_head .= ", 0";
			$weight = 100;
			foreach($keys as $key){
				$event_part_sql_head .= "+$weight*if(events.name like '%$key%',1,0)";
				$weight -= 1;
				$event_part_sql_head .= "+$weight*if(description like '%$key%',1,0)";
				$weight -= 1;
			}
			$event_part_sql_head .= " score";
		}
		$event_part_sql_body = " from `events` left join users on (users.id=events.user_id) where events.is_checked=1";
		if(count($keys) >= 1){
			$event_part_sql_body .= " and (0";
			foreach($keys as $key){
				$event_part_sql_body .= " or events.name like '%$key%' or description like '%$key%'";
			}
			$event_part_sql_body .= ")";
		}
		
		if(isset($work_field) && !empty($work_field)) $event_part_sql_body .= " and item_field like '%$work_field%'";
		if(isset($progress) && !empty($progress)){
			if($progress == 'planning'){
				$event_part_sql_body .= " and (begin_time is null or curdate()<begin_time)";
			}
			else if($progress == 'running'){
				$event_part_sql_body .= " and (curdate()>begin_time and curdate()<end_time)";
			}
			else if($progress == 'finished'){
				$event_part_sql_body .= " and (curdate()>end_time)";
			}
		}
		if(isset($req) && !empty($req)) $event_part_sql_body .= " and res_tags like '%$req%'";
		if(isset($res) && !empty($res)) $event_part_sql_body .= " and res_tags like '%$res%'";
		if(isset($tag) && !empty($tag)) $event_part_sql_body .= " and events.id in (select event_id from tagmap,tags where tag_id=tags.id and tags.name='$tag')";

		//get different counts
		$sql = "select t1.cnt+t2.cnt cnt from (select count(*) cnt $user_part_sql_body and (type='ngo' or type='ind')) t1, (select count(*) cnt $event_part_sql_body and events.type='ngo') t2";
		$ngo_count_array = $db_model->query($sql);
		$ngo_count = $ngo_count_array[0]['cnt'];
		$sql = "select t1.cnt+t2.cnt cnt from (select count(*) cnt $user_part_sql_body and type='csr') t1, (select count(*) cnt $event_part_sql_body and (events.type='csr' or events.type='ind')) t2";
		$csr_count_array = $db_model->query($sql);
		$csr_count = $csr_count_array[0]['cnt'];
		$sql = "select count(*) cnt $event_part_sql_body and (events.type='case')";
		$case_count_array = $db_model->query($sql);
		$case_count = $case_count_array[0]['cnt'];

		//add type
		if(isset($type) && !empty($type)) $user_part_sql_body .= " and type='$type'";
		if(isset($type) && !empty($type)) $event_part_sql_body .= " and events.type='$type'";
		
		//从不同的表中选择数据
		if(isset($model) && $model=='users'){
			$count_sql = "select count(*) cnt $user_part_sql_body";
			$final_sql = "$user_part_sql_head $user_part_sql_body";
		}
		else if(isset($model) && $model=='events'){
			$count_sql = "select count(*) cnt $event_part_sql_body";
			$final_sql = "$event_part_sql_head $event_part_sql_body";
		}
		else{
			$count_sql = "select t1.cnt+t2.cnt cnt from (select count(*) cnt $user_part_sql_body) t1, (select count(*) cnt $event_part_sql_body) t2";
			$final_sql = "select * from (($user_part_sql_head $user_part_sql_body) union ($event_part_sql_head $event_part_sql_body)) t";
		}
		if(count($keys) >= 2){
			$final_sql .= " order by score desc, create_time desc";
		}
		else{
			$final_sql .= " order by create_time desc";
		}

		
		//处理分页器
		import("ORG.Util.Page");
		$result_count_array = $db_model->query($count_sql);
		$result_count = $result_count_array[0]['cnt'];
		$rows_per_page = C('SEARCH_RESULT_PER_PAGE');
		if(!isset($p)) $p=1;
		$start_from_row = $rows_per_page * ($p-1);
		$final_sql .= " limit $start_from_row,$rows_per_page";
		$page = new Page($result_count, $rows_per_page);
		$page->setConfig('prev', '<');
		$page->setConfig('next', '>');
		$page->setConfig('theme', '%upPage%%linkPage%%downPage%');


		$pager_content = $page->show();
		
		$results = $db_model->query($final_sql);
		foreach($results as $key=>$result){
			foreach($keys as $k){
				$results[$key]['user_name'] = preg_replace('/('.$k.')/', '<span class="keyword">\1</span>', $results[$key]['user_name']);
				$results[$key]['event_name'] = preg_replace('/('.$k.')/', '<span class="keyword">\1</span>', $results[$key]['event_name']);
				$results[$key]['content'] = $c = preg_replace('/('.$k.')/', '<span class="keyword">\1</span>', $results[$key]['content']);
			}

			switch($result['type']){
				case 'ngo':
					$results[$key]['type_label'] = L('公益组织');
					break;
				case 'fund':
					$results[$key]['type_label'] = L('基金会');
					break;
				case 'csr':
				case 'ind':
					$results[$key]['type_label'] = L('企业社会责任');
					break;
				case 'case':
					$results[$key]['type_label'] = L('对接案例');
					break;
			}
			$results[$key]['model_label'] = '';
			if($result['event_id']){
				if($result['type']!='case'){
					$results[$key]['model_label'] = L('项目');
				}
				$sql = 'select url from media where event_id='.$result['event_id'].' and type=\'image\' limit 1';
				$image = $db_model->query($sql);
				$results[$key]['event_image'] = $image[0]['url'];
			}
			else if($result['event_id']==0){
				$results[$key]['model_label'] = L('名单');
				$sql = 'select url from media where event_id in (select id from events where user_id='.$result['user_id'].') and type=\'image\' limit 1';
				$image = $db_model->query($sql);
				$results[$key]['event_image'] = $image[0]['url'];
			}
		}
		
		$this->assign('result_count',$result_count);
		$this->assign('ngo_count',$ngo_count);
		$this->assign('csr_count',$csr_count);
		$this->assign('case_count',$case_count);
		$this->assign('q', $search_condition['q']);
		$this->assign('type', $type);
		$this->assign('model', $model);
		$this->assign('work_field', $work_field);
		$this->assign('progress', $progress);
		$this->assign('req', $req);
		$this->assign('res', $res);
		
		$this->assign('results', $results);
		$this->assign('pager_content', $pager_content);
		
		$this->display();
	}
}
?>