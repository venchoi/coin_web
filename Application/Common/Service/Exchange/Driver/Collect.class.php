<?php

namespace Common\Service\Exchange\Driver;
use Common\Service\Exchange\Driver;

class Collect extends Driver
{
    //交易所code
    protected $exchangeCode='Collect';
    //交易所code
    protected $exchangeName='Collect';


    /*
     *通过code数组获取币的行情数据，我们使用这个采集行情中的total_amount & market_value
     */

    public function getCoinQuotationsByCoinCode($codes)
    {
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(4);

        $quotationsData = $redisClient->hMget('market_coins',$codes);

        if(!$quotationsData)
        {
            return array();
        }

        foreach ($quotationsData as $k=>$v)
        {
            $coinQuotation[$k]=json_decode($quotationsData[$k],true);
        }

        return $coinQuotation;
    }



    /**
     * 获取单位币的USDT价格
     * @return mixed
     */
    public function getCoinUnitToUsdt()
    {
        $codes=array('btc','eth');
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(4);

        $quotationsData = $redisClient->hMget('market_coins',$codes);
        if(!$quotationsData)
        {
            return array();
        }

        //比特币的usdt价格
        $unitPrice=array();
        $coinQuotation=json_decode($quotationsData['btc'],true);
        $unitPrice['btc']=$coinQuotation['close'];

        foreach($codes as $code)
        {
            if($code!='btc')
            {
                $coinQuotation=json_decode($quotationsData[$code],true);

                if($coinQuotation['unit']=='btc')
                {
                    $unitPrice[$code]=$coinQuotation['close']*$unitPrice['btc'];
                }
                else
                {
                    $unitPrice[$code]=$coinQuotation['close'];
                }
            }
        }

        return $unitPrice;
    }

    /**
     * 根据codes获取该些币的行情，行情信息为json字符串
     * @param $codes    由code组成的索引数组
     * @return mixed    返回redis取出的行情信息
     */
    public function getQuotationsByCoinCode($codes,$isCodeToLower=1)
    {
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(4);

        if(!is_array($codes)) $codes=(array)$codes;

        if($isCodeToLower)
        {
            array_walk($codes,function(&$value)
            {
                $value=strtolower($value);
            }
            );
        }

        $quotationsData = $redisClient->hMget('market_coins',$codes);

        if(!$quotationsData)
        {
            return array();
        }

        return $quotationsData;
    }

    /**
     * 根据codes获取该些币的行情
     * @param $codes    由code组成的索引数组
     * @return mixed    返回redis取出的行情信息
     */
    public function getQuotationsAllCodes()
    {
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(4);

        $quotationsData = $redisClient->hGetAll('market_coins');

        $codes=array_keys($quotationsData);
        if(!$codes)
        {
            return array();
        }

        return $codes;
    }

    /**
     * 获取币的交易所涨跌幅
     * @param $codes    由code组成的索引数组
     * @return mixed    返回redis取出的行情信息
     */
    public function getRatioOfAllCoin()
    {
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(4);

        $quotationsData = $redisClient->hGetAll('market_coins');

        foreach ($quotationsData as $k=>$v)
        {
            $coinQuotation[$k]=json_decode($quotationsData[$k],true);
            $ratio[$k] = ($coinQuotation[$k]['close']-$coinQuotation[$k]['dopen'])/$coinQuotation[$k]['dopen'];
        }

        if(!$ratio)
        {
            return array();
        }
        return $ratio;
    }
}