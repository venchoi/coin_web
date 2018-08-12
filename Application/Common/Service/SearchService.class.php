<?php

namespace Common\Service;

/**
 * 搜索服务层
 * Class SearchService
 * @package Common\Service
 */
class SearchService extends BaseService
{
    public function curlSearch($keyword,$catalogId='',$page,$pageSize=20)
    {
        $url="http://47.74.238.60:10086/search/news.html";

        $postData=array(
            'catalog_id'=>$catalogId,
            'keywords'=>$keyword,
            'page'=>$page,
            'page_size'=>$pageSize,
        );

        $data=curl_post_json($url,$postData,true);

        if($data['error_code']=='0')
        {
            return $data['data'];
        }

        return array();
    }

    //搜索新闻和币
    public function searchNewsAndCoin($keyword,$page=1,$pageSize=20)
    {
        $keyword=$this->handleSearchWord($keyword);
        if(!$keyword) return array();

        $cacheKey=md5('search_coin_news_'.$keyword.$page.$pageSize);
        $data=S($cacheKey);

        if(!$data && $data!='--')
        {
            $searchData=$this->curlSearch($keyword,'',$page);

            $newsList=array();
            if($searchData && $searchData['data_list'])
            {
                $sNews=SVC("News");

                $searchData['data_list']=array_sort($searchData['data_list'],'score','desc');
                $newsList=$sNews->handleFastNewsList($searchData['data_list'],1);

                array_walk($newsList['newsIds'],function(&$value){
                    $value=(string)$value;
                });

                $newsList['count']=$searchData['data_count'];
                $newsList['coins']=array();
            }

            $data=$newsList?:'--';

            if($data !='--' && $data['newsIds'] && $page==1)
            {
                $dNewsTag=D("NewsTag");
                $sMarket=SVC('Market');
                $coins=$dNewsTag->getNewsRelatedCoin($data['newsIds'],array(),1);
                $coins=$sMarket->handleCoinQuotations($coins);
                $coins=$this->rankCoinsSearch($coins,$keyword);

                $data['coins']=$coins?:array();
                $data['coinIds']=$coins?array_column($coins,'coin_id'):array();
              

            }
            S($cacheKey,$data,10);
        }

        if($data=='--')
        {
            return array();
        }

        return $data;
    }

    //搜索新闻
    public function searchNews($keyword,$page=1,$pageSize=20,$searchType=0,$clearSearchEm=0)
    {
        //币的浏览行为单独记录
        if(!in_array($searchType,array(1)))
            $keyword=$this->handleSearchWord($keyword,$searchType);

        if(!$keyword) return array();

        $cacheKey=md5('search_news_'.$keyword.$page.$pageSize);
        $data=S($cacheKey);
//        $data=null;
        if(!$data && $data!='--')
        {
            $searchData=$this->curlSearch($keyword,'',$page);

            $newsList=array();
            if($searchData && $searchData['data_list'])
            {
                $sNews=SVC("News");

                $searchData['data_list']=array_sort($searchData['data_list'],'score','desc');
                $newsList=$sNews->handleFastNewsList($searchData['data_list'],1,$clearSearchEm);

                array_walk($newsList['newsIds'],function(&$value){
                    $value=(string)$value;
                });
            }

            $data=$newsList?:'--';

            S($cacheKey,$data,600);
        }

        if($data=='--')
        {
            return array();
        }

        return $data;
    }

    //获取搜索联想词
    public function getAssociateKeyword($keyword,$limit=10)
    {
        if(!$keyword) return array();

        $cacheKey=md5("associate_coin_keyword_keys");
        $searchKeys=S($cacheKey);

        if(!$searchKeys && $searchKeys!='--')
        {
            $dCoin=D("Coin");
            $coins=$dCoin->getSearchCoins();

            foreach ($coins as $coin)
            {
                $row = '';
                foreach ($coin as $value)
                {
                    if ($value)
                    {
                        $row .= trim($value) . ";";
                    }
                }
                $searchKeys[] = $row;
            }

            $searchKeys=$searchKeys?:'--';

            S($cacheKey,$searchKeys,3600);
        }

        if($searchKeys=='--')   $searchKeys=array();

        //搜索数据
        $result = array();
        mb_regex_encoding('utf-8');//指定编码处理
        foreach ($searchKeys as $searchKey)
        {
            if ($limit > 0)
            {
                if (mb_stripos($searchKey, $keyword) !== false)
                {
                    $limit--;
                    $row      = mb_eregi_replace(';', ' ', $searchKey);
                    $result[] = $row;
                }
            }
            else
            {
                break;
            }
        }

        return $result;
    }

    /**
     * 基础用户搜索行为和搜索词，并返回搜索词
     * @param $keyword
     * @return mixed
     */
    public function handleSearchWord($searchWord,$type=0)
    {
        //var_dump($searchWord,$type);
        if(!$searchWord) return '';

        $uid=0;
        $userInfo=get_current_user_data();
        if($userInfo)   $uid=$userInfo['uid'];

        //过滤掉emjio表情符
        preg_replace_callback('/./u', function (array $match){
                return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $searchWord);

        //用户搜索关键词入库
        $cacheSearchWordKey=md5('user_search_word_'.get_client_addr().$uid.$type.$searchWord);
        $hasCacheSearchWord=S($cacheSearchWordKey);
        if($searchWord && !$hasCacheSearchWord)
        {
            $dUserSearch=D("UserSearch");
            $data=array('uid'=>$uid,'type'=>$type,'search_word'=>$searchWord);

            $dUserSearch->addUserSearchKeyword($data);

            S($cacheSearchWordKey,1,1800);
        }

        return $searchWord;
    }

    /**
     * 根据币code获取币的相关新闻
     * @param $code
     * @param $page
     */
    public function getCoinSearchNews($code,$page,$pageSize=20)
    {
        $sCoin=SVC("Coin");
        $coinInfo=$sCoin->getCoinInfoByCode($code);
        if(!$coinInfo)
        {
            $this->setErrorMsg('400','该币的信息不存在!');
            return false;
        }

        //记录用户币的搜索行为
        $this->handleSearchWord($coinInfo['code'],1);

        $keyword=sprintf('%s%s%s%s',$coinInfo['code'],$coinInfo['ch_name'],$coinInfo['en_name'],$coinInfo['en_short_name']);
        $data=$this->searchNews($keyword,$page,$pageSize,1,1);

        return $data;
    }

    /**
     * 对币进行排序，将搜索的关键币前置
     */
    public function rankCoinsSearch($coins,$keyword)
    {
        foreach($coins as $k=>$v)
        {
            if(mb_stripos(($v['code'].' '.$v['ch_name']),$keyword) !== false)
            {
                $coinSort[]=$v;
            }
        }
        foreach ($coins as $k=>$v)
        {
            if(!in_array($v,$coinSort)){
                $restArray[]=$v;
            }
        }

        $coins=array_merge($coinSort,$restArray);
        return $coins;
    }

}