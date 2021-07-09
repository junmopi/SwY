<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 15:33
 */
namespace DouYin\UserManagement;

use Constant\ErrorCode;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class FansCheck extends DouYinBaseOpen {
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
     * 设置关注用户openID
     * @param string $follow_open_id 关注者openID
     */
    public function setFollowOpenId(string $follow_open_id)
    {
        $this->reqData['follower_open_id'] = $follow_open_id;
    }

    public function getDetail() : array
    {
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/fans/check?' . http_build_query($this->reqData);
        $sendRes = DouYinUtilBase::sendGetReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['data']['error_code'] > 0) {
            Log::log('DouYinFansCheck:' . $sendData['data']['description']);
            throw new DouYinException($sendData['data']['description'], ErrorCode::PRINT_GET_ERROR);
        }

        return $sendData;
    }
}