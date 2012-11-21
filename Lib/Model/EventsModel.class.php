<?php

class EventsModel extends RelationModel{
        protected $_link=array(
            'users'=>array(
                'mapping_type'=> BELONGS_TO,
                'class_name'  => 'Users',
                'foreign_key' => 'user_id',
                'as_fields' => 'name:creator_name,image',
            ),
        );

        protected $_validate = array(
            array('name', 'require', '事件名必须')
        );

        public function getImage($id){
            $media_model = M('Media');
            $media = $media_model->where(array('event_id'=>$id, 'type'=>'image'))->find();
            return $media['url'];
        }
}

?>
