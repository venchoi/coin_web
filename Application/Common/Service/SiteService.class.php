<?php
namespace Common\Service;

class SiteService extends BaseService
{
    //获取某事物下的类别标签，type=1为新闻
    public function getSiteClassTags($type=1)
    {
        $cacheKey=md5("site_class_tags_type_123123_".$type);
        $data=S($cacheKey);

        if(!$data && $data !='--')
        {
            $dSiteClassTag=D("SiteClassTag");

            $where=array('type'=>$type);
            $data=$dSiteClassTag->getSiteClassTags($where);

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

        return $data;
    }

    //app自定义推荐币
    public function  getUserDefineCoinCode()
    {
        $dRecommendCoin = D('RecommendCoin');
        $userDefineRecommendCoinInfo = $dRecommendCoin->getUserDefineRecommendCoin();
        $currentTimeStamp = time();
        //$currentTimeStamp = strtotime(date("Y-m-d 00:00:00"));
        foreach($userDefineRecommendCoinInfo as $k=>$v)
        {
            switch ($v['type'])
            {
                case '0':
                    if(($currentTimeStamp - strtotime($v['create_time']))>86400)
                    {
                        break;
                    }
                    else
                    {
                        $userDefineRecommendCoinCode[] = $v['coin_code'];
                        break;
                    }

                case '1':
                    if(($currentTimeStamp - strtotime($v['create_time']))>86400*7)
                    {
                        break;
                    }
                    else
                    {
                        $userDefineRecommendCoinCode[] = $v['coin_code'];
                        break;
                    }

                case '2':
                    if(($currentTimeStamp - strtotime($v['create_time']))>86400*30)
                    {
                        break;
                    }
                    else
                    {
                        $userDefineRecommendCoinCode[] = $v['coin_code'];
                        break;
                    }
            }
        }
        if(!$userDefineRecommendCoinCode)
        {
            return array();
        }
        return $userDefineRecommendCoinCode;
    }


    //app获取推荐币种
    public function getRecommendCoin()
    {
        $userInfo = get_current_user_data();
        $uid = $userInfo['uid'];

        //获取用户已经关注的币种
        $dCoin = D('Coin');
        $dUserFavorite=D("UserFavorite");
        $dataOfUserCoin=$dUserFavorite->getUserFavoriteByType($uid,2);


        //设置10分钟缓存
        $cacheKey=md5('get_recommend_coin'.serialize($dataOfUserCoin));
        $recommendCoinsList=S($cacheKey);
        //$recommendCoinsList = null;
        if(!$recommendCoinsList && $recommendCoinsList != '--')
        {
            $coinCodeUserConcern = $dCoin->getCoinCodeById($dataOfUserCoin);
            $sCoin=SVC("Coin");
            $allCoin=$sCoin->getAllCoinInfo('code');

            //热门关注
            $dUserFavorite = D('UserFavorite');
            $topConcernCoinCode = $dUserFavorite->getTopConcernCoinList();

            //后台自定义推荐币
            $dRecommendCoin = D('RecommendCoin');
            $userDefineRecommendCoinCode = $this->getUserDefineCoinCode();
            //var_dump($userDefineRecommendCoinCode);die;
            //小众币种
            $downConcernCoinCode = $dUserFavorite->getDownConcernCoinList();
            //var_dump($downConcernCoinCode);die;
            //新币发行
            $dNewCoin = D('NewCoin');
            $newCoinInfoWithinOneWeek = $dNewCoin->getNewCoinListWithinOneWeek();
            $newCoinCodeWithinOneWeek = array_column($newCoinInfoWithinOneWeek, 'code');

            //24小时热搜
            $dUserSearch = D('UserSearch');
            $userHotSearchCoinCode = $dUserSearch->getUserHotSearchCoinList();
            //各种榜单前三名

            //涨幅榜
            $sMarket = SVC('Market');
            $raiseRatioRank = $sMarket->getRankListByType('+ratio',3,1);
            $raiseRatioRankCode = array_column($raiseRatioRank,'code');

            //var_dump($raiseRatioRankCode);die;
            //跌幅榜
            $downRatioRank = $sMarket->getRankListByType('-ratio',3,1);
            $downRatioRankCode = array_column($downRatioRank,'code');

            //市值榜
            $topThreeMarketRank = $sMarket->getRankListByType('+market_value',3,1);
            $topThreeMarketRankCode = array_column($topThreeMarketRank,'code');

            //成交榜
            $topThreeAmountRank = $sMarket->getRankListByType('+total_amount',3,1);
            $topThreeAmountRankCode = array_column($topThreeAmountRank,'code');

            //合并以上数组
            $recommendCoinsList = array_merge(
                $topConcernCoinCode,
                $userDefineRecommendCoinCode,
                $downConcernCoinCode,
                $newCoinCodeWithinOneWeek,
                $userHotSearchCoinCode,
                $raiseRatioRankCode,
                $downRatioRankCode,
                $topThreeMarketRankCode,
                $topThreeAmountRankCode
                );

            //去重
            $recommendCoinsList = array_unique($recommendCoinsList);
            //去掉用户已经关注的币种
            $recommendCoinsList = array_diff($recommendCoinsList, $coinCodeUserConcern);

            //从用户关注里面任意取出五个币种的键名
            $randCoinArray = array_rand($recommendCoinsList, 5);

            //从用户关注里面任意取出五个币种的键值
            foreach ($randCoinArray as $k => $v)
            {
                $recommendFiveCoinsList[] = $recommendCoinsList[$v];
            }

            //获取推荐币
            foreach ($recommendFiveCoinsList as $k => $v)
            {
                $exchange = $dNewCoin->getExchangeByCoinCode($v);
                $reasonOfUserDefine = $dRecommendCoin->getReasonByCoinCode($v);
                if (in_array($v, $userDefineRecommendCoinCode))
                {
                    $reason = $reasonOfUserDefine;
                }
                elseif (in_array($v, $topConcernCoinCode))
                {
                    $reason = "热门关注";
                }
                elseif (in_array($v, $newCoinCodeWithinOneWeek))
                {
                    $reason = "一周内上线" . $exchange;
                }
                elseif (in_array($v, $raiseRatioRankCode))
                {
                    $reason = "涨幅榜前三" ;
                }
                elseif (in_array($v, $downRatioRankCode))
                {
                    $reason = "跌幅榜前三" ;
                }
                elseif (in_array($v, $topThreeMarketRankCode))
                {
                    $reason = "市值榜前三";
                }
                elseif (in_array($v, $topThreeAmountRankCode))
                {
                    $reason = "交易额榜前三";
                }
                elseif (in_array($v, $userHotSearchCoinCode))
                {
                    $reason = "24小时热搜";
                }
                else
                {
                    $reason = "小众币";
                }
                $coinInfo=$allCoin[$v];
                $quotationOfRecommmendCoin = $re = $sMarket->getCoinsQuotationOfOurExchangeData(array($v));

                if(!$coinInfo['big_logo_url'])
                {
                    $bigLogo = '--';
                }
                else
                {
                    $bigLogo = $coinInfo['big_logo_url'];
                }

                if(!$coinInfo['small_logo_url'])
                {
                    $smallLogo = '--';
                }
                else
                {
                    $smallLogo = $coinInfo['small_logo_url'];
                }

                if(!$quotationOfRecommmendCoin[$v]['price'])
                {
                    $price = 0.00;
                }
                else
                {
                    $price = $quotationOfRecommmendCoin[$v]['price'];
                }

                if(!$quotationOfRecommmendCoin[$v]['ratio'])
                {
                    $ratio = '0.00%';
                }
                else
                {
                    $ratio = $quotationOfRecommmendCoin[$v]['ratio'];
                }

                if(!$quotationOfRecommmendCoin[$v]['source_name'])
                {
                    $source_name = '--';
                }
                else
                {
                    $source_name = $quotationOfRecommmendCoin[$v]['source_name'];
                }

                $listData[] = array(
                    'reason' => $reason,
                    'code' => $v,
                    'coin_id'=>$coinInfo['coin_id'],
                    'price' => $price,
                    'ratio' => $ratio,
                    'source_name' => $source_name,
                    'big_logo_url'=>$bigLogo,
                    'small_logo_url'=>$smallLogo,
                );
            }
            $recommendCoinsList = array('dataList' => $listData);
            S($cacheKey, $recommendCoinsList, 600);
        }

        if($recommendCoinsList=='--')
        {
            return array();
        }
        return $recommendCoinsList;
    }

}