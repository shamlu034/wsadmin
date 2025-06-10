<?php

namespace App\Service;

// 钱包相关操作
use App\Models\ExchangeModel;
use App\Models\UserModel;
use App\Models\WalletModel;

class WithdrawService
{
    // 单例容器
    private static ?WithdrawService $instance = null;

    // 基类构造方法
    function __construct()
    {
    }

    static function getInstance(): WithdrawService
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // 签到钻石充值
    //          'user_id', // 充值用户
    //        'exchange_rate_id', // 充值类型id
    //        'exchange_rate', // 兑换比例
    //        'remark', // 订单备注
    //        'recharge_amount', // 充值金额
    //        'payment_screenshot', // 支付截图
    //        'before_balance', // 扣款前的钻石余额
    //        'after_balance', // 扣款后的钻石余额
    //        'recharge_address', // 充值地址
    //        'created_at', //  创建时间
    //        'updated_at', // 更新时间
    //        'insert_time', // 插入时间
    //        'operation_type', // 操作类型
    //        'diamonds_number', // 操作钻石数量
    //        'exchange_rate_info', // 币种信息
    function addDiamond($userId, $diamond, $checkLogId): array
    {
        // 汇率设置信息
        $exchangeInfo = ExchangeModel::where('nation_code', 'DIAMOND')->first();

        // 用户信息
        $userInfo = UserModel::where('id', $userId)->first();

        $diamonds_number = $diamond * $exchangeInfo->diamond_quantity;
        $insertData = [
            'user_id' => $userId, // 充值用户
            'exchange_rate_id' => $exchangeInfo->id, // 充值类型id
            'exchange_rate' => $exchangeInfo->diamond_quantity, // 兑换比例
            'remark' => '用户签到', // 订单备注
            'recharge_amount' => $diamond, // 充值金额
            'payment_screenshot' => '', // 支付截图
            'before_balance' => $userInfo->diamonds, // 扣款前的钻石余额
            'after_balance' => $userInfo->diamonds + $diamonds_number, // 扣款后的钻石余额
            'recharge_address' => $checkLogId, // 签到id
            'insert_time' => time(), // 插入时间
            'operation_type' => 1, // 操作类型
            'diamonds_number' => $diamonds_number, // 操作钻石数量
            'exchange_rate_info' => json_encode($exchangeInfo), // 币种信息
        ];

        // 数据入库操作
        WalletModel::create($insertData); // 创建记录
        UserModel::where('id', $userId)->increment('diamonds', $diamonds_number);   // 用户余额增加钻石

        return HelperService::getInstance()->successService([]);
    }

}
