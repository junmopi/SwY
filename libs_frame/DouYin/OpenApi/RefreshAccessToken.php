<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 15:05
 */

namespace DouYin\OpenApi;

use Constant\ErrorCode;
use DesignPatterns\Singletons\DouYinConfigSingleton;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class RefreshAccessToken extends DouYinBaseOpen{
    public function __construct(string $refresh_token)
    {
        parent::__construct();
        $config = DouYinConfigSingleton::getInstance()->getConfig();
        $this->reqData['client_key'] = $config->getClientKey();
        $this->reqData['grant_type'] = 'refresh_token';
        $this->reqData['refresh_token'] = $refresh_token;
    }

    private function __clone()
    {
    }

    public function getDetail() : array
    {
        $resArr = [
            'code' => 0
        ];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/oauth/refresh_token?' . http_build_query($this->reqData);
        $sendRes = DouYinUtilBase::sendGetReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['data']['error_code'] > 0) {
            $resArr['code'] = $sendData['data']['error_code'];
            $resArr['message'] = $sendData['data']['description'];
        }

        return $sendData['data'];
    }
}