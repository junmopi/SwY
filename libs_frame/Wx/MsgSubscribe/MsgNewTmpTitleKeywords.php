<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2019/12/20 0012
 * Time: 18:00
 */
namespace Wx\MsgSubscribe;

use Constant\ErrorCode;
use Exception\Wx\WxException;
use Tool\Tool;
use Wx\WxBaseMini;
use Wx\WxUtilBase;
use Wx\WxUtilBaseAlone;
use Wx\WxUtilOpenBase;

class MsgNewTmpTitleKeywords extends WxBaseMini
{
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 模板标题id
     * @var string
     */
    private $titleId = '';
    /**
     * 平台类型
     * @var string
     */
    private $platType = '';

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatekeywords';
        $this->appId = $appId;
    }

    public function __clone()
    {
    }

    /**
     * @param string $titleId
     * @throws \Exception\Wx\WxException
     */
    public function setTitleId(string $titleId)
    {
        if (strlen($titleId) > 0) {
            $this->reqData['tid'] = $titleId;
        } else {
            throw new WxException('模板标题id不合法', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * @param string $platType
     * @throws \Exception\Wx\WxException
     */
    public function setPlatType(string $platType)
    {
        if (in_array($platType, [WxUtilBase::PLAT_TYPE_MINI, WxUtilBase::PLAT_TYPE_OPEN_MINI], true)) {
            $this->platType = $platType;
        } else {
            throw new WxException('平台类型不合法', ErrorCode::WX_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        if (!isset($this->reqData['tid'])) {
            throw new WxException('模板标题id不能为空', ErrorCode::WX_PARAM_ERROR);
        }

        $resArr = [
            'code' => 0
        ];

        if ($this->platType == WxUtilBase::PLAT_TYPE_MINI) {
            $this->reqData['access_token'] = WxUtilBaseAlone::getAccessToken($this->appId);
        } else {
            $this->reqData['access_token'] = WxUtilOpenBase::getAuthorizerAccessToken($this->appId);
        }
        $this->curlConfigs[CURLOPT_URL] = $this->serviceUrl . '?' . http_build_query($this->reqData);
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
