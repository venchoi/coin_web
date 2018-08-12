<?php

namespace Common\Model;

/**
 * 新闻标签模型
 * Class NewsTagModel
 * @package Common\Model
 */
class NewsTagModel extends DataBaseModel
{
    protected $tableName="news_tag";

    //获取新闻的标签名列表(新闻标识)
    public function getNewsTagsName($newsIds,$where=array())
    {
        $newsTags=array();
        $where['news_id']=array('in',$newsIds);
        $where['tag_type']=5;
        $where['state']=1;

        $data=$this->field("news_id,tag_name")->where($where)->select();
        $data=$data?$data:array();

        foreach($newsIds as $newsId) {
            $newsTags[$newsId] = array();
        }

        foreach($data as $key=>$item)
        {
            $newsTags[$item['news_id']][]=$item['tag_name'];
        }

        return $newsTags;
    }

    //获取新闻的标签名列表(新闻标识)
    public function getNewsCoinTag($newsIds,$where=array(),$hasQuotation=1,&$coinsCode)
    {
        $newsCoinTag=array();
        $where['news_id']=array('in',$newsIds);
        $where['tag_type']=1;
        $where['state']=1;

        $data=$this->field("tag_id,news_id,tag_name")->group("news_id,tag_id")->where($where)->select();
        $data=$data?$data:array();

        $dCoin=D("Coin");
        $coinsInfo=$dCoin->getAllCoinIdAndCode();

        foreach($newsIds as $newsId) {
            $newsCoinTag[$newsId] = array();
        }

        foreach($data as $key=>$item)
        {
            $code=$coinsInfo[$item['tag_id']]?:"";

            if($hasQuotation)
            {
                $arr=array(
                    'code'=>$code,
                    'ch_name'=>$item['tag_name'],
                    'price'=>'--',
                    'ratio'=>'--'
                );
            }
            else
            {
                $arr=array(
                    'code'=>$code,
                    'ch_name'=>$item['tag_name'],
                );
            }

            $coinsCode[]=$code;
            $newsCoinTag[$item['news_id']][]=$arr;
        }

        return $newsCoinTag;
    }

    //获取新闻的关联的币
    public function getNewsRelatedCoin($newsIds,$where=array(),$hasQuotation=1,&$coinsCode)
    {
        $newsCoinTag=array();
        $where['news_id']=array('in',$newsIds);
        $where['tag_type']=1;
        $where['state']=1;

        $data=$this->field("news_id,tag_id,tag_name")->group("news_id,tag_id")->where($where)->group("tag_id")->select();
        $data=$data?$data:array();

        $sCoin=SVC("Coin");
        $coinsInfo=$sCoin->getAllCoinInfo('coin_id');

        $sUser=SVC("User");
        $userConcernCoins=$sUser->getUserConcernCoins();

        foreach($data as $key=>$item)
        {
            $code=$coinsInfo[$item['tag_id']]['code']?:"";
            $chName=$coinsInfo[$item['tag_id']]['ch_name']?:"";
            $enShortName=$coinsInfo[$item['tag_id']]['en_short_name']?:"";
            $bigLogoUrl=$coinsInfo[$item['tag_id']]['big_logo_url']?:"";
            $smallLogoUrl=$coinsInfo[$item['tag_id']]['small_logo_url']?:"";
            if(in_array($code,$userConcernCoins))
            {
                $isCollect=1;
            }
            else
            {
                $isCollect=0;
            }
            $arr=array(
                'coin_id'=>$item['tag_id'],
                'code'=>$code,
                'ch_name'=>$chName,
                'en_short_name'=>$enShortName,
                'small_logo_url'=>$smallLogoUrl,
                'big_logo_url'=>$bigLogoUrl,
                'is_collect'=>$isCollect
            );

            if($hasQuotation)
            {
                $arr['source_name']="Huobi";
                $arr['price']="--";
                $arr['ratio']='--';
            }

            $coinsCode[]=$code;
            $newsCoinTag[]=$arr;
        }

        return $newsCoinTag;
    }
}