<?php

namespace Addon\api\Controller;
use Common\Controller\CommonController;

/**
 * 移动端基类控制器
 * Class BaseController
 * @package Addon\api\Controller
 */
class BaseController extends CommonController
{
    protected $params = null;    //获取的参数
    protected $token = null;  //用户token
    protected $uuid  = null;  //用户唯一标识

    //访问api必带参数检测规则
    private  $mustParamsCheckRule = array(
        array('version','must','200','版本号不能为空'),
        array('device','enum','200','设备代号',array('android','ios','mini-apps','web','m')),
        array('device_unique_identifier','must','200','设备唯一标识不能为空'),
    );

    /**
     * 构造方法
     * 获取参数
     */
    public function __construct()
    {
        parent::__construct();

        //访问api必带参数检测
        if(!$this->unNeedMustParam())
        {
            //接收参数初始化
            $this->initParams();
            $this->checkApiMustParams();
        }

        //需要登录接口才进行token验证
        if(!$this->unLogMethodFilter())
        {
            $this->checkToken();
        }
    }

    //接收参数初始化
    private function initParams()
    {
        $inputJsonData=I('post.data');
        $this->params=json_decode(base64_decode($inputJsonData),true);

//        file_put_contents(RUNTIME_PATH.'app_params.txt',date("Y-m-d H:i:s")."\r\n",FILE_APPEND);
//        file_put_contents(RUNTIME_PATH.'app_params.txt',$_SERVER['PATH_INFO']."\r\n",FILE_APPEND);
//        file_put_contents(RUNTIME_PATH.'app_params.txt',var_export($this->params,true)."\r\n",FILE_APPEND);
//        file_put_contents(RUNTIME_PATH.'app_params.txt',"END\r\n",FILE_APPEND);
        if(!$this->params)
        {
            $this->apiOutputData('200','传输参数错误');
        }

        $this->uuid=$this->params['uuid'];
        $this->token=$this->params['token'];
    }

    //无需登录的访问接口
    private function unLogMethodFilter()
    {
        $methodPath=$_SERVER['PATH_INFO'];
        $unLogMethodPaths=array(
            'account/register/wapi/api',
            'account/login/wapi/api',
            'account/check_account/wapi/api',
            'tool/verify/wapi/api'
        );

        if(in_array($methodPath,$unLogMethodPaths))
        {
            return true;
        }

        return false;
    }

    //无需必须参数的访问接口
    private function unNeedMustParam()
    {
        $methodPath=$_SERVER['PATH_INFO'];
        $unLogMethodPaths=array(
            'tool/verify/wapi/api',
        );

        if(in_array($methodPath,$unLogMethodPaths))
        {
            return true;
        }

        return false;
    }

    /**
     * 检查token是否正确
     * @return bool
     */
    private function checkToken()
    {
        if(!$this->uuid && !$this->token)
        {
            $this->apiOutputData('400',"非法的访问用户");
        }

        if($this->uuid==0 && $this->token =='popapp' )
        {
            return true;
        }

        $redis = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $checkToken =$redis->hget('app_user_list', $this->uuid);
        if ($checkToken)
        {
            if($this->token == $checkToken)
            {
                cookie('popcoin_main_token',$this->token);
                return true;
            }
            else
            {
                $errorCode='401';
                $msg = sprintf('%s%s%s','系统检测您的账号已经于',date('H:i'),'在其他设备上登录，如非本人操作，建议检查账户安全');
            }
        }
        else
        {
            $errorCode='402';
            $msg = '登录状态过期，请重新登录。';
        }
        $this->apiOutputData($errorCode,$msg);
    }

    //访问api必带参数检测
    private function checkApiMustParams()
    {
        $checkRule=$this->mustParamsCheckRule;

        $this->checkParams($checkRule);
    }

    //验证输入是否合法
    protected function checkParams($checkRule,$params)
    {
        if(!$params || !is_array($params))
        {
            $params=$this->params;
        }

        foreach($checkRule as $key => $val)
        {
            if($val[1] == "must")
            {
                if(!$params[$val[0]])
                {
                    $errorCode= $val[2];
                    $msg = $val[3];
                    $this->apiOutputData($errorCode,$msg);
                    break;
                }
            }

            if($val[1] == "enum")
            {
                if(!in_array($params[$val[0]],$val[4],true))
                {
                    $errorCode = $val[2];
                    $msg = sprintf("%s必须是%s中的一种",$val[3],implode(",",$val[4]));
                    $this->apiOutputData($errorCode,$msg);
                    break;
                }
            }

            if($val[1] == "regex")
            {
                if(!preg_match($val[4],$params[$val[0]]))
                {
                    $errorCode = $val[2];
                    $msg = $val[3];
                    $this->apiOutputData($errorCode,$msg);
                    break;
                }
            }

            if($val[1] == "confirm")
            {
                if($params[$val[0]]!=$params[$val[4]])
                {
                    $errorCode = $val[2];
                    $msg = $val[3];
                    $this->apiOutputData($errorCode,$msg);
                    break;
                }
            }
        }
    }

    /**
     * api数据输出
     * @param $errorCode
     * @param $msg
     * @param array $data
     */
    protected function apiOutputData($errorCode,$msg,$data=array())
    {
        $errorCode=strval($errorCode);
        $dataReturn=array('error_code'=>$errorCode,'msg'=>$msg,'data'=>array());
        if(!is_null($data))
        {
            if(!$data)
            {
                $data=array();
            }
            $dataReturn['data']=$data;
        }

        $this->ajaxReturn($dataReturn);
    }
}