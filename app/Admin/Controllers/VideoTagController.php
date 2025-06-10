<?php

namespace App\Admin\Controllers;

use App\Models\BankModel;
use App\Models\UserModel;
use App\Models\VideoNationModel;
use App\Models\VideoTagModel;
use App\Models\VipModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

// 视频标签分类管理
class VideoTagController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '视频标签管理/';

    /**
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new VideoTagModel());
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });


        $grid->column('_id', __('分类ID'));
        $grid->column('type_name', __('分类名称'));
        $grid->column('admin_id', __('分类创建管理员'))->display(function ($v) {
            return Admin::user()->where('id', 1)->first()->name;
        });
        $grid->column('weight', __('排序权重'))->editable();
        $grid->column('status', __('状态'))->editable()->display(function ($v) {
            return $v == 1? '启用' : '未启用';
        });
        $grid->column('created_at', __('创建时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });


        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show|string
     */
    protected function detail(mixed $id)
    {
        return Show::class;
    }

    /**
     * 'type_name', // 分类名称
     * 'weight', // 分类排序权重
     * 'admin_id', // 维护该分类的管理
     * Make a form builder.
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new VideoTagModel());

        $form->text('type_name', __('分类名称'))->required();
        $form->number('weight', __('排序权'))->required()->default(1);

        $form->saving(function (Form $form) {
            $adminInfo = Admin::user();

            // 兑换比例
            $form->model()->admin_id = $adminInfo->id;
        });

        return $form;
    }
}
