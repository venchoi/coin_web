<?php
namespace Common\Service;
class UploadService extends BaseService
{
    //上传币log到oss
    public function uploadCoinIcon($word,$file,$type='small',$uploadType=1,$wordType)
    {
        $sAliyunOss=SVC('AliyunOss');
        $filename=($type=="big")?$word."_big.jpg":$word.'.jpg';

        if($uploadType==1)
        {
            $imgUrl=$sAliyunOss->uploadBase64Img($file,'coin_logo',$filename);
        }
        else
        {
            $imgUrl=$sAliyunOss->uploadNormalImg('file','coin_logo',$filename);
        }

        $result=false;
        if($imgUrl)
        {
            $dCoin=D("Coin");

            if($type=='big')
            {
                $save=array('big_logo_url'=>$imgUrl);
            }
            else
            {
                $save=array('small_logo_url'=>$imgUrl,'big_logo_url'=>$imgUrl);
            }

            $wordTypeField=array(1=>'coin_code',2=>'en_name');
            $wordField=$wordTypeField[$wordType]?:'coin_code';

            $where=array($wordField=>$word);
            $result=$dCoin->updateCoinInfoByWhere($where,$save);
        }

        if($result!==false)
        {
            return $imgUrl;
        }

        $this->setErrorMsg('500','上传的失败');
        return false;
    }

    //上传渠道icon到oss
    public function uploadChanelIcon($channelId,$file,$uploadType=1)
    {
        $sAliyunOss=SVC('AliyunOss');
        $filename=$channelId.'.jpg';
        if($uploadType==1)
        {
            $imgUrl=$sAliyunOss->uploadBase64Img($file,'channel_icon',$filename);
        }
        else
        {
            $imgUrl=$sAliyunOss->uploadNormalImg('file','channel_icon',$filename);
        }

        $result=false;
        if($imgUrl)
        {
            $dNewsChannel=D("NewsChannel");
            $data=array(
                'id'=>$channelId,
                'thumb_url'=>$imgUrl
            );

            $result=$dNewsChannel->save($data);
        }

        if($result!==false)
        {
            return $imgUrl;
        }

        $this->setErrorMsg('500','上传的失败');
        return false;
    }

    //上传用户反馈图片
    public function uploadUserViewImage($id,$keyName='file',$uploadType=2)
    {
        $sAliyunOss=SVC('AliyunOss');

        $filename=$id.'_'.rand(0,999).".jpg";


        $imgUrl=$sAliyunOss->uploadNormalImg($keyName,'user_view',$filename);

        $result=false;

        if($imgUrl)
        {
            $imgUrl = implode(',', $imgUrl);
            $dUserView = D("UserView");
            $save = array('image_url' => $imgUrl);
            $where = array('id' => $id);
            $result = $dUserView->updateUserViewInfoWhere($where, $save);
        }

        if($result!==false)
        {
            return $imgUrl;
        }

        $this->setErrorMsg('500','上传的失败');
        return false;

    }
}
