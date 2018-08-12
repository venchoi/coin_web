<?php

namespace Addon\api\Controller;

/**
 * 新闻相关接口
 * Class NewsController
 * @package Addon\ajax\Controller
 */
class NewsController extends BaseController
{
        //新闻列表接口
        public function news_list()
        {
            $params=$this->params;
            $type = $params['type'] ? $params['type'] : 'fast' ;
            $minId = $params['min_id'] ? $params['min_id'] : '' ;

            if(!in_array($type,array('fast','deep')))
            {
                $this->apiOutputData('200','错误的新闻类型',array());
            }

            $sNews=SVC("News");
            $newsList=$sNews->getNewsListByCateConf($type,'',20,$minId);

            $this->apiOutputData('0','succ.',$newsList);
        }

        //最新新闻列表接口（暂时用于轮询）
        public function latest_news_list()
        {
            $params=$this->params;
            $type = $params['type'];
            $maxTime = $params['max_time'];

            if(!in_array($type,array('fast','deep')))
            {
                $this->apiOutputData('200','错误的新闻类型',array());
            }

            $sNews=SVC("News");
            $newsList=$sNews->getLatestNewsListByCateConf($type,'',$maxTime);

            $this->apiOutputData('0','succ.',$newsList);
        }

        //最新新闻列表接口（暂时用于轮询）
        public function latest_news_num()
        {
            $params=$this->params;
            $type = $params['type'];
            $maxTime = $params['max_time'];

            if(!in_array($type,array('fast','deep')))
            {
                $this->ajaxCallMsg('250','错误的新闻类型',array());
            }

            $sNews=SVC("News");
            $newsNum=$sNews->getLatestNewsNumByCateConf($type,$maxTime);

            $this->ajaxCallMsg('0','succ.',$newsNum);
        }

        //新闻内页接口
        public function news_info()
        {
            $params=$this->params;
            $newsId = $params['news_uuid'];//news_id 为news表中的uuid
            if(!$newsId)
            {
                $this->apiOutputData('200','新闻唯一标识不能为空！');
            }
            $sNews = SVC("News");
            $data = $sNews->getNewsView($newsId,1);
            if(!$data)
            {
                $this->apiOutputData('400','无法获取新闻信息！');
            }
            $this->apiOutputData('0','成功返回深度新闻',$data);
        }


}