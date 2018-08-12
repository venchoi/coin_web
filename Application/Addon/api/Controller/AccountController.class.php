<?php
namespace Addon\api\Controller;

class AccountController extends BaseController
{
    //检查手机号是否注册
    public function check_account()
    {
        $params=$this->params;
        $areaCode = $params['area_code'];
        $account=$params['account'];
        $sAccount=SVC("Account");

        //检测登陆类别
        $logAccountType=$sAccount->checkAccountType($account,$params);

        $re=$sAccount->checkAccount($account,$logAccountType,$areaCode);

        $data=array('type'=>2,'nick'=>'');
        if($re)
        {
            $data['type']=1;
            $data['nick']=$re['nick'];

            $this->apiOutputData("0",'succ',$data);
        }

        $this->apiOutputData("0",'succ.',$data);
    }

    //用户登录接口1密码2短信验证
	public function login()
	{
        $sAccount=SVC("Account");
        $params=$this->params;

        //检测用户账号类别，返回账号信息
		$account=$params['account'];
        $logAccountType=$sAccount->checkAccountType($account,$params);

        if($logAccountType==1)
        {
            $rule=array(
                array('phone','regex','200','请输入正确的手机号！','/^1[2-9]\d{9}$/'),
                array('area_code','must','200','请输入区号！'),
            );
        }
        else
        {
            $rule=array(
                array('email','must','200','请输入邮箱！'),
            );
        }

        if($params['type']==1)
        {
            $checkRule=array(
                array('password','must','200','请输入密码！'),
                array('type','must','200','登录方式错误！'),
            );
        }
        else
        {
            $checkRule=array(
                array('password','must','200','请输入短信登录！'),
                array('type','must','200','登录方式错误！'),
            );
        }

        $checkRule=array_merge($rule,$checkRule);

        $this->checkParams($checkRule,$params);

		$re=$sAccount->handleAccountLogin($account,$params["password"],$params["type"],$logAccountType,$params["area_code"]);
		if(!$re)
		{
			$msgData=$sAccount->getErrorMsg();
			$this->apiOutputData($msgData['error_code'],$msgData['msg']);
		}

        $data=$sAccount->setUserRedis($re['id']);
		if($data) $data=$sAccount->getAppLoginData($data);

		$this->apiOutputData('0','登录成功!',$data);
	}
	
	//用户注册
	public function register()
    {
        $sAccount=SVC("Account");
        $params=$this->params;

        $checkRule=array(
            array('area_code','must','200','请输入区号！'),
            array('phone','regex','200','请输入正确的手机号！','/^1[2-9]\d{9}$/'),
            array('password','must','200','请输入密码！'),
            array('password','regex','200','密码必须包含字母和数字！','/.*(\d+.*[a-zA-Z]+|[a-zA-Z]+.*\d+).*/'),
        );
        $this->checkParams($checkRule);

        $sSms=SVC("Sms");
        $re=$sSms->checkSmsCode("reg",$params['phone'],$params['sms_code']);
        if($re===false)
        {
            $this->apiOutputData("200","短信登录错误！！");
        }

        $params['client']=$params['device'];
        if(!$params)
        {
            $params['client']='android';
        }

        $re=$sAccount->handleUserRegister($params);
        if($re===false)
        {
            $error=$sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'],$error['msg']);
        }

        $data=$sAccount->setUserRedis($re);
        if($data) $data=$sAccount->getAppLoginData($data);

        $this->apiOutputData('0','注册成功!',$data);
    }

    //账号密码重置
    public function reset_password()
    {
        $sAccount=SVC("Account");
        $params=$this->params;

        $checkRule=array(
            array('area_code','must','200','请输入区号！'),
            array('phone','must','200','请输入手机号！'),
            array('phone','regex','200','请输入正确的手机号！','/^1[2-9]\d{9}$/'),
            array('password','must','200','请输入密码！'),
            array('password','regex','200','密码必须包含字母和数字！','/.*(\d+.*[a-zA-Z]+|[a-zA-Z]+.*\d+).*/'),
            array('repassword','confirm','200','两次密码输入不一致！','password'),
        );
        $this->checkParams($checkRule);

        $re=$sAccount->resetPassword($params['phone'],$params['password'],1,$params['area_code']);
        if($re===false)
        {
            $error=$sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'],$error['msg']);
        }

        $this->apiOutputData('0','密码修改成功!');
    }

