<?php

namespace Common\Model;

/**
 * 广告位模型
 * Class SiteAdvListModel
 * @package Common\Model
 */
class SiteAdvListModel extends DataBaseModel
{
    protected $tableName="site_adv_list";

    //主要根据system_code进行数据查询
    public function getAdvsBySystemCode($systemCodes,$field='',$setKey="system_code",$where=array())
    {
        if(empty($systemCodes))
            return array();

        if($field=='')
            $field="title,sub_title,pic_url,target_url,system_code,height,width,start_time,end_time";

        if(is_array($systemCodes))
        {
            $where['system_code']=array('in',$systemCodes);
        }
        else
        {
            $where['system_code']=$systemCodes;
        }

        $advData=$this->where($where)->field($field)->select();

        if($setKey)
        {
            $advData=$this->setDataKey($setKey,$advData);
        }

        return $advData?$advData:array();
    }
}