<?php
namespace Common\Controller;

use Think\Controller;

class CommonController extends Controller
{
	public function __construct()
    {
        parent::__construct();
    }
	
	protected function ajaxCallMsg($error_code,$msg,$data=null)
	{
	   $dataReturn=array('error_code'=>$error_code,'msg'=>$msg,'data'=>array());
	   if(!is_null($data))
	   {
           $data=$data?$data:array();
		   $dataReturn['data']=$data;
	   }
	   
	   $this->ajaxReturn($dataReturn);
	}

    //后端tkd
    public function display($file)
    {
        //如果有tkd 重新定义
        $url = __SELF__;
        $url = explode("?",$url);
        $url = $url[0];
        preg_match_all('/\d+/',$url,$match_arr);
        $url = preg_replace('/\d+/','x',$url);
        $host=get_current_domain();

        $seo = S('tkd'.$url);
        if(!$seo)
        {
            $dSiteTkdRule = D('SiteTkdRule');
            $seo = $dSiteTkdRule ->getPageSdk($host,$url);

            if($seo)
            {
                S('tkd'.$url,$seo,1800);
            }
            else
            {
                S('tkd'.$url,'--',1800);
            }

        }

        if($seo&&($seo!='--'))
        {

            //直接替换
            $seo['title'] = str_replace("[0]",$match_arr[0][0],$seo['title']);
            $seo['title'] = str_replace("[1]",$match_arr[0][1],$seo['title']);
            $seo['title'] = str_replace("[2]",$match_arr[0][2],$seo['title']);
            $seo['title'] = str_replace("[3]",$match_arr[0][2],$seo['title']);

            $seo['keyword'] = str_replace("[0]",$match_arr[0][0],$seo['keyword']);
            $seo['keyword'] = str_replace("[1]",$match_arr[0][1],$seo['keyword']);
            $seo['keyword'] = str_replace("[2]",$match_arr[0][2],$seo['keyword']);
            $seo['keyword'] = str_replace("[3]",$match_arr[0][2],$seo['keyword']);

            $seo['description'] = str_replace("[0]",$match_arr[0][0],$seo['description']);
            $seo['description'] = str_replace("[1]",$match_arr[0][1],$seo['description']);
            $seo['description'] = str_replace("[2]",$match_arr[0][2],$seo['description']);
            $seo['description'] = str_replace("[3]",$match_arr[0][2],$seo['description']);

            preg_match('/\{\$(.*?)\}/',$seo['title'],$match_arr1);

            if($match_arr1)
            {
                $params = $match_arr1[1];
                $params_arr = explode('.',$params);
                foreach($params_arr as $key => $item)
                {
                    if($key === 0)
                    {
                        $d_ = $this->get($item);
                    }
                    else
                    {
                        $d_ = $d_[$item];
                    }

                }

                $seo['title'] = preg_replace('/\{\$(.*?)\}/',trim($d_),$seo['title']);
                $seo['keyword'] = preg_replace('/\{\$(.*?)\}/',trim($d_),$seo['keyword']);
                $seo['description'] = preg_replace('/\{\$(.*?)\}/',trim($d_),$seo['description']);

            }


            $oldSEO = $this->get('seo');
            $oldSEO['title'] = $seo['title'];
            $oldSEO['keyword'] = $seo['keyword'];
            $oldSEO['description'] = $seo['description'];

            $this->assign('seo',$oldSEO);
        }


        parent::display($file);
    }
}