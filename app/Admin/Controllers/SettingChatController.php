<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\ChatModel;
use App\Models\UserModel;
use App\Models\VipModel;
use App\Service\ChatService;
use App\Service\HelperService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

// 客服设置
class SettingChatController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客服管理/';

    /**
     * Make a grid builder.
     * 'uid',
     * 'name' // 客服名称
     * 'token', // 用户token
     * 'device_flag',
     * 'device_level',
     * 'status',
     * 'created_at',
     * 'updated_at',
     * 'end_time',
     * 'type', // 对话类型 1 客服 2用户
     * language // 语种
     * first_text // 招呼语
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid((new ChatModel()));
        $grid->model()->where('type', 1);

        $adminId = Admin::user()->id;
        if ($adminId != 1 && $adminId) {
            $grid->model()->where('admin_id', (string)Admin::user()->id);
        }

        // 去掉`查看`按钮
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();

        });

        $grid->column('_id', __('对话ID'));
        $grid->column('language', __('语种'))->display(function ($v) {

            return HelperService::getInstance()->getLang()[$v] ?? "";
        });
        $adminList = Administrator::all()->pluck("username", "id");
        $grid->column('name', __('客服名称'))->editable();
        $grid->column('first_img', __('第一句自动回复的图片'))->image();
        $grid->column('second_img', __('第二句自动回复的安卓图片'))->image();
        $grid->column('second_ios_img', __('第二句自动回复的ios图片'))->image();

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
        $show = new Show(ChatModel::findOrFail($id));


        return $show;
    }

    /**
     * Make a form builder.
     * 'name', // 用户名
     * 'uid', // 对话uid
     * 'token', // 用户token
     * 'device_flag',
     * 'device_level',
     * 'status',
     * 'created_at',
     * 'updated_at',
     * 'end_time',
     * 'type', // 对话类型 1 客服 2用户
     * 'language',  // 语种
     * 'first_text', // 招呼语
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new ChatModel());

        $form->text('name', __('客服名称'))->required();
        $adminList = Administrator::all()->pluck("username", "id");
        $form->select('admin_id', __('指派所属'))->options($adminList);
        // 语种选择
        $form->select('language', '选择语种：')->options(HelperService::getInstance()->getLang())->required();

        $form->image('first_img', __('自动回复第一个图片'))->required();

        $form->textarea('first_text', __('自动回复第一个短语'))->required();

        $form->image('second_img', __('第二句话术的安卓图片'))->required();
        $form->image('second_ios_img', __('第二句话术的IOS图片'))->required();

        $form->textarea('second_text', __('自动回复第二个短语'))->required();


        $form->textarea('three_text', __('输入手机号后第一个回复短语'))->required();
        $form->textarea('code_text', __('验证码输出短语'))->required();
        $form->textarea('logout_text', __('退出登陆时输出的短语'))->required();
        $form->textarea('login_success_text', __('登陆成功时输出短语'))->required();
        $form->textarea('error_text', __('输入的手机号不正确时的提示'))->required();

        // 是否启用
        $form->select('status', __('状态'))->options([
            1 => '启用',
            2 => '禁用'
        ])->default(1);

        $form->saving(function (Form $form) {

            // uid
            if (!$form->model()->uid) {
                $form->model()->uid = ChatService::getInstance()->generateUid();
            }

            // token
            if (!$form->model()->token) {
                $form->model()->token = ChatService::getInstance()->generateToken();
            }

            $form->model()->end_time = time();
            $form->model()->device_flag = 1;
            $form->model()->device_level = 1;
            $form->model()->type = 1;

        });
        return $form;
    }


}
