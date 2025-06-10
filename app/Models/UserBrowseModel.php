<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 用户浏览
class UserBrowseModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'user_browse';

    protected $fillable = [
        'video_id', // 视频地址
        'user_id', // 视频名称
        'created_at',
        'updated_at',
        'insert_time', //插入时间
    ];
}
