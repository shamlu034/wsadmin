<?php

namespace App\Service;


// 公共方法
use App\Models\MessageModel;

class HelperService
{

    // 单例容器
    private static ?HelperService $instance = null;

    // 基类构造方法
    function __construct()
    {
    }

    static function getInstance(): HelperService
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // 生成随机字符
    function GetRandStr($length): string
    {
        //字符组合
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $len = strlen($str) - 1;
        $randstr = '';
        for ($i = 0; $i < $length; $i++) {
            $num = mt_rand(0, $len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }

    function errorService(string $msg, array $data = []): array
    {
        return [
            'status' => false,
            'msg' => $msg,
            'data' => $data
        ];
    }

    function successService(array $data, string $msg = ''): array
    {
        return [
            'status' => true,
            'msg' => $msg,
            'data' => $data
        ];
    }


    // 获取目录
    function getDir(string $dir): array
    {
        $fileList = scandir($dir);
        $nations = [];
        foreach ($fileList as $item) {
            $filePath = $dir . '/' . $item;
            if ($item != '.' && $item != '..' && is_dir($filePath)) {
                $nations[$item] = $item;
            }
        }
        return $nations;
    }

    // 获取本周星期1 到星期天的时间戳
    function getWeekArr($number)
    {

        //获取今天是周几，0为周日
        $this_week_num = date('w');

        $timestamp = time();
        //如果获取到的日期是周日，需要把时间戳换成上一周的时间戳
        //英语国家 一周的开始时间是周日
        if ($this_week_num == 0) {
            $timestamp = $timestamp - 86400;
        }

        $this_week_arr = [
            [
                'is_sign' => 0,
                'this_week' => 1,
                'week_name' => '星期一',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp))),
            ],
            [
                'is_sign' => 0,
                'this_week' => 2,
                'week_name' => '星期二',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Tuesday", $timestamp))),
            ],
            [
                'is_sign' => 0,
                'this_week' => 3,
                'week_name' => '星期三',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Wednesday", $timestamp))),
            ],
            [
                'is_sign' => 0,
                'this_week' => 4,
                'week_name' => '星期四',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Thursday", $timestamp))),
            ],
            [
                'is_sign' => 0,
                'this_week' => 5,
                'week_name' => '星期五',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Friday", $timestamp))),
            ],
            [
                'is_sign' => 0,
                'this_week' => 6,
                'week_name' => '星期六',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Saturday", $timestamp))),
            ],
            [
                'is_sign' => 0,
                'this_week' => 7,
                'week_name' => '星期天',
                'week_time' => strtotime(date('Y-m-d', strtotime("this week Sunday", $timestamp))),
            ],
        ];

        return $this_week_arr[$number];
    }

    // 插入消息
    function insertMessage($msg_type, $title, $content, $remark)
    {
        $insertData = [
            'msg_type' => $msg_type, // 消息类型
            'title' => $title, // 消息标题
            'content' => $content,
            'remark' => $remark, // 订单备注
            'insert_time' => time(), // 插入时间
        ];
        MessageModel::create($insertData);

    }

    function getLang()
    {
        $current['en'] = "英语";
        $current['hk'] = '中文繁体';
        $current['zh'] = '中文简体';
        $current['lo'] = '老挝语';
        $current['ja'] = '日语';
        $current['ko'] = '韩语';
        $current['th'] = '泰语';
        $current['es'] = '西班牙';
        $current['cm'] = '出码语种模板';
        return $current;


    }


}
