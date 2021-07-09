<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 17:47
 */
namespace DouYin\VideoManagement;

use Constant\ErrorCode;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class VideoPartInit extends DouYinBaseOpen {
    public function __construct(string $open_id, string $access_token = '')
    {
        parent::__construct();
        $this->reqData['open_id'] = $open_id;
        $this->reqData['access_token'] = $access_token;
    }

    private function __clone()
    {
    }

    public function getDetail() : array
    {
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/video/part/init?' . http_build_query($this->reqData);
        $this->curlConfigs[CURLOPT_POSTFIELDS] = '{}';
        $this->curlConfigs[CURLOPT_HTTPHEADER] = [
            'application/json',
        ];
        $sendRes = DouYinUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['data']['error_code'] > 0) {
            Log::log('DouYinVideoPartInit:' . $sendData['data']['description']);
            throw new DouYinException($sendData['data']['description'], ErrorCode::PRINT_GET_ERROR);
        }

        return $sendData;
    }
}