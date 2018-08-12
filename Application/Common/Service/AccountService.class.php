<?php

namespace Common\Service;

/**
 * 账号相关服务层(账号服务,账号登陆检测等)
 * Class AccountService
 * @package Common\Service
 */
class AccountService extends BaseService
{
	//登录账号检测
    public function checkAccountType($account,&$params)
    {
		$pattern='/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i';
		if(preg_match($pattern,$account))
		{
            $params['email']=$params['account'];
            unset($params['account']);
			return 2;		//邮箱登录
		}

        $params['phone']=$params['account'];
        unset($params['account']);

        return 1;		//手机登录
    }

    public function checkAccount($account,$logAccountType,$mobileAreaCode)
    {
        if(!$logAccountType)
        {
            $logAccountType = 3;
        }

        $logAccountField=array(1=>'mobile',2=>'email',3=>'username');

        $dUserInfo=D("UserInfo");
        $where=array(
            $logAccountField[$logAccountType]=>$account,
            'state'=>1
        );
        if($logAccountType==1) $where['mobile_area_code']=$mobileAreaCode;
        $userInfo=$dUserInfo->getUserInfoByWhere($where);

        if(!$userInfo)
        {
            return array();
        }
        return $userInfo;
    }

	//处理用户登录
	public function handleAccountLogin($account,$password,$type,$logAccountType,$mobileAreaCode)
	{
		if(!$logAccountType)
        {
            $logAccountType = 3;
        }

		$logAccountField=array(1=>'mobile',2=>'email',3=>'username');
		$chAccountType=array(1=>'手机',2=>'邮箱',3=>'用户名');
		
		$dUserInfo=D("UserInfo");

		$where=array(
			$logAccountField[$logAccountType]=>$account,
			'state'=>1
		);
		if($logAccountType==1) $where['mobile_area_code']=$mobileAreaCode;
		$userInfo=$dUserInfo->getUserInfoByWhere($where);

		//如果该账号是手机登录，且未注册进行快速注册，否则进行登录验证
		if(!$userInfo && $logAccountType==1 && $type==2)
		{
            $params=array(
                'area_code'=>$mobileAreaCode,
                'phone'=>$account,
                'password'=>$password,
                'logType'=>$type,
            );

		    $re=$this->handleUserRegister($params,true);

		    $where=array('id'=>$re);
		    $userInfo=$dUserInfo->getUserInfoByWhere($where);
			if(!$userInfo)
            {
                $this->setErrorMsg("500","快速注册失败，请稍后再试!");
                return false;
            }
		}
		else
        {
            if($type==1)
            {
                $checkPass=getMsEncrypt($password,$userInfo['password_salt']);
                if($checkPass != $userInfo['password'])
                {
                    $this->setErrorMsg("400","密码错误!");
                    return false;
                }
            }
            else
            {
                $sSms=SVC("Sms");
                $re=$sSms->checkSmsCode("log",$account,$password);
                if($re===false)
                {
                    $this->setErrorMsg("400","短信验证码错误！！");
                    return false;
                }
            }
        }

		$where=array('id'=>$userInfo['id']);
		$dUserInfo->updateUserLogStatus($where);

		return $userInfo;
	}
	
	//账号密码重设
	public function resetPassword($phone,$password,$type,$areaCode)
	{
		$dUserInfo=D("UserInfo");
        $where=array();
        $re=array();

		if($type==1)
		{
            $where = array('mobile' => $phone, 'mobile_area_code' => $areaCode,'state'=>1);
            $re = $dUserInfo->where($where)->field('id,password_salt')->find();
        }

        if(!$re)
        {
            $this->setErrorMsg("400","该账号未注册");
            return false;
        }

        $passwordSalt=$re['password_salt'];
        if(!$passwordSalt)
        {
            $passwordSalt=rand(1000,9999);
        }

        $save=array('password'=>getMsEncrypt($password,$passwordSalt),'password_salt'=>$passwordSalt);
        $dUserInfo->updateUserInfoByWhere($where,$save);

        return true;
	}

