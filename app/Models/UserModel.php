<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserModel extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $appends = [
        'attention', // 关注数
        'video_number', // 作品数量
        'harbor_number', // 收藏数量
        'browse_number', // 浏览量
        'subordinate_number', // 下级数量
        'user_vip', // 用户会员
        'user_card', // 用户实名状态
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }


    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    // 一对多关联用户关注表
    function user_ention()
    {
        return $this->hasMany(UserEntionModel::class, 'user_id', 'id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // 用户名
        'email', // 用户邮箱
        'password', // 用户密码
        'nick_name', // 用户昵称
        'language', // 语种
        'parent_user_id', // 父级id
        'invitation_code', // 邀请码
        'phone_number', // 手机号
        'avatar', // 头像
        'introduction', // 个人介绍
        'chat_id', // 对话id
        'diamonds', // 用户钻石
        'fans_count', // 粉丝数量
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // 邮箱
    function getEmailAttribute($value): string
    {
        return strpos($value, '@') !== false ? $value : '';
    }

    // 获取被关注数量  ['attention', 'video_number'];
    function getAttentionAttribute($v)
    {
        return UserEntionModel::where('target_user_id', $this->id)->count();
    }

    // 获取作品数量
    function getVideoNumberAttribute($v)
    {
        return VideoModel::where('user_id', $this->id)->count();
    }


    // 获取收藏数量
    function getHarborNumberAttribute($v)
    {
        return VideoHarborModel::where('user_id', $this->id)->count();
    }


    // 获取我最近的浏览 browse_number
    function getBrowseNumberAttribute($v)
    {
        return UserBrowseModel::where('user_id', $this->id)
            ->whereNotBetween('insert_time', [(time() - 86400), time()])
            ->count();
    }

    //    下级数量
    function getSubordinateNumberAttribute($v)
    {
        return UserModel::where('parent_user_id', $this->id)
            ->count();
    }


    // 用户会员信息 user_vip
    function getUserVipAttribute($v)
    {
        return UserVipModel::where('user_id', $this->id)
            ->first();
    }


    // 用户实名状态 user_card
    function getUserCardAttribute($v)
    {
        return UserCardModel::where('user_id', $this->id)
            ->first();
    }
}
