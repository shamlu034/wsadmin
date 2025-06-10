<?php

namespace App\Service;

use Illuminate\Support\Facades\Storage;
use Str;

class UploadService
{

    // 单例容器
    private static ?UploadService $instance = null;

    // 基类构造方法
    function __construct()
    {

    }

    static function getInstance(): UploadService
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    // 图片上传
    public function upload($file): array
    {

        if (!$file) {
            return HelperService::getInstance()->errorService("请选择文件");
        }


        if (!$file->isValid()) {
            return HelperService::getInstance()->errorService("文件无效");
        }


        // 文件类型
        $fileType = explode("/", $file->getClientMimeType())[0];
        $ext = $file->getClientOriginalExtension();
        if (!in_array($ext, self::enableUploadExt())) {
            return HelperService::getInstance()->errorService('该文件不支持上传');
        }

        // 上传文件大小限制为2m
        $fileSize = round($file->getSize() / 1024, 2);
        if ($fileSize > 1000 * 1024) {
            return HelperService::getInstance()->errorService("文件大小不能超过1000M");
        }

        $uri = Storage::put($fileType, $file, ['visibility' => 'public']);
        $data = [
            'uri' => $uri,
            'url' => Storage::url($uri),
            'fileName' => Str::afterLast($uri, '/'),
            'fileType' => $fileType
        ];

        return HelperService::getInstance()->successService($data, 'success');
    }

    public function delete($url): array
    {
        // 完整的地址：http://baidu.com/web/images/xxx.jpg
        if (is_null($url)) {
            return HelperService::getInstance()->errorService('文件地址不能为空');
        }
        $explode = explode("/", $url);
        $fileName = "/" . implode("/", array_splice($explode, 4, 6));

        // 判断文件是否存在
        $existFile = Storage::disk(self::getOssType())->exists($fileName);
        if (!$existFile) {
            return HelperService::getInstance()->errorService("文件不存在！");
        }
        Storage::disk(self::getOssType())->delete($fileName);
        return HelperService::getInstance()->successService([], '删除成功');
    }

    /**
     * 文件存储桶
     */
    private static function backetName()
    {
        return env("AWS_BUCKET");
    }

    /**
     * 文件存储方式
     * @return string
     */
    private static function getOssType(): string
    {
        return 's3';
    }

    /**
     * 允许文件上传的后缀
     * @return string[]
     */
    private static function enableUploadExt(): array
    {
        $string = "jpg|png|jpeg|pdf|mp4|avi|mov|wmv|swf|flv";
        return explode("|", $string);
    }

    /**
     * 获取存储分类文件夹
     * @return string
     */
    private static function getFileDist($fileType = "image"): string
    {
        return match ($fileType) {
            "image" => "images",
            "video" => "videos",
            "audio" => "audios",
            "private" => "private",
            default => "others",
        };
    }

    // 分片上传
    public function uploadVideo($file): array
    {
//         = request()->file('video');

        if (!$file->isValid()) {
            return HelperService::getInstance()->errorService("文件无效");
        }

        // 文件类型
        $fileType = explode("/", $file->getClientMimeType())[0];
        $ext = $file->getClientOriginalExtension();
        if (!in_array($ext, self::enableUploadExt())) {
            return HelperService::getInstance()->errorService('该文件不支持上传');
        }

        // 上传文件大小限制 1g
        $fileSize = round($file->getSize() / 1024, 2);
        if ($fileSize > 1000 * 1024) {
            return HelperService::getInstance()->errorService("文件大小不能超过1000M");
        }

        $storageClient = Storage::disk(self::getOssType())->getAdapter();
        dd($storageClient);
        $data = [
//            'uri' => $uri,
//            'url' => Storage::url($uri),
//            'fileName' => Str::afterLast($uri, '/'),
//            'fileType' => $fileType
        ];

        return HelperService::getInstance()->successService($data, 'success');
    }
}

