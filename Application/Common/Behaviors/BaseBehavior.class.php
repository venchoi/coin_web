<?php
namespace Common\Behaviors;

class BaseBehavior extends \Think\Behavior
{
	
	public function run(&$param)
	{
	}
	
	protected function getRequestPath()
	{
	   $path=sprintf('%s/%s/%s',MODULE_NAME,CONTROLLER_NAME,ACTION_NAME);
	   return strtolower($path);
	}
	
	protected function getCurrentCacheFilePath($time='m')
	{
	    $fileMd5=md5(__SELF__);
		
		//检查临时目录
		$filePath=sprintf('%sHtml',RUNTIME_PATH);
		if(!file_exists($filePath))
		{
			mkdir($filePath,0777,true);
		}
		
		$timeMap=array(
		  'd'=>'Ymd',
		  'h'=>'YmdH',
		  'm'=>'YmdHi',
		);
		
		$dateType=$timeMap[$time];
		
		if(!$dateType)
		{
		    $dateType='YmdHi';
		}
		
		$filePath=sprintf('%s/%s',$filePath,date($dateType));
		
		if(!file_exists($filePath))
		{
			mkdir($filePath,0777,true);
		}

		//检查缓存,按照域名、文件请求、版本、时间来
		$filePath=sprintf('%s/%s_%s.html',$filePath,md5(get_current_domain()),$fileMd5);
		
		return $filePath;
	}
	
	protected function isMobileClient()
	{
	    $userAgent=$_SERVER['HTTP_USER_AGENT'];
		
		$mobileKeywords=array('android','ios','ipad','wp','iphone','mobile','tablet','nokia','MicroMessenger','wechat','weixin');
		
		foreach($mobileKeywords as $item)
		{
		    $rule=sprintf('/%s/i',$item);
			if(preg_match($rule,$userAgent))
			{
				return true;
				break;
			}
		}
		
		return false;
		
	}
	
}