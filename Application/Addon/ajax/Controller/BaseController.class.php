<?php
namespace Addon\ajax\Controller;
use Common\Controller\CommonController;

class BaseController extends CommonController
{
	public function _initialize()
	{
		
	}

	//接受请求类型为application/json提交请求
    public function getInputData()
    {
        $data=file_get_contents("php://input");
        $data=json_decode($data,true);

        return $data;
    }
}