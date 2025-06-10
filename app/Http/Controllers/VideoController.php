<?php

namespace App\Http\Controllers;

use App\Models\UserBrowseModel;
use App\Models\UserModel;
use App\Models\VideoHarborModel;
use App\Models\VideoLikeModel;
use App\Models\VideoModel;
use App\Models\VideoNationModel;
use App\Models\VideoTagModel;
use App\Models\VipModel;
use App\Service\UploadService;
use App\Service\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// 视频相关
class VideoController extends Controller
{
    // 获取视频分类
    function getVideoType(Request $request)
    {
        $list = VideoNationModel::where([])->orderBy('weight', 'asc')->get();

        return $this->success(['list' => $list]);
    }

    // 视频上传
    function uploadVideo(Request $request)
    {

//        $validator = Validator::make($request->all(), [
//            'video' => 'required',
//        ]);
//        if ($validator->fails()) {
//            return $this->validationError($validator->errors());
//        }
//
//        $res = UploadService::getInstance()->uploadVideo($request->file('video'));
//        dd($res);
    }


    // 获取视频  1 收藏 2 精选 3 原创 4 最新 5 热门
    function getVideoList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'page' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 类型
        $type = trim($request->input('type'));
        if (is_numeric($type)) {
            if (!in_array($type, [1, 2, 3, 4, 5])) {
                return $this->error_('type error');
            }

        }

        $limit = trim($request->input('limit'));
        $offset = (trim($request->input('page')) - 1) * $limit;

        // 收藏视频
        $count = 0;

        if (is_numeric($type)) {
            if ($type == 1) {
                $userInfo = auth()->user();
                if (!$userInfo) {
                    return $this->error_(trans('Unauthorized'));
                }

                // 根据收藏获取视频id
                $videoIds = VideoHarborModel::where('user_id', $userInfo->id)->pluck('video_id');

                // 获取视频
                $count = VideoModel::whereIn('_id', $videoIds)->count();
                $list = VideoModel::whereIn('_id', $videoIds)
                    ->offset($offset)
                    ->limit($limit)
                    ->orderBy('weight', 'asc')
                    ->get();
            } else {

                // 条件
                $where = [];

                if ($type == 2) {
                    $where['is_featured'] = 1;
                }


                if ($type == 3) {
                    $where['is_original'] = 1;
                }

                // 获取视频
                $count = VideoModel::where($where)->count();
                $list = VideoModel::where($where)
                    ->offset($offset)
                    ->limit($limit)
                    ->orderBy('weight', 'asc')
                    ->get();

                if ($type == 4) {
                    // 获取视频
                    $list = VideoModel::where($where)
                        ->offset($offset)
                        ->limit($limit)
                        ->orderBy('insert_time', 'desc')
                        ->get();
                }

                // 热门 hot_number
                if ($type == 5) {
                    // 获取视频
                    $list = VideoModel::where($where)
                        ->offset($offset)
                        ->limit($limit)
                        ->orderBy('browse_number', 'desc')
                        ->get();
                }

            }
        } else {
            // 获取视频
            $model = VideoModel::whereIn('video_type', [$type]);
            $count = $model->count();

            $list = $model
                ->offset($offset)
                ->limit($limit)
                ->orderBy('weight', 'asc')
                ->get();
        }


