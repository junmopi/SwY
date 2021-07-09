<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/6/19
 * Time: 11:09
 */

namespace Wx\Live;

use Constant\ErrorCode;
use Exception\Wx\WxException;
use Tool\Tool;
use Wx\WxBaseMini;
use Wx\WxUtilBase;
use Wx\WxUtilBaseAlone;
use Wx\WxUtilOpenBase;

class LiveList extends WxBaseMini {
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 开始
     * @var int
     */
    private $start = 0;
    /**
     * 记录数
     * @var int
     */
    private $limit = 0;
    /**
     * 平台类型
     * @var string
     */
    private $platType = '';

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxaapi/broadcast/room/create?access_token=';
        $this->appId = $appId;
        $this->platType = WxUtilBase::PLAT_TYPE_MINI;
    }

    public function __clone()
    {
    }

    /**
     * 设置范围
     * @param int $start
     * @param int $limit
     */
    public function setRange(int $start, int $limit)
    {
        $this->reqData['limit'] = ($limit > 0) && ($limit <= 100) ? $limit : 20;
        $this->reqData['start'] = $start;
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