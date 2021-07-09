<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/10/20
 * Time: 19:36
 */

namespace Wx;

use Constant\ErrorCode;
use Exception\Wx\WxException;
use Tool\Tool;

class WxBot extends WxBaseMini{
    /**
     * 消息类型
     * @var string
     */
    private $type = '';

    public function __construct()
    {
        parent::__construct();
        $this->serviceUrl = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=ecde8ad8-955f-4520-9d55-0345900686b7';
        $this->type = 'text';
    }

    public function __clone()
    {
    }

    /**
     * @param string $type
     * @throws \Exception\Wx\WxException
     */
    public function setType(string $type)
    {
        if (in_array($type, ['text', 'markdown', 'image', 'news'], true)) {
            $this->type = $type;
            $this->reqData['msgtype'] = $type;
        } else {
            throw new WxException('消息类型不合法', ErrorCode::WX_PARAM_ERROR);
        }
    }

    /**
     * @param string $content
     * @throws \Exception\Wx\WxException
     */
    public function setContent(string $content)
    {
        if (strlen($content) > 0) {
            $this->reqData[$this->type]['content'] = $content;
        } else {
            throw new WxException('发送内容不能为空', ErrorCode::WX_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        $resArr = [
            'code' => 0
        ];
        $this->reqData[$this->type]['mentioned_list'] = ['@all'];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceUrl;
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