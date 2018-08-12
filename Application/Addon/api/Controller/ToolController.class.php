<?php

namespace Addon\api\Controller;

/**
 * 小的工具类控制器
 * Class ToolController
 * @package Addon\ajax\Controller
 */
class ToolController extends BaseController
{
    /**
     * 获取图像验证码id
     */
    public function get_verify_id()
    {
        $id=md5(uniqid());

        $re['id']=$id;

        $this->apiOutputData('0','succ.',$re);
    }

    /**
     *用于生成验证码
     */
    public function verify()
    {
        $id=I('get.id');
        $config =    array(
            'fontSize'    =>    30,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
        );
        $Verify =  new \Think\VerifyApp($config);
        $Verify->entry($id);
    }

    //获取短息验证码
    public function get_sms_code()
    {
        $params=$this->params;
        $phone=$params['phone'];
        $areaCode=$params['area_code'];
        $verifyCode=$params['verify_code'];
        $verifyId=$params['verify_id'];
        $type=$params['type']?$params['type']:'log';

        if(empty($areaCode))
        {
            $this->apiOutputData('200','请填写区号!');
        }

        $pattern='/^1[2-9]\d{9}$/';
        if(!preg_match($pattern,$phone))
        {
            $this->apiOutputData('200','请输入正确的手机号!');
        }

        if(!check_app_verify($verifyCode,$verifyId))
        {
            $this->apiOutputData('200','图像验证码错误!');
        }

        if(!in_array($type,array('log','reg','find','bind','check')))
        {
            $this->apiOutputData('500','错误的操作类型!');
        }

        $sSms=SVC("Sms");
        $re=$sSms->sendSmsCode($type,$phone,$areaCode);
        if($re===false)
        {
            $error=$sSms->getErrorMsg();
            $this->apiOutputData($error['error_code'],$error['msg']);
        }

        $this->apiOutputData('0','短信发送成功!');
    }

    //短息验证码验证接口
    public function check_sms_code()
    {
        $params=$this->params;
        $phone=$params['phone'];
        $areaCode=$params['area_code'];
        $smsCode=$params['sms_code'];
        $type=$params['type']?$params['type']:'log';

        if(empty($areaCode))
        {
            $this->apiOutputData('200','请填写区号!');
        }

        $pattern='/^1[2-9]\d{9}$/';
        if(!preg_match($pattern,$phone))
        {
            $this->apiOutputData('200','请输入正确的手机号!');
        }

        if(!in_array($type,array('log','reg','find','bind','check')))
        {
            $this->apiOutputData('500','错误的操作类型!');
        }

        $sSms=SVC("Sms");
        $re=$sSms->checkSmsCode($type,$phone,$smsCode);
        if($re===false)
        {
            $error=$sSms->getErrorMsg();
            $this->apiOutputData($error['error_code'],$error['msg']);
        }

        $this->apiOutputData('0','succ.');
    }

    //获取app最新版本
    public function get_app_latest_version()
    {
        $params=$this->params;
        $device = $params['device'];
        $sVersion = SVC('Version');

        if(!in_array($device,array('android','ios')))
        {
            $this->apiOutputData('200','fail.','非法设备');
        }

        $data = $sVersion->getNewestVersionInfo($device);

        if(!$data)
        {
            $this->apiOutputData('400','fail.');
        }

        $this->apiOutputData('0','succ.',$data);
    }
}