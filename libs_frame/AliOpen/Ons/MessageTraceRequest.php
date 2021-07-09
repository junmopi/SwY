<?php
namespace AliOpen\Ons;

use AliOpen\Core\RpcAcsRequest;

class MessageTraceRequest extends RpcAcsRequest
{
    private $preventCache;
    private $onsRegionId;
    private $onsPlatform;
    private $topic;
    private $msgId;

    public function __construct()
    {
        parent::__construct('Ons', '2017-09-18', 'OnsMessageTrace');
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

    public function getTopic()
    {
        return $this->topic;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;
        $this->queryParameters['Topic'] = $topic;
    }

    public function getMsgId()
    {
        return $this->msgId;
    }

    public function setMsgId($msgId)
    {
        $this->msgId = $msgId;
        $this->queryParameters['MsgId'] = $msgId;
    }
}