	//处理用户注册
	public function handleUserRegister($params,$fastReg=false)
    {
        $dUserInfo=D("UserInfo");

        $re=$dUserInfo->checkFieldUnique(array('mobile','mobile_area_code'),array($params['phone'],$params['area_code']));
        if(!$re)
        {
            $this->setErrorMsg('400','该手机号已经注册！');
            return false;
        }

        $re=$dUserInfo->checkFieldUnique('email',$params['email']);
        if(!$re)
        {
            $this->setErrorMsg('400','该邮箱已经注册！');
            return false;
        }

        $re=$dUserInfo->checkFieldUnique('username',$params['username']);
        if(!$re)
        {
            $this->setErrorMsg('400','该用户名已经注册！');
            return false;
        }

        if(!$params['client']) $params['client']='pc';
        if(is_wechat()) $params['way']='wechat';
        if(!$params['way']) $params['way']='seo';

        $channel=sprintf("%s_%s_%s",$params['client'],$params['way'],'other');

        //快速注册参数设置
        $password='';
        $salt='';
        if(!$fastReg && $params['logType']!=2)
        {
            $salt=rand(1000,9999);
            $password=getMsEncrypt($params['password'],$salt);
        }

        $data=array(
            'uuid'=>uniqid("uid"),
            'username'=>'pop'.trim($params['area_code'],'+').$params['phone'],
            'password'=>$password,
            'password_salt'=>$salt,
            'nick'=>'pop'.time().'_'.rand(0,99),
            'head_pic_url'=>'',
            'mobile_area_code'=>(string)$params['area_code'],
            'mobile'=>(string)$params['phone'],
            'channel'=>$channel,
            'create_time'=>get_fulltime(),
            'update_time'=>get_fulltime(),
            'update_ip'=>get_client_addr(),
            'from_uid'=>(int)$params['from_uid'],
            'state'=>1,
        );

        $re=$dUserInfo->add($data);

        $uuid=$this->createUuid($re);
        $where=array('id'=>$re);
        $save=array('uuid'=>$uuid);
        $dUserInfo->updateUserInfo($where,$save);

        return $re;
    }

    //登陆数据cookie-redis方式处理
    public function setUserRedis($uid, $isUpdate = false, $remember=1)
    {
        $dUserInfo = D('UserInfo');
        $userInfo = $dUserInfo->getUserInfoById($uid);
        if(!$userInfo)
        {
            return false;
        }

        $str = substr($userInfo['phone'], 0, 3);
        if($str == '128')
        {
            $userInfo['phone'] = '';
        }

        $data = array(
            'uid' => $userInfo['id'],
            'uuid'=> $userInfo['uuid'],
            'phone' => phoneSecret($userInfo['mobile']),
            'email' => $userInfo['email'],
            'client_id' => $userInfo['id'] * 382 + 618,
            'months' => ceil((time() - strtotime($userInfo['create_time'])) / (30 * 86400)),
            'nickname' => $userInfo['nick'],
            'image_url' => $userInfo['head_pic_url'],
            'info'=>$userInfo['info'],
            'invite_code'=>base_convert($userInfo['id'],10,36)
        );
   
        if($isUpdate)
        {
            $data['token'] = update_user_data($userInfo['id'], $data);
        }
        else
        {
            $data['token'] = set_current_user_data($data, intval($remember)*29*86400+86400);
        }

        return $data;
    }

    //更新用户登录信息
    public function updateUserRedis()
    {
        $uid = get_current_user_id();
        if($uid)
        {
            $is_update = get_update_state($uid);
            if($is_update)
            {
                $this->setUserRedis($uid, true);
            }
        }

        return true;
    }

    //根据规则生成uuid
    public function createUuid($uid)
    {
        $octId=base_convert($uid,10,8);
        $len=strlen($octId);
        if($len<4)
        {
            $octId=(string)str_pad($octId,4,'0',STR_PAD_LEFT);
        }

        $uuid=sprintf("pop%s%s%s",substr($octId,0,2),$uid,substr($octId,-2));

        return $uuid;
    }

