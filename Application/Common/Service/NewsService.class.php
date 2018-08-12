<?php

namespace Common\Service;

/**
 * 新闻服务层
 * Class NewsService
 * @package Common\Service
 */
class NewsService extends BaseService
{
    private $newsCatalogConf=array(
        //类型key=>类型信息array(新闻分类id，需查字段,条数)
        'fast'=>array(array(200,300),'t_news.id,uuid,t_news.state,catalog_id,title,description,channel_id,source_thumb_url,source_url,source_name,thumb_url,video_url,recommend_level,attitude_up_count,attitude_down_count,reprint_count,t_news.update_time',20),            //7x24小时快讯
        'deep'=>array(200,'t_news.id,uuid,t_news.state,title,description,channel_id,source_url,source_name,source_thumb_url,thumb_url,video_url,attitude_up_count,attitude_down_count,reprint_count,t_news.update_time',20)            //深度
    );

    private $hotNewsCatalogConf=array(
        //类型key=>类型信息array(新闻分类id，需查字段,条数)
        'fast'=>array(300,'id,uuid,state,title,description,channel_id,source_url,source_name,source_thumb_url,thumb_url,recommend_level,attitude_up_count,attitude_down_count,reprint_count,update_time',20),            //7x24小时快讯
        'deep'=>array(200,'id,uuid,state,title,description,channel_id,source_url,source_name,source_thumb_url,thumb_url,attitude_up_count,attitude_down_count,reprint_count,update_time',20)            //深度
    );

    private $sensitiveWords=array('金色财经','链向财经','【','】','币世界','小葱');

    //根据分类配置获取最新的新闻列表(暂时用于轮询最新的新闻)
    public function getLatestNewsNumByCateConf($cateName,$timeStamp)
    {
        $cacheKey=md5('latest_news_num_'.$cateName.$timeStamp);
        $latestNewsInfo=S($cacheKey);
//        $latestNewsInfo=null;
        if(!$latestNewsInfo && $latestNewsInfo!='--')
        {
            $newsCatalogConf=$this->newsCatalogConf;
            $catalogId=$newsCatalogConf[$cateName][0];

            $dNews=D("News");
            $latestNewsInfo=$dNews->getLatestNewsNumByCatalogId($catalogId,array(),$timeStamp);

            S($cacheKey,$latestNewsInfo,10);
        }

        return $latestNewsInfo;
    }

    //根据分类配置获取最新的新闻列表(暂时用于轮询最新的新闻)
    public function getLatestNewsListByCateConf($cateName,$field,$timeStamp)
    {
        $cacheKey=md5('latest_'.$cateName.$field.$timeStamp);
        $newsList=S($cacheKey);
//        $newsList=null;
        if(!$newsList && $newsList!='--')
        {
            $newsCatalogConf=$this->newsCatalogConf;
            $catalogId=$newsCatalogConf[$cateName][0];
            $searchField=$field;
            if(!$searchField)
            {
                $searchField=$newsCatalogConf[$cateName][1];
            }

            $dNews=D("News");
            $newsList=$dNews->getLatestNewsListByCatalogId($catalogId,array(),$searchField,$timeStamp);

            switch($cateName)
            {
                case "fast":
                    $newsList=$this->handleFastNewsList($newsList);
                    break;
                case "deep":
                    $newsList=$this->handleDeepNewsList($newsList);
                    break;
            }

            if(!$newsList)
            {
                $newsList='--';
            }

            S($cacheKey,$newsList,10);
        }

        if($newsList=='--')
        {
            return array();
        }

        return $newsList;
    }

