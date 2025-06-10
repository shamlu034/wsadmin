<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 用户签到设置表
class UserCheckInModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'user_check_in';

    protected $fillable = [
        'name', // 设置名称 比如 第一天、 第二天
        'diamond_quantity', // 钻石数量
        'current', // 语种
        'status', // 是否启用
        'weight', // 权重
        'bg_img_url', // 背景图
        'created_at',
        'updated_at',
        'insert_time', //插入时间
    ];

}
