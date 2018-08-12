<?php


namespace Common\Service;

/**
 * 行情服务层
 * Class MarketService
 * @package Common\Service
 */
class MarketService extends BaseService
{
    private $flushTime=30;
    private $priExchanges=array('Huobi','Okex','Binance');      //交易所行情优先级数组

    //获取币行情信息
    public function getQuotations()
    {
        //处理coins的实时行情数据的
        $coins=$this->getQuotationsListByAll('',1,20,1);
        
		$coins=$coins['dataList'];
		if(!$coins) array();
		
        return $coins;
    }

    //行情分页列表查询
    public function getQuotationsListByAll($type,$page=1,$pageSize=20,$withConcern=0)
    {
        $userConcernCoins=array();
        if($withConcern)
        {
            $sUser=SVC("User");
            $userConcernCoins=$sUser->getUserConcernCoins();
        }

        //获取redis现有行情的所有code组成的一维数组
        $quotationsCodes = $this->getCoinCodesOfOurExchangeData();

        $sCoin=SVC("Coin");
        $coins=$sCoin->getAllCoinInfo('code');

        $coinsCode=$this->getCoinsCode($quotationsCodes,$coins,$userConcernCoins,$withConcern);

        $start=($page-1)*$pageSize;
        $selectCoinsCode=array_slice($coinsCode,$start,$pageSize);

        $data=$this->dealQuotationList($selectCoinsCode,$coins,$userConcernCoins,$withConcern);

        if($data)
        {
            $data=array('coinIds'=>array_column($data,'coin_id'),'dataList'=>$data);
        }
		else
		{
			$data=array();
		}

        return $data;
    }

    //获取有行情，并且排序的币
    private function getCoinsCode($quotationsCodes,$coins,$userConcernCoins,$withConcern)
    {
        $cacheKey=md5("get_has_quotation_coins_".serialize($userConcernCoins).$withConcern);
        $coinsCode=S($cacheKey);
        //var_dump($coinsCode);
        if(!$coinsCode)
        {
            $quotationsCode=array();
            foreach($quotationsCodes as $key=>$code)
            {
                $code=strtoupper($code);
                $quotationsCode[]=$code;
            }

            $coinsCode=array_keys($coins);
            $coinsCode=array_intersect($coinsCode,$quotationsCode);

            //带关注时候处理
            if($withConcern)
            {
                $coinsCode=array_unique(array_merge($userConcernCoins,$coinsCode));
            }

            S($cacheKey,$coinsCode,1800);
        }

        return $coinsCode;
    }

    //处理行情列表
    private function dealQuotationList($selectCoinsCode,$coins,$userConcernCoins,$withConcern)
    {
        //获取币的行情数据
        $quotationsData=$this->getCoinsQuotationOfOurExchangeData($selectCoinsCode);

        $data=array();
        foreach($selectCoinsCode as $code)
        {
            $coinQuotation=$quotationsData[$code];

            if($coinQuotation)
            {
                $coin=$coins[$code];

                $item=array(
                    "coin_id"=>$coin['coin_id'],
                    "code"=>$coin['code'],
                    "ch_name"=>$coin['ch_name'],
                    "en_short_name"=>$coin['en_short_name'],
                    "small_logo_url"=>$coin['small_logo_url'],
                    "big_logo_url"=>$coin['big_logo_url'],
                    "source_name"=>$coinQuotation['source_name'],
                    "price"=>$coinQuotation['price'],
                    "ratio"=>$coinQuotation['ratio'],
                );

                if($withConcern)
                {
                    $item['is_collect']=0;
					
					if(in_array($coin['code'],$userConcernCoins))
						$item['is_collect']=1;
                }

                $data[]=$item;
            }
        }

        return $data;
    }

    /**
     *处理coins的实时行情数据的
     * @param array $coins 0为coins为包含coin信息的二位数组，1为由code组成的以为数组
     * @param int $type 0时处理coins为包含coin信息的二位数组，1时处理由code组成的以为数组
     * @return array
     */
    public function handleCoinQuotations($coins=array(),$type=0)
    {
        if($type==0)
        {
            $coinsCode=array_column($coins,"code");
        }
        else if($type==1)
        {
            $coinsCode=$coins;
        }
        else
        {
            return array();
        }

        $quotationsData=array();
        if(is_array($coinsCode) && $coinsCode)
        {
            //获取币的行情数据
            $quotationsData=$this->getCoinsQuotationOfOurExchangeData($coinsCode);
        }

        foreach($coins as $key=>&$item)
        {
            if($type!=0)
            {
                $item=array(
                    'code'=>strtoupper($item)
                );
            }

            $coinQuotation=$quotationsData[$item['code']];

            if($coinQuotation)
            {
                $item['source_name']=$coinQuotation['source_name'];
                $item['price']=$coinQuotation['price'];
                $item['ratio']=$coinQuotation['ratio'];
            }
            else
            {
                unset($coins[$key]);
            }
        }
        unset($item);
        $coins=array_values($coins);

        return $coins;
    }

