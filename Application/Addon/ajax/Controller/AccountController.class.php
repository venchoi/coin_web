<?php
namespace Addon\ajax\Controller;

class AccountController extends BaseController
{
	public function _initialize()
	{
		parent::_initialize();
	}
	
	
	

    //用户登录接口1密码2短信验证
	public function login()
	{
        $sAccount=SVC("Account");
        $dUserInfo=D("UserInfo");
        $params=I('');


        //检测用户账号类别，返回账号信息
		$account=$params['account'];
        $logAccountType=$sAccount->checkAccountType($account,$params);

        if($logAccountType==1)
        {
            $rule=array(
                array('phone','/^1[2-9]\d{9}$/','请输入正确的手机号！',1,'regex',3),
                array('area_code','1,10','请输入区号！',1,'length',3),
            );
        }
        else
        {
            $rule=array(
                array('email','require','请输入邮箱！',1,'',3),
            );
        }

        if($params['type']==1)
        {
            $checkRule=array(
                array('password','require','请输入密码！',1,'',3),
//                array('password','6,16','密码长度为6-16位！',1,'length',3),
                array('type',array(1,5),'登录方式错误!',1,'between',3)
            );
        }
        else
        {
            $checkRule=array(
                array('password','require','请输入短信验证码！',1,'',3),
                array('type',array(1,5),'登录方式错误!',1,'between',3)
            );
        }

        $checkRule=array_merge($rule,$checkRule);

        $re=$dUserInfo->validate($checkRule)->create($params);
        if($re===false)
        {
            $error=$dUserInfo->getError();
            $this->ajaxCallMsg("200",$error);
        }

		$re=$sAccount->handleAccountLogin($account,$params["password"],$params["type"],$logAccountType,$params["area_code"]);
		if(!$re)
		{
			$msgData=$sAccount->getErrorMsg();
			$this->ajaxCallMsg($msgData['error_code'],$msgData['msg']);
		}

        $sAccount->setUserRedis($re['id']);
		$this->ajaxCallMsg('0','登录成功!');
	}
	
	//用户注册
	public function register()
    {
        $sAccount=SVC("Account");
        $dUserInfo=D("UserInfo");
        $params=I('');

        $checkRule=array(
            array('area_code','1,10','请输入区号！',1,'length',3),
            array('phone','/^1[2-9]\d{9}$/','请输入正确的手机号！',1,'regex',3),
            array('password','require','请输入密码！',1,'',3),
//            array('password','6,16','密码长度为6-16位！',1,'length',3),
            array('password','/.*(\d+.*[a-zA-Z]+|[a-zA-Z]+.*\d+).*/','密码必须包含字母和数字！',1,'regex',3),
        );
        $re=$dUserInfo->validate($checkRule)->create($params);
        if($re===false)
        {
            $error=$dUserInfo->getError();
            $this->ajaxCallMsg("200",$error);
        }

        $sSms=SVC("Sms");
        $re=$sSms->checkSmsCode("reg",$params['phone'],$params['sms_code']);
        //dump($re);die;
        if($re===false)
        {
            $this->ajaxCallMsg("400","短信验证码错误！！");
        }

        $re=$sAccount->handleUserRegister($params);
        if($re===false)
        {
            $error=$sAccount->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'],$error['msg']);
        }

        $sAccount->setUserRedis($re);
        $this->ajaxCallMsg('0','注册成功!');
    }

    //账号密码重置
    public function reset_password()
    {
        $sAccount=SVC("Account");
        $dUserInfo=D("UserInfo");
        $params=I('');

        $checkRule=array(
            array('area_code','1,10','请输入区号！',1,'length',3),
            array('phone','require','请输入手机号！',1,'',3),
            array('phone','/^1[2-9]\d{9}$/','请输入正确的手机号！',1,'regex',3),
            array('password','require','请输入密码！',1,'',3),
//            array('password','6,16','密码长度为6-16位！',1,'length',3),
            array('repassword','password','两次密码输入不一致！',1,'confirm',3),
            array('password','/.*(\d+.*[a-zA-Z]+|[a-zA-Z]+.*\d+).*/','密码必须包含字母和数字！',1,'regex',3),
        );
        $re=$dUserInfo->validate($checkRule)->create($params);
        if($re===false)
        {
            $error=$dUserInfo->getError();
            $this->ajaxCallMsg("200",$error);
        }

        $re=$sAccount->resetPassword($params['phone'],$params['password'],1,$params['area_code']);
        if($re===false)
        {
            $error=$sAccount->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'],$error['msg']);
        }

        $this->ajaxCallMsg('0','密码修改成功!');
    }

