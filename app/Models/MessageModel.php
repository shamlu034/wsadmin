<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 站内消息
class MessageModel extends Model
{
    use HasFactory;


    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'message';


    protected $attributes = [
    ];

    protected $fillable = [
        'title', // 消息标题
        'remark', // 消息内容
        'created_at', //  创建时间
        'updated_at', // 更新时间
        'insert_time', // 插入时间
    ];
}
