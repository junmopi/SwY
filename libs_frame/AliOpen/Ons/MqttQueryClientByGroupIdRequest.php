<?php
namespace AliOpen\Ons;

use AliOpen\Core\RpcAcsRequest;

class MqttQueryClientByGroupIdRequest extends RpcAcsRequest
{
    private $preventCache;
    private $onsRegionId;
    private $onsPlatform;
    private $groupId;

    public function __construct()
    {
        parent::__construct('Ons', '2017-09-18', 'OnsMqttQueryClientByGroupId');
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

    public function getGroupId()
    {
        return $this->groupId;
    }

    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        $this->queryParameters['GroupId'] = $groupId;
    }
}
