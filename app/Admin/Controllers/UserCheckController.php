<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\UserCheckInModel;
use App\Models\UserModel;
use App\Models\VipModel;
use App\Service\HelperService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserCheckController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '签到管理/';

    /**
     *  'name', // 设置名称 比如 第一天、 第二天
     *  'diamond_quantity', // 钻石数量
     *  'current', // 语种
     *  'status', // 是否启用
     *  'weight', // 权重
     *  'created_at',
     *  'updated_at',
     *  'insert_time', //插入时间
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new UserCheckInModel());
        $grid->column('_id', __('设定ID'));
        $grid->column('name', __('设置名称'));
        $grid->column('diamond_quantity', __('钻石数量'))->editable()->display(function ($v) {
            return '签到可获取：' . $v . ' 钻';
        });
        $grid->column('current', __('语种'));
        $grid->column('status', __('是否已经启用'))->switch();
        $grid->column('bg_img_url', __('背景图'))->image();
        $grid->column('weight', __('排序权重'))->editable();
        $grid->column('created_at', __('创建时间'))->display(function ($v) {
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
        $show = new Show(UserCheckInModel::findOrFail($id));

        $show->field('_id', __('ID'));
        $show->field('name', __('设置名称'));
        $show->field('status', __('是否启用'))->switch();
        $show->field('diamond_quantity', __('签到可获取的钻石数量'));
        $show->field('current', __('语种'));
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
        $form = new Form(new UserCheckInModel());

        // 语种选择
        $current = HelperService::getInstance()->getDir(base_path() . '/lang');
        $form->select('current', '选择语种：')->options($current)->required();
        $form->text('name', __('用户看到的签到项名称：'))->required();
        $form->image('bg_img_url', __('背景图：'))->required();

        $form->number('diamond_quantity', __('签到可获得的钻石数量：'))->required();
        $form->switch('status', __('是否启用设定项'))->default(1)->required();
        $form->number('weight', '排序权重：')->default(1)->required();

        $form->saving(function (Form $form) {

            $form->model()->insert_time = time();

        });
        return $form;
    }
}
