<?php
/**
 * 获取所有MQTT客户端在线状态
 * User: 姜伟
 * Date: 2019/7/18 0018
 * Time: 11:57
 */
namespace SyIot\BaiDu\Client;

use Constant\ErrorCode;
use Exception\Iot\BaiDuIotException;
use SyIot\IotBaseBaiDu;
use SyIot\IotUtilBaiDu;
use Tool\Tool;

class StatusQueryBatch extends IotBaseBaiDu
{
    /**
     * endpoint名称
     * @var string
     */
    private $endpointName = '';
    /**
     * 客户端ID列表
     * @var array
     */
    private $mqttID = [];

    public function __construct()
    {
        parent::__construct();
    }

    private function __clone()
    {
    }

    /**
     * @param string $endpointName
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setEndpointName(string $endpointName)
    {
        if (ctype_alnum($endpointName)) {
            $this->endpointName = $endpointName;
            $this->serviceUri = '/v2/endpoint/' . $endpointName . '/batch-client-query/status';
        } else {
            throw new BaiDuIotException('endpoint名称不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    public function setClientIdList(array $clientIdList)
    {
        if (empty($clientIdList)) {
            throw new BaiDuIotException('客户端ID列表不能为空', ErrorCode::IOT_PARAM_ERROR);
        }

        foreach ($clientIdList as $eClientId) {
            if (is_string($eClientId) && (strlen($eClientId) > 0)) {
                $this->mqttID[$eClientId] = 1;
            }
        }
    }

    public function getDetail() : array
    {
        if (strlen($this->endpointName) == 0) {
            throw new BaiDuIotException('endpoint名称不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        if (empty($this->mqttID)) {
            throw new BaiDuIotException('客户端ID不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        $this->reqData['mqttID'] = array_keys($this->mqttID);

        $this->reqHeader['Authorization'] = IotUtilBaiDu::createSign([
            'req_method' => self::REQ_METHOD_POST,
            'req_uri' => $this->serviceUri,
            'req_params' => [],
            'req_headers' => [
                'host',
            ],
        ]);
        $this->curlConfigs[CURLOPT_URL] = $this->serviceProtocol . '://' . $this->serviceDomain . $this->serviceUri;
        $this->curlConfigs[CURLOPT_POST] = true;
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        return $this->getContent();
    }
}
