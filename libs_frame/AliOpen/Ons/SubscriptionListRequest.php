<?php
namespace AliOpen\Ons;

use AliOpen\Core\RpcAcsRequest;

class SubscriptionListRequest extends RpcAcsRequest
{
    private $preventCache;
    private $onsRegionId;
    private $onsPlatform;

    public function __construct()
    {
        parent::__construct('Ons', '2017-09-18', 'OnsSubscriptionList');
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

    public function getOnsPlatform()
    {
        return $this->onsPlatform;
    }

    public function setOnsPlatform($onsPlatform)
    {
        $this->onsPlatform = $onsPlatform;
        $this->queryParameters['OnsPlatform'] = $onsPlatform;
    }
}
