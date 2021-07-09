<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 15:10
 */
namespace DouYin\OpenApi;

use Constant\ErrorCode;
use DesignPatterns\Singletons\DouYinConfigSingleton;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class SilentAuth extends DouYinBaseOpen {
    public function __construct()
    {
        parent::__construct();
        $config = DouYinConfigSingleton::getInstance()->getConfig();
        $this->reqData['client_key'] = $config->getClientKey();                 //应用唯一标识
        $this->reqData['scope'] = 'login_id';                                   //应用授权作用域
        $this->reqData['response_type'] = 'code';                               //填写code
        $this->reqData['redirect_uri'] = $config->getRedirectUri();    //授权成功后的回调地址，必须以http/https开头。域名必须对应申请应用时填写的域名，如不清楚请联系应用申请人。
        $this->reqData['state'] = '1';                              //用于保持请求和回调的状态
    }

    private function __clone()
    {
    }

    public function getDetail() : array
    {
        $url = $this->serviceDomain . '/oauth/authorize/v2?' . http_build_query($this->reqData);

        return [
            'url' => $url,
        ];
    }
}