<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 用户关注
class UserEntionModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'user_ention';


    protected $fillable = [
        'user_id', // 用户id
        'target_user_id', // 被关注id
        'attention_time', //关注时间
        'user_info',
        'created_at',
        'updated_at'
    ];

    // 被关注用户信息
    function getUserInfoAttribute($v)
    {

        return UserModel::where('id', $this->target_user_id)->first();
    }

}
