<?php
namespace Common\Service;

/**
 * kafka服务
 * Class KafkaService
 * @package Common\Service
 */
class KafkaService extends MicroService
{
    protected $ip='172.16.0.47';
    protected $port=9897;
    protected $clientName='KafkaServiceClient';

    //向卡夫卡推送一条消息
    public function publish($channel,$content)
    {
      $response = $this->client->publish($channel,$content);

      //关闭客户端
      $this->closeClient();

      return $response;
    }
}


