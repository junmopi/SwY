<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2019/12/20 0012
 * Time: 17:45
 */
namespace Wx\MsgSubscribe;

use Constant\ErrorCode;
use Exception\Wx\WxException;
use Tool\Tool;
use Wx\WxBaseMini;
use Wx\WxUtilBase;
use Wx\WxUtilBaseAlone;
use Wx\WxUtilOpenBase;

class MsgNewTmpTitleList extends WxBaseMini
{
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 类目 id，多个用逗号隔开
     * @var string
     */
    private $ids = '';
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
     * access_token
     * @var string
     */
    private $access_token = '';
    /**
     * 平台类型
     * @var string
     */
    private $platType = '';

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatetitles';
        $this->reqData['access_token'] = '';
        $this->reqData['start'] = 0;
        $this->reqData['limit'] = 30;
        $this->reqData['ids'] = '';
        $this->appId = $appId;
        $this->platType = WxUtilBase::PLAT_TYPE_MINI;
    }

    public function __clone()
    {
    }

    /**
     * 设置类目id组
     * @param string $ids
     */
    public function setCategoryIds(string $ids)
    {
        $idsArr = explode(',', $ids);
        if(is_array($idsArr)){
            $this->reqData['ids'] = $ids;
        } else {
            throw new WxException('类目id组不合法', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * 设置范围
     * @param int $page
     * @param int $limit
     */
    public function setRange(int $page, int $limit)
    {
        $truePage = $page > 0 ? $page : 1;
        $this->reqData['limit'] = ($limit > 0) && ($limit <= 20) ? $limit : 20;
        $this->reqData['start'] = ($truePage - 1) * $this->reqData['start'];
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
        if(strlen($this->reqData['ids']) == 0){
            throw new WxException('类目id必须设置', ErrorCode::WX_PARAM_ERROR);
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
        if (isset($sendData['data'])) {
            $resArr['data'] = $sendData;
        } else {
            $resArr['code'] = $sendData['errcode'];
            $resArr['message'] = $sendData['errmsg'];
        }

        return $resArr;
    }
}
