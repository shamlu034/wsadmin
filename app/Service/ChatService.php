<?php

namespace App\Service;

// 客服对话
use App\Models\ChatModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class ChatService
{


    // 单例容器
    private static ?ChatService $instance = null;

    // socket 功能相关地址地址
    private $socketAddress = '127.0.0.1:5001';

    // 基类构造方法
    function __construct()
    {

    }

    static function getInstance(): ChatService
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    // 注册chat 账户或登陆
    //  "uid": "xxxx", // 通信的用户唯一ID，可以随机uuid （建议自己服务端的用户唯一uid） （WuKongIMSDK需要）
    //  "token": "xxxxx", // 校验的token，随机uuid（建议使用自己服务端的用户的token）（WuKongIMSDK需要）
    //  "device_flag": 0, // 设备标识  0.app 1.web （相同用户相同设备标记的主设备登录会互相踢，从设备将共存）
    //  "device_level": 1 // 设备等级 0.为从设备 1.为主设备
    // status 对话类型 0 游客 1 非游客
    function registerOrLogin($chatId, $uid = '0', $device_flag = '1', $device_level = '1'): array
    {
        $http = new Client;

        // 查询对话信息是否存在
        $chatInfo = ChatModel::where(['uid' => $uid])->first();
        if (!$chatInfo) {
            // 创建对话信息
            $chatInfo = ChatModel::create([
                'uid' => ChatService::getInstance()->generateUid(), // 如果是游客， uid 随机生成
                'token' => ChatService::getInstance()->generateToken(),
                'device_flag' => $device_flag,
                'device_level' => $device_level,
                'status' => $uid == 0 ? "0" : "1",
                'end_time' => time(),
                'ip' => request()->ip(),
            ]);

        }

        try {
            // 请求参数
            $params = [
                "uid" => (string)$chatInfo->uid, // 通信的用户唯一ID，可以随机uuid （建议自己服务端的用户唯一uid） （WuKongIMSDK需要）
                "token" => $chatInfo->token, // 校验的token，随机uuid（建议使用自己服务端的用户的token）（WuKongIMSDK需要）
                "device_flag" => (int)$chatInfo->device_flag, // 设备标识  0.app 1.web （相同用户相同设备标记的主设备登录会互相踢，从设备将共存）
                "device_level" => (int)$chatInfo->device_level // 设备等级 0.为从设备 1.为主设备
            ];
            $res = $http->post($this->socketAddress . '/user/token', [
                'json' => $params
            ]);
        } catch (GuzzleException $e) {
            return HelperService::getInstance()->errorService($e->getMessage());
        }

        return HelperService::getInstance()->successService([
            'chatInfo' => $chatInfo,
            'postData' => $res->getReasonPhrase(),
        ]);
    }


    // 获取长链接地址
    function getSocketAddress($chatId): array|ResponseInterface
    {
        // 查询对话信息是否存在
        $chatInfo = ChatModel::where(['_id' => $chatId])->first();
        if (!$chatInfo) {
            // 创建对话信息
            return HelperService::getInstance()->errorService(trans('validation.exists', ['attribute' => 'chatInfo']));
        }

        $http = new Client;
        try {
            $res = $http->get($this->socketAddress . '/route?uid=' . $chatInfo->uid, []);
        } catch (GuzzleException $e) {
            return HelperService::getInstance()->errorService($e->getMessage());
        }

        return $res;
    }


    // 生成uid
    function generateUid(): string
    {
        $uid = HelperService::getInstance()->GetRandStr(5);

        $chatInfo = ChatModel::where('uid', $uid)->first();
        if ($chatInfo) {
            return $this->generateUid();
        }

        return $uid;
    }

    // 生成token
    function generateToken(): string
    {
        $token = HelperService::getInstance()->GetRandStr(20);

        $chatInfo = ChatModel::where('token', $token)->first();
        if ($chatInfo) {
            return $this->generateUid();
        }

        return $token;
    }
}
