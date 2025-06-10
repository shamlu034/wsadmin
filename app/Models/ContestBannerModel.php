<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ContestBannerModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'contest_banner';


    protected $attributes = [
        'status' => 1,  // 1 启用 0 未启用
        'weight' => 1,
        'end_time' => 0,
    ];

    protected $fillable = [
        'target_url',
        'img_url',
        'status',
        'created_at',
        'updated_at',
        'end_time',
        'weight', // 权重
        'remark', // 备注
        'type', //
        'nature', // 1 banner 2 广告
    ];





}
