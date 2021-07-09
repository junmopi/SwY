<?php
namespace AliOpen\Ons;

use AliOpen\Core\RpcAcsRequest;

class SubscriptionUpdateRequest extends RpcAcsRequest
{
    private $preventCache;
    private $onsRegionId;
    private $readEnable;
    private $onsPlatform;
    private $consumerId;

    public function __construct()
    {
        parent::__construct('Ons', '2017-09-18', 'OnsSubscriptionUpdate');
        $this->setMethod('POST');
    }

    public function getPreventCache()
    {
        return $this->preventCache;
    }

    public function setPreventCache($preventCache)
    {
        $this->preventCache = $preventCache;
        $this->queryParameters['PreventCache'] = $preventCache;
    }

    public function getOnsRegionId()
    {
        return $this->onsRegionId;
    }

    public function setOnsRegionId($onsRegionId)
    {
        $this->onsRegionId = $onsRegionId;
        $this->queryParameters['OnsRegionId'] = $onsRegionId;
    }

    public function getReadEnable()
    {
        return $this->readEnable;
    }

    public function setReadEnable($readEnable)
    {
        $this->readEnable = $readEnable;
        $this->queryParameters['ReadEnable'] = $readEnable;
    }

    public function getOnsPlatform()
    {
        return $this->onsPlatform;
    }

    public function setOnsPlatform($onsPlatform)
    {
        $this->onsPlatform = $onsPlatform;
        $this->queryParameters['OnsPlatform'] = $onsPlatform;
    }

    public function getConsumerId()
    {
        return $this->consumerId;
    }

    public function setConsumerId($consumerId)
    {
        $this->consumerId = $consumerId;
        $this->queryParameters['ConsumerId'] = $consumerId;
    }
}