    //根据分类配置获取新闻列表
    public function getNewsListByCateConf($cateName,$field,$limit,$minId='',$classTag)
    {
        $cacheKey=md5($cateName.$field.$limit.$minId.$classTag);
        $newsList=S($cacheKey);
//        $newsList=null;
        if(!$newsList && $newsList!='--')
        {
            $newsCatalogConf=$this->newsCatalogConf;
            $catalogId=$newsCatalogConf[$cateName][0];
            $searchField=$field;
            if(!$searchField)
            {
                $searchField=$newsCatalogConf[$cateName][1];
            }

            $searchLimit=$limit;
            if(!$searchLimit)
            {
                $searchLimit=$newsCatalogConf[$cateName][2];
            }

            $dNews=D("News");
            if($classTag)
            {
                $where=array('t_news_tag.tag_type'=>4,'t_news_tag.tag_id'=>(int)$classTag);
                $newsList=$dNews->getNewsListByCatalogIdAndClassTag($catalogId,$where,$searchField,$searchLimit,$minId);
            }
            else
            {
                $newsList=$dNews->getNewsListByCatalogId($catalogId,array(),$searchField,$searchLimit,$minId);
            }

            switch($cateName)
            {
                case "fast":
                    $newsList=$this->handleFastNewsList($newsList);
                    break;
                case "deep":
                    $newsList=$this->handleDeepNewsList($newsList);
                    break;
            }

            if(!$newsList)
            {
                $newsList='--';
            }

            S($cacheKey,$newsList,60);
        }

        if($newsList=='--')
        {
            return array();
        }

        return $newsList;
    }

    //获取用户收藏新闻
    public function getUserCollectNews($newsIds)
    {
        $cacheKey=md5('user_news_collect_'.serialize($newsIds));
        $newsList=S($cacheKey);
        $newsIdsKeyArr=array_keys($newsIds);
//        $newsList=null;
        if(!$newsList && $newsList != '--')
        {
            $dNews=D("News");
            $where=array();

            if($newsIdsKeyArr && is_array($newsIdsKeyArr))
            {
                $where['id']=array('in',$newsIdsKeyArr);

                $newsIdsStr=implode(',',$newsIdsKeyArr);
                $order="field(id,$newsIdsStr)";

                $newsList=$dNews->getNewsListByWhere($where,$order);

                $newsList=$this->handleUserCollectNews($newsList,$newsIds);
            }

            if(!$newsList)
            {
                $newsList='--';
            }

            S($cacheKey,$newsList,600);
        }

        if($newsList=='--')
        {
            return array();
        }

        return $newsList;
    }

