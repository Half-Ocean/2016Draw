<?php

class Logs extends \Phalcon\Mvc\Model
{
    public $id;
    public $user_id;
    public $award_id;
    public $create_time;

    public function getSource(){
        return "draw_logs";
    }


    public function addLogs( $user_id, $award_id ){
        if(empty($user_id)){
            return false;
        }

        $logModel = new Logs();
        $logModel->user_id = $user_id;
        $logModel->award_id = $award_id;
        $logModel->create_time = date("Y-m-d H:i:s");
        if(!$logModel->create()){
            return false;
        }

        $insertId = $logModel -> getWriteConnection() -> lastInsertId($logModel -> getSource());

        return $insertId;

    }



}