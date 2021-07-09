<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/6/19
 * Time: 15:04
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

class GoodsList extends WxBaseMini {
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 分页条数起点
     * @var int
     */
    private $offset = 0;
    /**
     * 分页大小，默认30，不超过100
     * @var int
     */
    private $limit = 0;
    /**
     * 商品状态，0：未审核。1：审核中，2：审核通过，3：审核驳回
     * @var int
     */
    private $status = 0;
    /**
     * 平台类型
     * @var string
     */
    private $platType = '';

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved';
        $this->appId = $appId;
        $this->platType = WxUtilBase::PLAT_TYPE_MINI;
    }

    public function __clone()
    {
    }

    /**
     * 设置范围
     * @param int $offset
     * @param int $limit
     */
    public function setRange(int $offset, int $limit)
    {
        $this->reqData['limit'] = ($limit > 0) && ($limit <= 100) ? $limit : 30;
        $this->reqData['offset'] = ($offset - 1) * $limit;
    }

    /**
     * 设置状态
     * @param int $status
     * @throws \Exception\Wx\WxException
     */
    public function setStatus(int $status)
    {
        if ($status >= 0) {
            $this->reqData['status'] = $status;
        } else {
            throw new WxException('状态不合法', ErrorCode::WX_PARAM_ERROR);
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