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
class FooterWidget extends EduWidget{

    public function render($data){

        //获取地址栏传送的课程id
        $tpl_data = array();
        $content = $this->renderFile('Footer',$tpl_data);
        return $content;
    }

}
?>
