<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/1/14
 * Time: 14:51
 */
namespace SyPrint\YiLianYun;

use Constant\ErrorCode;
use DesignPatterns\Singletons\PrintYlyConfigSingleton;
use Exception\SyPrint\YlyException;
use SyPrint\PrintBaseYly;
use SyPrint\PrintUtilBase;
use Tool\Tool;

class AccessToken extends PrintBaseYly
{
    private $code = '';

    private $secret = '';

    public function __construct(string $clientId, string $code = '')
    {
        parent::__construct();
        $time = time();
        $config = PrintYlyConfigSingleton::getInstance()->getYlyConfig($clientId);
        $this->secret = $config->getClientSecret();
        $this->reqData['client_id'] = $clientId;
        $this->reqData['scope'] = 'all';
        $this->reqData['grant_type'] = 'client_credentials';
        $this->reqData['timestamp'] = $time;
        $this->reqData['sign'] = $this->getSign($time);
        $this->reqData['id'] = $this->uuid4();
        $this->code = $code;
    }

    private function __clone()
    {
    }

    public function getDetail() : array
    {
        if (!empty($this->code)) {
            $this->reqData['code'] = $this->code;
            $this->reqData['grant_type'] = 'authorization_code';
        }
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/oauth/oauth';
        $this->curlConfigs[CURLOPT_POSTFIELDS] = http_build_query($this->reqData);
        $this->curlConfigs[CURLOPT_HTTPHEADER] = [
            'Expect:',
        ];
        $sendRes = PrintUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if (!is_array($sendData)) {
            throw new YlyException('获取access token出错', ErrorCode::PRINT_GET_ERROR);
        } elseif (!isset($sendData['body'])) {
            throw new YlyException($sendData['error_description'], ErrorCode::PRINT_GET_ERROR);
        }

        return $sendData['body'];
    }

    public function getSign($timestamp)
    {
        return md5($this->reqData['client_id'] . $timestamp . $this->secret);
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