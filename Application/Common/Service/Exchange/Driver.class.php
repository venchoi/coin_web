<?php
namespace Common\Service\Exchange;

/**
 * 抽象类
 * Class Driver
 * @package Common\Service\Exchange
 */
abstract class Driver
{
    /**
     * 获取单位币的USDT价格
     * @return mixed
     */
    public abstract function getCoinUnitToUsdt();

    /**
     * 根据codes获取该些币的行情
     * @param $codes    由code组成的索引数组
     * @return mixed    返回redis取出的行情信息
     */
    public abstract function getQuotationsByCoinCode($codes);

    /**
     * 根据codes获取该些币行情，行情信息为数组
     * @param $codes    由code组成的索引数组
     * @return mixed    返回redis取出的行情信息
     */
    public function getCoinQuotationsByCoinCode($codes,$isCodeToLower=1)
    {
        $quotationsData=$this->getQuotationsByCoinCode($codes,$isCodeToLower);

        $unitPrice=$this->getCoinUnitToUsdt();

        $coinQuotations=array();
        foreach($quotationsData as $key=>&$coinQuotation)
        {
            $key=strtoupper($key);

            $coinQuotation=json_decode($coinQuotation,true);
            $price=$this->getAmount($coinQuotation['close'],$coinQuotation['unit'],$unitPrice);
            $ratio=$this->getRatio($coinQuotation['preclose'],$coinQuotation['close'],$rankRatio);

            $coinQuotations[$key]=array(
                'price'=>$price,
                'ratio'=>$ratio,
                'source_name'=>$this->exchangeCode
            );
        }

        return $coinQuotations;
    }

    /**
     * 根据codes获取该些币的行情
     * @param $codes    由code组成的索引数组
     * @return mixed    返回redis取出的行情信息
     */
    public abstract function getQuotationsAllCodes();

    /**
     * 根据一个codes获取一个币的行情
     * @param $code 该币的code
     * @return mixed
     */
    public function getExchangeCoinQuotation($code)
    {
        $coinQuotation=array();
        $code=strtolower($code);
        $codes=array($code);

        $unitPrice=$this->getCoinUnitToUsdt();

        $quotationsData=$this->getQuotationsByCoinCode($codes);
        if($quotationsData)
        {
            $coinQuotation=json_decode($quotationsData[$code],true);

            $rankRatio='';
            $price=$this->getAmount($coinQuotation['close'],$coinQuotation['unit'],$unitPrice);
            $ratio=$this->getRatio($coinQuotation['preclose'],$coinQuotation['close'],$rankRatio);
            $amount=$this->getAmount($coinQuotation['amount'],$coinQuotation['unit'],$unitPrice);

            $coinQuotation=array(
                'price'=>$price,
                'ratio'=>$ratio,
                'rank_ratio'=>$rankRatio,
                'amount'=>$amount,
                'exchange_code'=>$this->exchangeCode,
                'exchange_name'=>$this->exchangeName
            );

        }

        return $coinQuotation;
    }

    /**
     * 金额换算
     * @param $price
     * @param $unit
     * @param $unitPrice
     * @return float|int|string
     */
    public function getAmount($amount,$unit,$unitPrice)
    {
        if($unit=='usdt')
        {

        }
        elseif($unit=='btc')
        {
            $amount=$amount*$unitPrice['btc'];
        }
        elseif($unit=='eth')
        {
            $amount=$amount*$unitPrice['eth'];
        }

        $amount=(string)$amount;
        if(!$amount)
        {
            $amount='--';
        }

        return $amount;
    }

    /**
     * 获取币的涨跌幅
     * @param $preClose
     * @param $close
     * @return string
     */
    public function getRatio($preClose,$close,&$rankRatio)
    {
        $ratio=$rankRatio=sprintf("%.2f",($close-$preClose)/$preClose*100);
        if($ratio>0)
        {
            $ratio='+'.$ratio;
        }
        elseif($ratio==0.00)
        {
            $ratio='0.00';
        }

        $ratio=$ratio.'%';

        return $ratio;
    }
}