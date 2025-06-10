<?php

namespace App\Admin\Controllers;

use App\Models\ChatModel;
use App\Models\CustomerModel;
use App\Service\ChatService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

// 客服设置
class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客服列表/';

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
        $grid = new Grid((new CustomerModel()));

        // 按照登陆的用户id 进行过滤
        $adminId = Admin::user()->id;
        if ($adminId != 1 && $adminId) {
            $grid->model()
            ->where('admin_id', Admin::user()->id)
            ->orWhere('admin_id', (string)Admin::user()->id);
        }

         $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('chat_id', '对话模板');


        });

        // 去掉`查看`按钮
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
             $actions->disableEdit();
        });
        $grid->column('uid', __('uid'));
        $grid->column('token', __('Token'));
        $grid->column('_id', __('对话ID'));
        $grid->column('chat_id', __('对话模板'))->display(function ($v) {
            $chatInfo = ChatModel::where('_id', $v)->first();
            return $chatInfo ? $chatInfo ->name : "";
        });

        $adminList = Administrator::all()->pluck("username", "id");
        $grid->column('admin_id', __('客服所属员工'))->editable('select', $adminList);

        //查看对话
        $grid->column('__', __('进入客服'))->display(function ($v) {
            $chatInfo = ChatModel::where('_id', $this->chat_id)->first();
            $str = '/#/admin?chat_id=' . $this->id. '&language=' . $chatInfo->language;
            return '<a target="_blank" href=' . $str . '>查看</a>';
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
     * @return Show
     */
    protected function detail(mixed $id): Show
    {
        $show = new Show(CustomerModel::findOrFail($id));


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
        $form = new Form(new CustomerModel());

        $adminList = Administrator::all()->pluck("username", "id");
        $form->select('admin_id', __('指派给员工'))->options($adminList)->required();

        // 语种选择
        $langList = ChatModel::where('type', 1)->pluck("name", "_id");
        $form->select('chat_id[]', '选择语种模板：')->options($langList)->attribute('multiple', 'true')->required();

        $form->saving(function (Form $form) {


            $chatIds = array_filter(array_unique($form->chat_id));
            foreach ($chatIds as $chat_id) {
                // $form->model()->uid = ;
                // $form->model()->token = ;
                // $form->model()-> = ;
                $arr = [
                    'uid' => ChatService::getInstance()->generateUid(),
                    'token' => ChatService::getInstance()->generateToken(),
                    'chat_id' => $chat_id,
                    'admin_id' => $form->admin_id,
                    'created_at' => time(),
                     'updated_at' => time(),
                ];

                CustomerModel::insert($arr);

            }

           return redirect()->to('/admin/customer');
        });
        return $form;
    }


}
