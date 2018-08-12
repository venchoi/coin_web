<?php
namespace Common\Service\Exchange;

class Exchange
{
    private $_exchange=array();     //当前交易所实例
    private static $exchange=array();     //交易所实例池

    /**
     * 构造方法，用于创建交易所实例
     * Exchange constructor.
     * @param $exchangeName
     */
    public function __construct($exchangeName)
    {
        $class='Common\Service\Exchange\Driver\\'.$exchangeName;
        $this->_exchange=new $class();
    }

    /**
     * 获取单例
     * @param $exchangeName
     * @return Exchange|mixed
     */
    public static function getInstance($exchangeName)
    {
        $exchange=self::$exchange[$exchangeName];
        if(!$exchange)
        {
            $exchange=new self($exchangeName);
            self::$exchange[$exchangeName]=$exchange;
        }

        return $exchange;
    }

    /**
     * 获取单位币的USDT价格
     * @return mixed
     */
    public function getCoinUnitToUsdt()
    {
        return $this->_exchange->getCoinUnitToUsdt();
    }

    /**
     * 根据codes获取该些币的行情，行情信息为json字符串
     * @param $codes    由code组成的索引数组
     * @return mixed    返回redis取出的行情信息
     */
    public function getQuotationsByCoinCode($codes)
    {
        return $this->_exchange->getQuotationsByCoinCode($codes);
    }

    /**
     * 根据codes获取该些币行情，行情信息为数组
     * @param $codes    由code组成的索引数组
     * @return mixed    返回redis取出的行情信息
     */
    public function getCoinQuotationsByCoinCode($codes,$isCodeToLower=1)
    {
        return $this->_exchange->getCoinQuotationsByCoinCode($codes,$isCodeToLower);
    }

    /**
     * 根据一个code获取币在各个交易所的行情
     * @param $code 该币的code
     * @return mixed
     */
    public function getExchangeCoinQuotation($code)
    {
        return $this->_exchange->getExchangeCoinQuotation($code);
    }

    /**
     * 根据该交易所所有的ocde
     */
    public function getQuotationsAllCodes()
    {
        return $this->_exchange->getQuotationsAllCodes();
    }

    /**
     * 根据该交易所所有的币的涨跌幅
     */
    public function getRatioOfAllCoin()
    {
        return $this->_exchange->getRatioOfAllCoin();
    }


}