<?php

namespace Common\Model;

/**
 * 网站类别标签模型
 * Class SiteClassTagModel
 * @package Common\Model
 */
class SiteClassTagModel extends DataBaseModel
{
    protected $tableName="site_class_tag";

    public function getSiteClassTags($where=array())
    {
        $where['state']=1;
        $data=$this->field("id,name,tag_type")->where($where)->select();

        return $data;
    }
}