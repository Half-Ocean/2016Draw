<?php

class UserLogs extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $count;
    public $notes;
    public $create_time;

    public function getSource(){
        return "draw_user_log";
    }


    public function addLogs( $user_id, $count , $notes ){
        if(empty($user_id) ){
            return false;
        }

        $logModel = new UserLogs();
        $logModel->user_id = $user_id;
        $logModel->count = $count;
        $logModel->notes = $notes;
        $logModel->create_time = date("Y-m-d H:i:s");
        if(!$logModel->create()){
            return false;
        }

        return true;
    }


}