<?php

namespace Common\Model;

/**
 * 币模型
 * Class NewsModel
 * @package Common\Model
 */
class UserViewModel extends DataBaseModel
{
    protected $tableName="user_view";

    public function addUserViewToSolve($uid,$params)
    {
        $where=array(
            'uid'=>$uid,
            'theme'=>$params['theme'],
            'contact_way'=>$params['contact_way'],
            'description'=>$params['description'],
        );
        $data=$this->create($where);
        if($data)
        {
            $re=$this->add($data);
        }
        return $re=$re?$re:array();
    }

    public function updateUserViewInfoWhere($where=array(),$save=array())
    {
        $re=$this->where($where)->save($save);

        return $re;
    }

}