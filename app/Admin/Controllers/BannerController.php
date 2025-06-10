<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\UserModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '二维码消息生成管理/';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new BannerModel());

        $grid->column('_id', __('ID'));
        $grid->column('target_url', __('生成消息'))->display(function() {
            $imgUrl = 'https://' . request()->getHost() . '/storage/' . $this->img_url;
            $str = '<img  style="width: 100%" src='.
            $imgUrl
            . ' alt="Message Image" class="message-image">';
            
           
            return htmlspecialchars($str);
        });
        $grid->column('remark', __('备注'));
        $grid->column('img_url', __('图片'))->image();

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
        $show = new Show(BannerModel::findOrFail($id));


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
        $form = new Form(new BannerModel());

        $form->text('remark', __('备注'));
        $form->image('img_url', __('图片上传'));
        // $form->text('target_url', __('跳转地址'));
        // $form->select('type', __('类型'))->options([
        //     'pc' => 'pc',
        //     'mobile' => 'mobile'
        // ]);
        // $states = [
        //     'on'  => ['value' => 1, 'text' => '打开', 'color' => 'primary'],
        //     'off' => ['value' => 2, 'text' => '关闭', 'color' => 'default'],
        // ];
        // $form->switch('status', __('是否启用'))->options($states);
        // $form->number('weight', __('权重'))->default(1);
        return $form;
    }
}