    //获取移动端登录数据
    public function getAppLoginData($data)
    {
        $data = array(
            'uuid'=> $data['uuid'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'client_id' => $data['client_id'],
            'months' => $data['months'],
            'nickname' => $data['nickname'],
            'head_pic_url' => $data['image_url'],
            'info'=>$data['info'],
            'invite_code'=>$data['invite_code'],
            'token'=>$data['token']
        );

        $sToken=SVC("Token");
        $sToken->setToken($data['uuid'],$data['token']);

        return $data;
    }

    //更新用户基本信息
    public function updateUserBasicInfo($uid,$data)
    {
        //用户信息更新
        $dUserInfo=D("UserInfo");
        $data=array(
            'id'=>$uid,
            'nick'=>$data['nick'],
            'info'=>(string)$data['info']
        );

        $re=$dUserInfo->save($data);

        if($re)
        {
            return true;
        }
        return false;
    }

	//更改用户数据
    public function updateUserAccountInfo($uid,$params)
    {
        $dUserInfo=D("UserInfo");
        $data=array(
            'id' => $uid,
            'mobile' => $params['phone'],
            'email' =>$params['email'],
            'wechat' =>(string) $params['wechat'],
            'weibo' => (string)$params['weibo'],
            'twitter' =>(string)$params['twitter']
        );

        $re=$dUserInfo->save($data);

        if($re)
        {
            return true;
        }
        return false;
    }


    //上传头像
    public function uploadHeadpic($savename = 'logodefault', $image_qua = '', $uid = '')
    {
        $upload = new \Think\Upload();
        //TODO 需要判定图片格式并转化为统一格式
        $upload->maxSize = 3145728;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath = '../Runtime/';
        $upload->savePath = 'Headpic/';
        $upload->autoSub = false;
        $upload->saveName = $savename;//logo+userid
        // 上传文件

        $info = $upload->uploadOne($_FILES['head_pic']);


        $path = '../Runtime/Headpic/' . $info['savename'];
        if(I('image_qua'))
        {
            $size = I('image_qua', array(), 'intval');
            if((!$size[0] && !$size[1]) || (!$size[1] && !$size[2]) || (!$size[2] && !$size[3]) || (!$size[3] && !$size[0]))
            {
                $this->setErrorMsg('208', '无效的裁剪尺寸!!');//修改头像失败
                return false;
            }

            //$size=array(12,34,23,33);
            $image = new \Think\Image(1,$path);


            //将图片裁剪为400x400并保存为corp.jpg
            $image->crop($size[2], $size[3], $size[0], $size[1])->save($path);
        }

        $filedata = array(
            'head_pic' => '@' . realpath($path) . ";type=" . $info['type'] . ";filename=" . $info['savename'],
            'key'=>'head_pic',
            'type'=>'head_pic',
            'filename'=>'logo_'.$uid.'.jpg',
            'token'=>'aliyun28198288',
        );

        $re = curl_post(C('ALIY_IMG_UPLOAD_URL'), $filedata);
        $url = json_decode($re, true);

        if($url['error_code'] == 0 && $re)
        {
            $line = $url['url'];

            $reline = $line . '?' . rand(10000,99999);
            // 更新头像
            $mUserInfo = D('UserInfo');
            if(!$uid)
            {
                $userData = get_current_user_data();
                $uid = $userData['uid'];
            }
            $where['id'] = $uid;

            $save['head_pic_url'] = $line;
            if($where['id'])
            {
                $mUserInfo->updateUserInfo($where, $save);
            }

            $this->setUserRedis($where['id'], true);

            return $reline;
        }
        else
        {
            $this->setErrorMsg('208', '头像上传失败，请稍候重试');//修改头像失败
            return false;
        }
    }

    //更改头像
    public function changeHeadpic($uid = '')
    {
        $userData = get_current_user_data();

        if(!$userData['uid'])
        {
            $userData['uid'] = $uid;
        }

        // 判断是否有重复图片，有则删除
        $filename = '../Runtime/Headpic/logo' . $userData['uid'];
        $ext = null;
        if(file_exists($filename . '.png'))
        {
            $ext = '.png';
        }
        elseif(file_exists($filename . '.gif'))
        {
            $ext = '.gif';
        }
        elseif(file_exists($filename . '.jpg'))
        {
            $ext = '.jpg';
        }
        if($ext)
        {
            unlink($filename . $ext);
        }
        $re = $this->uploadHeadpic('logo' . $userData['uid'], '', $userData['uid']);

        if($re)
        {
            return true;
        }
        return false;
    }

    //没有密码用户增加密码
    public function createUserAccountPassword($uid,$params)
    {
        $dUserInfo = D("UserInfo");
        $password = $params['password'];
        $passwordSalt = rand(1000,9999);
        $save =array(
            'id'=>$uid,
            'password'=>getMsEncrypt($password,$passwordSalt),
            'password_salt'=>$passwordSalt
        );
        //$re = $dUserInfo->updateUserInfoByWhere($where,$save);
        $re = $dUserInfo->save($save);
        if($re)
        {
            return true;
        }
        return false;
    }


    //更改用户密码
    public function updateUserAccountPassword($uid,$params)
    {
        $dUserInfo=D("UserInfo");
        $password=$params['password'];
        $passwordSalt=rand(1000,9999);

        $save=array(
            'id'=>$uid,
            'password'=>getMsEncrypt($password,$passwordSalt),
            'password_salt'=>$passwordSalt
        );
        $re = $dUserInfo->save($save);

        if($re)
        {
            return true;
        }
        return false;
    }

    //绑定用户手机
    public function handleUserPhoneReset($uid,$params)
    {
        $dUserInfo=D("UserInfo");
        $phone = $params['phone'];


        $save = array(
            'id'=>$uid,
            'mobile'=>$phone
        );


        $re = $dUserInfo->save($save);

        if ($re ===0)
        {
            $this->setErrorMsg('200','该手机号未改变！');
        }

        if($re)
        {
            return true;
        }
        return false;

    }

    public function addUserView($uid,$params)
    {
        $dUserView=D('UserView');
        $re=$dUserView->addUserViewToSolve($uid,$params);
        if(!$re)
        {
            return array();
        }
        return $re;
    }

}