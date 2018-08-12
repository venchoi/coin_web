<?php

namespace Common\Model;

/**
 * 版本模型
 * Class NewsModel
 * @package Common\Model
 */
class VersionModel extends DataBaseModel
{
    protected $tableName="app_version";

    public function getNewVersion($device)
    {
        $where = array('device'=>$device);
        $re = $this->where($where)->order('create_time desc')->field('app_url,is_force,update_log,version')->find();
        return $re?$re:array();
    }

}