    /**
     *处理coins的实时所有的行情数据的
     * @param array $coins 0为coins为包含coin信息的二位数组，1为由code组成的以为数组
     * @param int $type 0时处理coins为包含coin信息的二位数组，1时处理由code组成的以为数组
     * @return array
     */
    public function handleCoinCollectQuotations($coins=array(),$type=0)
    {
        if($type==0)
        {
            $coinsCode=array_column($coins,"code");
        }
        else if($type==1)
        {
            $coinsCode=array_unique($coins);
        }
        else
        {
            return array();
        }

        $quotationsData=array();
        if(is_array($coinsCode) && $coinsCode)
        {
            //获取币的行情数据,价格，涨跌幅，数据来源
            $quotationsData=$this->getCoinsQuotationOfOurExchangeData($coinsCode);
        }
        //获取采集数据，包括市值，24小时交易量
        array_walk($coins,function(&$value)
        {
            $value=strtoupper($value);
        }
        );
        $quotationsDataOfCollect = $this->getCoinsQuotationOfCollectData($coins);
        //var_dump($quotationsDataOfCollect);die;

        foreach($coins as $key=>&$item)
        {
            if($type!=0)
            {
                $item=array(
                    'code'=>$item
                );
            }

            $item['source_name']=$quotationsData[$item]['source_name'];
            $item['market_value']=$quotationsDataOfCollect[$item]['market_value'];
            $item['total_amount']=$quotationsDataOfCollect[$item]['total_amount'];
            $item['coin_amount']=$quotationsDataOfCollect[$item]['coin_amount'];
            $item['price']=$quotationsData[$item]['price'];
            $item['ratio']=$quotationsData[$item]['ratio'];

            $coinQuotationOfCollect=$quotationsDataOfCollect[$item['code']];
            if($coinQuotationOfCollect)
            {
                if(!$coinQuotationOfCollect['market_value'])
                {
                    $item['market_value'] = '--';
                }
                $item['market_value']=(string)$coinQuotationOfCollect['market_value'];

                if(!$coinQuotationOfCollect['total_amount'])
                {
                    $item['total_amount'] = '--';
                }
                $item['total_amount']=(string)$coinQuotationOfCollect['total_amount'];

                if(!$coinQuotationOfCollect['coin_amount'])
                {
                    $item['coin_amount'] = '--';
                }
                $item['coin_amount']=(string)$coinQuotationOfCollect['coin_amount'];
            }

            $coinQuotation=$quotationsData[$item['code']];
            if($coinQuotation)
            {
                if(!$coinQuotation['source_name'])
                {
                    $item['source_name'] = '--';
                }
                $item['source_name']=$coinQuotation['source_name'];

                if(!$coinQuotation['price'])
                {
                    $item['price'] = '--';
                }
                $item['price']=$coinQuotation['price'];

                if(!$coinQuotation['ratio'])
                {
                    $item['ratio'] = '--';
                }
                $item['ratio']=$coinQuotation['ratio'];
            }
        }

        return $coins;
    }

    //获取行情信息，key为币code的，value为行情信息
    public function handleQuotationsKeyCode($coinsCode)
    {
        $quotationsData=array();
        if(is_array($coinsCode) && $coinsCode)
        {
            //获取币的行情数据
            $quotationsData=$this->getCoinsQuotationOfOurExchangeData($coinsCode);
        }


        $coins=array();
        foreach($coinsCode as $key=>$code)
        {
            $coinQuotation=$quotationsData[$code];

            if($coinQuotation)
            {
                $coins[$code]=array(
                    'code'=>$code,
                    'source_name'=>$coinQuotation['source_name'],
                    'price'=>$coinQuotation['price'],
                    'ratio'=>$coinQuotation['ratio']
                );
            }
        }

        return $coins;
    }

    public function handleNewsCoinTagQuotations($newsCoinTag,$coinsCode)
    {
        $coinsCode=array_unique($coinsCode);
        $coinQuotations=$this->handleQuotationsKeyCode($coinsCode);

        $resNewsCoinTag=array();
        foreach($newsCoinTag as $key=>$item)
        {
            foreach($item as $k=>$v)
            {
                $coinQuotation=$coinQuotations[$v['code']];
                if($coinQuotations[$v['code']])
                {
                    $v['price']=$coinQuotation['price'];
                    $v['ratio']=$coinQuotation['ratio'];
                }
                else
                {
                    continue;
                }

                $resNewsCoinTag[$key][]=$v;
            }
        }

        return $resNewsCoinTag;
    }