    //快讯数据处理
    public function handleFastNewsList($newsList,$isSearch=0,$clearSearchEm=0)
    {
        $dNewsTag=D("NewsTag");
        $dataList=array();
        $dateDataList=array();
        $newsTagsName=array();
        $newsCoinTag=array();

        $newsIds=array_column($newsList,'id');
        if(!$newsIds)
        {
            $newsIds=array();
        }

        $minId=min($newsIds);
        if(!$minId)
        {
            $minId="";
        }

        if($newsIds)
        {
            $sMarket=SVC("Market");
            $coinsCode=array();
            $newsCoinTag=$dNewsTag->getNewsCoinTag($newsIds,array(),1,$coinsCode);
            $newsCoinTag=$sMarket->handleNewsCoinTagQuotations($newsCoinTag,$coinsCode);

            $newsTagsName=$dNewsTag->getNewsTagsName($newsIds);
        }

        foreach($newsList as $key=>&$item)
        {
            if(!in_array($item['catalog_id'],array(200,300)))
            {
                continue;
            }

            $coins=array();
            if($newsCoinTag[$item['id']])
            {
                $coins=array_slice($newsCoinTag[$item['id']],0,3);
            }

            $updateTimeStamp=strtotime($item['update_time']);

            $date=date("Ymd",$updateTimeStamp);

            $title='【'.str_replace($this->sensitiveWords,'',$item['title']).'】';

            if($isSearch)
            {
                $abstract=str_replace($this->sensitiveWords,'',$item['content']);

                if($clearSearchEm)
                {
                    $title=str_replace(array('<em>','</em>'),'',$title);
                    $abstract=str_replace(array('<em>','</em>'),'',$abstract);
                }
            }
            else
            {
                $abstract=str_replace($this->sensitiveWords,'',$item['description']);
            }

            $channelIcon=$this->getChannelIcon($item['channel_id'],$item['source_thumb_url']);

            $item=array(
                "news_id"=>$item['id'],
                "news_uuid"=>$item['uuid'],
                "catalog_id"=>$item['catalog_id'],
                "title"=>$title,
                "abstract"=>$abstract,
                "google_translation"=>array(
                    "title"=>"",
                    "abstract"=>""
                ),
                "tags"=>$newsTagsName[$item['id']], // 标签
                "source_url"=>$item['source_url'], // 源链接
                "thumb_url"=>$item['thumb_url'], //缩略图
                "video_url"=>$item['video_url'], //视频地址
                "channel_icon"=>$channelIcon,  //待确定
                "source_name"=>$item['source_name'],
                "coins"=>$coins,
                "recommend_level"=>$item["recommend_level"],
                "date"=>$date,
                "update_time"=>$updateTimeStamp,
                "lang_time"=>deal_min($updateTimeStamp,'ch')
            );

            $dateDataList[$date][]=$item;
        }

        krsort($dateDataList);

        $weekArray=array("日","一","二","三","四","五","六");
        foreach($dateDataList as $key=>$data)
        {
            $isOut='';
            $timeStamp=strtotime($key);
            $langDate=deal_date($key,'ch','m月d日',$isOut);
            $dateWeek=date("m月d日",$timeStamp).' '."星期".$weekArray[date("w",$timeStamp)];
            if($isOut)
            {
                $dateWeek="星期".$weekArray[date("w",$timeStamp)];
            }

            $dataList[]=array(
                'date'=>$key,
                'lang_date'=>$langDate,
                'date_week'=>$dateWeek,
                'news_list'=>array_sort($data,'update_time','desc'),
            );
        }

        $data=array('min_id'=>$minId,'newsIds'=>$newsIds,'dataList'=>$dataList);

        return $data;
    }

    //深度新闻数据处理
    public function handleDeepNewsList($newsList)
    {
        $dataList=array();
        $newsTagsName=array();

        $newsIds=array_column($newsList,'id');
        if(!$newsIds)
        {
            $newsIds=array();
        }

        $minId=min($newsIds);
        if(!$minId)
        {
            $minId="";
        }

        if($newsIds)
        {
            $dNewsTag=D("NewsTag");
            $newsTagsName=$dNewsTag->getNewsTagsName($newsIds);
        }

        foreach($newsList as $key=>&$item)
        {
            $updateTimeStamp=strtotime($item['update_time']);

            $title='【'.str_replace($this->sensitiveWords,'',$item['title']).'】';
            $abstract=str_replace($this->sensitiveWords,'',$item['description']);
            $channelIcon=$this->getChannelIcon($item['channel_id'],$item['source_thumb_url']);

            $item=array(
                "news_id"=>$item['id'],
                "news_uuid"=>$item['uuid'],
                "tag"=>$newsTagsName[$item['id']], // 只有一个标签的
                "title"=>$title,
                "abstract"=>$abstract,
                "thumb_url"=> $item['thumb_url'], // 文章配图url
                "video_url"=>$item['video_url'], //视频地址
                "channel_icon"=>$channelIcon,  //待确定
                "source_name"=>$item['source_name'],
                "time"=>date("m月d日 H:i",$updateTimeStamp),
                "update_time"=>$updateTimeStamp,
            );

            $dataList[]=$item;
        }

        $dataList=array_sort($dataList,'update_time','desc');

        $data=array('min_id'=>$minId,'newsIds'=>$newsIds,'dataList'=>$dataList);

        return $data;
    }

