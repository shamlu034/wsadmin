<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VideoContestController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VipController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
/*=====对外公开访问相关======*/
Route::post('emailRegister', [PublicController::class, 'emailRegister']); // 邮箱注册
Route::post('userNameRegister', [PublicController::class, 'userNameRegister']); // 邮箱注册
Route::post('getEmail', [PublicController::class, 'getEmail']); // 获取邮箱验证码
Route::post('registerChat', [PublicController::class, 'registerChat']); // 对话注册
Route::post('loginChat', [PublicController::class, 'loginChat']); // 登陆对话
Route::post('getSocketAddress', [PublicController::class, 'getSocketAddress']); // 获取邮箱验证码
Route::post('forgetPassword', [PublicController::class, 'forgetPassword']); // 忘记密码
Route::post('getBanner', [PublicController::class, 'getBanner']); // 获取banner
Route::post('getVipList', [PublicController::class, 'getVipList']); // 获取vip 说明
 
Route::post('getUserInfo', [PublicController::class, 'getUserInfo']); // 上传图片
Route::post('getLangCode', [PublicController::class, 'getLangCode']); // 上传图片
Route::post('myVideo', [PublicController::class, 'myVideo']); // 更新用户信息
Route::get('test', [PublicController::class, 'test']); // 更新用户信息
Route::post('webhook', [WebhookController::class, 'webhook']); // webhook
Route::get('zipFolder', [PublicController::class, 'zipFolder']); // webhook
Route::get('deleteCacheFile', [PublicController::class, 'deleteCacheFile']); // 删除文件夹
Route::post('sendTwo', [PublicController::class, 'sendTwo']); // number two message

// 权限验证相关
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);  // 登陆
    Route::post('logout', [AuthController::class, 'logout']); // 退出登陆
    Route::post('refresh', [AuthController::class, 'refresh']); // token 过期重新获取
//    Route::post('getUserInfo', [AuthController::class, 'me']); // 获取用户信息
});

// 需登陆用户相关
Route::group([
    'middleware' => 'api',
    'prefix' => 'user'
], function () {
    Route::post('boundEmail', [UsersController::class, 'boundEmail']); // 绑定邮箱 attentionUser
    Route::post('attentionUser', [UsersController::class, 'attentionUser']); // 关注 cancelAttention
    Route::post('cancelAttention', [UsersController::class, 'cancelAttention']); // 取消关注
    Route::post('cardIdVerified', [UsersController::class, 'cardIdVerified']); // 实名认证
    Route::post('getMyWatchlist', [UsersController::class, 'getMyWatchlist']); // 我的关注
    Route::post('getWatchMylist', [UsersController::class, 'getWatchMylist']); // 关注我的
    Route::post('updateUserInfo', [UsersController::class, 'updateUserInfo']); // 更新用户信息

});


// 银行卡相关
Route::group([
    'middleware' => 'api',
    'prefix' => 'bank'
], function () {
    Route::post('add', [BankController::class, 'add']); // 新增
    Route::post('update', [BankController::class, 'update']); // 修改
    Route::post('getBankList', [BankController::class, 'getBankList']); // 银行卡列表
    Route::post('deleteBank', [BankController::class, 'deleteBank']); // 删除银行卡
});


// 视频相关
Route::group([
    'prefix' => 'video'
], function () {
    Route::post('getVideoType', [VideoController::class, 'getVideoType']); // 获取视频类型
    Route::post('uploadVideo', [VideoController::class, 'uploadVideo']); // 上传视频
    Route::post('getVideoList', [VideoController::class, 'getVideoList']); // 获取视频列表
    Route::post('likeVideo', [VideoController::class, 'likeVideo']); // 喜欢视频
    Route::post('harborVideo', [VideoController::class, 'harborVideo']); // 收藏视频
    Route::post('deleteHarborVideo', [VideoController::class, 'deleteHarborVideo']); // 取消收藏
    Route::post('getMyLikeVideoList', [VideoController::class, 'getMyLikeVideoList']); // 我喜欢的
    Route::post('getMyHarborVideoList', [VideoController::class, 'getMyHarborVideoList']); // 我收藏的
    Route::post('getVideoTags', [VideoController::class, 'getVideoTags']); // 获取视频标签
    Route::post('getVideoTypeById', [VideoController::class, 'getVideoTypeById']); // 通过ID 获取视频类型
    Route::post('getVideoTagById', [VideoController::class, 'getVideoTagById']); // 通过ID 获取视频标签信息
    Route::post('browseVideo', [VideoController::class, 'browseVideo']); // 浏览视频
    Route::post('searchVideo', [VideoController::class, 'searchVideo']); // 视频搜索

});


// 签到相关
Route::group([
    'middleware' => 'api',
    'prefix' => 'check_in'
], function () {
    Route::post('getCheckInList', [CheckInController::class, 'getCheckInList']); // 获取签到列表
    Route::post('checkIn', [CheckInController::class, 'checkIn']); // 进行签到
});


// vip 相关
Route::group([
    'middleware' => 'api',
    'prefix' => 'vip'
], function () {
    Route::post('runVip', [VipController::class, 'runVip']); // 获取签到列表
});

// 每日大赛相关
Route::group([
    'middleware' => 'api',
    'prefix' => 'contest'
], function () {
    Route::post('getBanner', [VideoContestController::class, 'getBanner']); // 每日大赛banner
    Route::post('getContestVideo', [VideoContestController::class, 'getContestVideo']); // 每日大赛分类以及视频获取
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
