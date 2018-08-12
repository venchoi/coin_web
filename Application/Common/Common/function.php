<?php

function SVC($svcName)
{
    return D($svcName, 'Service');
}

//获取完整时间
function get_fulltime($time = false)
{
    if(!$time)
    {
        $now  = time();
    }
    else
    {
        $now = $time;
    }

    $time = is_numeric($now) ? $now : strtotime($now);

    return strftime("%Y-%m-%d %H:%M:%S", $time);
}

//获取含有代理的IP地址
function get_client_addr()
{
    return get_client_ip(0, true);
}

//获取当前域名
function get_current_domain()
{
    $srvName = $_SERVER['HTTP_HOST'];
    $srvPort = $_SERVER['SERVER_PORT'];

    if ($srvPort == 80)
    {
        $srvPort = '';
    }
    else
    {
        $srvPort = sprintf(':%s', $srvPort);
    }

    $resultDomain = sprintf('%s%s',$srvName, $srvPort);

    return $resultDomain;
}

//www开头的域名,pc端
function get_current_www_domain()
{
    return get_subsite_domain('www');
}

//m开头的域名,m端
function get_current_mobile_domain()
{
    return get_subsite_domain('m');
}

//api开头的域名,app端
function get_current_api_domain()
{
    return get_subsite_domain('api');
}

//获取当前cookie域
function get_current_cookie_domain()
{
	$srvName = $_SERVER['HTTP_HOST'];
    $srvPort = $_SERVER['SERVER_PORT'];

    $domainParts = explode('.', $srvName);
    if (count($domainParts) > 2)
    {
        unset($domainParts[0]);
    }

    if ($srvPort == 80)
    {
        $srvPort = '';
    }
    else
    {
        $srvPort = sprintf(':%s', $srvPort);
    }

    $resultDomain = sprintf('%s%s', join('.', $domainParts), $srvPort);

    return $resultDomain;
}

//获取子站域名
function get_subsite_domain($prefix)
{
    $srvName = $_SERVER['HTTP_HOST'];
    $srvPort = $_SERVER['SERVER_PORT'];

    $domainParts = explode('.', $srvName);
    if(count($domainParts) > 2)
    {
        unset($domainParts[0]);
    }

    if($srvPort == 80 || $srvPort == 443)
    {
        $srvPort = '';
    }
    else
    {
        $srvPort = sprintf(':%s', $srvPort);
    }

    $resultDomain = sprintf('%s.%s%s', $prefix, join('.', $domainParts), $srvPort);

    return $resultDomain;
}

//获取二维数据某项值列表
function get_array_item_list($arrayList,$keyname)
{
	$list=array();
	
	foreach($arrayList as $item)
	{
	  $list[]=$item[$keyname];
	}
	
	return $list;
}

//路由规则匹配
function match_path_rule($whiteList, $path)
{
    $bRet = false;

    $pathArr = explode('/', $path);  //路径分解
    //var_dump($path);
    foreach ($whiteList as $whiteNode)
    {
        $whiteItem = $whiteNode['path'];
        $whiteArr = explode('/', $whiteItem); //白名单分解

        if(count($pathArr) != count($whiteArr))
        {
            $bRet = false;
            continue;
        }

        $bRet = $whiteNode;

        for ($i = 0; $i < count($whiteArr); ++$i)
        {
            if($whiteArr[$i] == '*')
            {
                continue;
            }

            if(strtolower($whiteArr[$i]) != strtolower($pathArr[$i]))
            {
                $bRet = false;
                break;
            }
        }

        if($bRet)
        {
            break;
        }
    }

    return $bRet;
}

//curl模拟post请求
function curl_post($curlHttp, $postdata)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_URL, $curlHttp);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //不显示
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
}

//curl模拟post请求,true时content-type设置为application/json
function curl_post_json($url, $data = NULL, $json = false)
{
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_POST, 1);
$data = json_encode($data);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

if($json)
{
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json;charset=utf-8',
            'Content-Length:' . strlen($data))
    );

}
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$res = curl_exec($curl);

$errorno = curl_errno($curl);

if($errorno)
{
    return array('errorno' => false, 'errmsg' => $errorno);
}
curl_close($curl);
return json_decode($res, true);
}

//curl模拟get请求
function curl_get($curlHttp)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_TIMEOUT, 8);
    curl_setopt($curl, CURLOPT_URL, $curlHttp);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //不显示
    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
}

//数组排序
function array_sort($arr, $keys, $type = 'asc')
{
    if(!is_array($arr))
    {
        print_r('');
    }

    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v)
    {
        $keysvalue [$k] = $v [$keys];
    }

    if($type == 'asc')
    {
        asort($keysvalue);
    }
    else
    {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v)
    {
        array_push($new_array, $arr[$k]);
    }

    return $new_array;
}

function is_wechat()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if(strpos($user_agent, 'MicroMessenger') === false)
    {
        return 0;
    }
    else
    {
        return 1;
    }
}

//判断移动端
function is_mobile()
{
    $userAgent = '';
    if(isset($_SERVER['HTTP_USER_AGENT']))
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
    }

    $clientKeywords = array('iphone', 'ipad', 'android', 'mobile', 'wap', 'tablet', 'wp');
    if(preg_match('/'.implode('|',$clientKeywords).'/i',$userAgent))
    {
        return true;
    }

    return false;
}

//前2个日期处理成文字
function deal_date($date,$lang='ch',$dateFormat='m月d日',&$isOut)
{
    $isOut=0;
    if($date==date("Ymd"))
    {
        $today=array('ch'=>'今天','en'=>'Today');
        return $today[$lang];
    }

    if($date==date("Ymd",strtotime('-1 day')))
    {
        $yesterday=array('ch'=>'昨天','en'=>'Yesterday');
        return $yesterday[$lang];
    }

    $isOut=1;

    return date($dateFormat,strtotime($date));
}