    //处理用户收藏新闻
    public function handleUserCollectNews($newsList,$newsIds)
    {
        $dataList=array();
        $newsTagsName=array();
        $minTime='';
        $newsListIdsArr=array();

        if($newsIds && $newsList)
        {
            $newsListIdsArr=array_column($newsList,'id');
            $dNewsTag=D("NewsTag");
            $newsTagsName=$dNewsTag->getNewsTagsName($newsListIdsArr);

            $sMarket=SVC("Market");
            $coinsCode=array();
            $newsCoinTag=$dNewsTag->getNewsCoinTag($newsListIdsArr,array(),1,$coinsCode);
            $newsCoinTag=$sMarket->handleNewsCoinTagQuotations($newsCoinTag,$coinsCode);
        }

        foreach($newsList as $key=>&$item)
        {
            $updateTimeStamp=strtotime($item['update_time']);

            $coins=array();
            if($newsCoinTag[$item['id']])
            {
                $coins=array_slice($newsCoinTag[$item['id']],0,3);
            }

            $title='【'.str_replace($this->sensitiveWords,'',$item['title']).'】';
            $abstract=str_replace($this->sensitiveWords,'',$item['description']);
            $channelIcon=$this->getChannelIcon($item['channel_id'],$item['source_thumb_url']);

            $item=array(
                "news_id"=>$item['id'],
                "news_uuid"=>$item['uuid'],
                "favorite_id"=>$newsIds[$item['id']]['id'],
                "catalog_id"=>$item['catalog_id'],
                "title"=>$title,
                "abstract"=>$abstract,
                "google_translation"=>array(
                    "title"=>"",
                    "abstract"=>""
                ),
                "tags"=>$newsTagsName[$item['id']], // 标签
                "source_url"=>$item['source_url'], // 源链接
                "thumb_url"=>$item['thumb_url'], //缩略图
                "video_url"=>$item['video_url'], //视频地址
                "channel_icon"=>$channelIcon,  //待确定
                "source_name"=>$item['source_name'],
                "coins"=>$coins,
                "recommend_level"=>$item["recommend_level"],
                "time"=>date("m月d日 H:i",$updateTimeStamp),
                "update_time"=>$updateTimeStamp,
                "collect_time"=>strtotime($newsIds[$item['id']]['update_time']),
            );

            $dataList[]=$item;
            $minTime=$dataList[count($dataList)-1]['collect_time'];
        }

        $data=array('min_time'=>$minTime,'newsIds'=>$newsListIdsArr,'dataList'=>$dataList);

        return $data;
    }

    //根据热文分类配置获取热文新闻列表
    public function getHotNewsListByCataConf($cateName,$field,$limit=5,$minId)
    {
        $cacheKey=md5($cateName.$field.$limit.$minId.'hot_news');
        $newsList=S($cacheKey);
        //$newsList=null;
        if(!$newsList && $newsList!='--')
        {
            $newsCatalogConf=$this->hotNewsCatalogConf;
            $catalogId=$newsCatalogConf[0];
            $searchField=$field;
            if(!$searchField)
            {
                $searchField=$newsCatalogConf[1];
            }

            $searchLimit=$limit;
            if(!$searchLimit)
            {
                $searchLimit=$newsCatalogConf[2];
            }

            $dNews=D("News");
            $newsList=$dNews->getHotNewsListByCatalogId(200,array(),$searchField,$searchLimit,$minId);

            foreach($newsList as &$item)
            {
                $updateTimeStamp=strtotime($item['update_time']);

                $thumbUrl=$this->getChannelIcon($item['channel_id'],$item['sourc_thumb_url']);

                $title='【'.str_replace($this->sensitiveWords,'',$item['title']).'】';
                $abstract=str_replace($this->sensitiveWords,'',$item['description']);

                $item=array(
                    "news_id"=>$item['id'],
                    "news_uuid"=>$item['uuid'],
                    "title"=>$title,//标题
                    "abstract"=>$abstract,//摘要
                    "time"=>date("Y年m月d日 H:i",$updateTimeStamp),   //时间
                    "thumb_url"=>$thumbUrl,  //缩略图
                );
            }

            if(!$newsList)
            {
                $newsList='--';
            }

            S($cacheKey,$newsList,300);
        }

        if($newsList=='--')
        {
            return array();
        }

        return $newsList;
    }