    /**
     * @param $codes
     * @param int $type
     * @param int $dataType
     * @return array|mixed|string
     */
    public function getQuotationsInfoByCodes($codes,$type=1,$dataType=1)
    {
        if ($type == 0)
        {
            $dCoin=D("Coin");
            $coins = $dCoin->getCoinlistByCode($codes);
        }
        else
        {
            $coins=$codes;
        }

        if($dataType==1)    //火币价格+涨跌幅行情
        {
            $quotations = $this->handleCoinQuotations($coins, $type);
        }
        else    //采集来的行情
        {
            $quotations = $this->handleCoinCollectQuotations($coins, $type);
        }

        return $quotations;
    }

    /**
     * 根据自己目前现有的行情获取一组币的行情
     * @param $code
     * @return mixed
     */
    public function getCoinsQuotationOfOurExchangeData($codes)
    {
        ### 1.根据交易所排序级别获取交易所行情codes
        $coinsCodes=array();
        $unHasQuotationCodes=array();
        $exchangeCodes=array();

        array_walk($codes,function(&$value)
		{
            $value=strtolower($value);
        }
		);

        $exchangeAllCode=$this->getCoinCodesOfOurExchangeData('exchange');

        $i=0;
        foreach($this->priExchanges as $key=>$exchange)
        {
            if($i===0 || $unHasQuotationCodes)
            {
                if($i===0)
                {
                    $existCoinsCode = array_intersect($codes, $exchangeAllCode[$exchange]);
                    $unHasQuotationCodes = array_diff($codes, $exchangeAllCode[$exchange]);
                }
                else
                {
                    $existCoinsCode = array_intersect($unHasQuotationCodes, $exchangeAllCode[$exchange]);
                    $unHasQuotationCodes = array_diff($unHasQuotationCodes, $exchangeAllCode[$exchange]);
                }

                if ($existCoinsCode)
                {
                    $coinsCodes = array_merge($coinsCodes, $existCoinsCode);
                    $exchangeCodes[$exchange] = $existCoinsCode;
                }
            }
            else if($coinsCodes===$codes)
            {
                break;
            }

            $i++;
        }

        ### 2.根据交易所行情codes获取对应的行情信息
        $coinQuotation=array();
        foreach($exchangeCodes as $exchange=>$codes)
        {
            $exchangeInstance=\Common\Service\Exchange\Exchange::getInstance($exchange);

            $exchangeQuotation=$exchangeInstance->getCoinQuotationsByCoinCode($codes);
            $coinQuotation=array_merge($coinQuotation,$exchangeQuotation);
        }

        return $coinQuotation;
    }

    /**
     * 根据自己目前现有的行情获取一组币的行情
     * @param $code
     * @return mixed
     */
    public function getCoinsQuotationOfCollectData($codes)
    {
        $collectInstance=\Common\Service\Exchange\Exchange::getInstance("Collect");
        $coinQuotation=$collectInstance->getCoinQuotationsByCoinCode($codes);

        return $coinQuotation;
    }

    //获取现有交易所的所有codes数组
    public function getCoinCodesOfOurExchangeData($keyName='',$isUpper=0)
    {
        $cacheKey=md5("coin_codes_of_our_exchange_data_1adsfsdf".$keyName.$isUpper);
        $data=S($cacheKey);
//            $data=null;
        if(!$data && $data != '--')
        {
            $allCodes=array();

            foreach($this->priExchanges as $key=>$exchange)
            {

                $exchangeInstance=\Common\Service\Exchange\Exchange::getInstance($exchange);
                $exchangeAllCodes=$exchangeInstance->getQuotationsAllCodes();

                if($isUpper)
                {
                    array_walk($exchangeAllCodes,function(&$value)
					{
                        $value=strtoupper($value);
                    }
					);
                }

                if($keyName=='exchange')
                {
                    $allCodes[$exchange]=$exchangeAllCodes;
                }
                else
                {
                    $allCodes=array_merge($allCodes,$exchangeAllCodes);
                }
            }

            if($keyName == '' && $allCodes) $allCodes=array_unique($allCodes);

            $data='--';
			if($allCodes)
			{
				$data=$allCodes;
			}

            S($cacheKey,$data,1800);
        }

        if($data =='--')
        {
            return array();
        }

        return $data;
    }

    //获取新币列表
    public function getNewCoinsList($minTime='')
    {
        $cacheKey=md5('latest_new_coin'.$minTime);
        $newCoinsList=S($cacheKey);
        $newCoinsList=null;
        if(!$newCoinsList && $newCoinsList != '--')
        {
            $dNewCoin=D("NewCoin");
            $newCoinsList=$dNewCoin->getNewCoinInfo(array(),$minTime);
            $newCoinsList=$this->handleNewCoinsList($newCoinsList);

			if(!$newCoinsList) $newCoinsList='--';

            S($cacheKey,$newCoinsList,60);
        }

        if($newCoinsList=='--')
        {
            return array();
        }

        return $newCoinsList;

    }

