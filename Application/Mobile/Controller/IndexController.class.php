<?php
namespace Mobile\Controller;

class IndexController extends BaseController 
{
    //首页：7x24小时快讯
	public function index()
	{
	    $sNews=SVC("News");

        //快讯列表
        $fastNewsList=$sNews->getNewsListByCateConf('fast');
        $fastNewsList['max_time']=$fastNewsList['dataList'][0]['news_list'][0]['update_time'];

        //广告信息
        $sAdv=SVC("Adv");
        $ads=$sAdv->getAdvByPageSystemCodeConf('pc','index');

        //tab选择
        $url=explode('?',__SELF__);
        $url=str_replace(array('/',".html"),"",$url[0]);
        $url=$url?:'index';

        $this->assign("tab",$url);
        $this->assign("ads",$ads);
	    //$this->assign("hotNewsList",$hotNewsList);
        $this->assign("fastNewsList",$fastNewsList);

		$this->display('index');
	}

	public function browser_update()
	{
		$this->display('browser_update');
	}

	public function login()
    {
        $userInfo=get_current_user_data();
        if($userInfo)
        {
            redirect("/user/center.html");
        }

        $this->display("login");
    }

    public function find_password()
    {
        $this->display("find_password");
    }

    //注册协议
    public function reg_protocol()
    {
        $sUser=SVC("User");
        $data=$sUser->getRegProtocol();

        $this->assign("data",$data);
        $this->display("reg_protocol");
    }
}