<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/7/16 0016
 * Time: 19:21
 */
namespace SyIot;

use Constant\ErrorCode;
use Exception\Iot\BaiDuIotException;

class ConfigBaiDu
{
    /**
     * 访问ID
     * @var string
     */
    private $accessKey = '';
    /**
     * 访问密钥
     * @var string
     */
    private $accessSecret = '';

    public function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return string
     */
    public function getAccessKey() : string
    {
        return $this->accessKey;
    }

    /**
     * @param string $accessKey
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setAccessKey(string $accessKey)
    {
        if (ctype_alnum($accessKey)) {
            $this->accessKey = $accessKey;
        } else {
            throw new BaiDuIotException('访问ID不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @return string
     */
    public function getAccessSecret() : string
    {
        return $this->accessSecret;
    }

    /**
     * @param string $accessSecret
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setAccessSecret(string $accessSecret)
    {
        if (ctype_alnum($accessSecret)) {
            $this->accessSecret = $accessSecret;
        } else {
            throw new BaiDuIotException('访问密钥不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }
}
