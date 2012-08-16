<?php

class ReviewsModel extends RelationModel{
        protected $_link=array(
            'events'=>array(
                'mapping_type'=> BELONGS_TO,
                'class_name'  => 'Events',
                'foreign_key' => 'event_id',
                'mapping_name' => 'event',
            ),
            'users'=>array(
                'mapping_type'=> BELONGS_TO,
                'class_name'  => 'Users',
                'foreign_key' => 'user_id',
                'mapping_name' => 'user',
            ),
        );
}

?>
