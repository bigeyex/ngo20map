<?php

class ServiceAction extends Action{

	public function get_user_phone(){ // get as user_id
		$user_model = M('Users');
		$user = $user_model->find($_GET['id']);
		if(!empty($user)){
			$this->text_to_image($user['phone']);
		}
	}

	public function get_event_phone(){
		$event_model = M('Events');
		$event = $event_model->find($_GET['id']);
		if(!empty($event)){
			$this->text_to_image($event['contact_phone']);
		}
	}

	private function text_to_image($text){
		header ("Content-type: image/png");
		$string = $text;                                            
		$font   = 4;
		$width  = ImageFontWidth($font) * strlen($string);
		$height = ImageFontHeight($font);
		 
		$im = @imagecreate ($width,$height);
		$background_color = imagecolorallocate ($im, 255, 255, 255); //white background
		$text_color = imagecolorallocate ($im, 0, 0,0);//black text
		imagestring ($im, $font, 0, 0,  $string, $text_color);
		imagepng ($im);
	}
	
}
?>