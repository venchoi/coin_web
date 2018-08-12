<?php
namespace Common\Service;

class BlockChainNodeService extends BaseService
{
	public function getBitCoinNodesTopCountry()
	{
	    $tempDataList=D('CountryNodesBitcoin')->getTopList(15);
		
		$data['time']=get_fulltime();
		
		foreach($tempDataList as $item)
		{
			if($item['country_code']=='WORLD')
			{
				$data['time']=$item['update_time'];
				break;
			}
		}
		
		$data['dataList']=$tempDataList;
		
		return $data;
	}
}