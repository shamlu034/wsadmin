<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 会员配置表
class VipModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'vip';


    protected $attributes = [
        'weight' => 1,
        'end_time' => 0,
    ];

    protected $fillable = [
        'name',
        'diamond_number', // 兑换所需钻石数量
        'share_number', // 所需邀请人数
        'created_at', //  创建时间
        'updated_at', // 更新时间
        'long_time',  // 有效时长 0 永久  单位天
        'weight', // 权重
        'remark', // 备注
        'bg_img_url', // 背景图
    ];


}
