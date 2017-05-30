<?php

class IndexController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $userModel = new Users();
        $user_id = $this->cookies->get('gcsbbUid')->getValue();
        $userInfo = $userModel->getUserInfo($user_id);
        $count = $userInfo['draw_count'];
        if($count<=0){
            $count = 0;
        }

        //已经抽奖人数
        $sql = "select count(1) as count from draw_logs ";
        $count1 = $this->db->fetchOne($sql);
        //已经中奖人数
        $sql = "select count(1) as count from draw_logs where award_id > 0 ";
        $count2 = $this->db->fetchOne($sql);


        $this->view->setVar('userInfo',$userInfo);
        $this->view->setVar('count',$count);
        $this->view->setVar('count1',$count1['count']);
        $this->view->setVar('count2',$count2['count']);
        $this->view->setVar('stock',$this->getGoodsStock());

        $this->view->setVar('ilisten_title',"抽奖活动");
        $this->view->setVar('title',"工程师爸爸抽奖活动页面");

    }



    public function lightAction(){

        $userModel = new Users();
        $logModel = new Logs();
        $userlogModel = new UserLogs();

        $user_id = $this->cookies->get('gcsbbUid')->getValue();

        if( empty($user_id) ){
            echo json_encode(array('retcode'=>-1,'msg'=>"请先登陆后再抽奖"));
            exit;
        }

        $userInfo = $userModel->getUserInfo($user_id);
        $count = $userInfo['draw_count'];
        if($count<=0){
            $count = 0;
        }

        if( empty($userInfo) ){
            echo json_encode(array('retcode'=>-1,'msg'=>"不存在该用户"));
            exit;
        }


        if( $count <= 0 ){
            echo json_encode(array("retcode"=>1,"msg"=>"亲，您的抽奖次数不够:("));
            exit;
        }


        //概率1/2
        $chouzhongID = rand(1,5);
        if($chouzhongID == 1){

            $goods_list = $this->getGoodsList();
            $proArr = array();
            foreach($goods_list as $goods){
                if( $goods['stock'] <= 0 ){
                    continue;
                }
                $proArr[$goods['award_id']] = $goods['stock'];
            }
            $award_id = $this->get_rand($proArr);
        }else{
            //没抽中
            $award_id = 0;
        }

        $this->db->begin();
        //记录抽奖记录
        $ok = $logModel->addLogs($user_id,$award_id);
        $ok1 = $userlogModel->addLogs($user_id, ($count-1), "抽奖" );

        //抽奖次数减少一次
        $user_info = Users::findFirstByUserId($user_id);
        $user_info->draw_count = $count-1;
        $user_info->update_time = date("Y-m-d H:i:s");
        $ok2 = $user_info->save();

        if(!$ok || !$ok1 || !$ok2){
            $this->db->rollback();
            echo json_encode(array('retcode'=>-1,'msg'=>"抽奖失败，请重新尝试","ok"=>$ok,"ok1"=>$ok1, "ok2"=>$ok2));
            exit;
        }


        if( $award_id ){
            //抽中奖品
            //变更库存
            $award_info = Awards::findFirst($award_id);
            if( $award_info->stock <= 0 ){
                $this->db->rollback();
                echo json_encode(array('retcode'=>-1,'msg'=>"抽奖失败，库存不能为0，请重新尝试"));
                exit;
            }

            $award_info->stock = $award_info->stock -1;
            $award_info->update_time = date("Y-m-d H:i:s");
            if( !$award_info->save()){
                $this->db->rollback();
                echo json_encode(array('retcode'=>-1,'msg'=>"抽奖失败，变更库存失败，请重新尝试1"));
                exit;
            }


            //发送IM
            if( $award_info->award_type == "vip" ){
                //VIP
                $msg = "恭喜您参加抽奖获得奖品”".$award_info->award_name."“。VIP已经送出，请留意您的VIP有效期已经自动增加了。具体可访问 我的-订单-VIP订单 确认。如有疑问，请联系 工爸班主任的微信号：idaddy004 。";
                $goods_id = $award_info->goods_id;
                $goods_quantity = $award_info->goods_quantity;
                $order_sn = "draw##".$ok;
                $this->ilisten_service->buyVip( $user_id , $order_sn , $goods_id , 0, $goods_quantity , "gcsbb" , "gift" , $msg );
            }elseif( $award_info->award_type == "bk" ){
                //贝壳
                $bk = $award_info->goods_quantity;
                $msg = "恭喜您参加抽奖获得奖品”".$award_info->award_name."“。贝壳已经送出，具体可访问 我的-贝壳-贝壳明细 确认。如有疑问，请联系 工爸班主任的微信号：idaddy004 。";
                $this->ilisten_service->addBK( $user_id , $bk , $ok , 'DRAW' );
                $this->message_service->sendSystemMsg(734236, $user_id, $msg, "", 'system', "", "");
            }elseif( $award_info->award_type == "normal" ){
                //普通商品
                $msg = "恭喜您参加抽奖获得奖品“".$award_info->award_name."”，请联系 工爸班主任的微信号：idaddy003， 提交您的配送地址信息。";
                $this->message_service->sendSystemMsg(734236, $user_id, $msg, "", 'system', "", "");

            }elseif( $award_info->award_type == "xnd" ){
                //普通商品
                $msg = "恭喜您参加抽奖获得奖品“".$award_info->award_name."”，小牛顿VIP已经送出，请留意您的VIP有效期已经自动增加了。如有疑问，请联系 工爸班主任的微信号：idaddy004 。";
                $goods_id = $award_info->goods_id;
                $goods_quantity = $award_info->goods_quantity;
                $order_sn = "draw##".$ok;
                $this->xnd_service->purchaseOrder($user_id, $order_sn, $goods_id, 0 , $goods_quantity , "201761活动抽奖赠送" , "gift" ,$msg);
            }


            $this->db->commit();
            echo json_encode(array('retcode'=>3,'award'=>array(
                "award_id"=>$award_info->award_id,
                "award_name"=>$award_info->award_name,
                "award_price"=>$award_info->award_price,
            )));
            exit;
        }

        $this->db->commit();
        echo json_encode(array('retcode'=>2,'msg'=>"nothing"));
        exit;


    }


    public function bindModileAction(){

        $userModel = new Users();
        $user_id = $this->cookies->get('gcsbbUid')->getValue();
        $userInfo = $userModel->getUserInfo($user_id);

        if( isset($_GET["mobile"]) ){
            $mobile = $_GET["mobile"];
            if( !empty($userInfo['mobile']) ){
                echo json_encode(array("retcode"=>-1,"msg"=>"您的帐号已填写手机号，无需重复填写"));
                exit;
            }


            $ret = $this->member_service->isMobileBind($mobile);
            if( $ret->isbind == 1 ){
                echo json_encode(array("retcode"=>-1,"msg"=>"该手机号已绑定其他账户（ID:".$ret->user_id."），请尝试其他手机号"));
                exit;
            }


            $this->member_service->bindMobile($user_id , $mobile);
            echo json_encode(array("retcode"=>1,"msg"=>"该手机号填写成功:)"));
            exit;

        }

        $this->view->setVar('userInfo',$userInfo);
        $this->view->setVar('ilisten_title',"抽奖活动绑定手机页面");
        $this->view->setVar('title',"工程师爸爸抽奖活动页面-绑定手机");

    }




    public function goodsListAction(){

        //商品列表
        $nums = $this->getGoodsNum();
        $stock = $this->getGoodsStock();
        $list = $this->getGoodsList();
        $present = intval(doubleval($stock/$nums)*100);
        if($present > 100){
            $present = 100;
        }

        foreach($list as $k=>$v){
            switch($v['award_id']){
                case 11:
                    $list[$k]['url']="http://mall.idaddy.cn/mobile/index.php?app=goods&id=537";
                    break;
                case 10:
                    $list[$k]['url']="http://mp.weixin.qq.com/bizmall/malldetail?id=&pid=pi85twA7EjET98aw3LgVz9vknwY0&biz=MzIyODQyMTc3MQ==&scene=&action=show_detail&showwxpaytitle=1#wechat_redirect";
                    break;
                case 12:
                    $list[$k]['url']="https://item.jd.com/1552040221.html";
                    break;
                case 7:
                    $list[$k]['url']="http://item.jd.com/1755145.html";
                    break;
                case 8:
                    $list[$k]['url']="http://item.jd.com/11571255.html";
                    break;
                case 13:
                    $list[$k]['url']="http://xnd.idaddy.cn/";
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                    $list[$k]['url']="https://community.idaddy.cn/share/topic/403";
                    break;
                default:
                    $list[$k]['url']="javascript:;";
                    break;
            }
        }

        $this->view->setVar('present',$present);
        $this->view->setVar('list',$list);
        $this->view->setVar('stock',$stock);
        $this->view->setVar('nums',$nums);
        $this->view->setVar('ilisten_title',"奖品列表");
        $this->view->setVar('title',"工程师爸爸点灯人抽奖奖品列表");
    }



    public function historyAction(){

        $list =array();
        $logs = Logs::find(array(
            "award_id > 0 and create_time > '2017-05-30'",
            "order" =>"create_time desc",
            "limit" => 100
        ));

        foreach($logs as $log){

            $user_info = $this->member_service->getUserInfo($log->user_id)->user_info;
            $list[] = array(
                "user_id"=>$user_info->user_id,
                "nickname"=>$user_info->nickname,
                "avatar_url"=>$this->config->qiniu->imgPath .$user_info->avatar_url,
                "create_time"=>$log->create_time,
            );
        }


        $this->view->setVar('list',$list);
        $this->view->setVar('ilisten_title',"中奖用户（前100名）");
        $this->view->setVar('title',"工程师爸爸点灯人抽奖中奖用户列表");
    }


    public function myHistoryAction(){
        $userModel = new Users();
        $user_id = $this->cookies->get('gcsbbUid')->getValue();
        $userInfo = $userModel->getUserInfo($user_id);
        $userInfo['avatar_url']= $this->config->qiniu->imgPath .$userInfo['avatar_url'] ;

        $list =array();
        $logs = Logs::find(array(
            "award_id > 0 AND user_id = :user_id: and create_time > '2017-05-30'",
            "bind"=>array("user_id"=>$user_id),
            "order" =>"create_time desc",
            "limit" => 100
        ));

        foreach($logs as $log){

            $award_info = Awards::findFirstByAwardId($log->award_id);
            $list[] = array(
                "award_id"=>$log->award_id,
                "award_name"=>$award_info->award_name,
                "create_time"=> date("Y-m-d",strtotime($log->create_time)),
            );
        }

        $this->view->setVar('list',$list);
        $this->view->setVar('ilisten_title',"中奖用户（前100名）");
        $this->view->setVar('title',"工程师爸爸点灯人抽奖中奖用户列表");

    }



    private function getGoodsList(){

        $list = array();
        $goods_list = Awards::find(array("order"=>"update_time desc , award_price desc"));
        foreach( $goods_list as $goods ){

            $list[] = array(
                'award_id'=> $goods->award_id,
                'award_type'=> $goods->award_type,
                'goods_id'=> $goods->goods_id,
                'goods_quantity'=> $goods->goods_quantity,
                'award_name'=> $goods->award_name,
                'award_price'=> $goods->award_price,
                'num'=> $goods->num,
                'stock'=> $goods->stock,
            );

        }

        return $list;
    }


    private function getGoodsNum(){

        $nums = 0;
        $goods_list = Awards::find();
        foreach( $goods_list as $goods ){
            $nums += $goods->num;
        }

        return $nums;
    }

    private function getGoodsStock(){

        $stock = 0;
        $goods_list = Awards::find();
        foreach( $goods_list as $goods ){
            $stock += $goods->stock;
        }

        return $stock;

    }

    private function getMyIP(){

        if(getenv('HTTP_CLIENT_IP')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
            $onlineip = getenv('REMOTE_ADDR');
        } else {
            $onlineip = $_SERVER["REMOTE_ADDR"];
        }
        return $onlineip;
    }


    /** 概率算法 **/
    private function get_rand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);             //抽取随机数
            if ($randNum <= $proCur) {
                $result = $key;                         //得出结果
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
    

}


