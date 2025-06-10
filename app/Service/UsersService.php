<?php

namespace App\Service;


// 用户相关的一些操作
use App\Models\UserEntionModel;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;

class UsersService
{
    // 单例容器
    private static ?UsersService $instance = null;

    // 基类构造方法
    function __construct()
    {
    }

    static function getInstance(): UsersService
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    // 忘记密码
    function forgetPassword(string $userId, string $password): array
    {
        $userInfo = UserModel::where('id', $userId)->first();
        if (!$userInfo) {
            return HelperService::getInstance()->errorService(trans('validation.exists', ['attribute' => '']));
        }

        $userInfo->password = Hash::make($password);
        $userInfo->save();

        return HelperService::getInstance()->successService($userInfo->toArray());
    }

    // 关注用户
    public function attentionUser($target_user_id): array
    {
        // 当前用户的用户信息
        $userInfo = auth()->user();

        // 被关注用户信息
        $atUserInfo = UserModel::where('id', $target_user_id)->first();

        if ($userInfo->id == $target_user_id) {
            return HelperService::getInstance()->errorService('不能关注自己');
        }

        if (!$atUserInfo) {
            return HelperService::getInstance()->errorService('被关注用户不存在');
        }

        $ention = UserEntionModel::where(['user_id' => $userInfo->id, 'target_user_id' => $target_user_id])->first();
        if (!$ention) {
            $ention = UserEntionModel::create([
                'user_id' => $userInfo->id,
                'target_user_id' => $target_user_id,
                'attention_time' => time(),
                'user_info' => $atUserInfo,
            ]);
        }

        return HelperService::getInstance()->successService($ention->toArray());
    }

    // 取消关注
    public function cancelAttention($target_user_id)
    {
        // 当前用户的用户信息
        $userInfo = auth()->user();


        $ention = UserEntionModel::where(['user_id' => $userInfo->id, 'target_user_id' => $target_user_id])->first();
        if (!$ention) {
            return HelperService::getInstance()->successService([]);
        }

        $ention->delete();

        return HelperService::getInstance()->successService([]);
    }


    // 用户注册
    function register($request): array
    {

        // 写入用户信息
        $insertData = [
            'name' => trim($request->input('name')) ?? HelperService::getInstance()->GetRandStr(6),
            'password' => Hash::make(trim($request->input('password'))),
            'email' => trim($request->input('email')) ?? '',
            'invitation_code' => HelperService::getInstance()->GetRandStr(5),
            'chat_id' => trim($request->input('chat_id')),
            'language' => trim($request->input('language')) ?? 'en',
        ];

        // 检查是否有邀请码
        if ($invitation_code = trim($request->input('invitation_code'))) {

            // 获取邀请者的信息
            $parentUserInfo = UserModel::where('invitation_code', $invitation_code)->first();
            if (!$parentUserInfo) {
                return HelperService::getInstance()->errorService('邀请码不正确！');
            }

            $insertData['parent_user_id'] = $parentUserInfo->id;
        }

        // 检查用户信息是否存在
        $userInfo = trim($request->input('name')) ?
            UserModel::where('name', $request->input('name'))->first() :
            UserModel::where('email', $request->input('email'))->first();
        if (!$userInfo) {
            // 数据入库
            UserModel::create($insertData);
        }

        return HelperService::getInstance()->successService([]);
    }


    // 用户签到
    function checkIn($userId)
    {

    }


}
