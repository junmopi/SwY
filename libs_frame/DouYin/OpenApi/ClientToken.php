<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 15:00
 */
namespace DouYin\OpenApi;

use Constant\ErrorCode;
use DesignPatterns\Singletons\DouYinConfigSingleton;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class ClientToken extends DouYinBaseOpen{
    public function __construct()
    {
        parent::__construct();
        $config = DouYinConfigSingleton::getInstance()->getConfig();
        $this->reqData['client_key'] = $config->getClientKey();
        $this->reqData['client_secret'] = $config->getClientSecret();
        $this->reqData['grant_type'] = 'client_credential';
    }

    private function __clone()
    {
    }

    public function getDetail() : array
    {
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/oauth/client_token?' . http_build_query($this->reqData);
        $sendRes = DouYinUtilBase::sendGetReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['data']['error_code'] > 0) {
            Log::log('DouYinClientToken:' . $sendData['data']['description']);
            throw new DouYinException('获取抖音client_token失败', ErrorCode::PRINT_GET_ERROR);
        }

        return $sendData;
    }
}