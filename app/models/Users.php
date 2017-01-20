<?php

class Users extends \Phalcon\Mvc\Model
{
    protected $member_service;

    public $user_id;
    public $draw_count;
    public $create_time;
    public $update_time;

    public function getSource(){
        return "draw_user";
    }

    /*
     * 从依赖注入获取微服务实例
     */
    public function onConstruct() {
        $this->member_service = $this->getDI()->getShared('member_service');
    }


    public function getUserInfo( $user_id ){

        if( empty($user_id) ){
            return null;
        }

        $serverRet = $this->member_service->getUserInfo($user_id);
        if($serverRet->ret_code == 0){
            $user_info = $this->objToArray($serverRet->user_info);
        }

        if(empty($user_info)){
            return null;
        }

        $info = Users::findFirstByUserId($user_id);
        if(!$info){
            $this->add_user( $user_id );
            $user_info['draw_count'] = 0;
        }else{
            $user_info['draw_count'] = $info->draw_count;
        }

        return $user_info;
    }


    public function getUserInfoByToken( $authToken ){

        if( empty($authToken) ){
            return null;
        }

        $serverRet = $this->member_service->getUserInfoByToken($authToken , 1002);
        if($serverRet->ret_code == 0){
            $user_info = $this->objToArray($serverRet->user_info);
            $user_id = $user_info['user_id'];
        }else{
            return null;
        }

        if( empty($user_id) ){
            return null;
        }

        $info = Users::findFirstByUserId($user_id);
        if(!$info){
            $this->add_user( $user_id );
        }

        return $user_info;
    }



    private function add_user( $user_id ){

        if(empty($user_id)){
            return false;
        }

        $info = Users::findFirstByUserId($user_id);
        if($info){
            return false;
        }

        $userInfo = new Users();
        if($userInfo->create(array(
            "user_id"=>$user_id,
            "draw_count"=> 0,
            "create_time"=>date("Y-m-d H:i:s"),
            "update_time"=>date("Y-m-d H:i:s"),
        ))){

            return true;
        }

        return false;
    }




    private function objToArray($obj) {
        if(is_object($obj)) {
            $obj = (array)$obj;   //强制转换
        }
        if(is_array($obj)) {    //对象属性还是对象的时候，需要使用递归
            foreach($obj as $key=>$value) {
                $obj[$key] = $this->objToArray($value);    //递归循环调用
            }
        }
        return $obj;
    }



}