//前30分钟处理成文字
function deal_min($timeStamp,$lang='ch',$dateFormat="H:i")
{
    $minLang=array('ch'=>'分钟前','en'=>'');

    $period=time()-$timeStamp;
    if($period<1800)
    {
        $time=floor($period/60).$minLang[$lang];
    }
    else
    {
        $time=date($dateFormat,$timeStamp);
    }

    return $time;
}


function get_user_key_prefix($userId, $allPlatform = false)
{
    $base   = $userId.'$popcoinmaintoken#%s'.intval(in_dev()).intval(in_local_mode());
    $prefix = 'popcoin_main_%s';

    if ($allPlatform)
    {
        $dataKey[] = md5(sprintf($base,0));
        $dataKey[] = md5(sprintf($base,1));
        foreach ($dataKey as &$v)
        {
            $v = sprintf($prefix, $v);
        }
    }
    else
    {
        $dataKey = md5(sprintf($base, intval(is_mobile())));
        $dataKey = sprintf($prefix, $dataKey);
    }

    return $dataKey;
}
//记录用户信息
function set_current_user_data($userData, $timeOut = 604800)
{
    //生成记录数据
    $userData['first_login_time'] = get_fulltime();
    $dataKey = get_user_key_prefix($userData['uid']);
    $randKey=md5(sprintf('%s_%s',uniqid(),time()));

    //搜索key
    $searchKey=sprintf('%s*',$dataKey);

    //固定Key+随机值
    $dataKey=sprintf('%s_%s',$dataKey,$randKey);

    //写到redis
    $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
    $redisClient->select(1);


    //踢掉在线用户
    $keyList=$redisClient->keys($searchKey);

    $onlineCount=count($keyList);
    if($onlineCount>3)
    {
        $redisClient->delete($keyList);
    }

    $redisClient->hMset($dataKey, $userData);
    $redisClient->setTimeout($dataKey, $timeOut);
    $redisClient->select(0);

    //写到cookie
    $opt = array('expire' => $timeOut);
    cookie('popcoin_main_token', $dataKey, $opt);

    //最后返回结果,兼容app情况
    return $dataKey;
}

//清理用户信息
function clear_user_data($dataKey)
{
    if(!$dataKey)
    {
        return;
    }

    //先清除redis
    $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
    $redisClient->select(1);

    $redisClient->del($dataKey);

    $redisClient->select(0);

    //清除cookie
    cookie('popcoin_main_token', null);
}

//获取用户信息
function get_user_data($dataKey)
{
    $userData = array();

    if(!$dataKey)
    {
        return $userData;
    }

    //查redis
    $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
    $redisClient->select(1);
    $userData = $redisClient->hGetAll($dataKey);
    $redisClient->select(0);

    //生成用户邀请码
    if(!$userData["invite_code"] && $userData['uid'])
    {
        $userData["invite_code"] = base_convert($userData['uid'],10,36);
        update_user_data($userData['uid'], $userData);
    }

    return $userData;
}

//获取用当前户token
function get_current_user_token()
{
    $dataKey = cookie('popcoin_main_token');

    $cookieTkPat='/^popcoin_main_([a-z0-9]+)_([a-z0-9]+)$/i';
    if(!preg_match($cookieTkPat,$dataKey))
    {
        $dataKey='';
    }

    if(!$dataKey)
    {
        $dataKey = I('post.popcoin_main_token');
    }

    if(!$dataKey)
    {
        $dataKey = I('get.popcoin_main_token');
    }

    return $dataKey;
}

//获取当前用户信息
function get_current_user_data()
{
    $dataKey = get_current_user_token();

    return get_user_data($dataKey);
}

//清理当前用户信息
function clear_current_user_data()
{
    $dataKey = get_current_user_token();
    clear_user_data($dataKey);
}

//获取当前用户ID
function get_current_user_id()
{
    $userData = get_current_user_data();
    $uid = intval($userData['uid']);

    return $uid;
}

//获取是否更新用户信息状态
function get_update_state($uid)
{
    $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
    $redisClient->select(1);
    $re = $redisClient->hget('userDataState',$uid);
    $redisClient->hDel('userDataState',$uid);
    $redisClient->select(0);

    return $re;
}

//设置更此用户信息状态
function set_update_state($uid)
{
    //写到redis
    $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
    $redisClient->select(1);
    $re = $redisClient->hset('userDataState',$uid, 1);
    $redisClient->select(0);

    return $re;
}

//更新用户信息
function update_user_data($userId, $userData)
{
    $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
    $redisClient->select(1);
    $dataKey = get_user_key_prefix($userId, true);

    $keyList = array();
    foreach ($dataKey as $v)
    {
        $searchKey = $v.'_*';
        $keyList= array_merge($keyList, (array)$redisClient->keys($searchKey));
    }

    if($keyList)
    {
        foreach ($keyList as $v)
        {
            $redisClient->hMset($v, $userData);
        }
    }
    $redisClient->select(0);

    return $dataKey;
}

//获取秒级时间撮
function milltime() {
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}

//验证图像验证码
function check_verify($code,$id='')
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

//验证图像验证码
function check_app_verify($code,$id='')
{
    $verify = new \Think\VerifyApp();
    return $verify->check($code, $id);
}

//手机号码中间4位*处理
function phoneSecret($phone)
{
    $phone=preg_replace('/^(\d{3})(\d{4})(\d{4})$/','\\1****\\3',$phone);

    return $phone;
}

//加密方法
//1.md5(sha1())加密
function getMsEncrypt($data,$salt='')
{
    $data=sprintf("%s%s",$data,$salt);

    return md5(sha1($data));
}