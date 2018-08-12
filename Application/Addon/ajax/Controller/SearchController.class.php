<?php

namespace Addon\ajax\Controller;

/**
 * 搜索相关接口
 * Class UserController
 * @package Addon\ajax\Controller
 */
class SearchController extends BaseController
{
        //关键词搜索接口
        public function s()
        {
            $keyword=I('keyword','');
            $page=I('page',1,'intval');
            $type=I("type",'');

            $sSearch=SVC("Search");
            $data=$sSearch->searchNewsAndCoin($keyword,$page,20);

            $this->ajaxCallMsg('0','succ.',$data);
        }

        //关键词联想处理接口
        public function keyword()
        {
            $data=array();
            $keyword=I("keyword",'');
            $type=I('type',1,'intval');

            if($keyword)
            {
                switch($type)
                {
                    case 1:
                        $sSearch=SVC("Search");
                        $data=$sSearch->getAssociateKeyword($keyword);
                    break;
                }
            }

            $this->ajaxCallMsg('0','succ.',$data);
        }

        //币相关新闻搜索接口
        public function get_coin_news()
        {
            $keyword=I('keyword','');
            $page=I('page',1,'intval');

            $sSearch=SVC("Search");
            $data=$sSearch->getCoinSearchNews($keyword,$page,20);

            $this->ajaxCallMsg('0','succ.',$data);
        }
}