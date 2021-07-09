<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/6/17
 * Time: 18:33
 */

namespace Wx\OpenMini;

use Constant\ErrorCode;
use Exception\Wx\WxOpenException;
use Tool\Tool;
use Wx\WxBaseOpenMini;
use Wx\WxUtilBase;
use Wx\WxUtilOpenBase;

class BindTester extends WxBaseOpenMini {
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 微信号
     * @var string
     */
    private $wechatid = '';

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxa/bind_tester?access_token=';
        $this->appId = $appId;
    }

    public function __clone()
    {
    }

    /**
     * @param string $wechatid
     * @throws \Exception\Wx\WxOpenException
     */
    public function setWechatId(string $wechatid)
    {
        if (strlen($wechatid) > 0) {
            $this->reqData['wechatid'] = $wechatid;
        } else {
            throw new WxOpenException('微信号不合法', ErrorCode::WXOPEN_PARAM_ERROR);
        }
    }

    public function getDetail() : array{
        if (!isset($this->reqData['wechatid'])) {
            throw new WxOpenException('微信号不能为空', ErrorCode::WXOPEN_PARAM_ERROR);
        }

        $resArr = [
            'code' => 0,
        ];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceUrl . WxUtilOpenBase::getAuthorizerAccessToken($this->appId);
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        $this->curlConfigs[CURLOPT_SSL_VERIFYPEER] = false;
        $this->curlConfigs[CURLOPT_SSL_VERIFYHOST] = false;
        $sendRes = WxUtilBase::sendPostReq($this->curlConfigs);
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