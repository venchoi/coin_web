<?php

namespace Common\Service;

/**
 * 推送服务层
 * Class PushService
 * @package Common\Service
 */
class PushService extends BaseService
{
    //用户推送设置
    public function doUserPushSetting($clientType,$pushCode,$state)
    {
        $userInfo=get_current_user_data();
        $uid=$userInfo['uid'];
        if(!$uid)
        {
            $this->setErrorMsg('403','用户未登录');
            return false;
        }

        $dUserPushSetting=D('UserPushSetting');
        $re=$dUserPushSetting->editUserPushSetting($clientType,$uid,$pushCode,$state);
        if($re===false)
        {
            return false;
        }

        return true;
    }

    //用户推送设置
    public function getUserPushSetting($clientType)
    {
        $dUserPushSetting=D('UserPushSetting');

        $userInfo=get_current_user_data();
        $uid=$userInfo['uid'];

        if(!$uid)
        {
            $re=$dUserPushSetting->getUnLogPushSetting();
        }
        else
        {
            $re=$dUserPushSetting->getUserPushSetting($clientType,$uid);
        }

        if(!$re) $re=array();

        return $re;
    }

    //提交用户极光信息
    public function submitUserJpushInfo($params)
    {
        $dUserJpushInfo=D("UserJpushInfo");


        $re=$dUserJpushInfo->editUserJpushInfo($params);
        if($re===false)
        {
            return false;
        }

        return true;
    }
}