<?php

namespace Common\Model;

/**
 * 系统数据中心模型
 * Class SiteAdvListModel
 * @package Common\Model
 */
class SystemConfigDataModel extends DataBaseModel
{
    protected $tableName="system_config_data";

    /**
     * 获取有explode进行分割的数据
     * @param $systemCode   系统代码
     * @param string $explode 分割符
     * @return array       返回数组
     */
    public function getExplodeConfigDataBySystemCode($systemCode,$explode="\n")
    {
        $cacheKey=md5("br_system_config_data_".$systemCode."_".$explode);
        $data=S($cacheKey);

        if(!$data && $data!='--')
        {
            $where=array('system_code'=>$systemCode,'state'=>1);
            $re=$this->where($where)->find();
            if($re['content'])
            {
                $data=explode($explode,$re['content']);
            }

            $data=$data?:'--';

            S($cacheKey,$data,600);
        }

        if($data=='--')
        {
            return array();
        }

        return $data;
    }

    /**
     * 获取有json字符串格式的数据
     * @param $systemCode   系统代码
     * @return array       返回数组
     */
    public function getJsonConfigDataBySystemCode($systemCode)
    {
        $cacheKey=md5("json_system_config_data_".$systemCode);
        $data=S($cacheKey);

        if(!$data && $data!='--')
        {
            $where=array('system_code'=>$systemCode,'state'=>1);
            $re=$this->where($where)->find();
            if($re['content'])
            {
                $data=json_decode($re['content'],true);
            }

            $data=$data?:'--';

            S($cacheKey,$data,600);
        }

        if($data=='--')
        {
            return array();
        }

        return $data;
    }
}