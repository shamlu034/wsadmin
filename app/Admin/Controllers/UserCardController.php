<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\UserCardModel;
use App\Models\UserModel;
use App\Models\VipModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserCardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '实名认证审核/';

    /**
     * Make a grid builder.
     * 'user_id', // 用户id
     * 'card_name', // 真实姓名
     * 'card_id_number', // 证件号码
     * 'created_at', //  创建时间
     * 'updated_at', // 更新时间
     * 'insert_time',  //  入库时间
     * 'card_status', // 证件审核状态
     * 'img1', // 证件照片地址
     * 'img2', // 证件照片地址2
     * 'img4', // 证件照片地址2
     * 'img4', // 证件照片地址2
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new UserCardModel());

        $grid->column('_id', __('认证ID'));
        $grid->column('user_id', __('认证用户'))->display(function ($v) {
            return UserModel::where('id', $v)->first()->name;
        });
        $grid->column('card_name', __('真实姓名'));
        $grid->column('card_id_number', __('证件号码'));
        $grid->column('card_status', __('审核状态'))->display(function ($v) {
            $arr = [
                1 => '待审核',
                2 => '审核通过',
                3 => '审核拒绝通过'
            ];
            return $arr[$v];
        });

        $grid->column('img1', __('证件照1'))->image('', 150);
        $grid->column('img2', __('证件照2'))->image('', 150);
        $grid->column('img3', __('证件照3'))->image('', 150);
        $grid->column('img3', __('证件照4'))->image('', 150);
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
        $show = new Show(UserCardModel::findOrFail($id));

        $show->field('_id', __('认证ID'));
        $show->field('user_id', __('认证用户'))->as(function ($v) {
            $userInfo = UserModel::where('id', $v)->first();
            return $userInfo->name;
        });
        $show->field('card_name', __('真实姓名'));
        $show->field('card_id_number', __('证件号码'));
        $show->field('card_status', __('审核状态'))->as(function ($v) {
            $arr = [
                1 => '待审核',
                2 => '审核通过',
                3 => '审核拒绝通过'
            ];
            return $arr[$v];
        });

        $show->field('img1', __('证件照1'))->image();
        $show->field('img2', __('证件照2'))->image();
        $show->field('img3', __('证件照3'))->image();
        $show->field('img3', __('证件照4'))->image();
        $show->field('created_at', __('创建时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $show->field('updated_at', __('更新时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });


        return $show;
    }

    /**
     * Make a form builder.
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new UserCardModel());

        $userInfos = UserModel::where([])->pluck('name', 'id');

        $form->select('user_id', __('认证用户'))->options($userInfos->toArray());
        $form->text('card_name', __('真实姓名'));
        $form->text('card_id_number', __('证件号码'));
        $form->select('card_status', __('审核状态'))->options([
            1 => '待审核',
            2 => '审核通过',
            3 => '审核拒绝通过'
        ]);

        $form->image('img1', __('证件照1'));
        $form->image('img2', __('证件照2'));
        $form->image('img3', __('证件照3'));
        $form->image('img3', __('证件照4'));


        return $form;
    }
}
