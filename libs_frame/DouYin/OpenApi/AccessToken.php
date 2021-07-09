<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/1/14
 * Time: 14:51
 */
namespace DouYin\OpenApi;

use Constant\ErrorCode;
use Constant\Project;
use DesignPatterns\Singletons\DouYinConfigSingleton;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class AccessToken extends DouYinBaseOpen
{
    public function __construct(string $code)
    {
        parent::__construct();
        $config = DouYinConfigSingleton::getInstance()->getConfig();
        $this->reqData['client_key'] = $config->getClientKey();
        $this->reqData['client_secret'] = $config->getClientSecret();
        $this->reqData['grant_type'] = 'authorization_code';
        $this->reqData['code'] = $code;
    }

    private function __clone()
    {
    }

    public function getDetail() : array
    {
        $resArr = [
            'code' => 0
        ];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/passport/open/access_token/?' . http_build_query($this->reqData);
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