    //获取用户信息
    public function get_user_info()
    {
        $userInfo=get_current_user_data();
        if(!$userInfo)
        {
            $userInfo=array();
        }
        unset($userInfo['uid']);

        $this->apiOutputData('0','succ.',$userInfo);
    }

    //更该头像
    public function change_head_pic()
    {
        $userData = get_current_user_data();
        if(!$userData)
        {
            $this->apiOutputData('402', '未登录');//
        }

        $sAccount = SVC('Account');
        $re = $sAccount->changeHeadpic();
        if(!$re)
        {
            $error = $sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'], $error['msg']);//修改失败
        }
        else
        {
            $data['url'] = $re;
            $this->apiOutputData('0', 'success', $data);//成功设置
        }
    }

	//更新用户基本信息
    public function update_user_basic_info()
    {
        $params=$this->params;
        $userInfo=get_current_user_data();
        $uid=$userInfo['uid'];
        if(!$uid)
        {
            $this->apiOutputData('403','该用户未登陆!');
        }

        if(empty($params['nick']))
        {
            $this->apiOutputData('200','请输入昵称!');
        }

        $sAccount=SVC("Account");
        $re=$sAccount->updateUserBasicInfo($uid,$params);
        if(!$re)
        {
            $msgData=$sAccount->getErrorMsg();
            $this->apiOutputData($msgData['error_code'],$msgData['msg']);
        }

        $this->apiOutputData('0','修改成功!');
    }

    //账户信息管理
    public function update_user_account_info()
    {
        $userInfo = get_current_user_data();
        $uid=$userInfo['uid'];
        if(!$uid)
        {
            $this->apiOutputData('400','该用户未登录！');
        }

        $sAccount=SVC("Account");
        $params=$this->params;

        $checkRule=array(
            array('phone','must','200','请输入手机号！'),
            array('phone','regex','200','请输入正确的手机号！','/^1[2-9]\d{9}$/'),
            array('email','regex','200','请输入正确的邮箱！','/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/'),
        );
        $this->checkParams($checkRule);

        $re = $sAccount->updateUserAccountInfo($params['id'],$params);
        if($re===false)
        {
            $error=$sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'],$error['msg']);
        }

        $this->apiOutputData('0','修改成功!');
    }

    //退出登陆
    public function logout()
    {
        clear_current_user_data();
        sleep(1);
        $this->apiOutputData('0','succ');//用户退出成功
    }


    //登录后，若没有设置密码，进行密码设置
    public function create_password()
    {
        $userInfo = get_current_user_data();
        $uid = $userInfo['uid'];
        if (!$uid)
        {
            $this->apiOutputData('403', '该用户未登录！');
        }

        $sAccount = SVC('Account');
        $params=$this->params;

        $checkRule=array(
            array('password','must','200','请输入密码！'),
            array('password','regex','200','密码必须包含字母和数字！','/.*(\d+.*[a-zA-Z]+|[a-zA-Z]+.*\d+).*/'),
            array('repassword','confirm','200','两次密码输入不一致！','password'),
        );
        $this->checkParams($checkRule);

        $re = $sAccount->createUserAccountPassword($uid, $params);

        if ($re === false)
        {
            $error = $sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'], $error['msg']);
        }

        $this->apiOutputData('0', '密码设置成功!');
    }


