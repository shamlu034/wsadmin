<?php

namespace App\Service;

use App\Models\CheckInLogModel;
use App\Models\UserCheckInModel;

class CheckInService
{


    // 单例容器
    private static ?CheckInService $instance = null;

    // 基类构造方法
    function __construct()
    {
    }

    static function getInstance(): CheckInService
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // 连续签到统计
    function continuousCheckIns($userId): array
    {

        // 本周一
        $thisWeekTime = strtotime('this week Monday', time());

        // 下周一
        $nextWeekTime = strtotime('monday');

        $checkInQuery = CheckInLogModel::where('user_id', $userId)
            ->whereBetween('insert_time', [$thisWeekTime, $nextWeekTime]);

        // 如果本周签到为0
        if ($checkInQuery->count() == 0) {
            return HelperService::getInstance()->successService(['count' => 0]);
        }

        // 本周内连续签到
        $count = $checkInQuery->orderBy('check_in_time', 'desc')->count();

        return HelperService::getInstance()->successService(['count' => $count]);
    }


    // 进行签到
    function conductCheckIn($checkInId, $userId): array
    {
        // 获取签到设置信息
        $checkInfo = UserCheckInModel::where('_id', trim($checkInId))->first();
        if (!$checkInfo) {
            return HelperService::getInstance()->errorService('签到信息不存在！');
        }

        // 当天0点的时间戳
        $nowTime = strtotime(date('Y-m-d 00:00:00'));

        // 获取本周内最后一次签到
        $thisWeekTime = strtotime('this week Monday', time());  // 本周一
        $nextWeekTime = strtotime('monday');  // 下周一
        $lastCheckLogInfo = CheckInLogModel::where('user_id', $userId)
            ->whereBetween('insert_time', [$thisWeekTime, $nextWeekTime])
            ->orderBy('insert_time', 'desc')
            ->first();
        if (!$lastCheckLogInfo && $checkInfo->weight != 1) {
            return HelperService::getInstance()->errorService('请从！' . $checkInfo->name . '开始签到');
        }

        // 检查签到信息
        $checkLogInfo = CheckInLogModel::where([
            'check_in_id' => $checkInId,
            'user_id' => $userId, // 用户id
            'check_in_time' => $nowTime, // 签到时间
        ])->first();
        if ($checkLogInfo) {
            return HelperService::getInstance()->errorService('请勿重复签到！');
        }

        // 如果最后一次签到是第一天，那么下次就要对第二天进行签到
        if ($lastCheckLogInfo) {
            $lastCheckInfo = UserCheckInModel::where('_id', $lastCheckLogInfo->check_in_id)->first();

            $nextCheckInfo = UserCheckInModel::where([
                'current' => $lastCheckInfo->current,
                'weight' => (string)($lastCheckInfo->weight + 1),
            ])->first();
            if ($nextCheckInfo->id != $checkInfo->id) {
                return HelperService::getInstance()->errorService('您未对' . $nextCheckInfo->name . '签到');
            }
        }

        // 一天只能签到一次
        $nowCheckInfo = CheckInLogModel::where([
            'user_id' => $userId, // 用户id
            'check_in_time' => $nowTime, // 签到时间
        ])->first();
        if ($nowCheckInfo) {
            return HelperService::getInstance()->errorService('今日您已签到，请明日再来');
        }

        // 签到数据表写入
        $checkLogInfo = CheckInLogModel::create([
            'check_in_id' => $checkInId, // 签到设置的id
            'user_id' => $userId, // 用户id
            'check_in_time' => $nowTime, // 签到时间
            'insert_time' => time(), //插入时间
        ]);

        // 增加钻石
        WithdrawService::getInstance()->addDiamond($userId, $checkInfo->diamond_quantity, $checkLogInfo->id);


        return HelperService::getInstance()->successService($checkLogInfo->toArray());
    }

}