    //获取正文内容
    public function getNewsView($id,$isFavorite=0)
    {
        $cacheKey=md5("news_view_".$id);
        $newsView=S($cacheKey);
        //$newsView=null;
        if( !$newsView && $newsView!='--')
        {
            $dNews=D("News");
            $newsView=$dNews->getNewsViewById($id);
            if($newsView)
            {
                $newsId=$newsView['id'];
                $title=str_replace($this->sensitiveWords,'',$newsView['title']);
                $content=str_replace($this->sensitiveWords,'',$newsView['content']);

                //获取新闻相关币
                $sMarket=SVC("Market");
                $dNewsTag=D("NewsTag");
                $coinsCode=array();
                $newsCoinTag=$dNewsTag->getNewsCoinTag(array($newsId),array(),1,$coinsCode);
                $newsCoinTag=$sMarket->handleNewsCoinTagQuotations($newsCoinTag,$coinsCode);
                $coins=$newsCoinTag[$newsId];
                if(!$coins)
                {
                    $coins=array();
                }

                $channelIcon=$this->getChannelIcon($newsView['channel_id'],$newsView['source_thumb_url']);

                $newsView=array(
                    "news_id"=>$newsView['id'],
                    "news_uuid"=>$newsView['uuid'],
                    "title"=>$title,
                    "coin"=>$coins,
                    "channel_icon"=>$channelIcon,  //待确定
                    "source_name"=>$newsView['source_name'],
                    "content"=>htmlspecialchars_decode($content),
                    "video_url"=>$newsView['video_url'], //视频地址
                    "update_time"=>$newsView['update_time'],
                );
            }

            if(!$newsView)
            {
                $newsView='--';
            }

            S($cacheKey,$newsView,300);
        }

        if($newsView=='--')
        {
            return array();
        }

        //设置获取喜好时,获取用户的对应数据
        if($newsView && $isFavorite)
        {
            $userInfo=get_current_user_data();


            $sUser=SVC("User");

            $re=$sUser->getUserOwnFavorite($userInfo['uid'],'news',array('collect','thumb_up'),array($newsView['news_id']));

            $newsView['is_collect']=$re[0]['is_collect'];
            $newsView['thumb_up_count']=$re[0]['thumb_up'];
            $newsView['is_thumb_up']=$re[0]['is_thumb_up'];
            
        }

        return $newsView;
    }

    public function getAllNewsChannelIcons($key='channel_id')
    {
        $cacheKey="all_channel_icons_asdfsdf_".$key;
        $data=S($cacheKey);

        if(!$data && $data!='--')
        {
            $dNewsChannel=D("NewsChannel");
            switch($key)
            {
                case "channel_id":

                    $data = $dNewsChannel->getAllNewsChannelIconsKeyId();

                    break;
                default:

                    break;
            }

            if(!$data)
            {
                $data='--';
            }

            S($cacheKey,$data,1800);
        }

        if($data == '--')
        {
            return array();
        }

        return $data;
    }

    //获取渠道icon的地址
    public function getChannelIcon($newsChannelId,$sourceThumbUrl)
    {
        if($sourceThumbUrl)
        {
            return $sourceThumbUrl;
        }

        $allChannelIcons=$this->getAllNewsChannelIcons();

        $sourceThumbUrl=$allChannelIcons[$newsChannelId];
        if(!$sourceThumbUrl)
        {
            $sourceThumbUrl='https://lianxiangfiles.oss-cn-beijing.aliyuncs.com/article/vtuckquvz91525673981093.png?x-oss-process=style/news';
        }

        return $sourceThumbUrl;
    }
}