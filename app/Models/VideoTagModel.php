<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


// 视频分类
class VideoTagModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'video_tag';


    protected $attributes = [
        'status' => 1 //是否启用
    ];

    protected $fillable = [
        'type_name', // 分类名称
        'weight', // 分类排序权重
        'admin_id', // 维护该分类的管理
        'created_at',
        'updated_at',
    ];
}
