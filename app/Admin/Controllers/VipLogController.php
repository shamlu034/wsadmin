<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\UserModel;
use App\Models\VipLogModel;
use App\Models\VipModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

// 会员开通记录
class VipLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员开通记录/';

    /**
     * 'user_id', // 开通会员的用户
     * 'vip_id', // 开通会员的类型
     * 'type', // 开通会员的方式 1余额购买 2邀请用户
     * 'created_at', //入库时间
     * 'updated_at', // 更新时间
     * 'insert_time', // 入库时间
     * 'consumption', // 消费 如果是余额消费， 这个值记录钻石， 如果是邀请人数消费， 这个值记录邀请人数
     * 'vip_info', // 当时的会要邀请设置信息
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new VipLogModel());

        $grid->quickSearch(function ($model, $query) {
            $query = trim($query);
            $userIds = UserModel::where('name', 'like', "%{$query}%")->pluck('id');
            $model->whereIn('user_id', $userIds);
        })->placeholder('搜索用户名');


        $grid->column('_id', __('订单ID'));
        $grid->column('user_id', __('开通会员的用户'))->display(function ($v) {
            $userInfo = UserModel::where('id', $v)->first();
            return $userInfo->name ?? $userInfo->email;
        });
        $grid->column('vip_id', __('开通会员的类型'))->display(function ($v) {
            $vipInfo = VipModel::where('_id', $v)->first();
            return $vipInfo->name;
        });
        $grid->column('type', __('开通会员的方式'))->display(function ($v) {
            return $v == 1 ? '余额方式' : '兑换方式';
        });
        $grid->column('consumption', __('开通会员的花销'))->display(function ($v) {

            if ($this->type == 1) {
                return $v . '钻石兑换';
            }
            return '邀请' . $v . '人兑换';
        });
        $grid->column('vip_info', __('开通会员时会员的会员设置信息'))->modal('设置信息', function ($v) {
            $vipInfo = json_decode($v->vip_info, true);
            return new Table([], $vipInfo);
        })->style('width:220px; word-break: break-all');

        $grid->column('created_at', __('开通时间'))->display(function ($v) {
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
        $show = new Show(VipLogModel::findOrFail($id));


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
        $form = new Form(new VipLogModel());

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
