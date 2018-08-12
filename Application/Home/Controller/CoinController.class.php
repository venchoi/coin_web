<?php
namespace Home\Controller;

class CoinController extends BaseController 
{
    //币详情页
    public function view()
    {
        $code=I("code");

        $sCoin=SVC("Coin");
        $coinInfo=$sCoin->getCoinInfoByCode($code);
        if(!$coinInfo)
        {
            header('HTTP/1.1 404 Not Found');
            redirect("/404.html");
            exit;
        }

        $sSearch=SVC("Search");
        $keyword=$coinInfo['code'];
        $fastNewsList=$sSearch->getCoinSearchNews($keyword,1,20);

        $this->assign("fastNewsList",$fastNewsList);
        $this->assign("coinInfo",$coinInfo);
        $this->assign("keyword",$keyword);

        $this->display("view");
    }

    //币的行情
    public function quotations()
    {
        $this->display('quotations');
    }
}