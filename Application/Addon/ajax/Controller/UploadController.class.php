<?php
namespace Addon\ajax\Controller;

/**
 * 上传相关接口
 * Class UploadController
 * @package Addon\ajax\Controller
 */
class UploadController extends BaseController
{
    //图像上传接口
    public function upload_for_img()
    {
        $token=I('token');
        if($token !='aliyun28198288')
        {
            $this->ajaxCallMsg('400','deny permission.');
        }

        $sAliyunOss=SVC("AliyunOss");
        $key=I('key','file');
        $filename=I('filename','');
        $type=I('type','');

        if(!$_FILES[$key])
        {
            $this->ajaxCallMsg('200','file can not be empty.');
        }

        $result=$sAliyunOss->uploadNormalImg($key,$type,$filename);
        if(!$result)
        {
            $this->ajaxCallMsg('500','fail.');
        }

        $data['error_code'] = '0';
        $data['msg'] = 'succ.';
        $data['url'] = $result;
        $this->ajaxReturn($data);
    }

    //上传币的icon
    public function upload_coin_logo()
    {
        $token=I('token');
        if($token !='aliyun28198288')
        {
            $this->ajaxCallMsg('400','deny permission.');
        }

        $word=I('word');
        $file=I('file');
        $type=I('type','small');
        $uploadType=I('upload_type',1);
        $wordType=I('word_type',1);
        if(empty($word))
        {
            $this->ajaxCallMsg('200','word不能为空.');
        }

        if(!in_array($wordType,array(1,2)))
        {
            $this->ajaxCallMsg('200','非法的word类型');
        }

        $sUpload=SVC("Upload");
        $result=$sUpload->uploadCoinIcon($word,$file,$type,$uploadType,$wordType);
        if(!$result)
        {
            $this->ajaxCallMsg('500','fail.');
        }

        $this->ajaxCallMsg('0','succ.',array('url'=>$result));
    }

    //上传渠道的icon
    public function upload_channel_icon()
    {
        $token=I('token');
        if($token !='aliyun28198288')
        {
            $this->ajaxCallMsg('400','deny permission.');
        }

        $channelId=I('channel_id');
        $file=I('file');
        $uploadType=I('upload_type',1);
        if(empty($channelId))
        {
            $this->ajaxCallMsg('200','channel_id不能为空.');
        }

        $sUpload=SVC("Upload");
        $result=$sUpload->uploadChanelIcon($channelId,$file,$uploadType);
        if($result===false)
        {
            $this->ajaxCallMsg('500','fail.');
        }

        $this->ajaxCallMsg('0','succ.',array('url'=>$result));
    }
}