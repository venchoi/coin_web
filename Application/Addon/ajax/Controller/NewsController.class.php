<?php

namespace Addon\ajax\Controller;

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
            $type = I('type', 'fast');
            $minId = I('min_id', '');
            $classTag = I('class_tag', '');

            if (!in_array($type, array('fast', 'deep')))
            {
                $this->ajaxCallMsg('250', '错误的新闻类型', array());
            }

            $sNews = SVC("News");
            $newsList=$sNews->getNewsListByCateConf($type,'',20,$minId,$classTag);

            $this->ajaxCallMsg('0','succ.',$newsList);
        }

        //最新新闻列表接口（暂时用于轮询）
        public function latest_news_list()
        {
            $type=I('type');
            $maxTime=I("max_time");

            if(!in_array($type,array('fast','deep')))
            {
                $this->ajaxCallMsg('250','错误的新闻类型',array());
            }

            $sNews=SVC("News");
            $newsList=$sNews->getLatestNewsListByCateConf($type,'',$maxTime);

            $this->ajaxCallMsg('0','succ.',$newsList);
        }

        //最新新闻列表接口（暂时用于轮询）
        public function latest_news_num()
        {
            $type=I('type');
            $maxTime=I("max_time");

            if(!in_array($type,array('fast','deep')))
            {
                $this->ajaxCallMsg('250','错误的新闻类型',array());
            }

            $sNews=SVC("News");
            $newsNum=$sNews->getLatestNewsNumByCateConf($type,$maxTime);

            $this->ajaxCallMsg('0','succ.',$newsNum);
        }
}