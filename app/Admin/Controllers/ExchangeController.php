<?php

namespace App\Admin\Controllers;

use App\Models\BannerModel;
use App\Models\ExchangeModel;
use App\Models\UserModel;
use App\Models\VipModel;
use App\Service\HelperService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

// 重提设置
class ExchangeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '充值币种汇率设置/';


    /**
     * Make a grid builder.
     *'name',
     *  'diamond_number', // 兑换所需钻石数量
     *  'share_number', // 邀请人数
     *  'created_at', //  创建时间
     *  'updated_at', // 更新时间
     *  'long_time',  // 有效时长 0 永久  单位天
     *  'weight', // 权重
     *  'remark', // 备注
     *  'bg_img_url', // 背景图
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new ExchangeModel());

        $grid->column('_id', __('ID'));
        $grid->column('name', __('设定名称'));
        $grid->column('nation_code', __('货币类型'));
        $grid->column('diamond_quantity', __('钻石兑换比例'))->editable()->display(function ($v) {
            return '1 比：' . $v . ' 钻';
        });
        $grid->column('status', __('状态'))->display(function ($v) {
            return $v == 1 ? '已启用' : '已禁用';
        });
        $grid->column('created_at', __('创建时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });
        $grid->column('updated_at', __('更新时间'))->display(function ($v) {
            return date('Y-m-d H:i:s', strtotime($v));
        });

        $grid->actions(function ($actions) {

            // 去掉`查看`按钮
            $actions->disableView();
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
        $show = new Show(ExchangeModel::findOrFail($id));


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
     * 'nation_code', // 货币类型
     * 'diamond quantity', // 钻石兑换比例数量 1 ： x
     * 'status', // 状态
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new ExchangeModel());
//        $nations = HelperService::getInstance()->getDir(base_path() . '/lang');
        $nations['DIAMOND'] = '钻石';
        $nations['USDT-TRC'] = 'USDT-TRC';


        $form->text('name', __('设定项名称'))->options($nations);
        $form->select('nation_code', __('货币类型'))->options($nations);
        $form->number('diamond_quantity', __('钻石兑换比例数量'))
            ->help('如果国家选为中国，这里所填写的数量就是当前国家货币1：x 钻石数量 ')->default(10);
        $form->select('status', __('状态'))->options([
            1 => '启用',
            2 => '禁用'
        ])->default(1);

        return $form;
    }
}
