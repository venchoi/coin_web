<?php

namespace Common\Model;

/**
 * 新闻模型
 * Class NewsModel
 * @package Common\Model
 */
class NewsModel extends DataBaseModel
{
    protected $tableName="news";

    //主要根据分类id获取新闻列表
    public function getNewsListByCatalogId($catalogId,$where=array(),$field,$limit,$minId)
    {
        $where['t_news.state']=array('in',array(2,3));

        if(is_array($catalogId))
        {
            $where['catalog_id']=array('in',$catalogId);
        }
        else
        {
            $where['catalog_id']=$catalogId;
        }

        if(!empty($minId) && $minId>1)
        {
            $where['id']=array('lt',$minId);
        }

        //根据处理新闻开关设置处理$where
        $setWhere=$this->handleNewsCheckSetting($where);

        $newsList=$this->where($setWhere)->field($field)->limit($limit)->order("id desc")->select();
        if(!$newsList)
        {
            $newsList=array();
        }

        //setWhere不等于where的时候，说明存在有效的开关设置,进行新闻状态处理
        if($setWhere!=$where && $newsList)
        {
            $this->handleNewsState($newsList);
        }

        return $newsList;
    }

    //主要根据分类id获取新闻列表
    public function getNewsListByCatalogIdAndClassTag($catalogId,$where=array(),$field,$limit,$minId)
    {
        $where['t_news.state']=array('in',array(2,3));

        if(is_array($catalogId))
        {
            $where['catalog_id']=array('in',$catalogId);
        }
        else
        {
            $where['catalog_id']=$catalogId;
        }

        if(!empty($minId) && $minId>1)
        {
            $where['t_news.id']=array('lt',$minId);
        }

        //根据处理新闻开关设置处理$where
        $setWhere=$this->handleNewsCheckSetting($where);

        $newsList=$this->join("left join t_news_tag on t_news_tag.news_id=t_news.id")
                       ->where($setWhere)->field($field)->limit($limit)->order("id desc")->select();

        if(!$newsList)
        {
            $newsList=array();
        }

        //setWhere不等于where的时候，说明存在有效的开关设置,进行新闻状态处理
        if($setWhere!=$where && $newsList)
        {
            $this->handleNewsState($newsList);
        }

        return $newsList;
    }

    //主要根据分类id获取热文新闻列表
    public function getHotNewsListByCatalogId($catalogId,$where=array(),$field,$limit,$minId)
    {
        $where['t_news.state']=array('in',array(2,3));

        if(is_array($catalogId))
        {
            $where['catalog_id']=array('in',$catalogId);
        }
        else
        {
            $where['catalog_id']=$catalogId;
        }

        if(!empty($minId) && $minId>1)
        {
            $where['id']=array('lt',$minId);
        }

        //根据处理新闻开关设置处理$where
        $setWhere=$this->handleNewsCheckSetting($where);

        $newsList=$this->field($field)->where($setWhere)->limit($limit)->order("attitude_up_count desc,id desc")->select();

        //setWhere不等于where的时候，说明存在有效的开关设置,进行新闻状态处理
        if($setWhere!=$where && $newsList)
        {
            $this->handleNewsState($newsList);
        }

        return $newsList;
    }

    //主要根据分类id获取新闻列表(暂时用)
    public function getLatestNewsListByCatalogId($catalogId,$where,$field,$timeStamp)
    {
        $where['t_news.state']=array('in',array(2,3));

        if(is_array($catalogId))
        {
            $where['catalog_id']=array('in',$catalogId);
        }
        else
        {
            $where['catalog_id']=$catalogId;
        }

        $newsList=array();
        if(!empty($timeStamp))
        {
            $where['update_time']=array(array('gt',date("Y-m-d H:i:s",$timeStamp)),array('elt',date("Y-m-d H:i:59")));
            $setWhere=$this->handleNewsCheckSetting($where);

            $newsList=$this->field($field)->where($setWhere)->order("id desc")->select();

            //setWhere不等于where的时候，说明存在有效的开关设置,进行新闻状态处理
            if($setWhere!=$where && $newsList)
            {
                $this->handleNewsState($newsList);
            }
        }

        if(!$newsList)
        {
            $newsList=array();
        }

        return $newsList;
    }

    //根据时间撮获取最新新闻条数
    public function getLatestNewsNumByCatalogId($catalogId,$where,$timeStamp)
    {
        $where['t_news.state']=array('in',array(2,3));

        if(is_array($catalogId))
        {
            $where['catalog_id']=array('in',$catalogId);
        }
        else
        {
            $where['catalog_id']=$catalogId;
        }

        $latestNewsInfo=array('max_time'=>$timeStamp,'num'=>0);

        if(!empty($timeStamp))
        {
            $where['update_time']=array(array('gt',date("Y-m-d H:i:s",$timeStamp)),array('elt',date("Y-m-d H:i:59")));
            $setWhere=$this->handleNewsCheckSetting($where);

            $latestNewsArr=$this->field("max(update_time) as max_time,count(*) as news_num")->where($setWhere)->find();
        }
        else
        {
            $setWhere=$this->handleNewsCheckSetting($where);
            $latestNewsArr=$this->field("max(update_time) as max_time")->where($setWhere)->find();
            if($latestNewsArr)
            {
                $latestNewsArr['news_num']=0;
            }
        }

        if($latestNewsArr["max_time"])
        {
            $latestNewsArr['max_time']=strtotime($latestNewsArr['max_time']);
        }
        else
        {
            $latestNewsArr=$latestNewsInfo;
        }

        return $latestNewsArr;
    }