    /**
     * 获取新币列表
     * @param $newCoinsList
     * @return array
     */
    public function handleNewCoinsList($newCoinsList)
    {
        $dataList=array();
        foreach($newCoinsList as $key=>&$item)
        {
            $publishId=$item['publish_id'];
            $publishTimeStamp=strtotime($item['publish_time']);

            $date=date("Ymd",$publishTimeStamp);

            $item=array(
                "code"=>$item['code'],
                "is_first"=>$item['is_first'],
                "logo_url"=>$item['logo_url'],
                'trade_areas'=>$item['trade_areas'],
                'publisher'=>$item['publisher'],
                "publish_time"=>$publishTimeStamp,
                "publish_id"=>$publishId,
                "time"=>date( "H:i",$publishTimeStamp),
            );

            $dateDataList[$date][]=$item;
        }

        krsort($dateDataList);
        $publishTimeStamp=array_column($newCoinsList,'publish_time');
		
		$minTime=min($publishTimeStamp);
        if(!$minTime) $minTime='';

        foreach($dateDataList as $key=>$data)
        {
            $isOut='';
            $timeStamp=strtotime($key);
            $langDate=deal_date($key,'ch','m月d日',$isOut);

            $currentTimeStamp = strtotime(date("Y-m-d 00:00:00"));

            if(($timeStamp-$currentTimeStamp)>0)
            {
                $is_publish = 0;
            }
            else
            {
                $is_publish = 1;
            }


            $dataList[]=array(
                'coin'=>array_sort($data,'publish_time','desc'),
                'lang_date'=>$langDate,
                'is_publish'=>$is_publish,
                'date'=>$timeStamp,
            );
        }

        $data=array('min_time'=>$minTime,'dataList'=>$dataList);

        return $data;
    }

    /**
     * 获取推荐币（主流币和最新币）
     * @return array|mixed|string
     */
    public function getRecommendCoin()
    {
        $cacheKey=md5("recommend_coin_xsd1");
        $data=S($cacheKey);
        $resultData=array('pop_coin'=>array(),'new_coin'=>array());

        if(!$data && $data !='--')
        {
            $popCoinArr=array();
            $dSystemConfigData=D("SystemConfigData");
            $popCoin=$dSystemConfigData->getExplodeConfigDataBySystemCode('pop_coin',"\n");
            if($popCoin)
            {
                foreach($popCoin as $code)
                {
                    $popCoinArr[]=array(
                        'code'=>$code,
                        'price'=>'--',
                        'ratio'=>'0.00%',
                        'source_name'=>'Huobi'
                    );
                }
            }

            $dNewCoin=D("NewCoin");
            $newCoinArr=$dNewCoin->getRecentlyNewCoin();

            if($newCoinArr)
            {
                $publishTimeStamp=strtotime($newCoinArr['publish_time']);

                $newCoinArr=array(
                    "code"=>$newCoinArr['code'],
                    "date"=>date("m月d日",$publishTimeStamp),
                    "is_first"=>$newCoinArr["is_first"],
                    "logo_url"=>$newCoinArr["logo_url"],
                    "trade_areas"=>$newCoinArr["trade_areas"],
                    "publish_id"=>$newCoinArr['publish_id'],
                    "publish_time"=>$publishTimeStamp,
                    "publisher"=>$newCoinArr['publisher'],
                    "time"=>date("H:i",$publishTimeStamp)
                );
            }

            if(!$popCoinArr && !$newCoinArr)
            {
                $data='--';
            }
            else
            {
				if(!$popCoinArr)
                {
                    $popCoinArr=array();
                }

				if(!$newCoinArr)
                {
                    $newCoinArr=(object)array();
                }

                $data=array('pop_coin'=>$popCoinArr,'new_coin'=>$newCoinArr);
            }

            S($cacheKey,$data,1800);
        }

        if($data =='--')
        {
            return $resultData;
        }

        //获取币列表实时行情
        $sMarket=SVC("Market");
        $sMarket->getCoinsQuotationOfCoinList($data['pop_coin']);

        return $data;
    }

    //更新币列表的行情
    public function getCoinsQuotationOfCoinList(&$coinList)
    {
        if(!$coinList)
        {
            return;
        }

        $codes=array_column($coinList,'code');
        $coinsQuotation=$this->getCoinsQuotationOfOurExchangeData($codes);
        foreach($coinList as &$item)
        {
            $item['price']=$coinsQuotation[$item['code']]['price'];
            $item['ratio']=$coinsQuotation[$item['code']]['ratio'];
            $item['source_name']=$coinsQuotation[$item['code']]['source_name'];
        }
    }

