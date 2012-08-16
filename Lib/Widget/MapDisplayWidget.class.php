<?php

//显示一个地图，允许向地图上添加标签
class MapDisplayWidget extends EduWidget{

    public function render($data){
    	if(isset($data['width'])){
    		$width = $data['width'];
    	}
    	else{
    		$width = '960px';
    	}
    
        $content = $this->renderFile('MapDisplay',array('width'=>$width));
        return $content;
    }

}
?>
