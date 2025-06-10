<?php

namespace App\Service;

use App\Models\VideoHarborModel;
use App\Models\VideoLikeModel;

class VideoService
{
    // 单例容器
    private static ?VideoService $instance = null;

    // 基类构造方法
    function __construct()
    {
    }

    static function getInstance(): VideoService
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // 视频文件字段组装
    function makeVideo($list, $userId)
    {

        foreach ($list as $item) {
            // 是否喜欢
            $item->is_like = (bool)VideoLikeModel::where(['video_id' => $item->id, 'user_id' => $userId])->first();

            // 是否已首场
            $item->is_harbor = (bool)VideoHarborModel::where(['video_id' => $item->id, 'user_id' => $userId])->first();

        }

        return $list;
    }

}
