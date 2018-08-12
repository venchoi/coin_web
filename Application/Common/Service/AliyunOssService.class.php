<?php

namespace Common\Service;

class AliyunOssService extends BaseService
{
    private $Oss=null;

    public function __construct()
    {
        $this->getOssClient();
    }

    //获取Oss客户端连接
    private function getOssClient()
    {
        Vendor("AliyunOss.Oss");

        $this->Oss=\Oss::getInstance();
    }

    //普通$_FILES方式上传文件
    public function uploadNormalImg($keyName,$type,$filename)
    {

        $fileData=$this->uploadFileToTemp($keyName);

        if(!$fileData) return false;

        if(!$_FILES[$keyName]['name'])
        {
            $url = '';
            return $url;
        }

        if(is_array($fileData['filename']) && is_array($fileData['tempFilePath']))
        {
            foreach ($fileData['filename'] as $k =>$v)
            {
                if (empty($v))
                {
                    continue;
                }
                $v=$filename?:$fileData['filename'];
                $url[]=$this->uploadImg($fileData['tempFilePath'][$k],$type,$fileData['filename'][$k]);
            }

        }
        else
        {
            $url=$this->uploadImg($fileData['tempFilePath'],$type,$fileData['filename']);
        }

        if($url)
        {
            return $url;
        }

        return false;
    }

    //base64方式上传文件
    public function uploadBase64Img($base64Data,$type,$filename)
    {
        $fileData=$this->uploadBase64FileToTemp($base64Data,$filename);
        if(!$fileData) return false;

        $fileData['filename']=$filename?:$fileData['filename'];
        $url=$this->uploadImg($fileData['tempFilePath'],$type,$fileData['filename']);
        if($url)
        {
            return $url;
        }

        return false;
    }

    /**
     * 上传图片到oss
     * @param $file
     * @param $type
     * @param $filename 文件名
     * @return string 成功返回上传的目录地址，失败返回false
     */
    public function uploadImg($file,$type='',$fileName='')
    {
        $fileName=$fileName?:$file;

        switch($type)
        {
            case "channel_icon":
                $aliyunOssUrl='img/channel_icon/'.basename($fileName);      //获取上传渠道icon的url
                break;
            case "head_pic":
                $aliyunOssUrl='img/head_pic/'.basename($fileName);         //获取用户头像上传url
                break;
            case "coin_logo":
                $aliyunOssUrl='img/coin_logo/'.basename($fileName);
                break;
            case "user_view":
                $aliyunOssUrl='img/user_view/'.$this->getFileName($fileName,$randName=1);
                break;
            case "video":
                $aliyunOssUrl='video/'.$this->getFileName($fileName,$randName=1);
                break;
            default:
                $aliyunOssUrl='img/default'.time().'_'.rand(0,999);
                break;
        }

        $result=$this->Oss->uploadFile($file,$aliyunOssUrl);

        if($result===true)
        {
            return $this->Oss->getCurrentAliyunOssDomain().'/'.$aliyunOssUrl;
        }

        return false;
    }

    //普通文件上传方式传输文件到应用的临时目录
    public function uploadFileToTemp($keyName)
    {
        if(!$_FILES[$keyName])
        {
            $this->setErrorMsg('200','上传文件不存在');
            return false;
        }

        $tempFile=$_FILES[$keyName]['tmp_name'];
        $filename=$_FILES[$keyName]['name'];

        $data=array(
            'filename'=>$filename,
            'tempFilePath'=>$tempFile,
        );

        return $data;
    }

    //base64数据上传到应用的临时目录,并以文件存储的方式
    public function uploadBase64FileToTemp($base64Data,$filename)
    {
        $data=array();

        $filename = $filename?:time().'_'.rand(0,99);
        $path = RUNTIME_PATH.'Data/'.$filename;
        $num=file_put_contents($path,base64_decode($base64Data));
        if($num)
        {
            $data=array(
                'filename'=>$filename,
                'tempFilePath'=>$path,
            );
        }
        return $data;
    }

    //获取上传的文件名
    public function getFileName($fileName,$randName=1)
    {
        if($randName)
        {
            $pathInfo=pathinfo($fileName);
            $ext=$pathInfo["extension"];

            $fileName=sprintf("%s_%s.%s",date("YmdHis"),rand(0,999),$ext);
        }
        else
        {
            $fileName=basename($fileName);
        }

        return $fileName;
    }
}