    //根据id获取一篇文章的正文内容
    public function getNewsViewById($id,$where=array())
    {
        if($id !='d41d482eed3afbc926fefd96644d2269')     //改篇为用户协议
        $where['t_news.state']=array('in',array(2,3));

        $where['uuid']=$id;
        $field="id,uuid,state,title,channel_id,source_name,content,attitude_up_count,attitude_down_count,reprint_count,update_time";

        $data=$this->where($where)->field($field)->find();
        if(!$data)
        {
            $data=array();
        }

        return $data;
    }

    //根据条件获取所需新闻的信息(必须包含id)
    public function getNewsInfoByWhere($where,$field)
    {
        $newsInfo=array();

        //根据处理新闻开关设置处理$where
        $where=$this->handleNewsCheckSetting($where);

        $re=$this->where($where)->field($field)->select();
        foreach($re as $item)
        {
            $newsInfo[$item['id']]=$item;
        }

        if(!$newsInfo)
        {
            $newsInfo=array();
        }

        return $newsInfo;
    }

    //获取根据条件获取新闻列表
    public function getNewsListByWhere($where=array(),$order='id desc')
    {
        $where['t_news.state']=array('in',array(2,3));

        $field="id,uuid,state,catalog_id,title,description,channel_id,source_url,source_name,thumb_url,attitude_up_count,attitude_down_count,reprint_count,update_time,recommend_level";

        //根据处理新闻开关设置处理$where
        $setWhere=$this->handleNewsCheckSetting($where);
        $newsList=$this->field($field)->where($setWhere)->order($order)->select();

        //setWhere不等于where的时候，说明存在有效的开关设置,进行新闻状态处理
        if($setWhere!=$where && $newsList)
        {
            $this->handleNewsState($newsList);
        }

        if(!$newsList)
        {
            return array();
        }

        return $newsList;
    }

    /**
     * 根据处理新闻开关设置处理$where
     * @param $where
     * @return array
     */
    private function handleNewsCheckSetting($where)
    {
        $cacheKey=md5("news_check_setting".serialize($where));
        $setOptionConditionWhere=S($cacheKey);
        //$setOptionConditionWhere=null;
        if(!$setOptionConditionWhere && $setOptionConditionWhere != '--')
        {
            //获取关闭的审核的设置
            $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
            $redisClient->select(5);
            $option=$redisClient->hGetAll("News_Check_Setting");
            if(!$option)
            {
                $option=array();
            }

            $catalogIdsArr=array(300=>'fast',200=>'deep',100=>'normal');
            //根据审核设置对查询生成union查询条件进行处理
            $setOptionCondition=array();
            if(is_array($where['catalog_id']))
            {
                $catalogIds=$where['catalog_id'][1];
                foreach($catalogIds as $catalogId)
                {
                    $setOptionCondition[$catalogId]=json_decode($option[$catalogIdsArr[$catalogId]],true);
                }
            }
            else
            {
                $catalogIds=$where['catalog_id'];
                $setOptionCondition[$catalogIds]=json_decode($option[$catalogIdsArr[$catalogIds]],true);
            }

            //如果存在设置不需要审核的项时，进行where条件组合
            if($setOptionCondition)
            {
                $setOptionConditionWhere=array($where);
                foreach($setOptionCondition as $key=>$value)
                {
                    if($value['state']==="0" && $value['channel'])
                    {
                        $setWhere=$where;

                        $setWhere['catalog_id']=$key;
                        $setWhere['t_news.state']=1;

                        if($value['start_time'])
                            $setWhere['t_news.update_time'][]=array('EGT',$value['start_time']);

                        if($value['end_time'])
                            $setWhere['t_news.update_time'][]=array('ELT',$value['end_time']);

                        $channel=$value['channel'];
                        if(is_array($setWhere['channel_id']))
                        {
                            $channel=array_merge($value['channel'],$setWhere['channel_id']);
                        }
                        elseif(!empty($setWhere['channel_id']))
                        {
                            $channel[]=(int)$setWhere['channel_id'];
                        }

                        $setWhere['channel_id']=array('in',$channel);
                        $setOptionConditionWhere[]=$setWhere;
                    }
                }

                $setOptionConditionWhere['_logic']='or';
            }

            if(!$setOptionConditionWhere)
            {
                $setOptionConditionWhere='--';
            }

            S($cacheKey,$setOptionConditionWhere,60);
        }

        if($setOptionConditionWhere == '--' )
        {
            return $where;
        }

        return $setOptionConditionWhere;
    }

    //进行新闻状态处理
    private function handleNewsState($newsList)
    {
        $newsStateArr=array_column($newsList,'state','id');
        asort($newsStateArr);

        $newsIdsArr=array();
        foreach($newsStateArr as $key=>$value)
        {
            if($value!=1)
            {
                break;
            }

            $newsIdsArr[]=$key;
        }

        //$newsIdsArr 有状态为1的新闻，进行状态更新，更新为3
        if($newsIdsArr)
        {
            $this->where(array('id'=>array('in',$newsIdsArr)))->save(array('state'=>3));
        }
    }
}