<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


// 用户银行卡相关
class BankModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'bank';


    protected $attributes = [
        'status' => 0
    ];

    protected $fillable = [
        'account_holder', // 开户人
        'bank_deposit', // 开户行
        'bank_card', // 银行卡号
        'branch_name', // 支行名称
        'bank_address', // 办卡银行地址
        'bank_code', // 银行代码
        'family_address', // 家庭住址
        'status', // 银行卡审核状态 0 待审核 1 审核通过 2 审核被拒
        'created_at', //  创建时间
        'updated_at', // 更新时间
        'user_id',  // 绑卡的用户id
        'weight', // 权重
    ];
}
