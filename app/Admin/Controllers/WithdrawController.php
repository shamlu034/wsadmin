<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\DepositActions;
use App\Admin\Actions\ExtractActions;
use App\Models\BannerModel;
use App\Models\ExchangeModel;
use App\Models\UserModel;
use App\Models\VipModel;
use App\Models\WalletModel;
use App\Service\HelperService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;

// 充值提款列表
class WithdrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '充提管理/';


    /**
     * Make a grid builder.
     * 'user_id', // 充值用户
     *  'exchange_rate_id', // 充值类型id
     *  'exchange_rate', // 兑换比例
     *  'remark', // 订单备注
     *  'recharge_amount', // 充值金额
     *  'payment_screenshot', // 支付截图
     *  'before_balance', // 扣款前的钻石余额
     *  'after_balance', // 扣款后的钻石余额
     *  'recharge_address', // 充值地址
     *  'created_at', //  创建时间
     *  'updated_at', // 更新时间
     *  'insert_time', // 插入时间
     *  'operation_type', // 操作类型
     *  'diamonds_number', // 操作钻石数量
     * exchange_rate_info // xinxi
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new WalletModel());
        $grid->disableCreateButton();

        // 充值按钮
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append("<a href='/admin/withdraw/create' class='report-posts btn btn-sm import-post' style='border: 1px solid' >
        <i class='fa fa-upload'></i>
        充值</a>");
        });

        // 提现按钮
//        $grid->tools(function (Grid\Tools $tools) {
//            $tools->append(new ExtractActions);
//        });

        $grid->column('_id', __('订单号'))->style('width:100px; word-break: break-all');
        $grid->column('user_id', __('充值用户'))->display(function ($v) {
            $userInfo = UserModel::where('id', $v)->first();
            return $userInfo->name ?? $userInfo->email;
        });
        $grid->column('exchange_rate', __('兑换比例'))->display(function ($v) {
            return '1比' . $v . '钻';
        });
        $grid->column('recharge_amount', __('充值金额'))->display(function ($v) {
            $exchange_rate_info = json_decode($this->exchange_rate_info);
            return '【' . $v . '】' . $exchange_rate_info->nation_code;
        });

        $grid->column('diamonds_number', __('操作钻石数量'))->display(function ($v) {
            $str = $this->operation_type == 1 ? '充值' : '扣款';
            return $str . $v . '钻';
        });
        $grid->column('___', __('币种信息'))->modal('币种汇率信息', function ($model) {

            $data = json_decode($model->exchange_rate_info, true);
            return new Table([], $data);
        });
        $grid->column('payment_screenshot', __('支付截图'))->image('', 50, 50);
        $grid->column('before_balance', __('操作前的钻石余额'))->style('width:100px; word-break: break-all');
        $grid->column('after_balance', __('操作后的钻石余额'))->style('width:100px; word-break: break-all');
        $grid->column('recharge_address', __('充值地址'))->style('width:100px; word-break: break-all');
        $grid->column('remark', __('备注'));

        $grid->column('created_at', __('注册时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $grid->actions(function ($actions) {
            // 去掉`查看`按钮
            $actions->disableView();
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
        $show = new Show(WalletModel::findOrFail($id));


        $show->field('_id', __('ID'));

        $show->field('_id', __('ID'));
        $show->field('name', __('VIP类型名称'));
        $show->field('diamond_number', __('兑换所需钻石数量'));
        $show->field('share_number', __('邀请人数'));
        $show->field('long_time', __('有效时长'));

        $show->field('bg_img_url', __('背景图'))->image();
        $show->field('weight', __('排序权重'));
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('更新时间'));

        return $show;
    }

    /**
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new WalletModel());
        $form->setTitle('用户充值');
        // 用户信息选择
        $userList = UserModel::where([])->select(['id', 'name', 'email'])->get();
        $us = [];
        foreach ($userList as $userInfo) {
            $us[$userInfo->id] = $userInfo->name ?? $userInfo->email;
        }
        $form->select('user_id', __('选择操作用户:'))->options($us)->required();

        // 币种选择
        $exchangeRateList = ExchangeModel::where(['status' => '1'])->select(['id', 'nation_code'])->get();
        $exchangeTypes = [];
        foreach ($exchangeRateList as $v) {
            $exchangeTypes[$v->id] = $v->nation_code;
        }
        $form->select('exchange_rate_id', __('选择币种:'))->options($exchangeTypes)->required();

        $form->number('recharge_amount', __('充值金额:'))->default(0)->required();
        $form->text('recharge_address', __('充值地址:'));
        $form->image('payment_screenshot', __('充值截图:'));
        $form->text('remark', __('订单备注:'));

        $form->saving(function (Form $form) {

            // 获取用户信息
            $userInfo = UserModel::where('id', $form->user_id)->first();
            if (!$userInfo) {
                abort(500, '用户信息不存在');
            }

            // 获取币种信息
            $exchangeInfo = ExchangeModel::where('_id', $form->exchange_rate_id)->first();
            if (!$exchangeInfo) {
                abort(500, '未找到该币种的信息');
            }

            // 兑换比例
            $form->model()->exchange_rate = $exchangeInfo->diamond_quantity;

            // 充值的钻石
            $form->model()->diamonds_number = $form->recharge_amount * $exchangeInfo->diamond_quantity;
            UserModel::where('id', $form->user_id)->increment('diamonds', (int)$form->model()->diamonds_number);   // 用户余额增加钻石

            // 操作前的钻石余额
            $form->model()->before_balance = $userInfo->diamonds;

            // 操作后的钻石
            $form->model()->after_balance = $userInfo->diamonds + $form->model()->diamonds_number;

            // 插入时间
            $form->model()->insert_time = time();

            // 币种信息
            $form->model()->exchange_rate_info = json_encode($exchangeInfo);

            // 操作类型
            $form->model()->operation_type = 1; // 1 充值 2扣款
        });
        return $form;
    }
}
