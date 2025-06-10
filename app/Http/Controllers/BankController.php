<?php

namespace App\Http\Controllers;

use App\Models\BankModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api', ['except' => ['login']]);
    }


    // 添加银行卡
    function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_holder' => 'required|string',
            'bank_deposit' => 'required|string',
            'bank_card' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $params = array_filter($request->all());

        $userInfo = auth()->user();

        $params['user_id'] = $userInfo->id;
        $params['weight'] = 1;
        $params['status'] = 0;

        // 数据入库
        $bankInfo = BankModel::create($params);

        return $this->success($bankInfo);

    }


    // 银行卡信息修改
    function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $params = array_filter($request->all());
        $params['status'] = 0;

        // 数据修改
        $bankInfo = BankModel::where('_id', trim($request->input('bank_id')))->first();
        if (!$bankInfo) {
            return $this->error_(trans('validation.exists', ['attribute' => 'bank info']));
        }
        BankModel::where('_id', trim($request->input('bank_id')))->update($params);
        $bankInfo = BankModel::where('_id', trim($request->input('bank_id')))->first();
        return $this->success($bankInfo);

    }


    // 获取我的银行卡
    function getBankList(Request $request)
    {
        $userInfo = auth()->user();
        $list = BankModel::where(['user_id' => $userInfo->id])->get();
        return $this->success($list);
    }

    // 删除银行卡
    function deleteBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        BankModel::where(['_id' => trim($request->input('bank_id'))])->delete();
        return $this->success([]);
    }
}
