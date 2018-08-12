<?php

namespace Addon\ajax\Controller;
use Common\Service\Exchange\Exchange;

/**
 * 用户相关接口
 * Class UserController
 * @package Addon\ajax\Controller
 */
class UserController extends BaseController
{
        //用户喜好操作接口
        public function do_user_favorite()
        {
            $userInfo=get_current_user_data();
            $uid=$userInfo['uid'];
            if(!$uid)
            {
                $this->ajaxCallMsg('400','用户未登录',array());
            }

            $type=I("source_type");
            $targetId=I("source_id");
            $isFavorite=I("is_favorite");

            $sUser=SVC("User");
            $sUser->handleUserFavorite($uid,$type,$targetId,$isFavorite);

            $error=$sUser->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'],$error['msg'],array());
        }

        //根据id获取用户是否设置相应的喜好
        public function get_user_own_favorite()
        {
            $userInfo=get_current_user_data();
            $uid=$userInfo['uid'];

            $type=I('type');
            $favoriteTypes=I("favorite_types");
            $targetIds=I("source_ids");

            $sUser=SVC("User");
            $data=$sUser->getUserOwnFavorite($uid,$type,$favoriteTypes,$targetIds);

            $this->ajaxCallMsg('0','succ.',$data);
        }

        //获取用户收藏
        public function get_user_own_collect()
        {
            $userInfo=get_current_user_data();
            $minTime=I("min_time",0,'intval');
            $uid=$userInfo['uid'];
            $type=I('type','news');

            if(!$uid)
            {
                $this->ajaxCallMsg('400','该用户未登录');
            }

            switch($type)
            {
                case "news":

                    $sUser=SVC("User");
                    $data=$sUser->getUserOwnNewsCollect($uid,$minTime);

                    break;

                case "coin":

                    $sUser=SVC("User");
                    $data=$sUser->getUserOwnCoinCollect($uid,$minTime);

                    break;
            }

            $this->ajaxCallMsg('0','succ.',$data);
        }

        public function test()
        {
            $sKafka=SVC("Kafka");

            $data=$sKafka->publish("test","asdfsf");

            var_dump($data);
        }
}