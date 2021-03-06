<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2018/9/13 0013
 * Time: 7:34
 */
namespace Wx\OpenMini;

use Constant\ErrorCode;
use Exception\Wx\WxOpenException;
use Tool\Tool;
use Wx\WxBaseOpenMini;
use Wx\WxUtilBase;
use Wx\WxUtilOpenBase;

class CodeAudit extends WxBaseOpenMini
{
    /**
     * 应用ID
     * @var string
     */
    private $appId = '';
    /**
     * 审核列表
     * @var array
     */
    private $auditList = [];

    public function __construct(string $appId)
    {
        parent::__construct();
        $this->serviceUrl = 'https://api.weixin.qq.com/wxa/submit_audit?access_token=';
        $this->appId = $appId;
    }

    public function __clone()
    {
    }

    /**
     * @param array $auditList
     * @throws \Exception\Wx\WxOpenException
     */
    public function setAuditList(array $auditList)
    {
        $auditNum = count($auditList);
        if ($auditNum == 0) {
            throw new WxOpenException('审核列表不能为空', ErrorCode::WXOPEN_PARAM_ERROR);
        } elseif ($auditNum > 5) {
            throw new WxOpenException('审核列表数量不能超过5个', ErrorCode::WXOPEN_PARAM_ERROR);
        }

        $this->auditList = $auditList;
    }

    public function getDetail() : array
    {
        $resArr = [
            'code' => 0,
        ];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceUrl . WxUtilOpenBase::getAuthorizerAccessToken($this->appId);
        if (!empty($this->auditList)) {
            $this->reqData['item_list'] = $this->auditList;
            $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        }else{
            $this->curlConfigs[CURLOPT_POSTFIELDS] = (object)[];
        }
        $this->curlConfigs[CURLOPT_SSL_VERIFYPEER] = false;
        $this->curlConfigs[CURLOPT_SSL_VERIFYHOST] = false;
        $sendRes = WxUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['errcode'] == 0) {
            $resArr['data'] = $sendData;
        } else {
            $resArr['code'] = ErrorCode::WXOPEN_POST_ERROR;
            $resArr['message'] = $sendData['errmsg'];
        }

        return $resArr;
    }
}
