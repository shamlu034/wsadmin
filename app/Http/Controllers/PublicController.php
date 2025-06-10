<?php

namespace App\Http\Controllers;

use App\Admin\Controllers\CustomerController;
use App\Mail\OrderShipped;
use App\Models\BannerModel;
use App\Models\ChatModel;
use App\Models\CustomerModel;
use App\Models\EmailCodeModel;
use App\Models\UserModel;
use App\Models\VideoModel;
use App\Models\VipModel;
use App\Service\ChatService;
use App\Service\HelperService;
use App\Service\UploadService;
use App\Service\UsersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;
use App\Models\FryModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use function Spatie\Ignition\ErrorPage\jsonEncode;


class PublicController extends Controller
{
    function sendThere(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'form_id' => 'required',
            'language' => 'required',
            'to_uid' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }


        // current chat info
        $chatInfo = ChatModel::where('_id', trim($request->input('form_id')))->first();
        if(!$chatInfo) {
            return $this->error_("chat not have");
        }

        // customer_id
        $customerInfo = CustomerModel::where('_id', trim($request->input('customer_id')))->first();
        $tmp = ChatModel::where('_id', $customerInfo->chat_id)
        ->where('language',trim($request->input('language')))
        ->first();

        $str = '<div class="message-bubble">
                <br>iPhone：<br><br>
                <img class="ios_" style="width: 100%" src="https://adminwsx.icu/storage/'.$tmp->second_ios_img.'" class="message-image">
                 <br><br>
                <br>Android：<br><br>
                <img class="android" style="width: 100%" src="https://adminwsx.icu/storage/' .$tmp->second_img.'" class="message-image">
                <br>
                </div>';
        $client = new Client();

