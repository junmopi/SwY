<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 11:14
 */

namespace DouYin;

use Constant\ErrorCode;
use Exception\DouYin\DouYinException;
use Tool\Tool;

class DouYinConfig {
    /**
     * 应用唯一标识
     * @var string
     */
    private $clientKey = '';
    /**
     * 应用密钥
     * @var string
     */
    private $clientSecret = '';
    /**
     * 应用密钥
     * @var string
     */
    private $redirectUri = '';

    public function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return string
     */
    public function getClientKey() : string
    {
        return $this->clientKey;
    }

    /**
     * @param string $clientKey
     * @throws \Exception\DouYin\DouYinException
     */
    public function setClientKey(string $clientKey)
    {
        if (ctype_alnum($clientKey)) {
            $this->clientKey = $clientKey;
        } else {
            throw new DouYinException('应用唯一标识不合法', ErrorCode::PRINT_PARAM_ERROR);
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
     * @throws \Exception\DouYin\DouYinException
     */
    public function setClientSecret(string $clientSecret)
    {
        if (ctype_alnum($clientSecret)) {
            $this->clientSecret = $clientSecret;
        } else {
            throw new DouYinException('应用密钥不合法', ErrorCode::PRINT_PARAM_ERROR);
        }
    }

    /**
     * @return string
     */
    public function getRedirectUri() : string
    {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     * @throws \Exception\DouYin\DouYinException
     */
    public function setRedirectUri(string $redirectUri)
    {
        if (preg_match('/^(http|https)\:\/\/\S+$/', $redirectUri) == 0) {
            throw new DouYinException('回调地址不合法', ErrorCode::PRINT_PARAM_ERROR);
        } else {
            $this->redirectUri = $redirectUri;
        }
    }
}