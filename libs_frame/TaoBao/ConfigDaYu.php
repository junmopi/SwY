<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2018/9/7 0007
 * Time: 9:43
 */
namespace TaoBao;

use Constant\ErrorCode;
use Exception\TaoBao\AliDaYu\SmsException;
use Tool\Tool;

class ConfigDaYu
{
    /**
     * APP KEY
     * @var string
     */
    private $appKey = '';
    /**
     * APP 密钥
     * @var string
     */
    private $appSecret = '';

    public function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __toString()
    {
        return Tool::jsonEncode($this->getConfigs(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function getAppKey() : string
    {
        return $this->appKey;
    }

    /**
     * @param string $appKey
     * @throws \Exception\TaoBao\AliDaYu\SmsException
     */
    public function setAppKey(string $appKey)
    {
        if (ctype_alnum($appKey)) {
            $this->appKey = $appKey;
        } else {
            throw new SmsException('app key不合法', ErrorCode::SMS_PARAM_ERROR);
        }
    }

    /**
     * @return string
     */
    public function getAppSecret() : string
    {
        return $this->appSecret;
    }

    /**
     * @param string $appSecret
     * @throws \Exception\TaoBao\AliDaYu\SmsException
     */
    public function setAppSecret(string $appSecret)
    {
        if (ctype_alnum($appSecret)) {
            $this->appSecret = $appSecret;
        } else {
            throw new SmsException('app secret不合法', ErrorCode::SMS_PARAM_ERROR);
        }
    }

    /**
     * 获取配置数组
     * @return array
     */
    public function getConfigs() : array
    {
        return [
            'app.key' => $this->appKey,
            'app.secret' => $this->appSecret,
        ];
    }
}
