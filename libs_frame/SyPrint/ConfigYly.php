<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/1/14
 * Time: 15:04
 */

namespace SyPrint;

use Constant\ErrorCode;
use Exception\SyPrint\YlyException;
use InvalidArgumentException;

class ConfigYly {
    /**
     * 应用id
     * @var string
     */
    private $clientId = '';
    /**
     * 应用密钥
     * @var string
     */
    private $clientSecret = '';
    /**
     * 日志
     * @var string
     */
    private $log;
    /**
     * 配置有效状态
     * @var bool
     */
    private $valid = false;
    /**
     * 配置过期时间戳
     * @var int
     */
    private $expireTime = 0;

    public function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return string
     */
    public function getClientId() : string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @throws \Exception\SyPrint\YlyException
     */
    public function setClientId(string $clientId)
    {
        if (ctype_alnum($clientId)) {
            $this->clientId = $clientId;
        } else {
            throw new YlyException('应用id不合法', ErrorCode::PRINT_PARAM_ERROR);
        }
    }

    /**
     * @return string
     */
    public function getClientSecret() : string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     * @throws \Exception\SyPrint\YlyException
     */
    public function setClientSecret(string $clientSecret)
    {
        if (ctype_alnum($clientSecret)) {
            $this->clientSecret = $clientSecret;
        } else {
            throw new YlyException('应用密钥不合法', ErrorCode::PRINT_PARAM_ERROR);
        }
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog($log)
    {
        if (!method_exists($log, "info")) {
            throw new InvalidArgumentException("logger need have method 'info(\$message)'");
        }
        if (!method_exists($log, "error")) {
            throw new InvalidArgumentException("logger need have method 'error(\$message)'");
        }
        $this->log = $log;
    }

    /**
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     */
    public function setValid(bool $valid)
    {
        $this->valid = $valid;
    }

    /**
     * @return int
     */
    public function getExpireTime() : int
    {
        return $this->expireTime;
    }

    /**
     * @param int $expireTime
     */
    public function setExpireTime(int $expireTime)
    {
        $this->expireTime = $expireTime;
    }

    /**
     * 获取配置数组
     * @return array
     */
    public function getConfigs() : array
    {
        return [
            'clientId' => $this->clientId,
            'secret' => $this->clientSecret,
            'valid' => $this->valid,
            'expire.time' => $this->expireTime,
        ];
    }
}