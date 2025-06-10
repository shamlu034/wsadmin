<?php

use App\Admin\Controllers\BankController;
use App\Admin\Controllers\BannerController;
use App\Admin\Controllers\CheckLogController;
use App\Admin\Controllers\ContestBannerController;
use App\Admin\Controllers\CustomerController;
use App\Admin\Controllers\ExchangeController;
use App\Admin\Controllers\FryController;
use App\Admin\Controllers\SettingChatController;
use App\Admin\Controllers\UserCardController;
use App\Admin\Controllers\UserCheckController;
use App\Admin\Controllers\UserController;
use App\Admin\Controllers\UserVipController;
use App\Admin\Controllers\VideoContestController;
use App\Admin\Controllers\VideoController;
use App\Admin\Controllers\VideoNationController;
use App\Admin\Controllers\VideoTagController;
use App\Admin\Controllers\VipController;
use App\Admin\Controllers\VipLogController;
use App\Admin\Controllers\WithdrawController;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('/user', UserController::class); // 用户管理
    $router->resource('/banner', BannerController::class); // banner 管理
    $router->resource('/vip', VipController::class); // vip 管理
    $router->resource('/bank', BankController::class); // 银行卡 管理
    $router->resource('/exchange', ExchangeController::class); // 充提设置
    $router->resource('/withdraw', WithdrawController::class); // 充提列表
    $router->resource('/video_nation', VideoNationController::class); // 视频分类管理
    $router->resource('/video_tag', VideoTagController::class); // 视频分类管理
    $router->resource('/video', VideoController::class); // 视频管理
    $router->resource('/check_in', UserCheckController::class); // 签到管理
    $router->resource('/check_log', CheckLogController::class); // 签到管理
    $router->resource('/user_vip', UserVipController::class); // 会员用户管理
    $router->resource('/vip_log', VipLogController::class); // 会员开通记录
    $router->resource('/user_card', UserCardController::class); // 实名认证审核
    $router->resource('/video_contest', VideoContestController::class); // 实名认证审核
    $router->resource('/contest_banner', ContestBannerController::class); // 每日大赛banner
    $router->resource('/setting_chat', SettingChatController::class); // 客服设置
    $router->resource('/fry', FryController::class); // 鱼苗
    $router->resource('/customer', CustomerController::class); // 客服列表

});

