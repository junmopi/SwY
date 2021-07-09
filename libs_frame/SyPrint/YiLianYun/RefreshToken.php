<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/1/14
 * Time: 16:51
 */

namespace SyPrint\YiLianYun;

use DesignPatterns\Singletons\PrintYlyConfigSingleton;
use SyPrint\PrintBaseYly;
use Constant\ErrorCode;
use Exception\SyPrint\YlyException;
use SyPrint\PrintUtilBase;
use Tool\Tool;

class RefreshToken extends PrintBaseYly
{
    private $secret = '';

    public function __construct(int $clientId)
    {
        parent::__construct();
        $config = PrintYlyConfigSingleton::getInstance()->getYlyConfig($clientId);
        $this->secret = $config->getClientSecret();
        $this->reqData['client_id'] = $config->getClientId();
    }

    private function __clone()
    {
    }

    public function getDetail(string $refreshToken = '') : array
    {
        $time = Tool::getNowTime();
        $this->reqData['timestamp'] = $time;
        $this->reqData['sign'] = $this->getSign($time);
        $this->reqData['id'] = $this->uuid4();
        $this->reqData['scope'] = 'all';
        $this->reqData['grant_type'] = 'refresh_token';
        $this->reqData['refresh_token'] = $refreshToken;
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/oauth/oauth';
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        $this->curlConfigs[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/json; charset=utf-8',
        ];
        $sendRes = PrintUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if (!is_array($sendData)) {
            throw new YlyException('获取access token出错', ErrorCode::PRINT_GET_ERROR);
        } elseif (!isset($sendData['access_token'])) {
            throw new YlyException($sendData['errmsg'], ErrorCode::PRINT_GET_ERROR);
        }

        return $sendData;
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
}