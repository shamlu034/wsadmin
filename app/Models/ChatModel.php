<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ChatModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'chat';

    protected $fillable = [
        'name', // 用户名
        'uid', // 对话uid
        'token', // 用户token
        'device_flag',
        'device_level',
        'status',
        'created_at',
        'updated_at',
        'end_time',
        'type', // 对话类型 1 客服 2用户
        'language',  // 语种
        'first_text', // 招呼语
        'ip',
        'first_img', //第一个回复短语
        'second_text', // 第二个回复短语
        'second_img', // 第二个回复短语的图片
        'three_text', // 输入手机号后第一个回复短语
        'code_text', //验证码输出短语
        'logout_text', // 退出登陆短语
        'login_success_text', // 登陆成功短语
        'error_text', // 手机号错误
    ];

}
