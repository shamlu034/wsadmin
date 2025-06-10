<?php

namespace App\Service;

// 客服对话
use App\Models\ChatModel;
use App\Models\UserModel;
use App\Models\UserVipModel;
use App\Models\VipLogModel;
use App\Models\VipModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class VipService
{


    // 单例容器
    private static ?VipService $instance = null;


    // 基类构造方法
    function __construct()
    {

    }

    static function getInstance(): VipService
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    function runVip($userId, $type, $vipId): array
    {

        // 用户信息
        $userInfo = UserModel::where('id', $userId)->first();

        // 获取vip配置信息
        $vipInfo = VipModel::where('_id', trim($vipId))->first();
        if (!$vipInfo) {
            return HelperService::getInstance()->errorService('会员配置信息不存在！');
        }

        // 判断用户余额
        if ($type == 1) {
            // 余额不足
            if ($userInfo->diamonds < $vipInfo->diamond_number) {
                return HelperService::getInstance()->errorService('余额不足！');
            }
        }

        // 判断剩余有效邀请人数
        if ($type == 2) {

            // 邀请总人数
            $nextUserInfoCounts = UserModel::where('parent_user_id', $userInfo->id)->count();
            if ($nextUserInfoCounts < $vipInfo->share_number) {
                return HelperService::getInstance()->errorService('邀请人数不足！');
            }

            // 已使用邀请人数
            $consumption = VipLogModel::where([
                'user_id' => $userInfo->id,
                'type' => 2, // 邀请兑换类型
            ])->pluck('consumption')->toArray();
            $shareNumber = array_sum(array_column($consumption, 'consumption'));

            // 剩余邀请人数 小于 需邀请人数
            if ($shareNumber < $vipInfo->share_number) {
                return HelperService::getInstance()->errorService('邀请剩余人数不足！');
            }
        }


        // 开通时长, s
        $activationDuration = $vipInfo->long_time * 86400;

        // 当前时间
        $nowTime = time();

        // 查询当前用户的vip 开通状况
        $userVipInfo = UserVipModel::where(['user_id' => $userInfo->id])->first();
        if (!$userVipInfo) {
            // 进行开通
            $userVipInfo = UserVipModel::create([
                'user_id' => $userInfo->id, // 开通会员的用户
                'start_time' => $nowTime, // 开通时间
                'end_time' => $nowTime + $activationDuration, // 会员到期时间
                'type' => $vipInfo->long_time ? 2 : 1,//是否是终生会员 1 是 2否
            ]);
        } else {

            // 检查是否已经开通永久vip
            if ($userVipInfo->type == 1) {
                return HelperService::getInstance()->errorService('您已经是终生会员，无需重复开通！');
            }

            // 检查已经开通会员的会员过期状态, 如果已过期
            if ($userVipInfo->end_time < time()) {
                // 重置会员信息
                UserVipModel::where('user_id', $userInfo->id)->update([
                    'start_time' => $nowTime,
                    'end_time' => $nowTime + $activationDuration,
                    'type' => $vipInfo->long_time ? 2 : 1,
                ]);
            } else {
                // 没有过期的处理情况
                UserVipModel::where('user_id', $userInfo->id)->update([
                    'end_time' => $userVipInfo->end_time + $activationDuration,
                    'type' => $vipInfo->long_time ? 2 : 1,
                ]);
            }
        }

        //如果是余额开通，减去余额
        if ($type == 1) {
            UserModel::where('id', $userId)->decrement('diamonds', $vipInfo->diamond_number);
        }

        // 兑换日志写入
        VipLogModel::create([
            'user_id' => $userInfo->id, // 开通会员的用户
            'vip_id' => $vipInfo->id, // 开通会员的类型
            'type' => $type, // 开通会员的方式 1余额购买 2邀请用户
            'insert_time' => $nowTime, // 入库时间
            'consumption' => $type == 1 ? $vipInfo->diamond_number : $vipInfo->share_number, // 开通会员的消费
            'vip_info' => json_encode($vipInfo->toArray())
        ]);

        // 写入消息
        $title = '会员到账通知';
        $content = '您于' . date('Y-m-d H:i:s', time()) . '兑换的' . $vipInfo->name . '已经到账, 全部影片免费观看，更多特权请移步至';
        HelperService::getInstance()->insertMessage('vip', $title, $content, '');

        return HelperService::getInstance()->successService($userVipInfo->toArray());
    }
}