        $userInfo = auth()->user();
        $userId = $userInfo?->id ?? trim($request->input('user_id'));
        return $this->success([
            'address_domain' => $request->host() . '/storage',
            'list' => VideoService::getInstance()->makeVideo($list, (int)$userId),
            'count' => $count,
            'page' => trim($request->input('page')),
        ]);
    }

    // like
    function likeVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 检查视频是否存在
        $videoInfo = VideoModel::where('_id', trim($request->input('video_id')))->first();

        if (!$videoInfo) {
            return $this->error_('视频不存在！');
        }

        //检查用户信息是否存在
        $userInfo = UserModel::where('id', trim($request->input('user_id')))->first();
        if (!$userInfo) {
            return $this->error_('用户信息不存在！');
        }

        // 检查是否已经like
        $likeInfo = VideoLikeModel::where([
            'user_id' => $userInfo->id,
            'video_id' => $videoInfo->id
        ])->first();
        if (!$likeInfo) {
            // 插入like
            $likeInfo = VideoLikeModel::create([
                'user_id' => $userInfo->id,
                'video_id' => $videoInfo->id,
                'insert_time' => time()
            ]);

            // 喜欢加1
            VideoModel::where('_id', trim($request->input('video_id')))->increment('like', 1);
        }

        return $this->success($likeInfo);
    }


    // 收藏视频
    function harborVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 检查视频是否存在
        $videoInfo = VideoModel::where('_id', trim($request->input('video_id')))->first();

        if (!$videoInfo) {
            return $this->error_('视频不存在！');
        }

        //检查用户信息是否存在
        $userInfo = UserModel::where('id', trim($request->input('user_id')))->first();
        if (!$userInfo) {
            return $this->error_('用户信息不存在！');
        }

        // 检查是否已经like
        $likeInfo = VideoHarborModel::where([
            'user_id' => $userInfo->id,
            'video_id' => $videoInfo->id
        ])->first();
        if ($likeInfo) {
            // 收藏成功
            return $this->success($likeInfo);
        }

        $likeInfo = VideoHarborModel::create([
            'user_id' => $userInfo->id,
            'video_id' => $videoInfo->id,
            'insert_time' => time()
        ]);

        // 喜欢加1
        VideoModel::where('_id', trim($request->input('video_id')))->increment('harbor', 1);

        return $this->success($likeInfo);
    }


    // 取消收藏
    function deleteHarborVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 检查视频是否存在
        $videoInfo = VideoModel::where('_id', trim($request->input('video_id')))->first();

        if (!$videoInfo) {
            return $this->error_('视频不存在！');
        }

        //检查用户信息是否存在
        $userInfo = UserModel::where('id', trim($request->input('user_id')))->first();
        if (!$userInfo) {
            return $this->error_('用户信息不存在！');
        }

        // 检查是否已经like
        $likeInfo = VideoHarborModel::where([
            'user_id' => $userInfo->id,
            'video_id' => $videoInfo->id
        ])->first();
        if (!$likeInfo) {
            // 收藏成功
            return $this->error_('您未收藏该视频');
        }
        $likeInfo->delete();


        return $this->success($likeInfo);
    }


    // 获取我喜欢的视频列表
    function getMyLikeVideoList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }


        // 获取videos
        $videoIds = VideoLikeModel::where('user_id', (int)trim($request->input('user_id')))
            ->pluck('video_id');


        // 获取视频
        $list = VideoModel::whereIn('_id', $videoIds->toArray())->get();

        return $this->success([
            'list' => VideoService::getInstance()->makeVideo($list, (int)trim($request->input('user_id')))
        ]);
    }

    // 获取我收藏的
    function getMyHarborVideoList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 检查用户信息是否存在
        $videoIds = VideoHarborModel::where('user_id', (int)trim($request->input('user_id')))
            ->pluck('video_id');

        // 获取视频
        $list = VideoModel::whereIn('_id', $videoIds->toArray())->get();

        return $this->success([
            'list' => VideoService::getInstance()->makeVideo($list, (int)trim($request->input('user_id')))
        ]);
    }


    // 获取视频标签
    function getVideoTags(Request $request)
    {
        $data = VideoTagModel::where([])->orderBy('weight', 'asc')->get();
        return $this->success($data);
    }

    // 通过id 获取类型信息
    function getVideoTypeById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_type_id' => 'required|array', // 搜索内容
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $ids = array_filter($request->input('video_type_id'), function ($v) {
            return trim($v);
        });


        $list = VideoNationModel::whereIn('_id', $ids)->orderBy('weight', 'asc')->get();

        return $this->success(['list' => $list]);

    }

    // 通过id 获取标签信息
    function getVideoTagById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_tag_id' => 'required|array', // 搜索内容
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $ids = array_filter($request->input('video_tag_id'), function ($v) {
            return trim($v);
        });


        $list = VideoTagModel::whereIn('_id', $ids)->orderBy('weight', 'asc')->get();

        return $this->success(['list' => $list]);

    }

    // 浏览视频
    function browseVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 检查视频是否存在
        $videoInfo = VideoModel::where('_id', trim($request->input('video_id')))->first();

        if (!$videoInfo) {
            return $this->error_('视频不存在！');
        }

        //检查用户信息是否存在
        $userInfo = UserModel::where('id', trim($request->input('user_id')))->first();
        if (!$userInfo) {
            return $this->error_('用户信息不存在！');
        }

        // 检查是否已经浏览
        $likeInfo = UserBrowseModel::where([
            'user_id' => $userInfo->id,
            'video_id' => $videoInfo->id
        ])->first();
        if ($likeInfo) {
            // 收藏成功
            return $this->success($likeInfo);
        }

        $likeInfo = UserBrowseModel::create([
            'user_id' => $userInfo->id,
            'video_id' => $videoInfo->id,
            'insert_time' => time()
        ]);

        // 浏览量加1
        VideoModel::where('_id', trim($request->input('video_id')))->increment('browse_number', 1);

        return $this->success($likeInfo);
    }


    // 视频搜索
    function searchVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string', // 搜索内容
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 用户id
        $userInfo = auth()->user();
        $userId = $userInfo?->id ?? trim($request->input('user_id'));

        // 搜索内容
        $searchContent = trim($request->input('content'));

        // 搜索视频类型
        $typeIds = VideoNationModel::where('type_name', 'like', '%' . $searchContent . '%')->pluck('_id');

        // 搜索视频标签
        $tagIds = VideoTagModel::where('type_name', 'like', '%' . $searchContent . '%')->pluck('_id');

        // 通过视频名称搜索
        $videoIds = VideoModel::where('video_name', 'like', '%' . $searchContent . '%')->pluck('_id');

        // 开始视频搜索
        $list = VideoModel::whereIn('video_type', $typeIds)
            ->orWhereIn('video_tag', $tagIds)
            ->orWhereIn('_id', $videoIds)
            ->get();
        return $this->success([
            'list' => VideoService::getInstance()->makeVideo($list, (int)$userId)
        ]);
    }


}
