<?php

namespace App\Admin\Controllers;

use App\Models\UserModel;
use App\Service\ChatService;
use App\Service\HelperService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户管理/';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new UserModel());

        $grid->column('id', __('ID'));
        $grid->column('name', __('用户名'));
        $grid->column('is_upload_video', __('视频上传权限'))->switch([
            '1' => '启用',
            '0' => '未启用'
        ]);
        $grid->column('email', __('邮箱'))->display(function ($v) {
            return strpos($v, '@') !== false ? $v : '';

        });
        $grid->column('diamonds', __('用户余额(钻石)'));
        $grid->column('created_at', __('注册时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $grid->column('nick_name', __('昵称'));
        $grid->column('language', __('语言'));
        $grid->column('parent_user_id', __('上级用户ID'))->display(function ($v) {
            if ($v) {
                return UserModel::where('id', $v)->first()->name;
            }
            return '无';
        });
        $grid->column('avatar', __('头像'))->image('', 40, 100);
        $grid->column('card_name', __('真实姓名'));
        $grid->column('card_id_number', __('证件号码'));
        $grid->column('card_status', __('审核状态'))->display(function ($v) {
            switch ($v) {
                case 0:
                    return '未实名';
                case 1:
                    return '待审核';
                case 0:
                    return '审核通过';
                case 0:
                    return '审核被拒';
                default:
                    return '未知状态';
            }
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
        $show = new Show(UserModel::findOrFail($id));

        $show->field('name', __('用户名'));
        $show->field('email', __('邮箱'));
        $show->field('nick_name', __('用户昵称'));
        $show->field('language', __('语言'));
        $show->field('parent_user_id', __('上级id'));
        $show->field('invitation_code', __('邀请码'));
        $show->field('phone_number', __('手机号'));
        $show->field('avatar', __('头像'));
        $show->field('card_name', __('真实姓名'));
        $show->field('card_id_number', __('证件id'));
        $show->field('card_status', __('认证状态'));
        $show->field('introduction', __('个人介绍'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new UserModel());

        $form->text('name', __('用户名'))->required();
        $form->email('email', __('邮箱'))->required();
        $form->text('password', __('密码'))->required();
        $form->text('nick_name', __('昵称'));
        $form->text('language', __('语言'))->default('zh_CN')->required();
        $form->image('avatar', __('头像'))->required();
        $form->switch('is_upload_video', __('是否可上传视频'))->options([
            '1' => '启用',
            '0' => '未启用'
        ]);



        $form->saving(function (Form $form) {
            $form->model()->password = Hash::make(trim($form->password));

            // 创建对话
            $res = ChatService::getInstance()->registerOrLogin(
                null,
                $userInfo->id ?? 0,
                1,
                1,
            );

            $form->model()->chat_id = $res['data']['chatInfo']->id;

            $form->model()->invitation_code = HelperService::getInstance()->GetRandStr(5);
        });

        return $form;
    }
}
