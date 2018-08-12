<?php
namespace Common\Service;

use Vendor\Thrift\Transport\TSocket;
use Vendor\Thrift\Transport\TBufferedTransport;
use Vendor\Thrift\Protocol\TBinaryProtocol;

class MicroService
{

	//微服务IP
	protected $ip = 'localhost';

	//微服务端口
	protected $port = '80';

	//数据通道
	protected $transport = '';

	//客户端类名
	protected $clientName = 'client';

	protected $client=null;

	public function __construct()
	{
		$this->client=$this->getClient();
	}

	//获取微服务客户端
	public function getClient()
	{
		$tscoket = new TSocket($this->ip, $this->port);
		$tscoket->setSendTimeout(30 * 1000);
		$tscoket->setRecvTimeout(30 * 1000);
		$this->transport = new  TBufferedTransport($tscoket, 1024, 1024);
		$protocol = new TBinaryProtocol($this->transport);

        var_dump($protocol);
		$className = sprintf("Vendor\\ThriftCase\\%s",$this->clientName);
		$client = new $className($protocol);
		$this->transport->open();

		return $client;
	}

	public function closeClient()
	{
		$this->transport->close();
	}

}