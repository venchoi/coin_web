<?php

namespace Mobile\Controller;


class UserController extends BaseController
{
        //个人中心主页
        public function center()
        {
            $userInfo=get_current_user_data();
            if(!$userInfo)
            {
                redirect('/');
            }

            $sUser=SVC("User");
            $countUserFavorite = $sUser->countUserFavorite($userInfo['uid']);
            $this->assign("countUserFavorite",$countUserFavorite);

            $this->display("center");
        }

        //个人中心收藏
        public function news_collect()
        {
            $this->display("news_collect");
        }

        //用户关注
        public function concern()
        {
            $this->display("concern");
        }
}