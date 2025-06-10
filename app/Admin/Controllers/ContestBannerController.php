<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\ContestBannerModel;
use App\Models\UserModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ContestBannerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '每日大赛Banner/';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new ContestBannerModel());

        $grid->column('_id', __('ID'));
        $grid->column('target_url', __('跳转地址'));
        $grid->column('remark', __('备注'));
        $grid->column('img_url', __('图片'))->image();
        $grid->column('nature', __('是否为广告'))->display(function ($v) {
            return $v ==1  ? "是" : '否';
        });
        $grid->column('status', __('状态'))->display(function ($v) {
            return $v ? '已启用' : '未启用';
        });

        $grid->column('created_at', __('创建时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $grid->column('updated_at', __('更新时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $grid->column('type', __('类型'));
        $grid->column('weight', __('权重'))->editable();


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
        $show = new Show(ContestBannerModel::findOrFail($id));


        $show->field('_id', __('ID'));
        $show->field('target_url', __('跳转地址'));
        $show->field('remark', __('备注'));
        $show->field('img_url', __('图片'))->image();
        $show->field('status', __('状态'))->display(function ($v) {
            return $v ? '已启用' : '未启用';
        });
        $show->field('created_at', __('创建时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $show->field('updated_at', __('更新时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $show->field('type', __('类型'));
        $show->field('weight', __('权重'))->display(function ($v) {
            return $v ?? '0';
        });


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new ContestBannerModel());

        $form->text('remark', __('备注'))->required();
        $form->image('img_url', __('Banner图上传'))->required();
        $form->text('target_url', __('跳转地址'))->required();

        // 是否为广告
        $form->select('nature', __('是否为广告'))->options([
            1 => '是',
            0 => '否'
        ])->default(0)->required();

        $form->select('type', __('类型'))->options([
            'pc' => 'pc',
            'mobile' => 'mobile'
        ])->required();
        $states = [
            'on'  => ['value' => 1, 'text' => '打开', 'color' => 'primary'],
            'off' => ['value' => 2, 'text' => '关闭', 'color' => 'default'],
        ];
        $form->switch('status', __('是否启用'))->options($states)->default(1)->required();
        $form->number('weight', __('权重'))->default(1)->required();
        return $form;
    }
}
