<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

// 钱包充提相关
class WalletModel extends Model
{
    use HasFactory;


    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'wallet';


    protected $attributes = [
    ];

    protected $fillable = [
        'user_id', // 充值用户
        'exchange_rate_id', // 充值类型id
        'exchange_rate', // 兑换比例
        'remark', // 订单备注
        'recharge_amount', // 充值金额
        'payment_screenshot', // 支付截图
        'before_balance', // 扣款前的钻石余额
        'after_balance', // 扣款后的钻石余额
        'recharge_address', // 充值地址
        'created_at', //  创建时间
        'updated_at', // 更新时间
        'insert_time', // 插入时间
        'operation_type', // 操作类型
        'diamonds_number', // 操作钻石数量
        'exchange_rate_info', // 币种信息
    ];
}
