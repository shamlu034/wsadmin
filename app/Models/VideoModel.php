<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 视频表
class VideoModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'video';

    protected $appends = ['user_info'];

    protected $attributes = [
        'play_number' => 0, // 播放量
        'hot_number' => 0, // 热度
        'look_number' => 0, // 观看次数
        'dis_recommend' => 0, // 不推荐
        'recommend' => 0, // 推荐
        'like' => 0, // 喜欢
        'harbor' => 0, // 收藏数量
    ];

    protected $fillable = [
        'video_url', // 视频地址
        'video_name', // 视频名称
        'status', // 视频状态
        'play_number', // 播放量
        'hot_number', // 热度
        'look_number', // 观看次数
        'dis_recommend', // 不推荐
        'recommend', // 推荐
        'like', // 喜欢
        'admin_id', // 后台用户id
        'created_at',
        'updated_at',
        'insert_time',
        'weight', // 权重
        'user_id',
        'user_info',  // 用户信息
        'remark', // 备注
        'is_vip', // 是否是会员视频
        'video_money', // 视频单价
        'paid_role', // 收费角色
        'harbor', // 收藏数量
        'is_original', // 是否是原创
        'is_featured',  // 是否是精选视频
        'video_img', // 封面图
        'video_tag', // 视频标签
        'browse_number', //视频浏览量
        'ads_img', // 广告图
        'ads_url', // 广告外链
        'video_type', // 视频分类
        'video_contest_id',  // 每日大赛分类
    ];

    function getUserInfoAttribute($value)
    {
        return UserModel::where('id', $this->user_id)->first();
    }

}
