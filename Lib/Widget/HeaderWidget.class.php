<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HeaderWidgetclass
 *
 * @author Wang yu
 */
class HeaderWidget extends EduWidget{

    public function render($data){

        // $r_talk_model = M('RUserTalk');
        // $tpl_data['message_num'] = $r_talk_model->where(array(
        //     'user_id' => $_SESSION['login_user']['id'],
        //     'is_read' => 0,
        //     'is_deleted' => 0,
        // ))->count();
        // $tpl_data['title'] = $data['title'];
        // $css_class_of_tabs = array(
        // 	'Index_index' => '',
        // 	'Search_result' => '',
        // 	'Event_add' => '',
        // 	'User_recommend' => '',
        // 	'User_home' => '',
        // 	'User_my_events' => '',
        // 	'User_my_comments' => '',
        // );
        $css_class_of_tabs[MODULE_NAME] = 'class="current"';
        $css_class_of_tabs[MODULE_NAME . '_' .ACTION_NAME] = 'class="current"';
        if(!isset($_GET['id']) || $_GET['id'] != $_SESSION['login_user']['id']){
        	$css_class_of_tabs['User_home'] = '';
        }
        if(isset($_GET['id'])){
        	$css_class_of_tabs['Event_add'] = '';
        }
        if(isset($_GET['type'])){
        	$css_class_of_tabs[MODULE_NAME . '_' .ACTION_NAME . '_' . $_GET['type']] = 'class="current"';
        }
        $tpl_data = $data;
        $tpl_data['css_class'] = $css_class_of_tabs;

        if(isset($data['top_bar'])){
            $tpl_data['top_bar'] = $data['top_bar'];
        }
        
        $content = $this->renderFile('Header',$tpl_data);
        return $content;
    }

}
?>
