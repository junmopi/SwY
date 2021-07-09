<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/6/19
 * Time: 14:29
 */

namespace Wx\Live;

use Constant\ErrorCode;
use Exception\Wx\WxException;
use Log\Log;
use Tool\Tool;
use Wx\WxBaseMini;
use Wx\WxUtilBase;
use Wx\WxUtilBaseAlone;
use Wx\WxUtilOpenBase;

class GoodsResetAudit extends WxBaseMini {
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 商品ID
     * @var int
     */
    private $goodsId = 0;
    /**
     * 审核单ID
     * @var int
     */
    private $auditId = 0;
    /**
     * 平台类型
     * @var string
     */
    private $platType = '';

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/resetaudit?access_token=';
        $this->appId = $appId;
        $this->platType = WxUtilBase::PLAT_TYPE_MINI;
    }

    public function __clone()
    {
    }

    /**
     * 设置商品ID
     * @param int $goodsId
     */
    public function setGoodsId(int $goodsId)
    {
        if ($goodsId > 0) {
            $this->reqData['goodsId'] = $goodsId;
        } else {
            throw new WxException('商品ID不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * 审核单ID
     * @param int $auditId
     */
    public function setAuditId(int $auditId)
    {
        if ($auditId > 0) {
            $this->reqData['auditId'] = $auditId;
        } else {
            throw new WxException('审核单ID不能为空', ErrorCode::WX_PARAM_ERROR);
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
        $resArr = [
            'code' => 0
        ];

        if ($this->platType == WxUtilBase::PLAT_TYPE_MINI) {
            $this->curlConfigs[CURLOPT_URL] = $this->serviceUrl . WxUtilBaseAlone::getAccessToken($this->appId);
        } else {
            $this->curlConfigs[CURLOPT_URL] = $this->serviceUrl . WxUtilOpenBase::getAuthorizerAccessToken($this->appId);
        }
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        $this->curlConfigs[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/json',
        ];
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