<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/12
 * Time: 15:39
 */

namespace Addon\ajax\Controller;


class CoinController extends BaseController
{
    //usdt对各币种的汇率
    public function usdt_exchange_rate_by_code()
    {
        $currencyCode=I('currency_code');
        $currencies=array('CNY','GBP','HKD','JPY','TWD','KRW','EUR');
        if(!in_array($currencyCode,$currencies))
        {
            $this->ajaxCallMsg('200','只支持'.implode(',',$currencies).'等币种的查询');
        }

        $sCoin=SVC("Coin");
        $re=$sCoin->getUsdtExchangeRate('tcur');
        $re=$re[$currencyCode]?:array();

        $this->ajaxCallMsg('0','succ.',$re);
    }
}