    //更新单个币的行情
    public function getCoinQuotationOfCoin(&$coin)
    {
        if(!$coin['code'])
        {
            return;
        }

        $codes=array($coin['code']);
        $coinsQuotation=$this->getCoinsQuotationOfOurExchangeData($codes);
        $coin['price']=$coinsQuotation[$coin['code']]['price'];
        $coin['ratio']=$coinsQuotation[$coin['code']]['ratio'];
        $coin['source_name']=$coinsQuotation[$coin['code']]['source_name'];
    }

    /**
     * 根据交易所行情获取涨跌幅行情
     * @param $code
     * @return mixed
     */
    public function getCoinsRatioOfOUrExchangeData()
    {
        //火币涨跌幅
        $collectInstance=\Common\Service\Exchange\Exchange::getInstance('Huobi');
        $coinQuotationOfHuobi=$collectInstance->getRatioOfAllCoin();

        //ok涨跌幅
        $collectInstance=\Common\Service\Exchange\Exchange::getInstance('Okex');
        $coinQuotationOfOkex=$collectInstance->getRatioOfAllCoin();

        //币安涨跌幅
        $collectInstance=\Common\Service\Exchange\Exchange::getInstance('Biance');
        $coinQuotationOfBiance=$collectInstance->getRatioOfAllCoin();

        $coinQuotationOfAllExchange = array_unique(array_merge($coinQuotationOfBiance,$coinQuotationOfOkex,$coinQuotationOfHuobi));

        return $coinQuotationOfAllExchange;
    }

    /**
     * 获取新币实时行情
     * @param $publishIds
     * @return array
     */
    public function getRealtimeQuotationOfNewCoin($publishIds)
    {
        if(!$publishIds)
        {
            return array();
        }

        ##1.获取每个交易的所有币信息
        $cacheKey=md5("new_coin_info_key_exchanges_asdfsdfsld".serialize($publishIds));
        $newsCoinExchanges=S($cacheKey);

        if(!$newsCoinExchanges && $newsCoinExchanges!='--')
        {
            $dNewCoin=D("NewCoin");
            $newsCoinExchanges=$dNewCoin->getCoinInfoKeyExchange($publishIds);

            if(!$newsCoinExchanges)
            {
                $newsCoinExchanges='--';
            }

            S($cacheKey,$newsCoinExchanges,1800);
        }

        if($newsCoinExchanges=='--')
        {
            return array();
        }

        ##2.获取每条publish_id对应的币行情
        $newCoinQuotation=array();
        if($newsCoinExchanges)
        {
            foreach($newsCoinExchanges as $exchangeCode=>$coins)
            {
                if(in_array($exchangeCode,$this->priExchanges))
                {
                    $coinCodes=array_column($coins,'code');

                    $exchangeInstance=\Common\Service\Exchange\Exchange::getInstance($exchangeCode);
                    $coinQuotations=$exchangeInstance->getCoinQuotationsByCoinCode($coinCodes);

                    foreach($coins as $item)
                    {
                        $price=$coinQuotations[$item['code']]['price'];
                        $ratio=$coinQuotations[$item['code']]['ratio'];
                        $sourceName=$coinQuotations[$item['code']]['source_name'];

                        $newCoinQuotation[]=array(
                            'publish_id'=>$item['publish_id'],
                            'code'=>$item['code'],
                            'price'=>$price,
                            'ratio'=>$ratio,
                            'source_name'=>$sourceName,
                        );
                    }
                }
            }
        }

        return $newCoinQuotation;
    }

