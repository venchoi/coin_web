<?php

namespace Common\Model;

/**
 * 用户推送设置模型
 * Class UserSearchModel
 * @package Common\Model
 */
class UserPushSettingModel extends DataBaseModel
{
    protected $tableName="user_push_setting";
    //app推送设置集合
    private $pushNamesArr=array(
        1=>array('big_event','choice','user_concern','quotation_change','fast_news'),   //web
        2=>array('big_event','choice','user_concern','quotation_change','fast_news'),   //app
        3=>array('big_event','choice','user_concern','quotation_change','fast_news'),   //wechat
    );
    //推送设置集合
    private $pushNamesInfo=array(
        'big_event'=>array('name'=>'币圈大事件','description'=>'实时了解币圈重大事件','defaultState'=>1),
        'choice'=>array('name'=>'编辑精选','description'=>'专业编辑精心甄选最有价值的消息','defaultState'=>1),
        'user_concern'=>array('name'=>'我的关注','description'=>'我所有的币的重要消息','defaultState'=>1),
        'quotation_change'=>array('name'=>'行情异动','description'=>'数字货币的最重要消息','defaultState'=>1),
        'fast_news'=>array('name'=>'全部快讯','description'=>'快讯栏目的最新消息','defaultState'=>0),
        'deep_news'=>array('name'=>'全部深度','description'=>'快讯栏目的最新消息','defaultState'=>0),
    );

    //未登录推送设置集合
    public $unLogPushNamesInfo=array(
        array('name'=>'币圈大事件','description'=>'实时了解币圈重大事件','push_code'=>'choice','state'=>1),
        array('name'=>'编辑精选','description'=>'专业编辑精心甄选最有价值的消息','push_code'=>'choice','state'=>1),
        array('name'=>'我的关注','description'=>'我所有的币的重要消息','push_code'=>'user_concern','state'=>0),
        array('name'=>'行情异动','description'=>'数字货币的最重要消息','push_code'=>'quotation_change','state'=>1),
        array('name'=>'全部快讯','description'=>'快讯栏目的最新消息','push_code'=>'fast_news','state'=>0),
    );

    //进行用户推送设置
    public function editUserPushSetting($clientType,$uid,$pushCode,$state)
    {
        $where=$data=array(
            'uid'=>$uid,
            'client_type'=>$clientType,
            'push_name'=>$pushCode,
        );
        $re=$this->where($where)->find();

        $res=true;
        if(!$re)
        {
            $data['state']=$state;
            $res=$this->add($data);
        }
        else if($re['state']!=$state)
        {
            $save=array('state'=>$state);
            $res=$this->where($where)->save($save);
        }

        return $res!==false?true:false;
    }

    //获取用户推送设置
    public function getUserPushSetting($clientType,$uid)
    {
        $where=array('uid'=>$uid,'client_type'=>$clientType);
        $re=$this->where($where)->getField("push_name,state",true);
        $re=$re?:array();

        $pushNamesArr=$this->pushNamesArr[$clientType];
        $pushNamesInfo=$this->pushNamesInfo;
        $existSettings=array_keys($re);

        $diffSetting=array_diff($pushNamesArr,$existSettings);
        if($diffSetting)
        {
            foreach($diffSetting as $settingName )
            {
                $data=array(
                    'uid'=>$uid,
                    'client_type'=>$clientType,
                    'push_name'=>$settingName,
                    'state'=>$pushNamesInfo[$settingName]['defaultState']
                );

                $this->add($data);
            }
        }

        $userPushSetting=array();
        foreach($pushNamesArr as $pushName)
        {
            $state=$re[$pushName]?$re[$pushName]:$pushNamesInfo[$pushName]['state'];
            $userPushSetting[]=array(
                'name'=>$pushNamesInfo[$pushName]['name'],
                'description'=>$pushNamesInfo[$pushName]['description'],
                'push_code'=>$pushName,
                'state'=>(int)$state,
            );
        }

        return $userPushSetting;
    }

    //获取为登录下的设置信息
    public function getUnLogPushSetting()
    {
        return $this->unLogPushNamesInfo;
    }

}