    //获取用户信息
    public function get_user_info()
    {
        $userInfo=get_current_user_data();
        $userInfo=$userInfo?:array();
        unset($userInfo['uid']);
        unset($userInfo['info']);

        $this->ajaxCallMsg('0','succ.',$userInfo);
    }

    //更该头像
    public function change_head_pic()
    {
        $userData = get_current_user_data();
        if(!$userData)
        {
            $this->ajaxCallMsg('402', '未登录');//
        }

        $sAccount = SVC('Account');
        $imageQua = I('image_qua');
        $re = $sAccount->changeHeadpic($imageQua);
        if(!$re)
        {
            $error = $sAccount->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'], $error['msg']);//修改失败
        }
        else
        {
            $data['url'] = $re;
            $this->ajaxCallMsg('0', 'success', $data);//成功设置
        }
    }

	//更新用户基本信息
    public function update_user_basic_info()
    {
        $params=I('');
        $userInfo=get_current_user_data();
        $uid=$userInfo['uid'];
        if(!$uid)
        {
            $this->ajaxCallMsg('400','该用户未登录!');
        }

        if(empty($params['nick']))
        {
            $this->ajaxCallMsg('200','请输入昵称!');
        }

        $sAccount=SVC("Account");
        $re=$sAccount->updateUserBasicInfo($uid,$params);
        if($re===false)
        {
            $msgData=$sAccount->getErrorMsg();
            $this->ajaxCallMsg($msgData['error_code'],$msgData['msg']);
        }

        $this->ajaxCallMsg('0','修改成功!');
    }

    //账户信息管理
    public function update_user_account_info()
    {
        $userInfo = get_current_user_data();
        $uid=$userInfo['uid'];
        if(!$uid)
        {
            $this->ajaxCallMsg('400','该用户未登录！');
        }

        $sAccount=SVC("Account");
        $dUserInfo=D("UserInfo");
        $params=I('');

        $checkRule=array(
            array('phone','require','请输入手机号！',1,'',3),
            array('phone','/^1[2-9]\d{9}$/','请输入正确的手机号！',1,'regex',3),
            array('email','/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/','请输入正确的邮箱！',1,'regex',3),
        );

        $re=$dUserInfo->validate($checkRule)->create($params);
        if($re===false)
        {
            $error=$dUserInfo->getError();
            $this->ajaxCallMsg("400",$error);
        }

        $re = $sAccount->updateUserAccountInfo($params['id'],$params);
        if($re===false)
        {
            $error=$sAccount->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'],$error['msg']);
        }

        $this->ajaxCallMsg('0','修改成功!');
    }

    //退出登录
    public function logout()
    {
        clear_current_user_data();
        sleep(1);
        $this->ajaxCallMsg('0','succ');//用户退出成功
    }


    //登录后，若没有设置密码，进行密码设置
    public function create_password()
    {
        $dUserInfo = D('UserInfo');
        $userInfo = get_current_user_data();
        //$userInfo = $dUserInfo->getUserInfoById(20);
        $uid = $userInfo['uid'];

        if (!$uid) {
            $this->ajaxCallMsg('400', '该用户未登录！');
        }

        $sAccount = SVC('Account');
        $params = I('');
        $checkRule = array(
//            array('password', '6,16', '请输入6位长度以上的密码！', 1, 'length', 3),
            array('repassword', 'password', '两次密码输入不一致！', 1, 'confirm', 3),
            array('password', '/.*(\d+.*[a-zA-Z]+|[a-zA-Z]+.*\d+).*/', '密码必须包含字母和数字！', 1, 'regex', 3),
        );
        $re = $dUserInfo->validate($checkRule)->create($params);
        if ($re === false) {
            $error = $dUserInfo->getError();
            $this->ajaxCallMsg("400", $error);
        }

        $re = $sAccount->createUserAccountPassword($uid, $params);

        if ($re === false) {
            $error = $sAccount->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'], $error['msg']);
        }


        $this->ajaxCallMsg('0', '密码设置成功!');


    }


