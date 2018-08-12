<?php

namespace Addon\api\Controller;

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
            $userInfo = get_current_user_data();
            $uid = $userInfo['uid'];
            if (!$uid)
            {
                $this->apiOutputData('403', '用户未登录', array());
            }

            $params = $this->params;
            $type = $params['source_type'];
            $targetId = $params['source_id'];
            $isFavorite = $params['is_favorite'];

            $sUser = SVC("User");
            $sUser->handleUserFavorite($uid, $type, $targetId, $isFavorite);

            $error = $sUser->getErrorMsg();
            $this->apiOutputData($error['error_code'], $error['msg'], array());
        }

        //根据id获取用户是否设置相应的喜好
        public function get_user_own_favorite()
        {
            $userInfo = get_current_user_data();
            $uid = $userInfo['uid'];

            $params = $this->params;
            $type = $params['type'];
            $favoriteTypes = $params['favorite_types'];
            $targetIds = $params['source_ids'];

            if (!in_array($type, array('news', 'coin', 'author', 'story')))
            {
                $this->apiOutputData('200', '错误的TYPE类型');
            }

            if (!is_array($favoriteTypes) || empty($favoriteTypes) || !is_array($targetIds))
            {
                $this->apiOutputData('200', '参数类型错误');
            }

            if (!$targetIds)
            {
                $this->apiOutputData('200', '资源id不能为空');
            }

            $sUser = SVC("User");
            $data = $sUser->getUserOwnFavorite($uid, $type, $favoriteTypes, $targetIds);

            $this->apiOutputData('0', 'succ.', $data);
        }


        //获取用户收藏
        public function get_user_own_collect()
        {
            $userInfo = get_current_user_data();
            $params = $this->params;
            $minTime = $params["min_time"];
            $type = $params['type'];
            $uid = $userInfo['uid'];

            if (!$uid)
            {
                $this->apiOutputData('403', '该用户未登录');
            }

            if (!in_array($type, array('news', 'coin', 'type', 'story')))
            {
                $this->apiOutputData('200', '错误的TYPE类型');
            }

            if(!$minTime)
            {
                $minTime=0;
            }

            switch ($type)
            {
                case "news":

                    $sUser = SVC("User");
                    $data = $sUser->getUserOwnNewsCollect($uid, $minTime);

                    break;

                case "coin":

                    $sUser = SVC("User");
                    $data = $sUser->getUserOwnCoinCollect($uid, $minTime);

                    break;
            }

            $this->apiOutputData('0', 'succ.', $data);
        }

        //获取用户协议
        public function get_reg_protocol()
        {
            $sUser=SVC("User");
            $data=$sUser->getRegProtocol();

            $this->apiOutputData('0', 'succ.', $data);
        }

        //获取用户推送设置
        public function get_user_push_setting()
        {
            $sPush=SVC("Push");
            $pushSettings=$sPush->getUserPushSetting(2);

            $this->apiOutputData('0','succ.',$pushSettings);
        }

        //操作用户推送设置
        public function do_user_push_setting()
        {
            $params=$this->params;
            $pushCode=$params['push_code'];
            $state=$params['state'];
            if(!in_array($state,array(0,1)))
            {
                $this->apiOutputData('200','state参数错误');
            }

            $sPush=SVC("Push");
            $re=$sPush->doUserPushSetting(2,$pushCode,$state);
            if(!$re)
            {
                $this->apiOutputData('500','设置失败');
            }

            $this->apiOutputData('0','设置成功');
        }

        //提交用户极光信息
        public function submit_user_jpush_info()
        {
            $params=$this->params;
            $params['client_code']=$params['device'];

            if(!$params['register_id'])
            {
                $this->apiOutputData('200','register_id不能为空');
            }

            $sPush=SVC("Push");
            $re=$sPush->submitUserJpushInfo($params);
            if(!$re)
            {
                $this->apiOutputData('500','fail.');
            }

            $this->apiOutputData('0','succ');
        }
}