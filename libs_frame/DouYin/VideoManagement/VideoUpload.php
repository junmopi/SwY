<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 15:52
 */
namespace DouYin\VideoManagement;

use Constant\ErrorCode;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class VideoUpload extends DouYinBaseOpen {
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
     * 视频文件请求体
     */
    public function setVideo(string $videoPath, string $video_name)
    {
        $payload = '';
        $this->video = "--WebKitFormBoundaryuwYcfA2AIgxqIxA0\r\n"
                   . "Content-Type: application/x-www-form-urlencoded\r\n"
                   . "\r\n"
                   . $payload . "\r\n"
                   . "--WebKitFormBoundaryuwYcfA2AIgxqIxA0\r\n"
                   . "Content-Type: video/mp4\r\n"
                   . "Content-Disposition: form-data; name=\"video\"; filename=\"".$video_name."\"\r\n"
                   . "\r\n"
                   . file_get_contents($videoPath) . "\r\n"
                   . "--WebKitFormBoundaryuwYcfA2AIgxqIxA0--";
    }

    public function getDetail() : array
    {
        $first_newline = strpos($this->video, "\r\n");
        $multipart_boundary = substr($this->video, 2, $first_newline - 2);
        $request_headers = [];
        $request_headers[] = 'Content-Length: ' . strlen($this->video);
        $request_headers[] = 'Content-Type: multipart/form-data; boundary=' . $multipart_boundary;

        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/video/upload?' . http_build_query($this->reqData);
        $this->curlConfigs[CURLOPT_POSTFIELDS] = $this->video;
        $this->curlConfigs[CURLOPT_HTTPHEADER] = $request_headers;
        $sendRes = DouYinUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['data']['error_code'] > 0) {
            Log::log('DouYinVideoCreate:' . $sendData['data']['description']);
            throw new DouYinException($sendData['data']['description'], ErrorCode::PRINT_GET_ERROR);
        }

        return $sendData;
    }
}