<?php

namespace App\Http\Controllers;

// 签到
use App\Models\CheckInLogModel;
use App\Models\UserCheckInModel;
use App\Service\CheckInService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckInController extends Controller
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

    // 获取签到列表
    function getCheckInList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 用户信息
        $userInfo = auth()->user();

        // 签到列表
        $list = UserCheckInModel::where('current', trim($request->input('current')))
            ->orderBy('weight', 'asc')
            ->get();

        // 本周一
        $thisWeekTime = strtotime('this week Monday', time());

        // 下周一
        $nextWeekTime = strtotime('monday');
        foreach ($list as $item) {
            $where = [
                'user_id' => $userInfo->id,
                'check_in_id' => $item->id,
            ];
            $item->isCheckIn = (bool)CheckInLogModel::where($where)
                ->whereBetween('insert_time', [$thisWeekTime, $nextWeekTime])
                ->first();
        }


        // 连续签到
        $continuousCheckIns = CheckInService::getInstance()->continuousCheckIns($userInfo->id);

        return $this->success(
            [
                'list' => $list,
                'address' => $request->host() . '/storage',
                'continuousCheckIns' => $continuousCheckIns, // 连续签到天数
            ]
        );
    }


    // 签到
    function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check_in_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 用户信息
        $userInfo = auth()->user();
        $res = CheckInService::getInstance()->conductCheckIn($request->input('check_in_id'), $userInfo->id);

        // 返回请求结果
        return $res['status'] ? $this->success($res) : $this->error_($res['msg']);
    }

}
