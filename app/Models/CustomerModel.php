<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


// 用户银行卡相关
class CustomerModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'customer';


    protected $fillable = [
        'admin_id',
        'uid', // 对话uid
        'token', // 用户token
        'created_at',
        'updated_at',
        'chat_id'

    ];
}


