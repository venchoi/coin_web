<?php

namespace Common\Service;

/**
 * 用户服务层
 * Class UserService
 * @package Common\Service
 */
class UserService extends BaseService
{
    public function handleUserFavorite($uid,$type,$targetId,$isFavorite)
    {
        $dUserFavorite=D("UserFavorite");
        //1新闻2币3作者4概念5看好6看空7点赞
        $types=array('news'=>1,'coin'=>2,'author'=>3,'story'=>4,'attitude_up'=>5,'attitude_down'=>6,'thumb_up'=>7);
        $type=$types[$type];
        $data=array(
            'uid'=>$uid,
            'favorite_type'=>$type,
            'target_id'=>$targetId,
            'state'=>$isFavorite
        );
        //var_dump($data);
        if(!($data=$dUserFavorite->create($data)))
        {
            $this->setErrorMsg('250',$dUserFavorite->getError());
            return false;
        }

        if($type<=4)
        {
            $successMsg='取消收藏成功';
            if($isFavorite)
            {
                $successMsg='收藏成功';
            }
        }
        else
        {
            $failMsg='fail.';
            if($isFavorite)
            {
                $failMsg='succ.';
            }
        }

        $opposeAttitude='';
        $result=$dUserFavorite->saveUserFavorite($uid,$type,$targetId,$isFavorite,$opposeAttitude);
        if($result)
        {
            if(in_array($type,array(5,6,7)))
            {
                $dNews=D("News");
                $typeFieldArr=array(5=>'attitude_up_count',6=>'attitude_down_count',7=>'thumb_up_count');

                if($isFavorite)
                {
                    $dNews->where(array("id"=>$targetId))->setInc($typeFieldArr[$type]);

                    if($opposeAttitude)
                    {
                        $dNews->where(array("id"=>$targetId,$opposeAttitude=>array('gt',0)))->setDec($opposeAttitude);
                    }
                }
                else
                {
                    $dNews->where(array("id"=>$targetId,$typeFieldArr[$type]=>array('gt',0)))->setDec($typeFieldArr[$type]);
                }
            }

            $this->setErrorMsg('0',$successMsg);
            return true;
        }
        else
        {
            $this->setErrorMsg('500',$failMsg);
            return false;
        }
    }

    //获取用户的收藏
    public function getUserOwnFavorite($uid,$type,$favoriteTypes,$targetIds)
    {
        $dUserFavorite=D("UserFavorite");

        switch($type)
        {
            case "news";
                if($favoriteTypes==array("collect") && $uid)
                {
                    $head=array('news_id','is_collect');
                    $dataList=$dUserFavorite->getUserOwnCollect($uid,1,$targetIds,$head);
                }
                else
                {
                    $newsFavoriteTypesConf=array(
                        "collect"=>array(1,array("is_collect")),
                        "attitude_up"=>array(5,array("user_attitude","attitude_up_count")),
                        "attitude_down"=>array(6,array("user_attitude","attitude_down_count")),
                        "thumb_up"=>array(7,array("is_thumb_up","thumb_up_count")),
                    );

                    $types=array();
                    foreach($favoriteTypes as $favoriteType)
                    {
                        $types[]=$newsFavoriteTypesConf[$favoriteType][0];
                    }

                    $dataList=$dUserFavorite->getUserOwnNewsFavorite($uid,$types,$targetIds);
                }

                break;
            case "coin":
                $head=array('coin_id','is_collect');
                $dataList=$dUserFavorite->getUserOwnCollect($uid,2,$targetIds,$head);
                break;
            case "author":
                $head=array('author_id','is_collect');
                $dataList=$dUserFavorite->getUserOwnCollect($uid,3,$targetIds,$head);
                break;
            case "story":
                $head=array('story_id','is_collect');
                $dataList=$dUserFavorite->getUserOwnCollect($uid,4,$targetIds,$head);
                break;
            default:
                $dataList=array();
                break;

        }

        return $dataList;
    }

    //获取用户个人新闻收藏
    public function getUserOwnNewsCollect($uid,$minTime)
    {
        $dUserFavorite=D("UserFavorite");
        $where=array();
        $newsList=array();

        if($minTime)
        {
            $where['update_time']=array('lt',date("Y-m-d H:i:s",$minTime));
        }
        $userCollectNewsIds=$dUserFavorite->getUserFavoriteTagIdId($uid,1,$where);

        if($userCollectNewsIds)
        {
            $sNews=SVC("News");

            $newsList=$sNews->getUserCollectNews($userCollectNewsIds);
        }

        return $newsList;
    }

