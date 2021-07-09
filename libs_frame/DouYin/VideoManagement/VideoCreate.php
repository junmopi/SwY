<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/15
 * Time: 10:34
 */
namespace DouYin\VideoManagement;

use Constant\ErrorCode;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class VideoCreate extends DouYinBaseOpen {
    /**
     * 视频文件id
     * @var string
     */
    private $video_id = '';

    /**
     * 视频文件标题
     * @var string
     */
    private $text = '';

    public function __construct(string $open_id, string $access_token = '')
    {
        parent::__construct();
        $this->reqData['open_id'] = $open_id;
        $this->reqData['access_token'] = $access_token;
    }

    private function __clone()
    {
    }

    public function setVideoId(string $video_id)
    {
        $this->video_id = $video_id;
    }
    
    public function setText(string $text){
        $this->text = $text;
    }

    public function getDetail() : array
    {
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/video/create?' . http_build_query($this->reqData);
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode([
            'video_id' => $this->video_id,
            'text' => $this->text,
        ], JSON_UNESCAPED_UNICODE);
        $this->curlConfigs[CURLOPT_HTTPHEADER] = [
            'application/json',
        ];
        $sendRes = DouYinUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['data']['error_code'] > 0) {
            Log::log('DouYinVideoCreate:' . $sendData['data']['description']);
        }

        return $sendData;
    }
}