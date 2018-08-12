<?php

namespace Addon\ajax\Controller;

/**
 * 行情接口信息
 * Class MarketController
 * @package Addon\ajax\Controller
 */
class MarketController extends BaseController
{
        //排行前面币行情信息
        public function get_quotations()
        {
            $sMarket=SVC("Market");
            $data=$sMarket->getQuotations();

            if(!$data && $data!==array())
            {
                $this->ajaxCallMsg('500','获取数据失败',$data);
            }

            $this->ajaxCallMsg('0','succ.',$data);
        }

        //排行前面币行情信息
        public function quotations_list()
        {
            $sMarket=SVC("Market");
            $type=I('type','');
            $page=I('page',0,'intval');

            //$data=$sMarket->getQuotationsList($type,$page,20);
            $data=$sMarket->getQuotationsListByAll($type,$page,20,1);
            if(!$data && $data!==array())
            {
                $this->ajaxCallMsg('500','获取数据失败',$data);
            }

            $this->ajaxCallMsg('0','succ.',$data);
        }

        //获取coin的实时行情宝行涨跌幅，价格和来源信息
        public function get_realtime_quotation()
        {
            $code=I('code','');
            $type=I('type',0);
            $source_type = I('source_type',1);
            if(!$code)
            {
                $this->ajaxCallMsg('200','code不能为空');
            }

            if(!in_array($type,array(0,1)))
            {
                $this->ajaxCallMsg('200','错误的获取信息类型');
            }

            $sMarket=SVC("Market");
            $data=$sMarket->getQuotationsInfoByCodes($code,$type,$source_type);
            if(!$data && $data!==array())
            {
                $this->ajaxCallMsg('500','获取数据失败',$data);
            }

            $this->ajaxCallMsg('0','succ.',$data);
        }


        //获取涨跌家数统计
        public function up_down_count()
        {
            $sMarket=SVC('Market');
            $upDownCount=$sMarket->getCoinUpDownCount();
            if(!$upDownCount)
            {
                $this->ajaxCallMsg('500','fail.');
            }

            $this->ajaxCallMsg('0','succ.',$upDownCount);
        }

        //行情排行榜
    public function quotations_rank_list()
    {
        $isIndex=I('is_index');
        $page = I('page');
        $type = I('type');
        if($isIndex=='0')
        {
            $limit=10;
        }
        elseif($isIndex == '1')
        {
            $limit=5;
        }
		else
        {
            $limit = 50;
        }

        if(!in_array($isIndex,array(0,1,2)))
        {
            $this->ajaxCallMsg('400','is_index参数传递错误！');
        }
        $sMarket=SVC('Market');
        $rankList=$sMarket->getRankListOfPage($type,$limit,$page);

        if(!$rankList)
        {
            $this->ajaxCallMsg('400','fail.');
        }
        $this->ajaxCallMsg('0','succ.',$rankList);
    }

    //获取用户关注币的行情web端
    public function get_user_concern_coin()
    {
        $page = I('page',1);
        $rankType = I('type');
        $userInfo = get_current_user_data();
        $uid = $userInfo['uid'];
        if(!$uid)
        {
            $this->ajaxCallMsg('400','用户没有登录');
        }
        $sMarket=SVC('Market');
        $userConcernCoinInfo = $sMarket->getUserConcernCoinInfo($uid,$page,$rankType);
        if(!$userConcernCoinInfo)
        {
            $this->ajaxCallMsg('400','该页没有数据');
        }
        $this->ajaxCallMsg('0','succ.',$userConcernCoinInfo);
    }


    //币及交易所排行榜分页接口
    public function get_page_info_of_rank()
    {
        $pageType = I('page_type');//1为币榜单分页信息，2为交易所榜单信息
        $sMarket=SVC('Market');
        $numOfRank = $sMarket->getPageInfoOfRank($pageType);
        $this->ajaxCallMsg('0','succ.',$numOfRank);

    }
}