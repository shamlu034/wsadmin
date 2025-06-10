<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 用户会员表
class UserVipModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'user_vip';

    protected $fillable = [
        'user_id', // 开通会员的用户
        'start_time', // 开通时间
        'end_time', // 会员到期时间
        'created_at', // 入库时间
        'updated_at', // 更新时间
        'type', // 会员类型
    ];


}
