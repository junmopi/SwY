<?php
/**
 * 获取指定的policy信息
 * User: 姜伟
 * Date: 2019/7/17 0017
 * Time: 17:14
 */
namespace SyIot\BaiDu\Policy;

use Constant\ErrorCode;
use Exception\Iot\BaiDuIotException;
use SyIot\IotBaseBaiDu;
use SyIot\IotUtilBaiDu;

class PolicyInfo extends IotBaseBaiDu
{
    /**
     * endpoint名称
     * @var string
     */
    private $endpointName = '';
    /**
     * policy名称
     * @var string
     */
    private $policyName = '';

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
        } else {
            throw new BaiDuIotException('endpoint名称不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param string $policyName
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setPolicyName(string $policyName)
    {
        if (ctype_alnum($policyName)) {
            $this->policyName = $policyName;
        } else {
            throw new BaiDuIotException('policy名称不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        if (strlen($this->endpointName) == 0) {
            throw new BaiDuIotException('endpoint名称不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        if (strlen($this->policyName) == 0) {
            throw new BaiDuIotException('policy名称不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        $this->serviceUri = '/v1/endpoint/' . $this->endpointName . '/policy/' . $this->policyName;

        $this->reqHeader['Authorization'] = IotUtilBaiDu::createSign([
            'req_method' => self::REQ_METHOD_GET,
            'req_uri' => $this->serviceUri,
            'req_params' => [],
            'req_headers' => [
                'host',
            ],
        ]);
        $this->curlConfigs[CURLOPT_URL] = $this->serviceProtocol . '://' . $this->serviceDomain . $this->serviceUri;
        return $this->getContent();
    }
}
