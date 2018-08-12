<?php

namespace Addon\ajax\Controller;

/**
 * 小的工具类控制器
 * Class ToolController
 * @package Addon\ajax\Controller
 */
class ToolController extends BaseController
{
    /**
     *用于生成验证码
     */
    public function verify()
    {
        $config =    array(
            'fontSize'    =>    30,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
            //'useNoise'    =>    false, // 关闭验证码杂点
        );
        $Verify =  new \Think\Verify($config);
        $Verify->entry();
    }

    //获取短息验证码
    public function get_sms_code()
    {
        $phone=I('phone');
        $areaCode=I('area_code');
        $verifyCode=I('verify_code');
        $type=I('type','log');

        if(empty($areaCode))
        {
            $this->ajaxCallMsg('200','请填写区号!');
        }

        $pattern='/^1[2-9]\d{9}$/';
        if(!preg_match($pattern,$phone))
        {
            $this->ajaxCallMsg('200','请输入正确的手机号!');
        }

        if(!check_verify($verifyCode))
        {
            $this->ajaxCallMsg('200','图像验证码错误!');
        }

        if(!in_array($type,array('log','reg','find','bind','check')))
        {
            $this->ajaxCallMsg('500','错误的操作类型!');
        }

        $sSms=SVC("Sms");
        $re=$sSms->sendSmsCode($type,$phone,$areaCode);
        if($re===false)
        {
            $error=$sSms->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'],$error['msg']);
        }

        $this->ajaxCallMsg('0','短信发送成功!');
    }

    //短息验证码验证接口
    public function check_sms_code()
    {
        $phone=I('phone');
        $areaCode=I('area_code');
        $smsCode=I('sms_code');
        $type=I('type','log');


        if(empty($areaCode))
        {
            $this->ajaxCallMsg('200','请填写区号!');
        }

        $pattern='/^1[2-9]\d{9}$/';
        if(!preg_match($pattern,$phone))
        {
            $this->ajaxCallMsg('200','请输入正确的手机号!');
        }

        if(!in_array($type,array('log','reg','find','bind','check')))
        {
            $this->ajaxCallMsg('500','错误的操作类型!');
        }

        $sSms=SVC("Sms");
        $re=$sSms->checkSmsCode($type,$phone,$smsCode);
        if($re===false)
        {
            $error=$sSms->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'],$error['msg']);
        }

        $this->ajaxCallMsg('0','succ.');
    }
}