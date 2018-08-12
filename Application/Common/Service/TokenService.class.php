<?php
namespace Common\Service;
class TokenService extends BaseService
{
    //写入token
    public function setToken($userid,$token)
    {
        //把token写到redis
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->hset('app_user_list',$userid, $token);

        return $token;
    }

    //取出token
    public function getToken($userid)
    {
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $token = $redisClient->hget('app_user_list',$userid);

        return $token;
    }

    public function deleteToken($userid)
    {
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(0);
        $redisClient->hdel('app_user_list',$userid);
    }
}