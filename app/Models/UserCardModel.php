<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 用户实名认证
class UserCardModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'user_card';


    protected $fillable = [
        'user_id', // 用户id
        'card_name', // 真实姓名
        'card_id_number', // 证件号码
        'created_at', //  创建时间
        'updated_at', // 更新时间
        'insert_time',  //  入库时间
        'card_status', // 证件审核状态
        'img1', // 证件照片地址
        'img2', // 证件照片地址2
        'img3', // 证件照片地址3
        'img4', // 证件照片地址4
    ];
}
