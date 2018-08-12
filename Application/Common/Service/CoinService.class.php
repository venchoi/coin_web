<?php


namespace Common\Service;

/**
 * 币服务层
 * Class MarketService
 * @package Common\Service
 */
class CoinService extends BaseService
{
        //获取币信息
        public function getAllCoinInfo($key='')
        {
            $cacheKey=md5("all_coin_basic_info".$key);
            $data=S($cacheKey);

            if(!$data && $data!='--')
            {
                $dCoin=D("Coin");

                switch($key)
                {
                    case "code":
                        $data=$dCoin->getAllCoinBasicInfoKeyCode();
                        break;
                    case "coin_id":
                        $data=$dCoin->getAllCoinBasicInfoKeyId();
                        break;
                    default:
                        $data=$dCoin->getAllCoinBasicInfo();
                        break;
                }

				if(!$data) $data='--';

                S($cacheKey,$data,3600);
            }

            if($data=='--')
            {
                return array();
            }

            return $data;
        }

        //获取某个币的详细信息
        public function getCoinInfoByCode($code,$isFavorite=0,$hasQuotation=0)
        {
            $cacheKey=md5("coin_basic_info_".$code);
            $data=S($cacheKey);

            if(!$data && $data!='--')
            {
                $dCoin=D("Coin");
                $data=$dCoin->getCoinInfoByCode($code);

                if(!$data) $data='--';

                S($cacheKey,$data,300);
            }

            if($data=='--')
            {
                return array();
            }

            //设置获取喜好时,获取用户的对应数据
            if($data)
            {
                if($hasQuotation)
                {
                    $sMarket=SVC("Market");
                    $sMarket->getCoinQuotationOfCoin($data);
                }

                if($isFavorite)
                {
                    $userInfo=get_current_user_data();
                    $data['is_collect']=0;

                    if($userInfo)
                    {
                        $dUserFavorite=D("UserFavorite");
                        $re=$dUserFavorite->getUserFavorite($userInfo['uid'],2,$data['coin_id']);

                        if(!$re)
                        {
                            $data['is_collect']=0;
                        }
                        else
                        {
                            $data['is_collect']=1;
                        }

                    }
                }
            }

            return $data;
        }

        //获取用户收藏coin
        public function getUserCollectCoins($userCollectCoinIds)
        {
            $cacheKey=md5('user_collect_coin'.serialize($userCollectCoinIds));
            $data=S($cacheKey);
            $coinIdsKeyArr=array_keys($userCollectCoinIds);
            //$data=null;
            if(!$data && $data != '--')
            {
                $dCoin=D("Coin");
                $where=array();

                if($coinIdsKeyArr && is_array($coinIdsKeyArr))
                {
                    $where['id']=array('in',$coinIdsKeyArr);

                    $newsIdsStr=implode(',',$coinIdsKeyArr);
                    $order="field(id,$newsIdsStr)";
                    $minTime='';

                    $coinList=$dCoin->getCoinListByWhere($where,$order);

                    foreach($coinList as $key=>&$item)
                    {
                        $price='--';
                        $ratio='0.00%';

                        $item=array(
                            "coin_id"=>$item['coin_id'],
                            "favorite_id"=>$userCollectCoinIds[$item['coin_id']]['id'],
                            "code"=>$item['code'],
                            "ch_name"=>$item['ch_name'],
                            "en_short_name"=>$item['en_short_name'],
                            "small_logo_url"=>$item['small_logo_url'],
                            "big_logo_url"=>$item['big_logo_url'],
                            "source_name"=>"Huobi",
                            "price"=>$price,
                            "ratio"=>$ratio,
                            "collect_time"=>strtotime($userCollectCoinIds[$item['coin_id']]['update_time']),
                            "is_collect"=>1
                        );

                        $dataList[]=$item;
                        $minTime=$dataList[count($dataList)-1]['collect_time'];
                    }

                    $data=array('min_time'=>$minTime,'coinIds'=>$coinIdsKeyArr,'dataList'=>$dataList);
                }

                if(!$data) $data='--';

                S($cacheKey,$data,1800);
            }

            if($data=='--')
            {
                return array();
            }

            //获取币列表实时行情
            $sMarket=SVC("Market");
            $sMarket->getCoinsQuotationOfCoinList($data['dataList']);

            return $data;
        }

        /**
         * 获取usdt和个币种的汇率
         * @param string $key   key为tcur时为key为目标币种CODE的关联数组,否则索引索引数组
         * @return array|mixed
         */
        public function getUsdtExchangeRate($key='')
        {
            $cacheKey=md5("usdt_exchange_rate_".date("Ymd").'_'.$key);
            $currencyData=S($cacheKey);
//            $currencyData=null;
            if(!$currencyData)
            {
                $curr=array(
                    'CNY'=>array('mark'=>'¥'),	//人民币
                    'GBP'=>array('mark'=>'￡'),	//英镑
                    'HKD'=>array('mark'=>'HK'),	//港币
                    'JPY'=>array('mark'=>'JP¥'),	//日元
                    'TWD'=>array('mark'=>'NT'),	//台币
                    'KRW'=>array('mark'=>'₩'),	//韩国元
                    'EUR'=>array('mark'=>'€'), //欧元
                );

                $currencyData=array();

                $time=date("Y-m-d H:i:s");
                $currencies=array_keys($curr);
                foreach($currencies as $currency)
                {
                    $url='http://api.k780.com/?app=finance.rate&scur=USD&tcur=%s&appkey=34358&sign=e3b4ad6c8aa50de46a3ba0b3b075873b';
                    $url=sprintf($url,$currency);

                    $data=file_get_contents($url);

                    $data=json_decode($data,true);
                    if($data['success']==1)
                    {
                        $result=$data['result'];

                        $item=array(
                            "source_currency"=>'USDT',
                            "target_currency"=>$result['tcur'],
                            "target_mark"=>$curr[$result['tcur']]['mark'],
                            "ratenm"=>$result['ratenm'],
                            "rate"=>$result['rate'],
                            "update_time"=>$time
                        );

                        if($key=='tcur')
                        {
                            $currencyData[$item['target_currency']]=$item;
                        }
                        else
                        {
                            $currencyData[]=$item;
                        }
                    }
                }

                S($cacheKey,$currencyData,8400);
            }

            return $currencyData;
        }

        public function getCoinQuotationsOfExchange($code,$type)
        {
            $exchanges=array("Huobi","Biance","Okex");
            $sortConfigs=array(
                '+price'=>array('price','desc'),
                '-price'=>array('price','asc'),
                '+ratio'=>array('rank_ratio','desc'),
                '-ratio'=>array('rank_ratio','asc'),
                '+amount'=>array('amount','desc'),
                '-amount'=>array('amount','asc'),
            );

            $coinQuotations=array();
            foreach($exchanges as $exchange)
            {
                $exchangeInstance=\Common\Service\Exchange\Exchange::getInstance($exchange);

                $coinQuotation=$exchangeInstance->getExchangeCoinQuotation($code);

                if($coinQuotation)
                    $coinQuotations[]=$coinQuotation;
            }

            if($coinQuotations)
            {
                if(!$sortConfigs[$type])  $type='+price';

                $coinQuotations=array_sort($coinQuotations,$sortConfigs[$type][0],$sortConfigs[$type][1]);
            }

            $data=array('dataList'=>$coinQuotations);

            return $data;
        }
}