    /**
     * 获取涨跌家数统计
     */
    public function getCoinUpDownCount()
    {
        $columnOfRatio = $this->getCoinsRatioOfOUrExchangeData();

        foreach ($columnOfRatio as $k=>$v)
        {
            if($v>0.1)
            {
                $array1[] = $v;
            }
            elseif(0.1>$v && $v>0.08)
            {
                $array2[]=$v;
            }
            elseif(0.08>$v && $v>0.06)
            {
                $array3[]=$v;
            }
            elseif(0.06>$v && $v>0.04)
            {
                $array4[]=$v;
            }
            elseif(0.04>$v && $v>0.02)
            {
                $array5[]=$v;
            }
            elseif(0.02>$v && $v>0.00)
            {
                $array6[]=$v;
            }
            elseif(0.00>$v && $v>-0.02)
            {
                $array7[]=$v;
            }
            elseif(-0.02>$v && $v>-0.04)
            {
                $array8[]=$v;
            }
            elseif(-0.04>$v && $v>-0.06)
            {
                $array9[]=$v;
            }
            elseif(-0.06>$v && $v>-0.08)
            {
                $array10[]=$v;
            }
            elseif(-0.08>$v && $v>-0.1)
            {
                $array11[]=$v;
            }
            else
            {
                $array12[]=$v;
            }

            if($v>0.05)
            {
                $array13[]=$v;
            }
            elseif($v<-0.05)
            {
                $array14[]=$v;
            }

            if($v>0)
            {
                $array15[]=$v;
            }
            else
            {
                $array16[]=$v;
            }
        }

        $data = array(
            'chart_data' => array(
                array('name' => '>10%', 'num' => count($array1)),
                array('name' => '8~10%', 'num' => count($array2)),
                array('name' => '6~8%', 'num' => count($array3)),
                array('name' => '4~6%', 'num' => count($array4)),
                array('name' => '2~4%', 'num' => count($array5)),
                array('name' => '0~2%', 'num' => count($array6)),
                array('name' => '-2~-0%', 'num' => count($array7)),
                array('name' => '-4~-2%', 'num' => count($array8)),
                array('name' => '-6~-4%', 'num' => count($array9)),
                array('name' => '-8~-6%', 'num' => count($array10)),
                array('name' => '-10~-8%', 'num' => count($array11)),
                array('name' => '<-10%', 'num' => count($array12)),
            ),
            "down_gt_five" => count($array14),
            "down_total" => count($array16),
            "up_gt_five" => count($array13),
            "up_total" => count($array15),
        );
        return $data;
    }

    //web端需要分页的数据
    public function getRankListOfPage($type,$limit,$page)
    {
        $re = $this->getRankListByType($type,$limit,$page);
        if((str_replace('+','',$type) == 'exchange_trade_amount') ||(str_replace('-','',$type) == 'exchange_trade_amount'))
        {
            $pageData = $this->getPageInfoOfRank(2);
        }
        else
        {
            $pageData = $this->getPageInfoOfRank(1);
        }

        //var_dump($pageData);die;
        $data=array('dataList'=>$re,'pageInfo'=>array('page'=>$page,'totalCount'=>$pageData['allCount'],'page_num'=>$pageData['pageCount']));
        return $data;
    }


    //根据类型获取排行榜
    public function getRankListByType($type,$limit,$page)
    {
        $totalType=array('+price','-price','+ratio','-ratio','+market_value','-market_value','+total_amount','-total_amount','+coin_amount','-coin_amount','+exchange_trade_amount','-exchange_trade_amount');

        if(!in_array($type,$totalType))
        {
            return array();
        }

        $types=array(
            '+price'=>array('price','desc'),
            '-price'=>array('price','asc'),
            '+market_value'=>array('market_value','desc'),
            '-market_value'=>array('market_value','asc'),
            '+total_amount'=>array('total_amount','desc'),
            '-total_amount'=>array('total_amount','asc'),
            '+coin_amount'=>array('coin_amount','desc'),
            '-coin_amount'=>array('coin_amount','asc'),
            '+ratio'=>array('ratio','desc'),
            '-ratio'=>array('ratio','asc'),
            '+exchange_trade_amount'=>array('exchange_trade_amount','desc'),
            '-exchange_trade_amount'=>array('exchange_trade_amount','asc'),
        );
        $typeRank=$types[$type];
        $rankList=$this->getQuotationsOfAllCoin($typeRank,$limit,$page);
		
		if(!$rankList) $rankList=array();
		
        return $rankList;
    }

