<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 11:46
 */

namespace DouYin\OpenApi;

use Constant\ErrorCode;
use DesignPatterns\Singletons\DouYinConfigSingleton;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class Authorization extends DouYinBaseOpen {
    private $secret = '';

    public function __construct()
    {
        parent::__construct();
        $config = DouYinConfigSingleton::getInstance()->getConfig();
        $this->secret = $config->getClientSecret();
        $this->reqData['client_key'] = $config->getClientKey();                //应用唯一标识
        $this->reqData['response_type'] = 'code';                 //填写code
        $this->reqData['redirect_uri'] = $config->getRedirectUri();    //授权成功后的回调地址，必须以http/https开头。域名必须对应申请应用时填写的域名，如不清楚请联系应用申请人。
        $this->reqData['state'] = '1';                              //用于保持请求和回调的状态
    }

    private function __clone()
    {
    }

    public function setScope(string $scope){
        $this->reqData['scope'] = $scope;                          //应用授权作用域,多个授权作用域以英文逗号（,）分隔
    }

    public function getDetail() : array
    {
        $url = $this->serviceDomain . '/platform/oauth/connect?' . http_build_query($this->reqData);

        return [
            'url' => $url,
        ];
    }
}