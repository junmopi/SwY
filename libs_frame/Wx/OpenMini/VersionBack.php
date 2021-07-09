<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/5/22
 * Time: 15:06
 */

namespace Wx\OpenMini;

use Tool\Tool;
use Wx\WxBaseOpenMini;
use Wx\WxUtilBase;
use Wx\WxUtilOpenBase;

/**
 * 小程序版本回退
 * @package Wx\OpenMini
 */
class VersionBack extends WxBaseOpenMini {
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxa/revertcoderelease?access_token=';
        $this->appId = $appId;
    }

    public function __clone()
    {
    }

    public function getDetail() : array
    {
        $resArr = [
            'code' => 0,
        ];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceUrl . WxUtilOpenBase::getAuthorizerAccessToken($this->appId);
        $sendRes = WxUtilBase::sendGetReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['errcode'] == 0) {
            $resArr['data'] = $sendData;
        } else {
            $resArr['code'] = $sendData['errcode'];
            $resArr['message'] = $sendData['errmsg'];
        }

        return $resArr;
    }
}