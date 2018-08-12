<?php
namespace Common\Model;

class CountryNodesBitcoinModel extends MongoBaseModel
{
	protected $tableName = 'country_nodes_bitcoin';
	
	public function getTopList($num=10)
	{
		$dataList=$this->order('node_count desc')->limit($num)->select();
		
		$countryMap=C('COUNTRY_CODE');
		
		$countryMap['WORLD']='全球';
		
		foreach($dataList as $k=>$v)
		{
			if($v['country_code']=='OHTER')
			{
				unset($dataList[$k]);
			    continue;
			}
			$dataList[$k]['country_name']=$countryMap[$v['country_code']];
		}
		
		return $dataList;
	}
	
}