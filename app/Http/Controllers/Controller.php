<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\MessageBag;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    function __construct()
    {
        if ($lang = request()->input('language')) {
            App::setLocale($lang);
        }
    }


    // 验证不通过的错误信息返回
    function validationError(MessageBag $params): JsonResponse
    {
        // 提取key
        $keys = array_keys($params->toArray());

        // 错误消息
        $errorMessage = $params->first();


        $data = [
            'code' => 400,
            'error_field' => current($keys),
            'msg' => $errorMessage,
            'data' => $params,
        ];

        return response()->json($data);
    }

    // 错误信息返回
    function error_($msg, $field = '')
    {
        $data = [
            'code' => 400,
            'error_field' => $field,
            'msg' => str_replace(' ', '', $msg),
            'data' => [],
        ];

        return response()->json($data);
    }

    // 成功
    function success($data_, $msg = ''): JsonResponse
    {
        $data = [
            'code' => 200,
            'error_field' => 'success',
            'msg' => str_replace(' ', '', $msg),
            'data' => $data_,
        ];

        return response()->json($data);
    }

}
