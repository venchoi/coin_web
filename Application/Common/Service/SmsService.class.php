<?php

namespace Common\Service;

/**
 * 短信服务层
 * Class SmsService
 * @package Common\Service
 */
class SmsService extends BaseService
{
    private $smsTpl=array(
        'log'=>'【POPCOIN】您的登录短信验证码是%s,短信验证码10分钟内有效',
        'reg'=>'【POPCOIN】您的注册短信验证码是%s,短信验证码10分钟内有效',
        'find'=>'【POPCOIN】您的找回密码的短信验证码是%s,短信验证码10分钟内有效',
        'bind'=>'【POPCOIN】您的绑定短信验证码是%s,短信验证码10分钟内有效',
        'check'=>'【POPCOIN】您的验证短信验证码是%s,短信验证码10分钟内有效',
    );

    //短信码验证
    function checkSmsCode($smsType,$phone,$code)
    {
        if(!$code)
        {
            $this->setErrorMsg('200','请输入短信验证码！');
            return false;
        }

        //写到redis
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(1);

        $key=sprintf("sms_code:%s:%s",$smsType,$phone);
        $checkCodeArr=$redisClient->hMget($key,array('sms_code','count'));
        if(!empty($checkCodeArr['sms_code']))
        {
            if($checkCodeArr['count']+1>3)
            {
                $redisClient->delete($key);   //连续试错3次，删除验证码

                $this->setErrorMsg('400','短信验证码已经失效！！');
                return false;
            }

            if($code==$checkCodeArr['sms_code'])
            {
                //$redisClient->delete($key);   //验证成功，删除验证码

                return true;
            }

            //增加1
            if($checkCodeArr['sms_code'])
                $redisClient->hIncrBy($key, 'count', 1);
        }

        $this->setErrorMsg('400','短信验证码错误！！');
        return false;
    }

    //短信发送处理
    public function sendSmsCode($smsType,$phone,$areaCode)
    {
        if(!$this->sendCheckUserPhone($smsType,$phone,$areaCode))
        {
            return false;
        }

        //写到redis
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(1);

        $code=rand(100000,999999);
        $key=sprintf("sms_code:%s:%s",$smsType,$phone);
        $checkCode=$redisClient->hMset($key,array('sms_code'=>$code,'count'=>0));
        $redisClient->expire($key,600);
        if(!$checkCode)
        {
            $this->setErrorMsg('500','系统错误！！');
            return false;
        }

        //短信发送
        $re=$this->way1SendSms($phone,$code,$smsType);
        if($re['result'])
        {
            $this->setErrorMsg('500','系统错误,短信码发送失败！！');
            return false;
        }

        return true;
    }

    public function sendCheckUserPhone($smsType,$phone,$areaCode)
    {
        if(in_array($smsType,array('log')))
        {
            $dUserInfo = D("UserInfo");
            $re=$dUserInfo->checkPhoneExist($phone,$areaCode);
            if (!$re)
            {
                $this->setErrorMsg('400','该手机号未注册！');
                return false;
            }
        }

        if (in_array($smsType,array('reg')))
        {
            $dUserInfo = D("UserInfo");
            $re=$dUserInfo->checkPhoneExist($phone,$areaCode);
            if ($re)
            {
                $this->setErrorMsg('400','该手机号已注册！');
                return false;
            }
        }

        return true;
    }

    //方式一，发送短息
    private function way1SendSms($phone,$smsCode,$type)
    {
        $smsTpl=$this->smsTpl;

        $time=milltime();
        $data=array(
            "phone"=>$phone,
            "msgcont"=>sprintf($smsTpl[$type],$smsCode),
            "spnumber"=>'75',
            "username"=>'szjx',
            "sendtime"=>$time,
            "password"=>'1304e3',
            "order"=>'sendsms'
        );

        $data['password']=strtolower(md5(urlencode($data['username'].$data['phone'].$data['msgcont'].$data['spnumber'].$data['sendtime'].$data['password'])));

        $url='http://api.shortmsg.cn/interface.do?'.http_build_query($data);

        $content=curl_get($url);

        $postObj = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

        $jsonStr = json_encode($postObj);
        $jsonArray = json_decode($jsonStr,true);

        return $jsonArray;
    }
}