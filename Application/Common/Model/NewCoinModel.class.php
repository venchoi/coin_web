<?php

namespace Common\Model;

/**
 * 新闻模型
 * Class NewsModel
 * @package Common\Model
 */
class NewCoinModel extends DataBaseModel
{
    protected $tableName="new_coin";

    /**
     *
     * @param array $where
     * @return array
     */
    public function getNewCoinInfo($where=array(),$minTime,$limit=10)
    {
        $fields="id as publish_id,code,logo_url,is_first,publisher,trade_areas,publish_time";
        $where['state']=1;

        if($minTime)
        {
            $where['publish_time']=array('lt',date("Y-m-d H:i:s",$minTime));
        }

        $newCoinsList=$this->field($fields)->limit($limit)->where($where)->order("publish_time desc")->select();
        if(!$newCoinsList)
        {
            $newCoinsList=array();
        }

        return $newCoinsList;
    }

    /**
     * 获取最新一条的新币发行信息
     * @param array $where
     * @return array
     */
    public function getRecentlyNewCoin($where=array())
    {
        $fields="id as publish_id,code,logo_url,is_first,publisher,trade_areas,publish_time";
        $where['state']=1;

        $newCoin=$this->field($fields)->order("publish_time desc")->find();
        if(!$newCoin)
        {
            $newCoin=array();
        }

        return $newCoin;
    }

    /*
     * 获取最近一周新发币种的coin_code，发布在**交易所
     */
    public function getNewCoinListWithinOneWeek()
    {
        $dayTime=mktime(0, 0, 0, date('m'), date('d')-7, date('Y'));//获取时间戳
        $re = $this->field('publisher,code,publish_time')->select();

        foreach ($re as $k=>$v)
        {
            $coinTime  = strtotime($v['publish_time']);
            $timeNow = time();
            if(($timeNow - $dayTime)>($timeNow - $coinTime))
            {
                $data[] = array( 'code'=>$v['code'],'exchange'=>$v['publisher']);
            }

        }

        if(!$data)
        {
            $data=array();
        }

        return $data;
    }

    public function getExchangeByCoinCode($coinCode)
    {
        $where = array('code'=>$coinCode);
        $re = $this->where($where)->getField('publisher');
        if(!$re)
        {
            $re=array();
        }

        return $re;
    }

    public function getCoinInfoKeyExchange($publishIds,$where)
    {
        if(is_array($publishIds))
        {
            $where['id']=array('in',$publishIds);
        }
        else
        {
            $where['id']=$publishIds;
        }

        $data=$this->field("id,code,publisher")->where($where)->select();

        $exchangeCoins=array();
        foreach($data as $key=>$item)
        {
            $exchangeCoins[$item['publisher']][]=array(
                'code'=>$item['code'],
                'publish_id'=>$item['id']
            );
        }

        return $exchangeCoins;
    }

}