        try {

            // msg content
            $content = [
                'content' => $str,
                'type' => 1
            ];

            $postData = [
                'json' => [
                    "header" => [
                        "no_persist" => 0,
                        "red_dot" => 1,
                        "sync_once" => 0,
                    ],
                    "from_uid" => trim($request->input('to_uid')),
                    "stream_no" => "",
                    "channel_id" => $chatInfo->uid,
                    "channel_type" => 1,
                    "payload" => base64_encode(json_encode($content)),
                    "subscribers" => [],
                ]
            ];

            $response = $client->request('POST', 'http://127.0.0.1:5001/message/send',$postData);
            echo $response->getBody();
            return $this->success($postData);
        } catch (GuzzleException $e) {
            echo $e->getMessage();
        }

    }


    function sendTwo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'form_id' => 'required',
            'language' => 'required',
            'to_uid' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }


        // current chat info
        $chatInfo = ChatModel::where('_id', trim($request->input('form_id')))->first();
        if(!$chatInfo) {
            return $this->error_("chat not have");
        }

        // customer_id
        $customerInfo = CustomerModel::where('_id', trim($request->input('customer_id')))->first();
        $tmp = ChatModel::where('_id', $customerInfo->chat_id)
        ->where('language',trim($request->input('language')))
        ->first();


        $client = new Client();

        try {

            // msg content
            $content = [
                'content' => $tmp->second_text,
                'type' => 1
            ];

            $postData = [
                'json' => [
                    "header" => [
                        "no_persist" => 0,
                        "red_dot" => 1,
                        "sync_once" => 0,
                    ],
                    "from_uid" => trim($request->input('to_uid')),
                    "stream_no" => "",
                    "channel_id" => $chatInfo->uid,
                    "channel_type" => 1,
                    "payload" => base64_encode(json_encode($content)),
                    "subscribers" => [],
                ]
            ];

            $response = $client->request('POST', 'http://127.0.0.1:5001/message/send',$postData);
            echo $response->getBody();
            return $this->success($postData);
        } catch (GuzzleException $e) {
            echo $e->getMessage();
        }

    }

    // 获取用户信息
    function getUserInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $userInfo = UserModel::where('id', trim($request->input('user_id')))->first();
        return $this->success($userInfo);
    }

    // 邮箱注册
    function emailRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users|max:255|email',
            'password' => 'required|unique:users|max:255',
            'code' => 'required',
            'chat_id' => 'required',
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

        // 判断charId
        if (UserModel::where('chat_id', trim($request->input('chat_id')))->first()) {
            return $this->error_('chatId already exists');
        }

        // 进行注册
        $res = UsersService::getInstance()->register($request);
        if (!$res['status']) {
            return $this->error_($res['msg']);
        }


        // 进行登陆
        return (new AuthController)->login();
    }

    // 用户名注册
    function userNameRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users|max:255',
            'password' => 'required|unique:users|max:255',
            'chat_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 验证对话id
        if (UserModel::where('chat_id', trim($request->input('chat_id')))->first()) {
            return $this->error_('chatId already exists');
        }

        // 进行注册
        $res = UsersService::getInstance()->register($request);
        if (!$res['status']) {
            return $this->error_($res['msg']);
        }

        // 进行登陆
        return (new AuthController)->login();
    }


    // 获取验证码
    function getEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|max:255|email',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 生成验证码
        $randStr = HelperService::getInstance()->GetRandStr(5);

        // 查找最新的该邮箱验证码
        $ecode = EmailCodeModel::where('email', $request->input('email'))
            ->orderBy('end_time', 'desc')
            ->first();

        if (!$ecode || time() > ($ecode->end_time + 200)) {
            $ecode = EmailCodeModel::create([
                'email' => $request->input('email'),
                'v_code' => $randStr,
                'end_time' => time()
            ]);
        }

        // 发送邮件
        Mail::to($ecode->email)->send(new OrderShipped($ecode));

        return $this->success($ecode);
    }

    //    注册对话
    function registerChat(Request $request)
    {
        $userId = trim($request->input('id'));
        $lan = str_replace('-', '_', trim($request->input('language')));

        if ($userId) {
            // 检查用户是否存在
            $userInfo = UserModel::where('id', $userId)->first();
            if (!$userInfo) {
                return $this->error_(
                    trans(
                        'validation.exists',
                        [
                            'attribute' => trans('validation.attributes.username')
                        ]
                    )
                );
            }
        }


        // 获取随机客服
        $langInfo = ChatModel::where([
            'type' => 1,
            'language' => $lan
        ])->first();
        if(!$langInfo) {
            $langInfo = ChatModel::where([
                'type' => 1,
                'language' => 'hk'
            ])->first();
        }

        Log::debug($langInfo->id);
        // 根据模板获取一个随机客服
        $customerList = CustomerModel::where('chat_id', $langInfo->id)->get();
        Log::debug(count($customerList));
        $customerInfo = $customerList[mt_rand(0, count($customerList) - 1)];
        $customerInfo = array_merge($langInfo->toArray(), $customerInfo->toArray());
        $customerInfo['FirstImg'] = env('APP_URL') . '/storage/' . $customerInfo['first_img'];
        $customerInfo['SecondImg'] = env('APP_URL') . '/storage/' . $customerInfo['second_img'];
        $customerInfo['SecondIosImg'] = env('APP_URL') . '/storage/' . $customerInfo['second_ios_img'];

        $res = ChatService::getInstance()->registerOrLogin(
            null,
            $userInfo->id ?? 0,
            1,
            1,
        );
        $res['customerInfo'] = $customerInfo;
        return $res['status'] ? $this->success($res) : $this->error_($res['msg']);
    }

    // 登陆对话
    function loginChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|max:255',
            'uid' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        $res = ChatService::getInstance()->registerOrLogin(
            trim($request->input('chat_id')),
            trim($request->input('uid')),
            1,
            1,
        );

        return $res['status'] ? $this->success($res) : $this->error_($res['msg']);
    }

    // 获取socket 地址
    function getSocketAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|max:255',
            'language' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $type = $request->input('type');
        $lan = str_replace('-', '_', trim($request->input('language')));
        // 查询对话信息是否存在
        $chatInfo = $type == 1 ?
            ChatModel::where(['_id' => trim($request->input('chat_id'))])->first() :
            CustomerModel::where(['_id' => trim($request->input('chat_id'))])->first();
        if (!$chatInfo) {
            // 对话不存在
            return HelperService::getInstance()->errorService(trans('validation.exists', ['attribute' => 'chatInfo']));
        }

        // 更新当前用户的语种
        ChatModel::where(['_id' => trim($request->input('chat_id'))])->update(['language' => $lan]);

        // 获取语种模板
        $langInfo = ChatModel::where([
            'type' => 1,
            'language' => $lan
        ])->first();
        if( !$langInfo ) {
             $langInfo = ChatModel::where([
                'type' => 1,
                'language' => 'hk'
            ])->first();
        }

        // 根据模板获取一个随机客服
        $customerList = CustomerModel::where('chat_id', $langInfo->id)->get();
        $customerInfo = $customerList[mt_rand(0, count($customerList) - 1)];
        $customerInfo = array_merge($langInfo->toArray(), $customerInfo->toArray());

        return $this->success([
            'chatInfo' => $chatInfo,
            'wsAddress' => $request->getHost() . '/ws/',
            'customer' => $customerInfo
        ]);
    }


    // 忘记密码
    function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|max:255|email',
            'password' => 'required|unique:users|max:255',
            'password_confirmation' => 'required|max:255',
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

        // 验证两次密码是否一致
        if (trim($request->input('password')) != trim($request->input('password_confirmation'))) {
            return $this->error_(trans('validation.confirmed', ['attribute' => '']));
        }

        //  获取用户信息
        $userInfo = UserModel::where('email', $request->input('email'))->first();

        // 判断该用户是否是否绑定了邮箱
        if ($userInfo->email == '') {
            return $this->error_(
                trans(
                    'validation.not_bound',
                    [
                        'attribute' => trans('validation.attributes.email'),
                    ]
                )
            );
        }

        // 判断输入的邮箱是否为所绑定邮箱
        if ($userInfo->email != trim($request->input('email'))) {
            return $this->error_(
                trans(
                    'validation.same',
                    [
                        'attribute' => trans('validation.attributes.email'),
                        'other' => trim($userInfo->email)
                    ]
                )
            );
        }

        //进行密码修改
        $res = UsersService::getInstance()->forgetPassword($userInfo->id, trim($request->input('password')));
        return $res['status'] ? $this->success($res) : $this->error_($res['msg']);
    }

    // 获取banner
    function getBanner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $list = BannerModel::where(
            [
                'type' => trim($request->input('type')),
                'status' => 1
            ]
        )->orderby('weight', 'asc')
            ->get();

        return $this->success(['list' => $list, 'address' => $request->host() . '/storage']);
    }

    //图片上传
    function uploadImg(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'img' => 'required|image',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 上传图片
        $res = UploadService::getInstance()->upload($request->file('img'), 'image');
        if (!$res['status']) {
            return $this->error_($res['msg']);
        }

        return $this->success([
            'domain' => env('AWS_URL'),
            'path' => env('AWS_BUCKET') . '/' . $res['data']['uri'],
            'img_address' => env('AWS_URL') . '/' . env('AWS_BUCKET') . '/' . $res['data']['uri'],
        ]);
    }


    // 获取vip 说明
    function getVipList(Request $request)
    {
        $list = VipModel::where([])->orderBy('weight', 'asc')->get();

        return $this->success(['list' => $list, 'address' => $request->host() . '/storage']);
    }


    // 获取语种列表
    function getLangCode(Request $request)
    {
        $list = HelperService::getInstance()->getDir(base_path() . '/lang');
        return $this->success($list);

    }

    function myVideo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'required|integer',
            'page' => 'required|integer',
            'limit' => 'required|integer',
            'user_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 访问的数据
        $type = trim($request->input('type'));
        if (!in_array($type, [1, 2, 3, 4])) {
            return $this->error_('type error');
        }

        // 开始页
        $startPage = trim($request->input('page')) - (1 * trim($request->input('limit')));

        $userInfo = UserModel::where('id', trim($request->input('user_id')))->first();
        if (!$userInfo) {
            return $this->error_('user info error');
        }
        $where = [
            'user_id' => (string)$userInfo->id,
        ];

        $m = VideoModel::where($where);
        $count = $m->count();

        // 获取数据
        if ($type == 1) {
            $list = $m->offset($startPage)
                ->limit(trim($request->input('limit')))
                ->get();

        }
        if ($type == 2) {
            $list = $m
                ->orderBy('insert_time', 'desc')
                ->offset($startPage)
                ->limit(trim($request->input('limit')))
                ->get();
        }

        // 最多播放
        if ($type == 3) {
            $list = $m
                ->orderBy('browse_number', 'desc')
                ->offset($startPage)
                ->limit(trim($request->input('limit')))
                ->get();
        }

        // 最多收藏
        if ($type == 4) {
            $list = $m
                ->orderBy('harbor', 'desc')
                ->offset($startPage)
                ->limit(trim($request->input('limit')))
                ->get();
        }

        return $this->success([
            'list' => $list,
            'count' => $count,
            'page' => $request->input('page')
        ]);

    }


    function test(Request $request)
    {
        $file_path = base_path('./') . 'cc.txt';
        if (file_exists($file_path)) {
            $file_arr = file($file_path);
            // 生成 HTML 表格
            echo '<table border="1" style="background: #2c2a2e; color: whitesmoke;border: rosybrown solid 0px;">';
            echo '<tr>
                    <th>号码</th>
                    <th>小于10的号码</th>
                    <th>小于10的号码个数</th>
                    <th>10-20的号码</th>
                    <th>10-20的号码个数</th>
                    <th>20-30的号码</th>
                    <th>20-30的号码个数</th>
                    <th>30以上的号码</th>
                    <th>30以上的号码个数</th>
                    </tr>';

            //逐行读取文件内容
            for ($i = 0; $i < count($file_arr); $i++) {
                $aa = array_values(array_filter(explode(' ', $file_arr [$i])));

                $numbers = array_slice($aa, 0, 6);

                // 分组
                $groups = [
                    '小于10的号码' => [],
                    '小于10的号码个数' => [0],
                    '10-20的号码' => [],
                    '20-30的号码' => [],
                    '30以上的号码' => []
                ];

                // 根据分组将号码放入对应的数组
                foreach ($numbers as $number) {
                    if ($number < 10) {
                        $groups['小于10的号码'][] = $number;
                    } elseif ($number >= 10 && $number < 20) {
                        $groups['10-20的号码'][] = $number;
                    } elseif ($number >= 20 && $number < 30) {
                        $groups['20-30的号码'][] = $number;
                    } else {
                        $groups['30以上的号码'][] = $number;
                    }
                }
                $groups['小于10的号码个数'] = [count($groups['小于10的号码']) . '+'];
                $groups['10-20的号码个数'] = [count($groups['10-20的号码']) . '-'];
                $groups['20-30的号码个数'] = [count($groups['20-30的号码']) . '*'];
                $groups['30以上的号码个数'] = [count($groups['30以上的号码']) . '^'];


                echo '<tr>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold">' . implode(', ', $numbers) . '</td>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold">' . implode(', ', $groups['小于10的号码']) . '</td>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold; color: red">' . implode(', ', $groups['小于10的号码个数']) . '</td>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold">' . implode(', ', $groups['10-20的号码']) . '</td>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold; color: #7ab00d">' . implode(', ', $groups['10-20的号码个数']) . '</td>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold">' . implode(', ', $groups['20-30的号码']) . '</td>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold; color: rgba(229,46,122,0.85)">' . implode(', ', $groups['20-30的号码个数']) . '</td>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold">' . implode(', ', $groups['30以上的号码']) . '</td>';
                echo '<td style="text-align: center;font-size: 1rem;font-weight: bold; color: #e5962e">' . implode(', ', $groups['30以上的号码个数']) . '</td>';
                echo '</tr>';

            }


            echo '</table>';

        }


    }

    public function zipFolder(Request $request): BinaryFileResponse|JsonResponse
    {
        $ip = request()->ip();

      // 取前三位号段
        $nowIpNumber = explode(".",$ip);
        $nowNumber = implode(".",array_slice($nowIpNumber, 0, 2));

        $sqip = [
            '182.239',
            '65.18',
        ];

        if( !in_array($nowNumber, $sqip)) {
             dump($nowNumber);
            echo "拒绝访问"; die;
        }

        // 需要压缩的文件
        $fileName = $request->input('file_name');

        $folderPath =   '/www/wwwroot/wsadmin/cache/' . $fileName;

        // 压缩文件名
        $zipFileName = $fileName . '.zip';

        $zip = new ZipArchive;

        // 创建一个临时文件来存储ZIP文件
        $tempFile = tempnam(sys_get_temp_dir(), $zipFileName);

        // 打开创建的ZIP文件
        if ($zip->open($tempFile, ZipArchive::CREATE) === TRUE) {

            // 设置要压缩的目录
            $directoryToZip = $folderPath; // 调整这个路径到你想要压缩的目录

            // 这个函数用于递归添加目录到zip文件
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directoryToZip),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                // 跳过目录（它们会被递归添加）
                if (!$file->isDir()) {
                    // 获取真实路径
                    $filePath = $file->getRealPath();

                    if ($filePath !== false) {
                        // 获取相对路径
                        $relativePath = substr($filePath, strlen($directoryToZip));
                        // 添加到zip文件中
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            // 关闭ZIP文件
            $zip->close();

            // 返回ZIP文件作为下载
            return response()->download($tempFile, $zipFileName, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
        } else {
            dd("无法创建ZIP文件");
        }
    }

    function deleteCacheFile(Request $request)
    {


        // 获取 uids
        $uids = FryModel::select('uid')->get();
        $uids = array_column($uids->toArray(),'uid');



        // 获取所有的缓存目录
        $directory = base_path() . '/cache/';

        $contents = scandir($directory);

        // 遍历结果并筛选出文件夹
        $folders = [];
        foreach ($contents as $item) {
            // 排除当前目录（.）和父目录（..）
            if ($item != "." && $item != "..") {
                // 如果是文件夹，则加入到结果数组中
                if (is_dir($directory . DIRECTORY_SEPARATOR . $item)) {

                    if(!in_array($item,$uids)) {
                        // 删除目录
                        $dir = $directory . $item;
                        shell_exec('rm -rf ' . $dir);


                    //     echo("删除无效缓存：" . $dir . '<br>');
                    //   $a = $this->deleteDirectory($directory . $item);
                    //     echo($dir . "删除成功？：" . $a . '<br>');
                    }
                }
            }
        }

         //get  all chat
        $chatUids = ChatModel::select('uid')->get();
        $chatUids_ = array_column($chatUids->toArray(),'uid');
        dump('chatInfo count is :' . count($chatUids_));
        //get fry uids
        $fryUids = FryModel::select('uid')->get();
        $fryUids_ = array_column($fryUids->toArray(),'uid');

        // delechat chat
        foreach ($chatUids_ as $chatuid) {
            if(!in_array($chatuid, $fryUids_)) {
                 ChatModel::where('type','!=',1)->where('uid',$chatuid )->delete();
            }
        }

        // 输出结果
        return $this->success($folders);
    }


    // 删除目录
    function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }

}
