<?php
namespace Home\Controller;

class IndexController extends BaseController 
{
    //首页：7x24小时快讯
	public function index()
	{
	    $sNews=SVC("News");
	    //热文列表
        //$hotNewsList=$sNews->getHotNewsListByCataConf('deep');
        //快讯列表
        $fastNewsList=$sNews->getNewsListByCateConf('fast');
        $fastNewsList['max_time']=$fastNewsList['dataList'][0]['news_list'][0]['update_time'];

        //新闻类别标签
        $sSite=SVC("Site");
        $newsClassTags=$sSite->getSiteClassTags(1);

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
        $this->assign("newsClassTags",$newsClassTags);

		$this->display('index');
	}

	//首页：深度
	public function deep()
    {
        $sNews=SVC("News");
        //深度列表
        $deepNewsList=$sNews->getNewsListByCateConf('deep');

        $this->assign("deepNewsList",$deepNewsList);

        $this->display("deep");
    }

    //注册协议
    public function reg_protocol()
    {
        $sUser=SVC("User");
        $data=$sUser->getRegProtocol();

        $this->assign("data",$data);
        $this->display("reg_protocol");
    }

    // 关于我们
    public function about()
    {
        $this->display("about");
    }

    // 行情
    public function quotations()
    {
        $this->assign("tab","quotations");

        $this->display("quotations");
    }
	public function browser_update()
	{
		$this->display('browser_update');
    }
    
    public function test()
	{
		$this->display('test');
    }
}