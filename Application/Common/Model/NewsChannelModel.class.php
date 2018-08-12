<?php

namespace Common\Model;

/**
 * 新闻渠道模型
 * Class NewsChannelModel
 * @package Common\Model
 */
class NewsChannelModel extends DataBaseModel
{
    protected $tableName="news_channel";

    public function getAllNewsChannelIconsKeyId($key='id')
    {
        $data=$this->getField("id,thumb_url",true);
        if($data)
        {
            $data=array();
        }

        return $data;
    }
}