<?php

namespace Common\Model;

/**
 * 用户极光身份信息模型
 * Class UserSearchModel
 * @package Common\Model
 */
class UserJpushInfoModel extends DataBaseModel
{
    protected $tableName="user_jpush_info";

    public function editUserJpushInfo($params)
    {
        $registerId=(string)$params['register_id'];
        $data=array(
            'client_code'=>(string)$params['client_code'],
            'alias'=>(string)$params['alias'],
            'tag'=>(string)$params['tag'],
            'register_id'=>$registerId,
        );

        $re=$this->where(array("register_id"=>$registerId))->find();
        if($re)
        {
            $data['update_time']=date("Y-m-d H:i:s");
            $res=$this->where(array('id'=>$re['id']))->save($data);
        }
        else
        {
            $res=$this->add($data);
        }

        return $res;
    }
}