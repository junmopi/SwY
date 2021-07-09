<?php
namespace SyMessagePush\Ali;

use AliOpen\Core\RpcAcsRequest;

class QueryAliasesRequest extends RpcAcsRequest
{
    private $appKey;
    private $deviceId;

    public function __construct()
    {
        parent::__construct('Push', '2016-08-01', 'QueryAliases');
        $this->setMethod('POST');
    }

    public function getAppKey()
    {
        return $this->appKey;
    }

    public function setAppKey($appKey)
    {
        $this->appKey = $appKey;
        $this->queryParameters['AppKey'] = $appKey;
    }

    public function getDeviceId()
    {
        return $this->deviceId;
    }

    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
        $this->queryParameters['DeviceId'] = $deviceId;
    }
}
