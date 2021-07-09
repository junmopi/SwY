<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/15
 * Time: 10:16
 */
namespace DouYin\VideoManagement;

use Constant\ErrorCode;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class VideoPartUpload extends DouYinBaseOpen {
    /**
     * 视频文件
     * @var string
     */
    private $video = '';

    public function __construct(string $open_id, string $access_token = '')
    {
        parent::__construct();
        $this->reqData['open_id'] = $open_id;
        $this->reqData['access_token'] = $access_token;
    }

    private function __clone()
    {
    }

    /**
     * 设置位置
     * @param int $part_number 表示该分片在整个视频内的相对位置，从1开始（1即表示第一段视频分片）
     */
    public function setNumber(int $part_number)
    {
        $this->reqData['part_number'] = $part_number;
    }

    public function setUploadId(string $upload_id)
    {
        $this->reqData['upload_id'] = $upload_id;
    }

    /**
     * 视频文件请求体
     */
    public function setVideo(array $video)
    {
        $this->reqData['video'] = $video;
    }

    public function getDetail() : array
    {
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/video/part/upload?' . http_build_query($this->reqData);
        unset($this->reqData['open_id'], $this->reqData['upload_id'], $this->reqData['part_number'],$this->reqData['access_token']);
        $this->curlConfigs[CURLOPT_POSTFIELDS] = $this->reqData;
        $this->curlConfigs[CURLOPT_HTTPHEADER] = [
            'Content-Type: multipart/form-data',
        ];
        Log::log('CURLCONFIGS:' . print_r($this->curlConfigs,true));
        $sendRes = DouYinUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        Log::log('XXXXXXXXXXXX:' . print_r($sendData,true));
        if ($sendData['data']['error_code'] > 0) {
            Log::log('DouYinVideoPartUpload:' . $sendData['data']['description']);
            throw new DouYinException($sendData['data']['description'], $sendData['data']['error_code']);
        }

        return [
            'data' => $sendData,
        ];
    }
}