<?php

namespace Common\Model;

/**
 * tdk规则模型
 * Class SiteAdvListModel
 * @package Common\Model
 */
class SiteTkdRuleModel extends DataBaseModel
{
    protected $tableName="site_tkd_rule";

    public function getPageSdk($host,$url)
    {
        $seo = $this -> where(array('url_rule'=>$url,'host_rule'=>$host))->find();

        return $seo;
    }
}