    //获取排行榜数据榜单，从redis里面取数据，为真实数据
    public function getQuotationsOfAllCoin($typeRank,$limit,$page)
    {
        $typeRankText=$typeRank[0];
        $typeRankSign=$typeRank[1];

        //获取交易所的数据
        if($typeRankText == 'exchange_trade_amount')
        {
            $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
            $redisClient->select(4);
            $quotationsDataFromExchange = $redisClient->hGetAll('market_exchange');

            foreach ($quotationsDataFromExchange as $k=>$v)
            {
                $exchangeInfo[] = json_decode($v,true);
            }

            $exchangeQuotation=array_sort($exchangeInfo,'total_amount',$typeRankSign);

            foreach ($exchangeQuotation as $k=>$v)
            {
				//if(!$v['total_amount']) $v['total_amount']='--';
				$supportCurrency = $v['support_currency'];
				if(substr($supportCurrency,0,1) ==',')
                {
                    $supportCurrency = substr($supportCurrency,1,strlen($supportCurrency)-1);
                }
                $exchange[] = array(
                    'name' => $v['name'],
                    'total_amount'=>$v['total_amount'],
                    'trade_pair'=>$v['trade_pair'],
                    'country'=>$v['country'],
                    'support_currency'=>$supportCurrency,
                    'website_address'=>$v['exchange_url'],
                    'logo_url' =>$v['image']
                );
            }

            $start=($page-1)*$limit;
            $exchange=array_slice($exchange,$start,$limit);
            return $exchange;
        }


        //非交易所榜单
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(4);

        $quotationsDataFromMarket = $redisClient->hGetAll('market_coins');

        $cacheKey=md5("key_of_rank_coin_code");
        $keyOfRankCoinCodes=S($cacheKey);
        
        if(!$keyOfRankCoinCodes && $keyOfRankCoinCodes !='--')
        {
            $keyOfMarketCode=array_keys($quotationsDataFromMarket);
            $ourExchangeCodes=$this->getCoinCodesOfOurExchangeData('',1);

            $keyOfRankCoinCodes=array_intersect($keyOfMarketCode,$ourExchangeCodes);
			
			if(!$keyOfRankCoinCodes) $keyOfRankCoinCodes='--';
			
            S($cacheKey,$keyOfRankCoinCodes,60);
        }

        if($keyOfRankCoinCodes == '--')
        {
            return array();
        }

        $sCoin = SVC('Coin');
        $allCoin=$sCoin->getAllCoinInfo('code');
        //数据库有的币和上面的币取交集，保证有数据
        $allCoinCode = array_keys($allCoin);
        $keyOfRankCoinCodes = array_intersect($allCoinCode,$keyOfRankCoinCodes);

        $ourCoinQuotations=$this->getCoinsQuotationOfOurExchangeData($keyOfRankCoinCodes);

        foreach($keyOfRankCoinCodes as $key=>$value)
        {
            $coinQuotation=json_decode($quotationsDataFromMarket[$value],true);

            $price=$ourCoinQuotations[$value]['price'];
            $ratio=$ourCoinQuotations[$value]['ratio'];
            $ratioRank=str_replace('%','',$ratio);
            $sourceName=$ourCoinQuotations[$value]['source_name'];
            $marketValue=$coinQuotation['market_value'];
            $totalAmount=$coinQuotation['total_amount'];
            $totalAmount=sprintf("%.2f",$totalAmount);
            $coinAmount=$coinQuotation['coin_amount'];
            $coinAmountRank=str_replace('万','',$coinAmount);
            $coinAmountRank=str_replace(',','',$coinAmountRank);
            $coinInfo=$allCoin[$value];
            $coins[]=array(
                'big_logo_url'=>$coinInfo['big_logo_url'],
                'coin_id'=>$coinInfo['coin_id'],
                'ch_name'=>$coinInfo['ch_name'],
                'en_name'=>$coinInfo['en_name'],
                'en_short_name'=>$coinInfo['en_short_name'],
                'small_logo_url'=>$coinInfo['small_logo_url'],
                'code'=>$value,
                'source_name'=>$sourceName,
                'coin_amount'=>$coinAmount,
                'price'=>$price,
                'ratio'=>$ratio,
                'market_value'=>$marketValue,
                'total_amount'=>$totalAmount,
                'ratio_rank'=>$ratioRank,
                'coin_amount_rank'=>$coinAmountRank
            );
        }

        if($typeRankText == 'ratio')
        {
            $coinQuotation=array_sort($coins,'ratio_rank',$typeRankSign);
            $start=($page-1)*$limit;
            $coins=array_slice($coinQuotation,$start,$limit);
        }
        elseif($typeRankText == 'coin_amount')
        {
            $coinQuotation=array_sort($coins,'coin_amount_rank',$typeRankSign);
            $start=($page-1)*$limit;
            $coins=array_slice($coinQuotation,$start,$limit);
        }
        else
        {
            $coinQuotation=array_sort($coins,$typeRankText,$typeRankSign);
            $start=($page-1)*$limit;
            $coins=array_slice($coinQuotation,$start,$limit);
        }


        return $coins;
    }

