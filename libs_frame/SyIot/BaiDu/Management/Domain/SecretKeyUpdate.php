<?php
/**
 * 更新权限组密钥
 * User: 姜伟
 * Date: 2019/7/18 0018
 * Time: 23:59
 */
namespace SyIot\BaiDu\Management\Domain;

use Constant\ErrorCode;
use Exception\Iot\BaiDuIotException;
use SyIot\IotBaseBaiDu;
use SyIot\IotUtilBaiDu;

class SecretKeyUpdate extends IotBaseBaiDu
{
    /**
     * 权限组名称
     * @var string
     */
    private $domainName = '';

    public function __construct()
    {
        parent::__construct();
    }

    private function __clone()
    {
    }

    /**
     * @param string $domainName
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setDomainName(string $domainName)
    {
        if (ctype_alnum($domainName)) {
            $this->domainName = $domainName;
            $this->serviceUri = '/v3/iot/management/domain/' . $domainName;
        } else {
            throw new BaiDuIotException('权限组名称不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        if (strlen($this->domainName) == 0) {
            throw new BaiDuIotException('权限组名称不能为空', ErrorCode::IOT_PARAM_ERROR);
        }

        $this->reqHeader['Authorization'] = IotUtilBaiDu::createSign([
            'req_method' => self::REQ_METHOD_PUT,
            'req_uri' => $this->serviceUri,
            'req_params' => [
                'updateSecretKey' => '',
            ],
            'req_headers' => [
                'host',
            ],
        ]);
        $this->curlConfigs[CURLOPT_URL] = $this->serviceProtocol . '://' . $this->serviceDomain . $this->serviceUri . '?updateSecretKey';
        $this->curlConfigs[CURLOPT_CUSTOMREQUEST] = self::REQ_METHOD_PUT;
        return $this->getContent();
    }
}
