<?php
namespace Mobile\Controller;

use Common\Controller\CommonController;

class BaseController extends CommonController
{
	public function _initialize()
	{
		$this->initCdn();
        $this->initEnv();
		$this->setMainMenu('index');
	}
	
	protected function setMainMenu($name)
	{
	    $this->assign('pageMenuMain',$name);
	}

    //运行环境初始化
    private function initEnv()
    {
        if(in_local_mode())
        {
            define('PHP_ENV', 'local');
            return;
        }

        if(in_dev())
        {
            define('PHP_ENV', 'test');
            return;
        }

        define('PHP_ENV', '');
    }

    //CDN信息初始化
	private function initCdn()
    {
        if(in_local_mode() || in_dev())
        {
            $this->initOfflineCdn();
            return;
        }

        $this->initOnlineCdn();
    }
	
	private function initOfflineCdn()
    {
        if(in_local_mode())
        {
//           define('CDN_URL', 'http://localhost:8899');
              define('CDN_URL', 'http://m.test.popcoin.live');
        }
        else
        {
            define('CDN_URL', 'http://www.test.popcoin.live');
        }
        
        define('CDN_VER', time());
    }

    private function initOnlineCdn()
    {

        define('CDN_URL', '');

        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(1);
        $time = $redisClient->get('web_cdn_time_www');
		
		if(!$time)
		{
			$time=time();
		}
		
        define('CDN_VER', $time);
    }
}