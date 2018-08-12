<?php

require_once __DIR__ . '/autoload.php';

use \OSS\OssClient;

class Oss
{
    private $client=null;
    private $accessId;
    private $accessKey;
    private $endpoint;
    private $bucket;

    private static $_instance=array();

    public function __construct($option)
    {
        $this->getOssClient($option);
    }

    /**
     * 获取Oss客户端连接
     * @param $option
     * @return null
     * @throws \OSS\Core\OssException
     */
    public function getOssClient($option)
    {
        $this->accessId=$option["ALIY_OSS_ACCESS_ID"]?:C("ALIY_OSS_ACCESS_ID");
        $this->accessKey=$option["ALIY_OSS_ACCESS_KEY"]?:C("ALIY_OSS_ACCESS_KEY");
        $this->endpoint=$option["ALIY_OSS_ENDPOINT"]?:C("ALIY_OSS_ENDPOINT");
        $this->bucket=$option["ALIY_OSS_BUCKET"]?:C("ALIY_OSS_BUCKET");

        try
        {
            $ossClient = new OssClient($this->accessId, $this->accessKey,$this->endpoint, false);
        }
        catch (OssException $e)
        {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }

        $this->client=$ossClient;
    }

    /**
     * 获取类的单例
     * @param $option
     * @return mixed|Oss
     */
    public static function getInstance($option=array())
    {
        $instanceKey=md5(serialize($option));
        $instance=self::$_instance[$instanceKey];

        if(!$instance)
        {
            $instance=new self($option);

            self::$_instance[$instanceKey]=$instance;
        }

        return $instance;
    }

    /**
     * 上传指定的本地文件内容
     * @param string $filePath 上传文件地址
     * @param string $object 上传到aliyun oss的路径
     * @return bool
     */
    public function uploadFile($filePath,$object,$options)
    {
        try
        {
            $this->client->uploadFile($this->bucket, $object, $filePath, $options);
        }
        catch (OssException $e)
        {
            return false;
        }

        return true;
    }

    /**
     * 获取存储节点
     * @return mixed
     */
    public function getEndPoint()
    {
        return $this->endpoint;
    }

    /**
     * 获取bucket
     * @return mixed
     */
    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * 获取当前文件存储域名
     */
    public function getCurrentAliyunOssDomain()
    {
        return sprintf("http://%s.%s",$this->bucket,$this->endpoint);
    }

}