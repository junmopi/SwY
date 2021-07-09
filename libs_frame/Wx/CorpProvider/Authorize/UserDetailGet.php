<?php
namespace Wx\CorpProvider\Authorize;

use Constant\ErrorCode;
use Exception\Wx\WxCorpProviderException;
use Tool\Tool;
use Wx\WxBaseCorpProvider;
use Wx\WxUtilBase;
use Wx\WxUtilCorpProvider;

/**
 * 获取访问用户敏感信息
 * @package Wx\CorpProvider\Authorize
 */
class UserDetailGet extends WxBaseCorpProvider
{
    /**
     * 成员票据
     * @var string
     */
    private $user_ticket = '';

    public function __construct()
    {
        parent::__construct();
        $this->serviceUrl = 'https://qyapi.weixin.qq.com/cgi-bin/service/getuserdetail3rd?access_token=';
    }

    private function __clone()
    {
    }

    /**
     * @param string $userTicket
     * @throws \Exception\Wx\WxCorpProviderException
     */
    public function setUserTicket(string $userTicket)
    {
        if (strlen($userTicket) > 0) {
            $this->reqData['user_ticket'] = $userTicket;
        } else {
            throw new WxCorpProviderException('成员票据不合法', ErrorCode::WXPROVIDER_CORP_PARAM_ERROR);
        }
    }

    public function getDetail(): array
    {
        if (!isset($this->reqData['user_ticket'])) {
            throw new WxCorpProviderException('成员票据不能为空', ErrorCode::WXPROVIDER_CORP_PARAM_ERROR);
        }

        $resArr = [
            'code' => 0,
        ];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceUrl . '?' . WxUtilCorpProvider::getSuiteToken();
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        $sendRes = WxUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['errcode'] == 0) {
            $resArr['data'] = $sendData;
        } else {
            $resArr['code'] = ErrorCode::WXPROVIDER_CORP_POST_ERROR;
            $resArr['message'] = $sendData['errmsg'];
        }

        return $resArr;
    }
}
