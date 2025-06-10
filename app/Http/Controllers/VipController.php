<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use App\Models\UserVipModel;
use App\Models\VipLogModel;
use App\Models\VipModel;
use App\Service\VipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// vip 相关
class VipController extends Controller
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



    // 开通vip
    function runVip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vip_id' => 'required',
            'type' => 'required' // 1余额兑换 2 邀请人数兑换
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $type = trim($request->input('type'));

        if (!in_array($type, [1, 2])) {
            return $this->error_('错误请求');
        }

        $res = VipService::getInstance()->runVip(auth()->user()->id, $type, $request->input('vip_id'));
        return $res['status'] ? $this->success($res) : $this->error_($res['msg']);

    }
}
