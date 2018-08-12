<?php

namespace Addon\api\Controller;

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
            $params=$this->params;
            $keyword=$params['keyword']?$params['keyword']:'';
            $page=$params['page']>1?(int)$params['page']:1;
            $data=array();

            if($keyword)
            {
                $sSearch=SVC("Search");
                $data=$sSearch->searchNewsAndCoin($keyword,$page,20);
            }

            $this->apiOutputData('0','succ.',$data);
        }

        //关键词联想处理接口
        public function keyword()
        {
            $data=array();
            $params=$this->params;
            $keyword=$params['keyword']?$params['keyword']:'';
            $type=$params['type']?$params['type']:1;

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

            $this->apiOutputData('0','succ.',$data);
        }
}