<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// vip 兑换记录表
class VipLogModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'vip_log';


    protected $attributes = [
    ];

    protected $fillable = [
        'user_id', // 开通会员的用户
        'vip_id', // 开通会员的类型
        'type', // 开通会员的方式 1余额购买 2邀请用户
        'created_at', //入库时间
        'updated_at', // 更新时间
        'insert_time', // 入库时间
        'consumption', // 消费 如果是余额消费， 这个值记录钻石， 如果是邀请人数消费， 这个值记录邀请人数
        'vip_info', // 当时的会要邀请设置信息
    ];
}
