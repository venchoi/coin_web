<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/7
 * Time: 19:19
 */

namespace Addon\api\Controller;


class CoinController extends BaseController
{
    //获取币的信息
    public function coin_info()
    {
        $params=$this->params;
        if(!$params['code'])
        {
            $this->apiOutputData('200','code不能为空');
        }

        $sCoin=SVC("Coin");
        $coinInfo=$sCoin->getCoinInfoByCode($params['code'],1,1);
        if(!$coinInfo)
        {
            $this->apiOutputData('400','该币的信息不存在');
        }

        $this->apiOutputData('0','succ.',$coinInfo);
    }

    //币相关新闻搜索接口
    public function coin_news()
    {
        $params=$this->params;
        $code=$params['code'];
        $page=$params['page'];
        if(!$code)
        {
            $this->apiOutputData('200','code不能为空');
        }

        if($page<1)
        {
            $page=1;
        }

        $sSearch=SVC("Search");
        $data=$sSearch->getCoinSearchNews($code,$page);

        $this->apiOutputData('0','succ.',$data);
    }

    //币行情
    public function coin_quotation()
    {
        $params=$this->params;
        $code=$params['code'];
        $type=$params['type'];
        if(!$code)
        {
            $this->apiOutputData('200','code不能为空');
        }

        $sCoin=SVC("Coin");
        $coinQuotations=$sCoin->getCoinQuotationsOfExchange($code,$type);

        $this->ajaxCallMsg('0','succ.',$coinQuotations);
    }

    //usdt对各币种的汇率
    public function usdt_exchange_rate()
    {
        $sCoin=SVC("Coin");
        $re=$sCoin->getUsdtExchangeRate();

        $this->apiOutputData('0','succ.',$re);
    }

    //usdt对各币种的汇率
    public function usdt_exchange_rate_by_code()
    {
        $params=$this->params;
        $currencyCode=$params['currency_code'];
        $currencies=array('CNY','GBP','HKD','JPY','TWD','KRW','EUR');
        if(!in_array($currencyCode,$currencies))
        {
            $this->apiOutputData('200','只支持'.implode(',',$currencies).'等币种的查询');
        }

        $sCoin=SVC("Coin");
        $re=$sCoin->getUsdtExchangeRate('tcur');

        $re=$re[$currencyCode];
        if(!$re)
        {
            $re=array();
        }

        $this->apiOutputData('0','succ.',$re);
    }
}