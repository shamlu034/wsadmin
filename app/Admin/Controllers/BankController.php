<?php

namespace App\Admin\Controllers;

use App\Models\BankModel;
use App\Models\UserModel;
use App\Models\VipModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BankController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'VIP管理/';

    /**
     * Make a grid builder.
     *'name',
     *  'diamond_number', // 兑换所需钻石数量
     *  'share_number', // 邀请人数
     *  'created_at', //  创建时间
     *  'updated_at', // 更新时间
     *  'long_time',  // 有效时长 0 永久  单位天
     *  'weight', // 权重
     *  'remark', // 备注
     *  'bg_img_url', // 背景图
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new BankModel());
        $grid->column('_id', __('ID'));
        $grid->column('user_id', __('绑卡用户'))->display(function ($userId) {
            $userInfo = UserModel::where('id', $userId)->select(['name', 'email'])->first();
            return $userInfo->name ?? $userInfo->email;
        });
        $grid->column('account_holder', __('开户人'));
        $grid->column('bank_deposit', __('开户行'));
        $grid->column('bank_card', __('银行卡号'));
        $grid->column('branch_name', __('支行名称'));
        $grid->column('bank_address', __('办卡银行地址'));
        $grid->column('bank_code', __('银行代码'));
        $grid->column('family_address', __('家庭住址'));
        $grid->column('status', __('审核状态'))->display(function ($v) {
            //0 待审核 1 审核通过 2 审核被拒
            switch ($v) {
                case 0:
                    return '待审核';
                case 1:
                    return '审核通过';
                case 2:
                    return '审核被拒';
            }
        });

        $grid->column('weight', __('排序权重'))->editable();

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
        $show = new Show(BankModel::findOrFail($id));

        $show->field('_id', __('ID'));
        $show->field('user_id', __('绑卡用户'))->display(function ($userId) {
            $userInfo = UserModel::where('id', $userId)->select(['name', 'email'])->first();
            return $userInfo->name ?? $userInfo->email;
        });
        $show->field('account_holder', __('开户人'));
        $show->field('bank_deposit', __('开户行'));
        $show->field('bank_card', __('银行卡号'));
        $show->field('branch_name', __('支行名称'));
        $show->field('bank_address', __('办卡银行地址'));
        $show->field('bank_code', __('银行代码'));
        $show->field('family_address', __('家庭住址'));
        $show->field('status', __('审核状态'));

        $show->field('weight', __('排序权重'))->editable();
        //123
        return $show;
    }

    /**
     * 'account_holder', // 开户人
     * 'bank_deposit', // 开户行
     * 'bank_card', // 银行卡号
     * 'branch_name', // 支行名称
     * 'bank_address', // 办卡银行地址
     * 'bank_code', // 银行代码
     * 'family_address', // 家庭住址
     * 'status', // 银行卡审核状态 0 待审核 1 审核通过 2 审核被拒
     * 'created_at', //  创建时间
     * 'updated_at', // 更新时间
     * 'user_id',  // 绑卡的用户id
     * Make a form builder.
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new BankModel());

        $form->text('account_holder', __('开户人'));
        $form->text('bank_deposit', __('开户行'));
        $form->number('bank_card', __('银行卡号'));
        $form->text('branch_name', __('支行名称'));
        $form->text('bank_address', __('办卡银行地址'));
        $form->text('bank_code', __('银行代码'));
        $form->text('family_address', __('家庭住址'));

        $form->select('status', __('审核状态'))->options([
            0 => '待审核',
            1 => '审核通过',
            2 => '审核被拒'
        ]);

        $userList = UserModel::where([])->select(['id', 'name', 'email'])->get();
        $options = [];
        foreach ($userList as $item) {
            $options[$item->id] = $item->name ?? $item->email;
        }

        $form->select('user_id', __('选择绑卡用户'))->options($options);
        $form->number('weight', __('权重'))->default(1);

        return $form;
    }
}
