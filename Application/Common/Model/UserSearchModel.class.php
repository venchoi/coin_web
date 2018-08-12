<?php

namespace Common\Model;

/**
 * 用户搜索关键词模型
 * Class UserSearchModel
 * @package Common\Model
 */
class UserSearchModel extends DataBaseModel
{
    protected $tableName="user_search";

    //增加用户搜索关键词
    public function addUserSearchKeyword($data)
    {
        $re=$this->add($data);

        return $re;
    }

    //24小时热搜
    public function getUserHotSearchCoinList()
    {
        $dayTime=date("Y-m-d H:i:s", strtotime("-1 day"));
        $where['create_time'] = array('EGT',$dayTime);
        $map['type'] = 1;

        $re = $this ->where($where)->where($map)->field('search_word,count(search_word) as rank')->group('search_word')->order('rank desc')->limit(10)->select();
        $data = array_column($re,'search_word');
        return $data?$data:array();
    }
}