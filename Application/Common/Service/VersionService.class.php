<?php

namespace Common\Service;

/**
 * 版本管理服务层
 * Class UserService
 * @package Common\Service
 */
class VersionService extends BaseService
{
    public function getNewestVersionInfo($device)
    {
        $dVersion = D("Version");
        $cacheKey=md5("key_of_version".$device);
        $keyOfVersion=S($cacheKey);
        //$keyOfVersion = null;
        if(!$keyOfVersion && $keyOfVersion !='--')
        {
            $keyOfVersion = $dVersion->getNewVersion($device);
            S($cacheKey,$keyOfVersion,60);
        }
        if(!$keyOfVersion)
        {
            return array();
        }
        return $keyOfVersion;

    }



}