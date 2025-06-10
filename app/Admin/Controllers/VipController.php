<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\UserModel;
use App\Models\VipModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VipController extends AdminController
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
        $grid = new Grid(new VipModel());

        $grid->column('_id', __('ID'));
        $grid->column('name', __('VIP类型名称'));
        $grid->column('diamond_number', __('兑换所需钻石数量'))->editable()->display(function ($v) {
            return $v . '钻';
        });
        $grid->column('share_number', __('邀请人数'))->editable()->display(function ($v) {
            return $v . '人';
        });
        $grid->column('long_time', __('有效时长'))->editable()->display(function ($v) {
            return $v . '天';
        });

        $grid->column('bg_img_url', __('背景图'))->image();
        $grid->column('weight', __('排序权重'))->editable();
        $grid->column('created_at', __('创建时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $grid->column('updated_at', __('更新时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
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
        $show = new Show(VipModel::findOrFail($id));


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
     * Make a form builder.
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new VipModel());

        $form->text('name', __('名称'));
        $form->image('bg_img_url', __('背景图'));
        $form->number('diamond_number', __('兑换所需钻石数量'));
        $form->number('share_number', __('兑换所需邀请人数'));
        $form->number('long_time', __('有效时长（天，如果永久填入0）'));
        $form->number('weight', __('权重'))->default(1);
        $form->text('remark', __('备注'));

        return $form;
    }
}
