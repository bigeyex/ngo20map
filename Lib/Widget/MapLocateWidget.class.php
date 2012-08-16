<?php
/**
 * Description of MapLocateWidgetclass
 *
 * @author Wang yu
 */
class MapLocateWidget extends EduWidget{

    public function render($data){

        if(!isset($data['place_label'])){
        	$data['place_label'] = L('具体位置');
        	$data['map_place_label'] = L('如果地图中所示位置不是您的项目位置，点击地图来标注项目位置。');
        }
        
        $content = $this->renderFile('MapLocate',$data);
        return $content;
    }

}
?>
