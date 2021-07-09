<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/6/19
 * Time: 10:05
 */
namespace Wx\Live;

use Constant\ErrorCode;
use Exception\Wx\WxException;
use Tool\Tool;
use Wx\WxBaseMini;
use Wx\WxUtilBase;
use Wx\WxUtilBaseAlone;
use Wx\WxUtilOpenBase;

class LiveCreate extends WxBaseMini {
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 直播间名称
     * @var string
     */
    private $name = '';
    /**
     * 背景图mediaID
     * @var string
     */
    private $coverImg = '';
    /**
     * 直播计划开始时间戳
     * @var int
     */
    private $startTime = '';
    /**
     * 直播计划结束时间戳
     * @var int
     */
    private $endTime = '';
    /**
     * 主播昵称
     * @var string
     */
    private $anchorName = '';
    /**
     * 主播微信号
     * @var string
     */
    private $anchorWechat = '';
    /**
     * 分享图mediaID
     * @var string
     */
    private $shareImg = '';
    /**
     * 直播间类型
     * @var int
     */
    private $type = '';
    /**
     * 横屏、竖屏
     * @var int
     */
    private $screenType = '';
    /**
     * 	是否关闭点赞
     * @var int
     */
    private $closeLike = '';
    /**
     * 是否关闭货架
     * @var int
     */
    private $closeGoods = '';
    /**
     * 是否关闭评论
     * @var int
     */
    private $closeComment = '';
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
     * @param string $name
     * @throws \Exception\Wx\WxException
     */
    public function setName(string $name)
    {
        if (strlen($name) > 0) {
            $this->reqData['name'] = $name;
        } else {
            throw new WxException('直播间名字不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * @param string $coverImg
     * @throws \Exception\Wx\WxException
     */
    public function setCoverImg(string $coverImg)
    {
        if (strlen($coverImg) > 0) {
            $this->reqData['coverImg'] = $coverImg;
        } else {
            throw new WxException('背景图不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * @param int $startTime
     * @param int $endTime
     * @throws \Exception\Wx\WxException
     */
    public function setTime(int $startTime, int $endTime)
    {
        if ($startTime == 0) {
            throw new WxException('直播开始时间不能为空', ErrorCode::WX_PARAM_ERROR);
        } elseif($endTime == 0) {
            throw new WxException('直播结束时间不能为空', ErrorCode::WX_PARAM_ERROR);
        }elseif($startTime > $endTime){
            throw new WxException('开始时间不能大于结束时间', ErrorCode::WX_PARAM_ERROR);
        }
        $this->reqData['startTime'] = $startTime;
        $this->reqData['endTime'] = $endTime;
    }

    /**
     * @param string $anchorName
     * @throws \Exception\Wx\WxException
     */
    public function setAnchorName(string $anchorName)
    {
        if (strlen($anchorName) > 0) {
            $this->reqData['anchorName'] = $anchorName;
        } else {
            throw new WxException('主播昵称不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * @param string $anchorWechat
     * @throws \Exception\Wx\WxException
     */
    public function setAnchorWechat(string $anchorWechat)
    {
        if (strlen($anchorWechat) > 0) {
            $this->reqData['anchorWechat'] = $anchorWechat;
        } else {
            throw new WxException('主播微信号不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * @param string $shareImg
     * @throws \Exception\Wx\WxException
     */
    public function setShareImg(string $shareImg)
    {
        if (strlen($shareImg) > 0) {
            $this->reqData['shareImg'] = $shareImg;
        } else {
            throw new WxException('分享图不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * @param array $param
     * @throws \Exception\Wx\WxException
     */
    public function setParam(array $param)
    {
        $this->reqData['type'] = $param['type'];
        $this->reqData['screenType'] = $param['screenType'];
        $this->reqData['closeLike'] = $param['closeLike'];
        $this->reqData['closeGoods'] = $param['closeGoods'];
        $this->reqData['closeComment'] = $param['closeComment'];
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