<?php

namespace SyPrint\YiLianYun;

use DesignPatterns\Singletons\PrintYlyConfigSingleton;
use SyPrint\PrintBaseYly;
use SyPrint\PrintUtilBase;
use Tool\Tool;

class YlyRpcClient extends PrintBaseYly
{
    private $secret = '';

    private $action = '';

    public function __construct($token, $clientId, $action, $params)
    {
        parent::__construct();
        $config = PrintYlyConfigSingleton::getInstance()->getYlyConfig($clientId);
        $this->secret = $config->getClientSecret();
        $this->reqData = $params;
        $this->reqData['client_id'] = $clientId;
        $this->reqData['access_token'] = $token;
        $this->action = $action;
    }

    private function __clone()
    {
    }

    public function getSign($timestamp)
    {
        return md5(
            $this->reqData['client_id'].
            $timestamp.
            $this->secret
        );
    }


    public function uuid4(){
        mt_srand((double)microtime() * 10000);
        $charId = strtolower(md5(uniqid(rand(), true)));
        $hyphen = '-';
        $uuidV4 =
            substr($charId, 0, 8) . $hyphen .
            substr($charId, 8, 4) . $hyphen .
            substr($charId, 12, 4) . $hyphen .
            substr($charId, 16, 4) . $hyphen .
            substr($charId, 20, 12);
        return $uuidV4;
    }

    public function getDetail() : array
    {
        $resArr = [
            'code' => 0,
        ];
        $time = Tool::getNowTime();
        $this->reqData['timestamp'] = $time;
        $this->reqData['sign'] = $this->getSign($time);
        $this->reqData['id'] = $this->uuid4();

        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/' . $this->action;
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        $this->curlConfigs[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/json; charset=utf-8',
        ];
        $sendRes = PrintUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['error'] == 0) {
            $resArr['data'] = $sendData;
        } else {
            $resArr['code'] = $sendData['error'];
            $resArr['message'] = $sendData['error_description'];
        }

        return $resArr;
    }
}
