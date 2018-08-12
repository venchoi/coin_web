<?php

namespace Common\Model;

/**
 * 用户喜好模型
 * Class SiteAdvListModel
 * @package Common\Model
 */
class UserInfoModel extends DataBaseModel
{
    protected $tableName="user_info";

    public function getUserInfoByWhere($where=array())
    {
        $re=$this->where($where)->find();



        return $re;
    }

    public function updateUserInfoByWhere($where,$save)
    {
        $re=$this->where($where)->save($save);

        return $re?true:false;
    }

    public function updateUserLogStatus($where)
    {
        $save=array(
            'update_time'=>get_fulltime(),
            'update_ip'=>get_client_addr()
        );

        $re=$this->where($where)->save($save);

        return $re!==false?true:false;
    }

    public function getUserInfoById($uid)
    {
        $where=array(
            'id'=>$uid,
            'state'=>1
        );

        $re=$this->where($where)->find();

        return $re;
    }

    public function updateUserInfo($where,$save)
    {
        $re=$this->where($where)->save($save);

        return $re?true:false;
    }

    //检测所指定字段的记录是否存在，存在返回true，不存在返回false
    public function checkPhoneExist($phone,$areaCode)
    {
        $re = $this->where(array('mobile'=>$phone,'mobile_area_code'=>$areaCode))->find();
        return $re?true:false;
    }
}