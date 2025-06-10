<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\DepositActions;
use App\Admin\Actions\ExtractActions;
use App\Models\BannerModel;
use App\Models\ExchangeModel;
use App\Models\UserModel;
use App\Models\VideoContestModel;
use App\Models\VideoModel;
use App\Models\VideoNationModel;
use App\Models\VideoTagModel;
use App\Models\VipModel;
use App\Models\WalletModel;
use App\Service\HelperService;
use App\Service\UploadService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;

// 视频管理
class VideoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '视频管理/';


    /**
     * harbor 收藏
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new VideoModel());

        $grid->column('_id', __('视频ID'))->style('width:100px; word-break: break-all');
        $grid->column('video_url', __('查看视频'))->display(function ($v) {
            $str = '<a target=_blank href=' . $v . '>查看</a>';
            return $str;
        });

        $grid->column('video_name', __('视频名称'))->style('width:100px; word-break: break-all');

        $grid->column('video_type', __('视频类型'))->display(function ($v) {
            return '#' . implode('<br>#', VideoNationModel::whereIn('_id', $v)->pluck('type_name')->toArray());
        })->style('width:100px; word-break: break-all');

        $grid->column('video_tag', __('视频标签'))->display(function ($v) {
            return '#' . implode('<br>#', VideoTagModel::whereIn('_id', $v)->pluck('type_name')->toArray());
        })->style('width:100px; word-break: break-all');

        $grid->column('is_featured', __('是否为精选'))->display(function ($v) {
            return $v == 1 ? '是' : '<span style="color: red" >否</span>';
        });
        $grid->column('is_original', __('是否为原创'))->display(function ($v) {
            return $v == 1 ? '是' : '<span style="color: red" >否</span>';
        });
        $grid->column('user_id', __('所属用户'))->display(function ($userId) {
            $userInfo = UserModel::where('id', $userId)->first();
            $uname = $userInfo->name ?? $userInfo->email;
            return '<span style="color: rgba(229,46,122,0.85)" >' . $uname . '</span>';
        });
        $grid->column('status', __('视频状态'))->display(function ($v) {
            return $v == 1 ? '<span style="color: #2ea8e5" >已上架</span>' : '<span style="color: #e5962e" >未上架</span>';

        });
        $grid->column('video_money', __('视频单价'))->display(function ($v) {
            return $v == 0 ? '免费视频' : $v . '钻';
        });
        $grid->column('paid_role', __('收费对象'))->default(0)->display(function ($v) {
            return $v == 1 ? '普通用户收费' : '所有用户收费';
        });
        $grid->column('hot_number', __('热度'))->default(0);
        $grid->column('browse_number', __('播放量'))->default(0);
        $grid->column('look_number', __('观看次数'))->default(0);
        $grid->column('recommend', __('推荐数'))->default(0);
        $grid->column('dis_recommend', __('不推荐数'))->default(0);
        $grid->column('like', __('喜欢量'))->default(0);
        $grid->column('harbor', __('收藏量'))->default(0);

        $grid->column('dis_recommend', __('不推荐量'));
        $grid->column('weight', __('权重排序'))->editable();

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
        $show = new Show(VideoModel::findOrFail($id));


        $show->field('_id', __('视频ID'))->style('width:100px; word-break: break-all');
        $show->field('video_url', __('查看视频'));
//            ->display(function ($v) {
//            $s = printf('<a target="_blank" href="s%">查看</a>', $v);
//            return $s;
//        });
        $show->field('video_name', __('视频名称'))->style('width:100px; word-break: break-all');
        $show->field('user_id', __('所属用户'))->display(function ($userId) {
            $userInfo = UserModel::where('id', $userId)->first();
            $uname = $userInfo->name ?? $userInfo->email;
            return '<span style="color: rgba(229,46,122,0.85)" >' . $uname . '</span>';
        });
        $show->field('status', __('视频状态'))->display(function ($v) {
            return $v == 1 ? '<span style="color: #2ea8e5" >已上架</span>' : '<span style="color: #e5962e" >未上架</span>';

        });
        $show->field('video_money', __('视频单价'))->display(function ($v) {
            return $v == 0 ? '免费视频' : $v . '钻';
        });
        $show->field('paid_role', __('收费对象'))->default(0)->display(function ($v) {
            return $v == 1 ? '普通用户收费' : '所有用户收费';
        });
        $show->field('hot_number', __('热度'))->default(0);
        $show->field('play_number', __('播放量'))->default(0);
        $show->field('look_number', __('观看次数'))->default(0);
        $show->field('recommend', __('推荐数'))->default(0);
        $show->field('dis_recommend', __('不推荐数'))->default(0);
        $show->field('like', __('喜欢量'))->default(0);
        $show->field('harbor', __('收藏量'))->default(0);

        $show->field('dis_recommend', __('不推荐量'));
        $show->field('weight', __('权重排序'))->editable();


        return $show;
    }

    /**
     * 'video_url', // 视频地址
     * 'video_name', // 视频名称
     * 'status', // 视频状态
     * 'play_number', // 播放量
     * 'hot_number', // 热度
     * 'look_number', // 观看次数
     * 'dis_recommend', // 不推荐
     * 'recommend', // 推荐
     * 'like', // 喜欢
     * 'admin_id', // 后台用户id
     * 'created_at',
     * 'updated_at',
     * 'insert_time',
     * 'weight', // 权重
     * 'user_id',
     * 'user_info',  // 用户信息
     * 'remark', // 备注
     * 'is_vip', // 是否是会员视频
     * 'video_money', // 视频单价
     * 'paid_role', // 收费角色
     * 'harbor', // 收藏数量
     * 'is_original', // 是否是原创
     * 'is_featured',  // 是否是精选视频
     * 'video_img', // 封面图
     * 'video_tag', // 视频标签
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form(new VideoModel());
        $form->setTitle('上传视频');

        $form->text('video_name', __('视频名称:'))->required();

        // 用户信息选择
        $userList = UserModel::where(['is_upload_video' => 1])->select(['id', 'name', 'email'])->get();
        $userInfos = [];
        foreach ($userList as $userInfo) {
            $userInfos[$userInfo->id] = $userInfo->name ?? $userInfo->email;
        }
        $form->select('user_id', __('选择操作用户:'))->options($userInfos)->required();

        // 视频类型
        $videoType = VideoNationModel::all()->pluck('type_name', 'id');
        $form->checkbox('video_type', __('视频分类:'))->options($videoType->toArray())->canCheckAll();

        // 封面图
        $form->image('video_img', '封面图')->required();

        // 视频标签
        $videoType = VideoTagModel::all()->pluck('type_name', 'id');
        $form->checkbox('video_tag', __('视频标签:'))->options($videoType->toArray())->canCheckAll();

        // 每日大赛分类选择
        $videoContests = VideoContestModel::all()->pluck('type_name', 'id');
        $form->select('video_contest_id', '每日大赛')->options($videoContests);

        // 是否是会员视频
        $form->switch('is_vip', '是否为会员专属视频')->options([
            1 => '是',
            0 => '否'
        ])->default(0);

        // 视频单价
        $form->number('video_money', '视频单价(钻石)')->required()->default(0)->help('视频单价为0钻石，则为免费福利视频');


        // 收费角色， 如果视频单价不为0， 该权限设置则有意义 1 仅对普通用户收费  2. 对所有用户收费
        $form->select('paid_role', '收费用户角色:')->options([
            1 => '仅对普通用户收费',
            2 => '对所有用户收费'
        ])->help('视频单价不为0， 该权限设置则有意义 ')->required();

        // 上传视频
        $form->largefile('video_url', __('视频上传:'));

        // 是否上架视频
        $form->switch('status', '是否上架视频')->required()->options([
            1 => '是',
            0 => '否'
        ])->default(1);

        // 是否原创
        $form->switch('is_original', '是否原创')->options([
            1 => '是',
            0 => '否'
        ])->default(1);

        // 是否精选
        $form->switch('is_featured', '是否精选')->options([
            1 => '是',
            0 => '否'
        ])->default(1);


        // 排序
        $form->number('weight', '排序')->required()->default(1);


        $form->image('ads_img', __('广告图'))->default('');
        $form->url('ads_url', __('广告外链'))->default('');
        $form->text('remark', __('视频备注:'));

        $form->saving(function (Form $form) {

            // 获取用户信息
            $userInfo = UserModel::where('id', $form->user_id)->first();
            if (!$userInfo) {
                abort(500, '用户信息不存在');
            }

            // 检查分类
            $aa = count(array_filter($form->video_type));
            if (!$aa) {
                abort(500, '请选择视频分类！');
            }

            // 检查标签
            $aa = count(array_filter($form->video_tag));
            if (!$aa) {
                abort(500, '请选择视频标签！');
            }

            // 检查视频, 并且组装视频地址
            if (!$form->video_url) {
                abort(500, '请上传视频');
            }
            $preg = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
            if (!preg_match($preg, $form->video_url)) {
                $videoUrl = explode('_', $form->video_url);
                $videoUrl[0] = 'video';
                $vUrl = 'http://' . request()->host() . '/storage/' . implode('/', $videoUrl);
                $form->model()->video_url = $vUrl;
                $form->video_url = $vUrl;
            }


            // 插入时间
            $form->model()->insert_time = time();

            // 播放量
            $form->model()->play_number = 0;

            // 后台用户的id
            $adminInfo = Admin::user();
            $form->model()->admin_id = $adminInfo->id;

        });
        return $form;
    }
}
