<?php

namespace Common\Service;

/**
 * 广告服务层
 * Class AdvService
 * @package Common\Service
 */
class AdvService extends BaseService
{
    private $pageAdvSystemCodeConfig=array(
        'pc'=>array(
            'index'=>array('pc_index_top_right'),        //首页广告，包含快讯，深度，关注
            'news_id_x'=>array(''),               //新闻内页广告
        ),
    );

    //按页面配置的system_code获取页面广告信息
    public function getAdvByPageSystemCodeConf($client,$page,$field="")
    {
        $cacheKey=md5($client.$page.$field);
        $advData=S($cacheKey);
        //$advData=null;
        if(!$advData && $advData!='--')
        {
            $pageAdvConfig=$this->pageAdvSystemCodeConfig;
            $advSystemCodes=$pageAdvConfig[$client][$page];

            $dSiteAdvList=D("SiteAdvList");
            $advData=$dSiteAdvList->getAdvsBySystemCode($advSystemCodes,$field);

            $advData=$advData?$advData:'--';

            S($cacheKey,$advData,180);
        }

        if($advData=='--')
        {
            return array();
        }

        return $advData;
    }
}