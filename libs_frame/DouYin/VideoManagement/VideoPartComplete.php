<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/15
 * Time: 10:26
 */
namespace DouYin\VideoManagement;

use Constant\ErrorCode;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;


class VideoPartComplete extends DouYinBaseOpen {
    public function __construct(string $open_id, string $access_token = '')
    {
        parent::__construct();
        $this->reqData['open_id'] = $open_id;
        $this->reqData['access_token'] = $access_token;
    }

    private function __clone()
    {
    }

    public function setUploadId(string $upload_id)
    {
        $this->reqData['upload_id'] = $upload_id;
    }

    public function getDetail() : array
    {
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/video/part/complete?' . http_build_query($this->reqData);
        $this->curlConfigs[CURLOPT_POSTFIELDS] = '{}';
        $this->curlConfigs[CURLOPT_HTTPHEADER] = [
            'application/json',
        ];
        $sendRes = DouYinUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);

        return $sendData;
    }
}