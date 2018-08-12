<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/3
 * Time: 22:28
 */

namespace Home\Controller;


class UserController extends BaseController
{
        public function center()
        {
            $userInfo=get_current_user_data();
            $sUser=SVC("User");

            if(!$userInfo)
            {
                redirect('/');
            }


            $userInfo=$sUser->getUserInfoByUid($userInfo['uid']);
            $userInfo['set_password']=1;
            if($userInfo[0]["password"])
            {
                $userInfo['set_password']=0;
            }

            $countUserFavorite = D('UserFavorite')->countUserFavorite($userInfo[0]['id']);
            $this->assign("countUserFavorite",$countUserFavorite);
            $this->assign("userInfo",$userInfo);
            $this->display("center");
        }

        public function get_test()
        {
            $sMarket=SVC("Market");

            $data=$sMarket->getQuotationsListByAll();

            var_dump($data);
        }
}