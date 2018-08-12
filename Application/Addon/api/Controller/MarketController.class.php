<?php

namespace Addon\api\Controller;

/**
 * 行情接口信息
 * Class MarketController
 * @package Addon\ajax\Controller
 */
class MarketController extends BaseController
{
        //排行前面币行情信息
        public function quotations_list()
        {
            $sMarket=SVC("Market");
            $params=$this->params;
            $type = $params['type'];
            if(!$params['type'])
            {
                $type = '';
            }
            $page = $params['page'];
            if(!$params['page'])
            {
                $page = 1;
            }


            $data=$sMarket->getQuotationsListByAll($type,$page,20,1);
            if(!$data && $data!==array())
            {
                $this->apiOutputData('500','获取数据失败',$data);
            }

            $this->apiOutputData('0','succ.',$data);
        }

        //获取coin的实时行情宝行涨跌幅，价格和来源信息
        public function get_realtime_quotation()
        {
            $params=$this->params;
            $code = $params['codes'];
            if(!$params['codes'])
            {
                $code = '';
            }
            $type = $params['type'];
            if(!$params['type'])
            {
                $type = 0;
            }
            $sourceType = $params['source_type'];
            if(!$params['source_type'])
            {
                $type = 1;
            }


            if(!$code)
            {
                $this->apiOutputData('200','codes不能为空！');
            }

            if(!is_array($code))
            {
                $this->apiOutputData('200','codes为数组！');
            }

            if(!in_array($type,array(0,1)))
            {
                $this->apiOutputData('200','错误的获取信息类型');
            }

            $sMarket=SVC("Market");
            $data=$sMarket->getQuotationsInfoByCodes($code,$type,$sourceType);

            if(!$data && $data!==array())
            {
                $this->apiOutputData('500','获取数据失败',$data);
            }

            $this->apiOutputData('0','succ.',$data);
        }

        //获取新币日历
        public function new_coin_calender()
        {
            $params=$this->params;
            $minTime=$params['min_time'];
            if(!$minTime)
            {
                $minTime = '';
            }
            $sMarket=SVC('Market');
            $newCoinsList=$sMarket->getNewCoinsList($minTime);
            if(!$newCoinsList)
            {
                $this->apiOutputData('500','fail.');
            }

            $this->apiOutputData('0','succ.',$newCoinsList);
        }

        //获取新币日历实时行情
        public function get_new_coin_realtime_quotation()
        {
            $params=$this->params;
            $publishIds=$params['publish_ids'];
            if(!$publishIds || !is_array($publishIds))
            {
                $this->apiOutputData('200','发行id列表不能为空');
            }

            $sMarket=SVC('Market');
            $newCoinRealtimeQuotation=$sMarket->getRealtimeQuotationOfNewCoin($publishIds);

            $this->apiOutputData('0','succ.',$newCoinRealtimeQuotation);
        }

        //获取推荐币,包含主流币和最新币
        public function recommend_coin()
        {
            $sMarket=SVC('Market');
            $recommendCoins=$sMarket->getRecommendCoin();
            if(!$recommendCoins)
            {
                $this->apiOutputData('500','fail.');
            }

            $this->apiOutputData('0','succ.',$recommendCoins);
        }

        //获取涨跌家数统计
        public function up_down_count()
        {
            $sMarket=SVC('Market');
            $upDownCount=$sMarket->getCoinUpDownCount();
            if(!$upDownCount)
            {
                $this->apiOutputData('500','fail.');
            }

            $this->apiOutputData('0','succ.',$upDownCount);
        }

        //行情排行榜
        public function quotations_rank_list()
        {
            $params=$this->params;
            $isIndex=$params['is_index'];
            $type = $params['type'];
            $page = $params ['page'];
            if($isIndex=='0')
            {
                $limit=20;
            }
            elseif($isIndex == '1')
            {
                $limit=5;
            }
            else
            {
                $limit = 12;
            }

            if(!in_array($isIndex,array(0,1,2)))
            {
                $this->apiOutputData('400','is_index参数传递错误！');
            }
            $sMarket=SVC('Market');
            $rankList=$sMarket->getRankListByType($type,$limit,$page);
            //dump($rankList);die;
            if(!$rankList)
            {
                $this->apiOutputData('400','fail.');
            }
            $this->apiOutputData('0','succ.',$rankList);
        }

        //获取推荐币接口
        public function get_recommend_coins()
        {
            $sSite = SVC('Site');
            $recommendCoinList = $sSite->getRecommendCoin();
            if(!$recommendCoinList)
            {
                $this->apiOutputData('400','获取推荐失败！');
            }
            $this->apiOutputData('0','succ.',$recommendCoinList);

        }

}