    //获取用户关注币的信息
    public function getUserConcernCoinInfo($uid,$page,$rankType)
    {

        $types=array(
            '+price'=>array('price','desc'),
            '-price'=>array('price','asc'),
            '+market_value'=>array('market_value','desc'),
            '-market_value'=>array('market_value','asc'),
            '+total_amount'=>array('total_amount','desc'),
            '-total_amount'=>array('total_amount','asc'),
            '+coin_amount'=>array('coin_amount','desc'),
            '-coin_amount'=>array('coin_amount','asc'),
            '+ratio'=>array('ratio','desc'),
            '-ratio'=>array('ratio','asc'),
            '+exchange_trade_amount'=>array('exchange_trade_amount','desc'),
            '-exchange_trade_amount'=>array('exchange_trade_amount','asc'),
        );
        $typeRank=$types[$rankType];
        $typeRankText=$typeRank[0];
        $typeRankSign=$typeRank[1];

        $sCoin=SVC("Coin");
        $allCoin=$sCoin->getAllCoinInfo('code');

        $dUserFavorite=D("UserFavorite");
        $userCollectCoinIds=$dUserFavorite->getUserFavoriteTagIdId($uid,2);
        $coinId = array_column($userCollectCoinIds,'target_id');
		
        $dCoin = D('Coin');
        $coinCode = $dCoin->getCoinCodeById($coinId);
        $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
        $redisClient->select(4);
        $quotationsData = $redisClient->hMget('market_coins',$coinCode);


        foreach ($quotationsData as $k=>$v)
        {
            $coinQuotation[$k] = json_decode($v,true);
        }
        $count = count($coinQuotation);
        $dBase = D('Base');
        $pageData = $dBase->pagination($page,$count,$pageSize=50);

        $ourCoinQuotations=$this->getCoinsQuotationOfOurExchangeData($coinCode);
        foreach ($coinQuotation as $k=>$v)
        {
            $coinCode = $k;
            $coinInfo=$allCoin[$coinCode];

            $price=$ourCoinQuotations[$k]['price'];
            $ratio=$ourCoinQuotations[$k]['ratio'];
            $ratioRank=str_replace('%','',$ratio);
            $totalAmount = $v['total_amount'];
            $marketValue = $v['market_value'];

            $coinAmount=$v['coin_amount'];
            $coinAmountRank=str_replace('万','',$coinAmount);
            $coinAmountRank=str_replace(',','',$coinAmountRank);

            if(!$totalAmount) $totalAmount ='--';
			if(!$marketValue) $totalAmount ='--';
			
            $listData[]=array(
                'code'=> $coinCode,
                'big_logo_url'=>$coinInfo['big_logo_url'],
                'coin_id'=>$coinInfo['coin_id'],
                'ch_name'=>$coinInfo['ch_name'],
                'en_name'=>$coinInfo['en_name'],
                'en_short_name'=>$coinInfo['en_short_name'],
                'small_logo_url'=>$coinInfo['small_logo_url'],
                'price'=>$price,
                'ratio'=>$ratio,
                'coin_amount'=>$coinAmount,
                'ratio_rank'=>$ratioRank,
                'total_amount'=>$totalAmount,
                'market_value'=>$marketValue,
                'coin_amount_rank'=>$coinAmountRank
            );
        }

        if($typeRankText == 'ratio')
        {
            $listData=array_sort($listData,'ratio_rank',$typeRankSign);
        }
        elseif($typeRankText == 'coin_amount')
        {
            $listData=array_sort($listData,'coin_amount_rank',$typeRankSign);
        }
        else
        {
            $listData=array_sort($listData,$typeRankText,$typeRankSign);
        }

        $data = array('dataList' => $listData,'pageInfo'=>array('page'=>$page,'totalCount'=>$pageData['allCount'],'page_num'=>$pageData['pageCount']));
        $start=($page-1)*50;
        $data=array_slice($data,$start,50);
		
		if(!$data) $data=array();
		
        return $data;
    }

    //获取排序之后的币的信息
    public function sortCoinQuotations($coins)
    {
        $sUser=SVC("User");
        $userConcernCoins=$sUser->getUserConcernCoinsId();

        $coinsId=array_column($coins,'coin_id');//原来搜索出来的相关币的coin_id
        $coinsCode=array_intersect($userConcernCoins,$coinsId);
        $restCoin=array_diff($coinsId,$coinsCode);
        $coinsCode=array_unique(array_merge($coinsCode,$restCoin));//获取关注币之后的的coin_id

        $sortCoins=array();

        foreach($coinsCode as $k=>$v)
        {
            $key=array_keys();
            $sortCoins[]=$coins[$key];
        }

        return $sortCoins;
    }

    //获取排行榜分页信息
    public function getPageInfoOfRank($pageType)
    {
        //交易所分页信息
        if($pageType==1)
        {
            $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
            $redisClient->select(4);
            $quotationsDataFromMarket = $redisClient->hGetAll('market_coins');
            $keyOfMarketCode=array_keys($quotationsDataFromMarket);
            $ourExchangeCodes=$this->getCoinCodesOfOurExchangeData('',1);
            $keyOfRankCoinCodes=array_intersect($keyOfMarketCode,$ourExchangeCodes);
            $count = count($keyOfRankCoinCodes);
        }
        else
        {
            $redisClient = \Com\Chw\RedisLib::getInstance('REDIS_CACHE');
            $redisClient->select(2);
            $quotationsDataFromExchange = $redisClient->hGetAll('market_exchange');
            $count = count($quotationsDataFromExchange);
        }

        $dBase = D('Base');
        $pageData = $dBase->pagination($page=1,$count,$pageSize=50);
        return $pageData;
    }

}