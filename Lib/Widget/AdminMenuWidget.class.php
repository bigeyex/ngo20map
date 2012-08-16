<?php
/**
 * Description of MapLocateWidgetclass
 *
 * @author Wang yu
 */
class AdminMenuWidget extends EduWidget{

    public function render($data){

        //获取地址栏传送的课程id
        $tpl_data = array();
        $content = $this->renderFile('AdminMenu',$tpl_data);
        return $content;
    }

}
?>
