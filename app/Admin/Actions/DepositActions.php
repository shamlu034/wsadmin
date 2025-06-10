<?php

namespace App\Admin\Actions;

use App\Models\ExchangeModel;
use App\Models\UserModel;
use App\Models\WalletModel;
use Encore\Admin\Actions\Action;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use MongoDB\Laravel\Eloquent\Model;

class DepositActions extends Action
{
    public $name = '充提管理->用户充值:';

    protected $selector = '.report-posts';

    public function handle(Collection $collection, Request $request)
    {
        foreach ($collection as $model) {
            //
            dd($model);
        }

        return $this->response()->success('举报已提交！')->refresh();
    }

    public function form(): void
    {

        // 用户信息选择
        $userList = UserModel::where([])->select(['id', 'name', 'email'])->get();
        $us = [];
        foreach ($userList as $userInfo) {
            $us[$userInfo->id] = $userInfo->name ?? $userInfo->email;
        }
        $this->select('user_id', __('选择操作用户:'))->options($us);

        // 币种选择
        $exchangeRateList = ExchangeModel::where(['status' => '1'])->select(['id', 'nation_code'])->get();
        $exchangeTypes = [];
        foreach ($exchangeRateList as $v) {
            $exchangeTypes[$v->id] = $v->nation_code;
        }
        $this->select('exchange_rate_id', __('选择币种:'))->options($exchangeTypes);

        $this->integer('recharge_amount', __('充值金额:'))->default(0);
        $this->text('recharge_address', __('充值地址:'));
        $this->image('payment_screenshot', __('充值截图:'));
        $this->text('remark', __('订单备注:'));

    }

    public function html()
    {
        return "<a class='report-posts btn btn-sm import-post' style='border: 1px solid' >
        <i class='fa fa-upload'></i>
        充值</a>";
    }

}
