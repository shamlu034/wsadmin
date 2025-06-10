<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\UserModel;
use App\Models\UserVipModel;
use App\Models\VipModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserVipController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员用户管理/';

    /**
     * Make a grid builder.
     * 'user_id', // 开通会员的用户
     * 'start_time', // 开通时间
     * 'end_time', // 会员到期时间
     * 'created_at', // 入库时间
     * 'updated_at', // 更新时间
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new UserVipModel());
        $grid->disableActions();
        $grid->disableCreation();

        $grid->column('_id', __('会员ID'));
        $grid->column('user_id', __('开通用户'))->display(function ($v) {
            $userInfo = UserModel::where('id', $v)->first();
            return $userInfo->name ?? $userInfo->email;
        });

        // 是否是永久会员
        $grid->column('type', __('是否是永久会员'))->display(function ($v) {
            return $v == 1 ? '永久会员' : '非永久会员';
        });


        $grid->column('start_time', __('会员生效时间'))->display(function ($v) {
            if ($this->type == 1) {
                return '永久会员';
            }
            return date('Y-m-d H:i:s', $v);
        });
        $grid->column('end_time', __('到期时间'))->display(function ($v) {
            if ($this->type == 1) {
                return '永久会员';
            }
            return date('Y-m-d H:i:s', $v);
        });

        $grid->column('created_at', __('开通时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $grid->column('updated_at', __('更新时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });

        $grid->column('__', __('查看订单记录'))->display(function ($v) {
            $userInfo = UserModel::where('id', $this->user_id)->first();

            return '<a href=/admin/vip_log?&__search__=' . $userInfo->name . '>查看</a>';
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
        $show = new Show(UserVipModel::findOrFail($id));


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
        $form = new Form(new UserVipModel());

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
