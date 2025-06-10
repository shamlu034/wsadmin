<?php

namespace App\Http\Controllers;

// 每日大赛相关
use App\Models\BannerModel;
use App\Models\ContestBannerModel;
use App\Models\VideoContestModel;
use App\Models\VideoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VideoContestController extends Controller
{
    function getBanner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $where = [
            'type' => trim($request->input('type')),
            'status' => 1,
            'nature' => '0'
        ];

        $list = ContestBannerModel::where($where)
            ->orderby('weight', 'asc')
            ->get();


        return $this->success(['list' => $list, 'address' => $request->host() . '/storage']);
    }


    function getContestVideo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // 每日大赛栏目的分类
        $data = VideoContestModel::where(['status' => 1])->orderBy('weight', 'asc')->get();

        // 视频
        foreach ($data as $item) {

            $videoData = VideoModel::where(['video_contest_id' => $item->id])
                ->orderBy('weight', 'asc')
                ->offset(0)
                ->limit(10)
                ->get();
            $item->video = $videoData;
        }

        // 广告
        $where = [
            'type' => trim($request->input('type')),
            'status' => 1,
            'nature' => '1'
        ];
        $advertise = ContestBannerModel::where($where)
            ->orderby('weight', 'asc')
            ->first();


        return $this->success(['list' => $data, 'advertise' => $advertise, 'address' => $request->host() . '/storage']);
    }
}
