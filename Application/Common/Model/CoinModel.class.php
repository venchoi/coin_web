<?php

namespace Common\Model;

/**
 * 币模型
 * Class NewsModel
 * @package Common\Model
 */
class CoinModel extends DataBaseModel
{
    protected $tableName="coin";

    public function getAllCoinBasicInfo()
    {
        $fields="id as coin_id,coin_code as code,ch_name,en_name,en_short_name,small_logo_url,big_logo_url";
        $data=$this->field($fields)->where(array('state'=>1))->select();

        if(!$data)
        {
            return array();
        }
        return $data;
    }

    public function getAllCoinBasicInfoKeyCode()
    {
        $fields="coin_code,id as coin_id,coin_code as code,ch_name,en_name,en_short_name,small_logo_url,big_logo_url";
        $data=$this->where(array('state'=>1))->getField($fields);

        if(!$data)
        {
            return array();
        }
        return $data;
    }

    public function getAllCoinBasicInfoKeyId()
    {
        $fields="id as coin_id,coin_code as code,ch_name,en_name,en_short_name,small_logo_url,big_logo_url";
        $data=$this->where(array('state'=>1))->getField($fields);

        if(!$data)
        {
            return array();
        }
        return $data;
    }

    public function getCoinInfoByCode($code)
    {
        $where=array('coin_code'=>$code,'state'=>1);
        $fields="id as coin_id,coin_code as code,ch_name,en_name,en_short_name,small_logo_url,big_logo_url,description,content,white_book,official_website_address,facebook,twitter,github";
        $data=$this->where($where)->field($fields)->find();
        if($data)
        {
            $data['office_no']=array(
                'facebook'=>$data['facebook'],
                'twitter'=>$data['twitter'],
                'github'=>$data['github'],
            );

            $data['official_website_address']=explode(',',$data['official_website_address']);
            unset($data['facebook']);
            unset($data['twitter']);
            unset($data['github']);
        }

        if(!$data)
        {
            return array();
        }
        return $data;
    }

    public function getAllCoinIdAndCode()
    {
        $cacheKey=md5('all_coin_id_code');
        $data=S($cacheKey);

        if(!$data && $data!='--')
        {
            $data=$this->where(array('state'=>1))->getField('id,coin_code');

            if(!$data)
            {
                $data = '--';
            }

            S($cacheKey,$data,1800);
        }

        if($data=='--')
        {
            return array();
        }

        if(!$data)
        {
            return array();
        }
        return $data;
    }

    public function updateCoinInfoByCode($code,$save,$where=array())
    {
        $where['coin_code']=$code;

        $re=$this->where($where)->save($save);

        return $re;
    }

    public function updateCoinInfoByWhere($where=array(),$save=array())
    {
        $re=$this->where($where)->save($save);

        return $re;
    }

    //获取查询coin字段
    public function getSearchCoins()
    {
        $data=$this->field("coin_code,en_name,ch_name")->select();

        if(!$data)
        {
            return array();
        }
        return $data;
    }

    //根据条件获取币列表
    public function getCoinListByWhere($where,$order='id desc')
    {
        $field="id as coin_id,coin_code as code,ch_name,en_short_name,small_logo_url,big_logo_url";
        $data=$this->field($field)->where($where)->order($order)->select();

        if(!$data)
        {
            return array();
        }
        return $data;
    }

    //根据code获取
    public function getCoinlistByCode($code,$where=array())
    {
        if(is_array($code))
        {
            $where['coin_code']=array('in',$code);
        }
        else
        {
            $where['coin_code']=(string)$code;
        }

        $field="id as coin_id,coin_code as code,ch_name,en_short_name,small_logo_url,big_logo_url";
        $data=$this->field($field)->where($where)->select();

        if(!$data)
        {
            return array();
        }
        return $data;
    }

    public function getAllCoinCode($limit)
    {
        $data=$this->where('state=1')->limit($limit)->getField('coin_code',true);
        if(!$data)
        {
            return array();
        }
        return $data;
    }


    public function getCoinCodeById($coinId)
    {
        $data = array();
        foreach ($coinId as $k=>$v)
        {
            $data[]=$this->where(array('id'=>$v))->getField('coin_code');
        }
        return $data;
    }

}