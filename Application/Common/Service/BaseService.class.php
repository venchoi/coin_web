<?php
namespace Common\Service;
use Common\Model\BaseModel;

class BaseService extends BaseModel
{
     protected $error_code=0;
     protected $msg='succ.';

     public function setErrorMsg($errorCode,$msg)
     {
            $this->error_code=$errorCode;
            $this->msg=$msg;
     }

     public function getErrorMsg()
     {
         $error=array('error_code'=>$this->error_code,'msg'=>$this->msg);

         return $error;
     }
}