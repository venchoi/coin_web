<?php

namespace Common\Model;

/**
 * 用户喜好模型
 * Class SiteAdvListModel
 * @package Common\Model
 */
class UserFavoriteModel extends DataBaseModel
{
    protected $tableName="user_favorite";

    protected $_validate  = array(
        array('uid',array(0,999999999),'非法用户!',0,'between',3),
        array('favorite_type',array(1,10),'不正确的类型!',1,'between',3),
        array('target_id',array(1,99999999999),'资源ID错误!',0,'between',3),
        array('state','0,1','错误的状态操作!',0,'in',3),
    );

    //用户喜好设置
    public function saveUserFavorite($uid,$type,$targetId,$state,&$opposeAttitude)
    {
        $otherWhere=$where=array(
            'uid'=>$uid,
            'favorite_type'=>$type,
            'target_id'=>$targetId,
        );

        $re=$this->where($where)->find();

        //看好看空唯一处理,只能看好，或者看空
        if(in_array($type,array(5,6)))
        {
            $opposeAttitude='';
            if($type==5)
            {
                $otherWhere['favorite_type']=6;
            }
            else
            {
                $otherWhere['favorite_type']=5;
            }

            $opposeRe=$this->where($otherWhere)->save(array('state'=>0));
            if($opposeRe)
            {
                $opposeAttitudeArr=array(5=>'attitude_down_count',6=>'attitude_up_count');
                $opposeAttitude=$opposeAttitudeArr[$type];
            }
        }

        if($re)
        {
            $save=array(
                'state'=>$state,
                'update_time'=>get_fulltime(),
                'update_addr'=>get_client_addr()
            );

            $result=$this->where($where)->save($save);
        }
        else
        {
            $save=array(
                'uid'=>$uid,
                'favorite_type'=>$type,
                'target_id'=>$targetId,
                'state'=>1,
                'update_time'=>get_fulltime(),
                'update_addr'=>get_client_addr()
            );
            $result=$this->add($save);
        }

        return $result?true:false;
    }

    //获取用户喜好类型下的所有资源id
    public function getUserFavoriteSourceByUid($uid,$types,$where=array(),$limit='')
    {
        $userFavoriteSource=array();
        $where['uid']=$uid;
        $where['favorite_type']=array('in',$types);
        $where['state']=1;

        if($limit>0)
        {
            $data=$this->field("target_id,favorite_type")->where($where)->select();
        }
        else
        {
            $data=$this->field("target_id,favorite_type")->where($where)->limit($limit)->order("update_time desc")->select();
        }

        $data=$data?$data:array();

        foreach($types as $type) {
            $userFavoriteSource[$type] = array();
        }

        foreach($data as $key=>$item)
        {
            $userFavoriteSource[$item['favorite_type']][]=$item['target_id'];
        }

        return $userFavoriteSource;
    }

    //获取用户是否设置为某个资源的某种类型的喜好
    public function getUserFavorite($uid,$type,$targetId)
    {
        $where=array(
            'uid'=>$uid,
            'favorite_type'=>$type,
            'target_id'=>$targetId,
            'state'=>1,
        );

        $re=$this->where($where)->find();

        return $re?$re:array();
    }

    //获取用户关于新闻的喜好操作
    public function getUserOwnNewsFavorite($uid,$types,$targetIds)
    {
        if(!$targetIds) return array();

        $userFavoriteSource=array();
        $where=array('target_id'=>array('in',$targetIds));

        if($uid)
            $userFavoriteSource=$this->getUserFavoriteSourceByUid($uid,$types,$where);

        $where=array('id'=>array('in',$targetIds));
        $dNews=D("News");
        $newsCountInfo=$dNews->getNewsInfoByWhere($where,'id,thumb_up_count,attitude_up_count,attitude_down_count');
        $typeCountArr=array(5=>array("attitude_up","attitude_up_count"),6=>array("attitude_down","attitude_down_count"),7=>array("thumb_up","thumb_up_count"));
        $isType=array(1=>'is_collect',5=>"user_attitude",6=>"user_attitude",7=>"is_thumb_up");

        $userFavorites=array();
        foreach($targetIds as $targetId)
        {
            $item=array('news_id'=>$targetId);

            foreach($types as $type)
            {
                if(in_array($type,array(1,7)))
                    $item[$isType[$type]]=in_array($targetId,$userFavoriteSource[$type])?1:0;

                if(in_array($type,array(5,6)) && !isset($item['user_attitude']))
                    $item['user_attitude']=in_array($targetId,$userFavoriteSource[5])?'up':(in_array($targetId,$userFavoriteSource[6])?"down":'');

                if(in_array($type,array(5,6,7)))
                {
                    $typeCount=$typeCountArr[$type];
                    $item[$typeCount[0]]=$newsCountInfo[$targetId][$typeCount[1]]?:0;
                }
            }

            $userFavorites[]=$item;
        }

        return $userFavorites;
    }

    //获取用户的收藏
    public function getUserOwnCollect($uid,$type,$targetIds,$head)
    {
        if(!$targetIds) return array();

        $where=array(
            'uid'=>$uid,
            'favorite_type'=>$type,
            'target_id'=>array('in',$targetIds),
            'state'=>1,
        );

        $collectTargetIds=$this->where($where)->getField("target_id",true);

        $userCollects=array();
        foreach($targetIds as $targetId)
        {
            $isCollect=in_array($targetId,$collectTargetIds)?1:0;
            $userCollects[]=array($head[0]=>$targetId,$head[1]=>$isCollect);
        }

        return $userCollects;
    }

    //以一维数组形式获取tag_id=>id
    public function getUserFavoriteTagIdId($uid,$type,$where=array(),$limit=20)
    {
        $where['uid']=$uid;
        $where['favorite_type']=$type;
        $where['state']=1;

        $data=$this->where($where)->order("update_time desc")->limit($limit)->getField("target_id,id,update_time",true);

        return $data?$data:array();
    }

    //统计用户收藏的新闻，及关注的币种的数量
    public function countUserFavorite($uid)
    {
        $re = $this->where(array('uid'=>$uid,'state'=>1))->field('uid,SUM(favorite_type=1) as news_count,SUM(favorite_type=2) as coins_count')->find();
        return $re;
    }

    //获取用户某类型的所有喜好
    public function getUserFavoriteByType($uid,$type)
    {
        $where['uid']=$uid;
        $where['favorite_type']=$type;
        $where['state']=1;

        $data=$this->where($where)->order("update_time desc")->getField("target_id",true);

        return $data?:array();
    }

    //热门关注
    public function getTopConcernCoinList()
    {
        $where = array('state'=>1,'favorite_type'=>2);
        $re = $this ->where($where)->field('target_id,count(target_id) as rank')->group('target_id')->order('rank desc')->limit(10)->select();
        $coinId = array_column($re,'target_id');
        $dCoin = D('Coin');
        $coinCode = $dCoin->getCoinCodeById($coinId);
        return $coinCode? $coinCode : array() ;
    }

    //小众币
    public function getDownConcernCoinList()
    {
        $where = array('state'=>1,'favorite_type'=>2);
        $re = $this ->where($where)->field('target_id,count(target_id) as rank')->group('target_id')->order('rank asc')->limit(10)->select();
        $coinId = array_column($re,'target_id');
        $dCoin = D('Coin');
        $coinCode = $dCoin->getCoinCodeById($coinId);
        return $coinCode? $coinCode : array() ;
    }


}