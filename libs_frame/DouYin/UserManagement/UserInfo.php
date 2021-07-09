<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 15:17
 */
namespace DouYin\UserManagement;

use Constant\ErrorCode;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class UserInfo extends DouYinBaseOpen {
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
        $resArr = [
            'code' => 0
        ];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/passport/open/userinfo/?' . http_build_query($this->reqData);
        $sendRes = DouYinUtilBase::sendGetReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['data']['error_code'] > 0) {
            $resArr['code'] = $sendData['data']['error_code'];
            $resArr['message'] = $sendData['data']['description'];
        }else{
            $resArr['data'] = $sendData['data'];
        }

        return $resArr;
    }
}