    //获取用户个人币收藏
    public function getUserOwnCoinCollect($uid,$minTime)
    {
        $dUserFavorite=D("UserFavorite");
        $where=array();
        $coinList=array();

        if($minTime)
        {
            $where['update_time']=array('lt',date("Y-m-d H:i:s",$minTime));
        }
        $userCollectCoinIds=$dUserFavorite->getUserFavoriteTagIdId($uid,2,$where);

        if($userCollectCoinIds)
        {
            $sCoin=SVC("Coin");

            $coinList=$sCoin->getUserCollectCoins($userCollectCoinIds);
        }

        return $coinList;
    }

    //获取用户关注币的code数组
    public function getUserConcernCoins()
    {
        $userInfo = get_current_user_data();
        $uid = $userInfo['uid'];
        if (!$uid)
        {
            return array();
        }

        $dUserFavorite=D("UserFavorite");
        $data=$dUserFavorite->getUserFavoriteByType($uid,2);

        $cacheKey=md5("user_all_concern_coins_".serialize($data));
        $resData=S($cacheKey);

        if(!$resData && $resData!='--')
        {
            $resData=array();

            $sCoin=SVC("Coin");
            $allCoinInfo=$sCoin->getAllCoinInfo('coin_id');

            foreach($data as $value)
            {
                $code=$allCoinInfo[$value]['code'];

                if($code) $resData[]=$code;
            }

            if(!$resData) $resData='--';
        }

        if($resData=='--')
        {
            return array();
        }

        return $resData;
    }

    //获取用户关注币的code的id的数组
    public function getUserConcernCoinsId()
    {
        $userInfo = get_current_user_data();
        $uid = $userInfo['uid'];
        if (!$uid)
        {
            return array();
        }

        $dUserFavorite=D("UserFavorite");
        $data=$dUserFavorite->getUserFavoriteByType($uid,2);

        $cacheKey=md5("user_all_concern_coins_".serialize($data));
        $resData=S($cacheKey);

        if(!$resData && $resData!='--')
        {
            $resData=array();

            $sCoin=SVC("Coin");
            $allCoinInfo=$sCoin->getAllCoinInfo('coin_id');

            foreach($data as $value)
            {
                $code=$allCoinInfo[$value]['coin_id'];

                if($code) $resData[]=$code;
            }

            if(!$resData) $resData='--';
        }

        if($resData=='--')
        {
            return array();
        }

        return $resData;
    }



    //统计用户各类喜好的数量
    public function countUserFavorite($uid)
    {
        $dUserFavorite=D('UserFavorite');
        $countUserFavorite = $dUserFavorite->countUserFavorite($uid);

        if(!$countUserFavorite) $countUserFavorite=array();

        return $countUserFavorite;
    }

    //通过用户id获取用户信息
    public function getUserInfoByUid($uid)
    {
        $dUserInfo = D('UserInfo');
        $where = array(
            'id' => $uid
        );
        $data = $dUserInfo->where($where)->field('id,uuid,password,password_salt,head_pic_url,nick,info,mobile,email,wechat,weibo,twitter')->select();

        if(!$data) $data=array();

        return $data;
    }

    //通过条件获取用户信息
    public function saveUserInfo($where,$save)
    {
        $dUserInfo = D("UserInfo");
        $data = $dUserInfo->where($where)->save($save);

        if(!$data) $data=array();

        return $data;
    }

    //获取用户注册协议
    public function getRegProtocol()
    {
        $cacheKey=md5("user_protocol_d41d482eed3afbc926fefd96644d2269");
        $regProtocol=S($cacheKey);

        if(!$regProtocol)
        {
            $id="d41d482eed3afbc926fefd96644d2269";
            $dNews=D("News");
            $data=$dNews->getNewsViewById($id);

            $regProtocol=array(
                'title'=>$data['title'],
                'content'=>htmlspecialchars_decode($data['content']),
            );

            S($cacheKey,$regProtocol,1800);
        }

        if(!$regProtocol)
        {
            return array();
        }

        return $regProtocol;
    }
}