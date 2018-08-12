<?php
namespace Common\Model;
use Think\Model;

class BaseModel extends Model
{
	//字段检测
	protected $autoCheckFields = false;
	
	//错误码
	private $errorCode=0;

    //错误码
    private $msg=0;

    //自定义显示映射
	private $showMap=array();
	
	//构造函数
	public function __construct()
	{
		$this->_initialize();
	}
	
	//初始化
	protected function _initialize() 
	{
		$this->initDbConfig();
	}
	
	//初始化db配置
	protected function initDbConfig()
	{
		
	}
	
	//设置数据库配置
	protected function setDbConfig($dbConfigKey)
	{
		$this->tablePrefix=C(sprintf('%s.DB_PREFIX',$dbConfigKey));
		$this->db(1,$dbConfigKey);
	}

	//设置错误消息
    protected function setErrorMsg($error_code,$msg)
    {
        $this->errorCode=$error_code;
        $this->msg=$msg;
    }

    //获取错误消息
    public function getErrorMsg()
    {
        return array('error_code'=>$this->errorCode,'msg'=>$this->msg);
    }

    //为数据设立键
    public function setDataKey($keyName,$data)
    {
        $endData=array();
        foreach($data as $key=>$item)
        {
            $k=$item[$keyName];
            unset($item[$keyName]);

            $endData[$k]=$item;
        }

        return $endData;
    }

    //检测字段唯一性true表示无相同字段，唯一false表示已经存在
    public function checkFieldUnique($field,$fieldVal,$id=0)
    {
        if(is_array($field))
        {
            $where=array();
            foreach($field as $key=>$value)
            {
                $where[$value]=$fieldVal[$key];
            }

            $searchId=$this->where($where)->getField($this->getPk());
        }
        else
        {
            $searchId=$this->where(array($field=>$fieldVal))->getField($this->getPk());
        }

        if(!$searchId)
        {
            return true;
        }

        if(isset($searchId) && $searchId==$id)
        {
            return true;
        }

        return false;
    }

    //分页函数
    public function pagination($page,$allCount,$pageSize=30,$eachPage=10)
    {
        $pageCount=intval($allCount/$pageSize);
        if($allCount%$pageSize>0)
        {
            $pageCount++;
        }

        if($page<1)
        {
            $page=1;
        }

        if($page>$pageCount)
        {
            $page=$pageCount;
        }

        $fromPage=intval(($page-1)/$eachPage)*$eachPage+1;
        $toPage=$fromPage+$eachPage-1;

        $prevPage=$page-1;
        $nextPage=$page+1;

        if($fromPage<1)
        {
            $fromPage=1;
        }
        if($toPage>$pageCount)
        {
            $toPage=$pageCount;
        }

        if($prevPage<1)
        {
            $prevPage=1;
        }
        if($nextPage>$pageCount)
        {
            $nextPage=$pageCount;
        }

        $pageData=array(
            'page'=>$page,
            'allCount'=>$allCount,
            'pageCount'=>$pageCount,
            'pageSize'=>$pageSize,
            'fromPage'=>$fromPage,
            'toPage'=>$toPage,
            'prevPage'=>$prevPage,
            'nextPage'=>$nextPage
        );

        return $pageData;
    }

}