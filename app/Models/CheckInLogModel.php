<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 签到里诶博阿
class CheckInLogModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'check_in_log';

    protected $fillable = [
        'check_in_id', // 签到设置的id
        'user_id', // 用户id
        'check_in_time', // 签到时间
        'created_at',
        'updated_at',
        'insert_time', //插入时间
    ];

}