    //登录后，在个人页面进行密码修改（需输入原来密码）
    public function update_password()
    {

        $dUserInfo = D('UserInfo');
        $userInfo = get_current_user_data();
        //$userInfo = $dUserInfo->getUserInfoById(20);
        if (!$userInfo['uid'])
        {
            $this->ajaxCallMsg('400', '该用户未登录！');
        }

        $userInfo = $dUserInfo->getUserInfoById($userInfo['uid']);
        if(!$userInfo)
        {
            $this->ajaxCallMsg('400', '该用户不存在');
        }



        $params = I('');
        $sAccount = SVC("Account");
        $passwordSalt = $userInfo['password_salt'];
        $oldpassword = getMsEncrypt($params['old_password'], $passwordSalt);



        if ($oldpassword !== ($userInfo['password']))
        {
            $this->ajaxCallMsg('400', '原密码不正确！');
        }

        $checkRule = array(
            array('old_password', 'require', '请输入原来密码！', 1, '', 3),
//            array('password', '6,16', '请输入6-16位长度以上的密码！', 1, 'length', 3),
            array('repassword', 'password', '两次密码输入不一致！', 1, 'confirm', 3),
            array('password', '/.*(\d+.*[a-zA-Z]+|[a-zA-Z]+.*\d+).*/', '密码必须包含字母和数字！', 1, 'regex', 3),
        );

        $re = $dUserInfo->validate($checkRule)->create($params);

        if ($re === false)
        {
            $error = $dUserInfo->getError();
            $this->ajaxCallMsg("400", $error);
        }

        $re = $sAccount->updateUserAccountPassword($userInfo['id'], $params);
        if ($re === false)
        {
            $error = $sAccount->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'], $error['msg']);
        }

        $this->ajaxCallMsg('0', '密码修改成功!');

    }


    //验证手机号
    public function account_bind_phone()
    {
        $sAccount = SVC("Account");
        $dUserInfo = D("UserInfo");
        $params = I('');
        $userInfo = get_current_user_data();
        //$userInfo = $dUserInfo->getUserInfoById(9);test
        $uid = $userInfo['uid'];


        $checkRule = array(
            array('area_code', '1,10', '请输入区号！', 1, 'length', 3),
            array('phone', '/^1[2-9]\d{9}$/', '请输入正确的手机号！', 1, 'regex', 3)
        );

        $re = $dUserInfo->validate($checkRule)->create($params);

        if ($re === false)
        {
            $error = $dUserInfo->getError();
            $this->ajaxCallMsg('400', $error);
        }

        $sSms = SVC("Sms");

        $re = $sSms->checkSmsCode("bind", $params['phone'], $params['sms_code']);

        if ($re === false) {
            $this->ajaxCallMsg("400", "短信验证码错误！！");
        }

        $re = $sAccount->handleUserPhoneReset($uid,$params);

        if ($re === false) {
            $error = $sAccount->getErrorMsg();
            $this->ajaxCallMsg($error['error_code'], $error['msg']);
        }

        $this->ajaxCallMsg('0', '手机绑定成功!');


    }

    //验证原来手机号码
    public function check_user_current_phone()
    {
        //$sAccount = SVC('Account');
        $dUserInfo = D('UserInfo');
        $params = I('');

        $userInfo = get_current_user_data();

        $uid = $userInfo['uid'];
        $userInfo = $dUserInfo->getUserInfoById($uid);
        $uPhone = $userInfo['mobile'];

        $sSms = SVC("Sms");

        $re = $sSms->checkSmsCode("check", $uPhone, $params['sms_code']);
        if ($re === false) {
            $this->ajaxCallMsg("400", "短信验证码错误！！");
        }

        $this->ajaxCallMsg("0", "验证手机成功！");

    }

    //发送验证短信验证码
    public function send_check_code()
    {
        $dUserInfo = D('UserInfo');
        $userInfo = get_current_user_data();

        $uid = $userInfo['uid'];
        $userInfo = $dUserInfo->getUserInfoById(16);
        $uPhone = $userInfo['mobile'];

        $sSms = SVC("Sms");
        $re = $sSms->sendSmsCode("check",$uPhone);
        if($re === false)
        {
            $this->ajaxCallMsg("400", "发送验证码错误！！");
        }
        $this->ajaxCallMsg("0", "短信验证码发送成功！");

    }

    //发送绑定短信验证码
    public function send_bind_code()
    {

        $params = I('');

        $sSms = SVC("Sms");
        $re = $sSms->sendSmsCode("bind",$params['phone']);
        if($re === false)
        {
            $this->ajaxCallMsg("400", "发送验证码错误！！");
        }
        $this->ajaxCallMsg("0", "短信验证码发送成功！");

    }



}