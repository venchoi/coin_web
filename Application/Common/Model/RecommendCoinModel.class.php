<?php

namespace Common\Model;

/**
 * 版本模型
 * Class NewsModel
 * @package Common\Model
 */
class RecommendCoinModel extends DataBaseModel
{
    protected $tableName="recommend_coin";

    public function getUserDefineRecommendCoin()
    {
        $recommendCoin = $this->where(array('state'=>1))->field('coin_code,reason,type,create_time')->select();

        if(!$recommendCoin)
        {
            $recommendCoin = array();
        }
        return $recommendCoin;
    }

    public function getReasonByCoinCode($coinCode)
    {
        $where = array('coin_code'=>$coinCode);
        $re = $this->where($where)->getField('reason');
        if(!$re)
        {
            $re=array();
        }

        return $re;
    }

}