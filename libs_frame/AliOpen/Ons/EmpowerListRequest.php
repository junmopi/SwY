<?php
namespace AliOpen\Ons;

use AliOpen\Core\RpcAcsRequest;

class EmpowerListRequest extends RpcAcsRequest
{
    private $preventCache;
    private $onsRegionId;
    private $onsPlatform;
    private $empowerUser;
    private $topic;

    public function __construct()
    {
        parent::__construct('Ons', '2017-09-18', 'OnsEmpowerList');
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

    public function getEmpowerUser()
    {
        return $this->empowerUser;
    }

    public function setEmpowerUser($empowerUser)
    {
        $this->empowerUser = $empowerUser;
        $this->queryParameters['EmpowerUser'] = $empowerUser;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;
        $this->queryParameters['Topic'] = $topic;
    }
}
