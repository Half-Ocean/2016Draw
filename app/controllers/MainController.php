<?php

class MainController extends \Phalcon\Mvc\Controller
{

    public function indexAction(){

        $search_uid = isset($_REQUEST['search_uid'])?intval($_REQUEST['search_uid']):'';
        $sql = "select * from draw_user where 1 = 1 ";

        if(!empty($search_uid)){
            $sql .= " AND user_id = ".intval($search_uid);
        }

        $sql .=" order by user_id desc limit 200";
        $list = $this->db->fetchAll($sql);

        $this->view->setVar('search_uid',$search_uid);
        $this->view->setVar('list',$list);
        $this->view->setVar('ilisten_title',"用户列表");
        $this->view->setVar('title',"用户列表");

    }



    public function logAction(){

        $search_uid = isset($_REQUEST['search_uid'])?intval($_REQUEST['search_uid']):'';
        $sql = "select l.*,d.award_name,d.award_type
                from draw_logs as l
                left join draw_awards as d on d.award_id = l.award_id
                where 1 = 1 AND l.award_id>0 ";

        if(!empty($search_uid)){
            $sql .= " AND l.user_id = ".intval($search_uid);
        }

        $sql .=" order by l.id desc limit 500";
        $list = $this->db->fetchAll($sql);

        $this->view->setVar('search_uid',$search_uid);
        $this->view->setVar('list',$list);
        $this->view->setVar('ilisten_title',"抽奖记录列表");
        $this->view->setVar('title',"抽奖记录列表");

    }



    public function userlogAction(){

        $search_uid = isset($_REQUEST['search_uid'])?intval($_REQUEST['search_uid']):'';
        $sql = "select * from draw_user_log where 1 = 1 ";

        if(!empty($search_uid)){
            $sql .= " AND user_id = ".intval($search_uid);
        }
        $sql .=" order by id desc limit 500";
        $list = $this->db->fetchAll($sql);

        $this->view->setVar('search_uid',$search_uid);
        $this->view->setVar('list',$list);
        $this->view->setVar('ilisten_title',"抽奖次数变更日志列表");
        $this->view->setVar('title',"抽奖次数变更日志列表");

    }


    public function userupdateAction(){

        $user_id = isset($_REQUEST['user_id'])?intval($_REQUEST['user_id']):0;
        $count = isset($_REQUEST['count'])?intval($_REQUEST['count']):0;
        $notes = isset($_REQUEST['notes'])?trim($_REQUEST['notes']):'';
        $im = isset($_REQUEST['im'])?trim($_REQUEST['im']):'';

        if(empty($user_id)){
            echo json_encode(array("retcode"=>-1,"msg"=>"用户ID不能为空:("));
            exit;
        }

        $userInfo = Users::findFirstByUserId($user_id);
        if($userInfo && empty($notes)){
            echo json_encode(array("retcode"=>-1,"msg"=>"变更原因不能为空:("));
            exit;
        }

        //新增用户
        if( empty($userInfo) ){
            $userInfo = new Users();
            $userInfo->user_id =$user_id;
            $userInfo->create_time =date("Y-m-d H:i:s");
            $userInfo->update_time =date("Y-m-d H:i:s");
            $userInfo->count =0;
            if(!$userInfo->create()){
                echo json_encode(array("retcode"=>-1,"msg"=>"新建用户失败:("));
                exit;
            }

            if(empty($notes)){
                $notes = "添加用户信息";
            }

        }

        $userInfo->update_time = date("Y-m-d H:i:s");
        $userInfo->count = $count;
        if(!$userInfo->save()){
            echo json_encode(array("retcode"=>-1,"msg"=>"变更用户抽奖次数失败:("));
            exit;
        }

        $userLogModel = new UserLogs();
        $userLogModel->user_id = $user_id;
        $userLogModel->count = intval($count);
        $userLogModel->notes = $notes;
        $userLogModel->create_time = date("Y-m-d H:i:s");

        if(!$userLogModel->create()){
            echo json_encode(array("retcode"=>-1,"msg"=>"添加用户抽奖变更日志失败:("));
            exit;
        }

        if( $im ){
            $this->message_service->sendSystemMsg(734236, $user_id, $im, "", 'system', "", "");
        }

        echo json_encode(array("retcode"=>-1,"msg"=>"success"));
        exit;

    }


}