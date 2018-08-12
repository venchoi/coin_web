<?php
namespace Mobile\Controller;

class SearchController extends BaseController 
{
    //搜索主页
    public function index()
    {
        $keyword=I('post.keyword','');
        $keyword=str_replace(array(' ','-'),'',$keyword);

        $this->assign("keyword",$keyword);

        $this->display("index");
    }

    //关键词
    public function keyword()
    {
        $this->display("keyword");
    }
}