    //登陆后，在个人页面进行密码修改（需输入原来密码）
    public function update_password()
    {
        $userInfo = get_current_user_data();
        $params = $this->params;
        if (!$userInfo['uid'])
        {
            $this->apiOutputData('403', '该用户未登录！');
        }

        $sAccount = SVC("Account");
        $sUser= SVC("User");
        $userInfo=$sUser->getUserInfoByUid($userInfo['uid']);
        $userInfo=$userInfo[0];

        if(!$userInfo)
        {
            $this->apiOutputData('400', '该用户不存在');
        }

        $passwordSalt = $userInfo['password_salt'];
        $oldpassword = getMsEncrypt($params['old_password'], $passwordSalt);

        if ($oldpassword !== ($userInfo['password']))
        {
            $this->apiOutputData('200', '原密码不正确！');
        }

        $checkRule=array(
            array('password','must','200','请输入密码！'),
            array('password','regex','200','密码必须包含字母和数字！','/.*(\d+.*[a-zA-Z]+|[a-zA-Z]+.*\d+).*/'),
            array('repassword','confirm','200','两次密码输入不一致！','password'),
        );
        $this->checkParams($checkRule);

        $re = $sAccount->updateUserAccountPassword($userInfo['id'], $params);
        if ($re === false)
        {
            $error = $sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'], $error['msg']);
        }

        $this->apiOutputData('0', '密码修改成功!');

    }


    //验证手机号
    public function account_bind_phone()
    {
        $sAccount = SVC("Account");
        $params=$this->params;
        $userInfo = get_current_user_data();
        $uid = $userInfo['uid'];

        $checkRule=array(
            array('area_code','must','200','请输入密码！'),
            array('phone','regex','200','密码必须包含字母和数字！','/^1[2-9]\d{9}$/'),
        );
        $this->checkParams($checkRule);

        $sSms = SVC("Sms");

        $re = $sSms->checkSmsCode("bind", $params['phone'], $params['sms_code']);

        if ($re === false)
        {
            $this->apiOutputData("200", "短信登录错误！！");
        }

        $re = $sAccount->handleUserPhoneReset($uid,$params);

        if ($re === false)
        {
            $error = $sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'], $error['msg']);
        }

        $this->apiOutputData('0', '手机绑定成功!');


    }

    //验证原来手机号码
    public function check_user_current_phone()
    {
        $sUser = SVC('User');
        $params=$this->params;

        $userInfo = get_current_user_data();

        $uid = $userInfo['uid'];
        $userInfo = $sUser->getUserInfoByUid($uid);
        $uPhone = $userInfo['mobile'];

        $sSms = SVC("Sms");

        $re = $sSms->checkSmsCode("check", $uPhone, $params['sms_code']);
        if ($re === false)
        {
            $this->apiOutputData("200", "短信登录错误！！");
        }

        $this->apiOutputData("0", "验证手机成功！");

    }

    //修改昵称接口
    public function change_user_nick()
    {
        $params = $this->params;
        $userInfo=get_current_user_data();
        $uid=$userInfo['uid'];
        $where = array('id'=>$uid);
        $save = array('nick'=>$params['nick']);
        $sUser = SVC("User");
        $re = $sUser->saveUserInfo($where,$save);
        $sAccount=SVC("Account");
        if($re===false)
        {
            $error=$sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'],$error['msg']);
        }

        $this->apiOutputData('0','修改成功!');
    }

    //修改简介接口
    public function change_user_info()
    {
        $params = $this->params;
        $userInfo=get_current_user_data();
        $uid=$userInfo['uid'];
        $where = array('id'=>$uid);
        $save = array('info'=>$params['info']);
        $sUser = SVC("User");
        $re = $sUser->saveUserInfo($where,$save);
        $sAccount=SVC("Account");

        if($re===false)
        {
            $error=$sAccount->getErrorMsg();
            $this->apiOutputData($error['error_code'],$error['msg']);
        }

        $this->apiOutputData('0','修改成功!');
    }

    //添加用户反馈
    public function add_user_view()
    {
        $params=$this->params;
        $userInfo = get_current_user_data();
        $uid = $userInfo['uid'];
        if(!$uid)
        {
            $uid=0;
        }

        $sAccount = SVC('Account');
        $re=$sAccount->addUserView($uid,$params);
        if($re)
        {
            $sUpload=SVC("Upload");
            $sUpload->uploadUserViewImage($re,'img_file',2);

            $this->apiOutputData('0','反馈成功');
        }

        $this->apiOutputData('500','反馈失败');
    }
}