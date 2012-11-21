<?php

class NewsletterAction extends Action{

	public function get(){
		$user_id = $_GET['id'];
		$user_model = M('Users');
		$event_model = M('Events');

		$user = $user_model->find($user_id);
		$province = $user['province'];
		$work_field = str_replace(', ', ' ', $user['work_field']);

		$same_field_csr = $event_model->where(array(
			));
	}
	
}
?>