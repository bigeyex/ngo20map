<?php

class RUserTalkModel extends RelationModel{
        protected $_link=array(
            'Talks'=>array(
                'mapping_type'=> BELONG_TO,
                'class_name'  => 'Talks',
                'foreign_key' => 'talk_id',
                'mapping_name' => 'talk',
            ),
        );
}

?>
