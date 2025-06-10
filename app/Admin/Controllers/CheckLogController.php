<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\CheckInLogModel;
use App\Models\ExchangeModel;
use App\Models\UserCheckInModel;
use App\Models\UserModel;
use App\Models\VipModel;
use App\Models\WalletModel;
use App\Service\HelperService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

// 签到记录
class CheckLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '签到记录/';

    /**
     * 'check_in_id', // 签到设置的id
     * 'user_id', // 用户id
     * 'check_in_time', // 签到时间
     * 'created_at',
     * 'updated_at',
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new CheckInLogModel());
        $grid->disableActions();
        $grid->disableCreation();


        $grid->column('_id', __('记录ID'));
        $grid->column('check_in_id', __('签到类型'))->display(function ($v) {
            return UserCheckInModel::where('_id', $v)->first()->name;
        });
        $grid->column('user_id', __('签到用户'))->display(function ($v) {
            $userInfo = UserModel::where('id', $v)->first();
            return $userInfo->name ?? $userInfo->email;
        });
        $grid->column('created_at', __('签到时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });

        $grid->column('__', __('记录详情'))->modal('记录详情', function () {
            $info = WalletModel::where('recharge_address', $this->id)->first();

            $array = [
                '订单号' => $info->id,
                '充值类型' => ExchangeModel::where('_id', $info->exchange_rate_id)->first()->name,
                '签到前用户余额' => $info->before_balance,
                '签到后用户余额' =>$info->after_balance,
                '签到时间' =>$info->created_at,
                '订单备注' =>$info->remark,
            ];

            return new Table([], $array);
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail(mixed $id): Show
    {

    }

    /**
     * Make a form builder.
     * @return Form
     */
    protected function form(): Form
    {

    }
}
