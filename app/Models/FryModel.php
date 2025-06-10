<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 鱼苗
class FryModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'fry';

// 不维护 Eloquent 默认的 created_at 和 updated_at 字段
    public $timestamps = false;

    // 手动设置时间戳字段的值
    public function saveWithTimestamps(array $attributes = [])
    {
        // 手动设置 created_at 和 updated_at 字段的值
        $attributes['created_at'] = now();
        $attributes['updated_at'] = now();

        return $this->newQuery()->insert($attributes);
    }

    protected $fillable = [
        'admin_id', // 被分配给那个后台管理人员
        'language', // 语种
        'status',  // 状态 1已登陆
        'uid', // 用户的uid
        "phone_number", // 用户的手机号
        'remark', // 备注
        'bg_img_url', // 背景图
        'nick_name', // 所需邀请人数
        'created_at', //  创建时间
        'updated_at', // 更新时间
    ];

//    function getCreatedAtAttribute($v)
//    {
//        dd($v);
////        return date("Y-m-d H:i:s",$v);
//    }


}
