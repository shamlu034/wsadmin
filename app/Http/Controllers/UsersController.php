<?php

namespace App\Http\Controllers;

use App\Models\EmailCodeModel;
use App\Models\UserCardModel;
use App\Models\UserEntionModel;
use App\Models\UserModel;
use App\Models\VideoModel;
use App\Service\UsersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\Models\User;


// 用户操作
class UsersController extends Controller
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


    // 绑定邮箱
    function boundEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users|max:255|email',
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 验证code
        $ecode = EmailCodeModel::where('email', $request->input('email'))
            ->orderBy('end_time', 'desc')
            ->first();
        if (!$ecode || $request->input('code') != $ecode->v_code) {
            return $this->error_(trans('validation.public_error', ['attribute' => trans('validation.code')]));
        }

        //  获取用户信息
        $userInfo = auth()->user();
        UserModel::where('id', $userInfo->id)
            ->update(['email' => trim($request->input('email'))]);

        $userInfo->email = trim($request->input('email'));
        return $this->success($userInfo);
    }


    // 关注用户
    function attentionUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $res = UsersService::getInstance()->attentionUser($request->input('id'));
        return $res['status'] ? $this->success($res) : $this->error_($res['msg']);
    }

    // 取关
    function cancelAttention(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $res = UsersService::getInstance()->cancelAttention($request->input('id'));
        return $res['status'] ? $this->success($res) : $this->error_($res['msg']);
    }


    // 实名认证 认证状态0未认证 1 待审核 2审核通过 3 审核被拒
    //          'card_name', // 真实姓名
    //        'card_id_number', // 证件号码
    //        'created_at', //  创建时间
    //        'updated_at', // 更新时间
    //        'insert_time',  //  入库时间
    //        'card_status', // 证件审核状态
    //        'img1', // 证件照片地址
    //        'img2', // 证件照片地址2
    //        'img4', // 证件照片地址2
    //        'img4', // 证件照片地址
    function cardIdVerified(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_name' => 'required|string',
            'card_id_number' => 'required|string',
            'img1' => 'required',
            'img2' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 用户信息
        $userInfo = auth()->user();

        // 检查是否已经提交认证信息
        $cardInfo = UserCardModel::where('user_id', $userInfo->id)->first();

        if ($cardInfo && $cardInfo->card_status != 3) {
            return $this->error_('您已提交审核资料， 请勿重复提交！');
        }

        // 如果不存在， 数据入库
        if (!$cardInfo) {
            // 数据入库
            $cardInfo = UserCardModel::create([
                'user_id' => $userInfo->id,
                'card_name' => trim($request->input('card_name')), // 真实姓名
                'card_id_number' => trim($request->input('card_name')), // 证件号码
                'insert_time' => time(),  //  入库时间
                'card_status' => 1, // 证件审核状态
                'img1' => trim($request->input('img1')), // 证件照片地址
                'img2' => trim($request->input('img2')), // 证件照片地址2
                'img3' => trim($request->input('img3')), // 证件照片地址2
                'img4' => trim($request->input('img4')), // 证件照片地址
            ]);
        } else {
            // 这里是存在并且等于3的情况, 更新审核状态
            UserCardModel::where('user_id', $userInfo->id)->update([
                'card_name' => trim($request->input('card_name')), // 真实姓名
                'card_id_number' => trim($request->input('card_name')), // 证件号码
                'card_status' => 1, // 证件审核状态
                'img1' => trim($request->input('img1')), // 证件照片地址
                'img2' => trim($request->input('img2')), // 证件照片地址2
                'img3' => trim($request->input('img3')), // 证件照片地址2
                'img4' => trim($request->input('img4')), // 证件照片地址
            ]);
        }


        return $this->success($cardInfo);
    }

    // 获取我的关注列表
    function getMyWatchlist(Request $request, $type = 1)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required|integer',
            'limit' => 'required|integer',
            'sort' => 'integer'
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 请求类型
        $sort = trim($request->input('sort'));

        $userInfo = auth()->user();
        $page = ($request->input('page') - 1) * trim($request->input('limit'));

        $where = $type == 1 ?
            [
                ['user_id', '=', $userInfo->id]
            ] :
            [
                ['target_user_id', '=', $userInfo->id]
            ];

        // 最新关注
        $list = UserEntionModel::where($where)
            ->orderByDesc('attention_time')
            ->offset($page)
            ->limit(trim($request->input('limit')))
            ->get();

        // 最早关注
        if ($sort == 2) {
            $list = UserEntionModel::where($where)
                ->orderBy('attention_time', 'asc')
                ->offset($page)
                ->limit(trim($request->input('limit')))
                ->get();
        }

        // 粉丝最多
        if ($sort == 3) {
            $list = UserEntionModel::where($where)
                ->orderByDesc('user_info.fans_count')
                ->offset($page)
                ->limit(trim($request->input('limit')))
                ->get();
        }

        return $this->success($list);
    }


    // 获取关注我的
    function getWatchMylist(Request $request)
    {
        return $this->getMyWatchlist($request, 2);
    }

    // 修改会员资料
    function updateUserInfo(Request $request)
    {
        $data = [
            'name' => trim($request->input('name')), // 用户名
            'nick_name' => trim($request->input('nick_name')),// 用户昵称
            'language' => trim($request->input('language')), // 语种
            'phone_number' => trim($request->input('phone_number')), // 手机号
            'avatar' => trim($request->input('avatar')), // 头像
            'introduction' => trim($request->input('introduction')), // 个人介绍
        ];

        $data = array_filter($data);

        // 用户资料更新
        $userInfo = auth()->user();
        UserModel::where('id', $userInfo->id)->update($data);
        return $this->success(UserModel::where('id', $userInfo->id)->first());
    }


}
