<?php

class EventsModel extends RelationModel{
    public function getEventImage($user_id){
        $sql = "select * from media where event_id in (select id from events where user_id=$user_id) limit 1";
        $media = $this->query($sql);
        return $media[0];
    }
}

?>
