<?php
namespace Home\Controller;

class NewsController extends BaseController 
{
    
	public function fast()
	{
		$this->setMainMenu('news');
		
		$this->display('fast');
	}

	//新闻内页
	public function view()
    {
        $id=I('id');
        if(!$id)
        {
            header('HTTP/1.1 404 Not Found');
            redirect("/404.html");
            exit;
        }

        $sNews=SVC("News");
        $data=$sNews->getNewsView($id);
        if(!$data)
        {
            header('HTTP/1.1 404 Not Found');
            redirect("/404.html");
            exit;
        }

        //热文列表
        $hotNewsList=$sNews->getHotNewsListByCataConf('deep');

        $this->assign("hotNewsList",$hotNewsList);
        $this->assign("data",$data);

        $this->display("view");
    }
	
}