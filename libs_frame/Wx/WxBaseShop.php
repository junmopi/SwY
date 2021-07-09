<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2018/9/11 0011
 * Time: 8:52
 */
namespace Wx;

use Constant\ErrorCode;
use DesignPatterns\Singletons\WxConfigSingleton;
use Exception\Wx\WxException;
use Log\Log;
use Tool\Tool;

abstract class WxBaseShop extends WxBase
{
    const MATERIAL_TYPE_IMAGE = 'image';
    const MATERIAL_TYPE_VOICE = 'voice';
    const MATERIAL_TYPE_VIDEO = 'video';
    const MATERIAL_TYPE_THUMB = 'thumb';
    const MESSAGE_TYPE_MPNEWS = 'mpnews';
    const MESSAGE_TYPE_TEXT = 'text';
    const MESSAGE_TYPE_VOICE = 'voice';
    const MESSAGE_TYPE_MUSIC = 'music';
    const MESSAGE_TYPE_IMAGE = 'image';
    const MESSAGE_TYPE_VIDEO = 'video';
    const MESSAGE_TYPE_WXCARD = 'wxcard';
    const MERCHANT_TYPE_SELF = 'self'; //商户类型-自身
    const MERCHANT_TYPE_SUB = 'sub'; //商户类型-子商户,属于服务商下

    protected static $totalMaterialType = [
        self::MATERIAL_TYPE_IMAGE => '图片',
        self::MATERIAL_TYPE_VOICE => '语音',
        self::MATERIAL_TYPE_VIDEO => '视频',
        self::MATERIAL_TYPE_THUMB => '缩略图',
    ];
    protected static $totalMessageType = [
        self::MESSAGE_TYPE_MPNEWS => '图文',
        self::MESSAGE_TYPE_TEXT => '文本',
        self::MESSAGE_TYPE_VOICE => '语音',
        self::MESSAGE_TYPE_MUSIC => '音乐',
        self::MESSAGE_TYPE_IMAGE => '图片',
        self::MESSAGE_TYPE_VIDEO => '视频',
        self::MESSAGE_TYPE_WXCARD => '卡券',
    ];
    protected static $totalMerchantType = [
        self::MERCHANT_TYPE_SELF => '自身',
        self::MERCHANT_TYPE_SUB => '子商户',
    ];

    /**
     * 商户类型
     * @var string
     */
    protected $merchantType = '';

    public function __construct()
    {
        parent::__construct();
        $this->merchantType = self::MERCHANT_TYPE_SELF;
    }

    /**
     * @param \Wx\WxConfigShop $configShop
     * @throws \Exception\Wx\WxException
     */
    protected function setAppIdAndMchId(WxConfigShop $configShop)
    {
        if ($this->merchantType == self::MERCHANT_TYPE_SELF) {
            $this->reqData['appid'] = $configShop->getAppId();
            $this->reqData['mch_id'] = $configShop->getPayMchId();
        } else {
            $spInfo = WxConfigSingleton::getInstance()->getWxSpConfig($configShop->getAppId()); //获取服务商微信系统配置信息
            $merchantAppId = strlen($configShop->getMerchantAppId()) > 0 ? $configShop->getMerchantAppId() : $spInfo['app_id'];    //服务商app_id
            $this->reqData['appid'] = $merchantAppId;   //wxd4f51caa4860fa27,wx83b911a880137623
            $this->reqData['mch_id'] = $spInfo['mchid'];    //1535416151,1580748041
            $this->reqData['sub_appid'] = $configShop->getAppId();
            $this->reqData['sub_mch_id'] = $configShop->getPayMchId();
        }
    }
}
