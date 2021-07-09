<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/6/19
 * Time: 11:54
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

class GoodsAdd extends WxBaseMini {
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 背景图mediaID
     * @var string
     */
    private $coverImgUrl = '';
    /**
     * 商品名称
     * @var string
     */
    private $name = '';
    /**
     * 价格类型
     * @var int
     */
    private $priceType = 0;
    /**
     * 价格
     * @var int
     */
    private $price = 0;
    /**
     * 价格2
     * @var int
     */
    private $price2 = 0;
    /**
     * 小程序路径
     * @var string
     */
    private $url = '';
    /**
     * 平台类型
     * @var string
     */
    private $platType = '';

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxaapi/broadcast/goods/add?access_token=';
        $this->appId = $appId;
        $this->platType = WxUtilBase::PLAT_TYPE_MINI;
    }

    public function __clone()
    {
    }

    /**
     * @param string $name
     * @throws \Exception\Wx\WxException
     */
    public function setName(string $name)
    {
        if (strlen($name) > 0) {
            $this->reqData['goodsInfo']['name'] = $name;
        } else {
            throw new WxException('商品名称不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * @param string $coverImgUrl
     * @throws \Exception\Wx\WxException
     */
    public function setCoverImg(string $coverImgUrl)
    {
        if (strlen($coverImgUrl) > 0) {
            $this->reqData['goodsInfo']['coverImgUrl'] = $coverImgUrl;
        } else {
            throw new WxException('背景图不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * 设置价格类型
     * @param int $priceType
     * @throws \Exception\Wx\WxException
     */
    public function setPriceType(int $priceType)
    {
        if ($priceType > 0) {
            $this->reqData['goodsInfo']['priceType'] = $priceType;
        } else {
            throw new WxException('价格类型不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * 设置价格
     * @param int $price
     * @param int $price2
     * @throws \Exception\Wx\WxException
     */
    public function setPrice(int $price, int $price2)
    {
        if ($this->reqData['goodsInfo']['priceType'] == 1) {
            if($price <= 0){
                throw new WxException('价格不能为空', ErrorCode::WX_PARAM_ERROR);
            }
            $this->reqData['goodsInfo']['price'] = $price * 10 / 1000;
        } elseif ($this->reqData['goodsInfo']['priceType'] == 2) {
            if($price <= 0){
                throw new WxException('左边界价格不能为空', ErrorCode::WX_PARAM_ERROR);
            } elseif ($price2 <= 0){
                throw new WxException('右边界价格不能为空', ErrorCode::WX_PARAM_ERROR);
            } elseif ($price >= $price2){
                throw new WxException('右边界价格必须大于左边界价格', ErrorCode::WX_PARAM_ERROR);
            }
            $this->reqData['goodsInfo']['price'] = $price * 10 / 1000;
            $this->reqData['goodsInfo']['price2'] = $price2 * 10 / 1000;
        } elseif ($this->reqData['goodsInfo']['priceType'] == 3) {
            if($price <= 0){
                throw new WxException('原价不能为空', ErrorCode::WX_PARAM_ERROR);
            } elseif ($price2 <= 0){
                throw new WxException('现价不能为空', ErrorCode::WX_PARAM_ERROR);
            } elseif ($price < $price2){
                throw new WxException('现价不能小于原价', ErrorCode::WX_PARAM_ERROR);
            }
            $this->reqData['goodsInfo']['price'] = $price * 10 / 1000;
            $this->reqData['goodsInfo']['price2'] = $price2 * 10 / 1000;
        }
    }

    /**
     * @param string $url
     * @throws \Exception\Wx\WxException
     */
    public function setUrl(string $url)
    {
        if (strlen($url) > 0) {
            $this->reqData['goodsInfo']['url'] = $url;
        } else {
            throw new WxException('小程序路径不能为空', ErrorCode::WX_PARAM_ERROR);
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