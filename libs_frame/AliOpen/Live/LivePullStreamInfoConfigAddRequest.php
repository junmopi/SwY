<?php
namespace AliOpen\Live;

use AliOpen\Core\RpcAcsRequest;

class LivePullStreamInfoConfigAddRequest extends RpcAcsRequest
{
    private $sourceUrl;
    private $appName;
    private $securityToken;
    private $domainName;
    private $endTime;
    private $startTime;
    private $ownerId;
    private $streamName;

    public function __construct()
    {
        parent::__construct('live', '2016-11-01', 'AddLivePullStreamInfoConfig', 'live', 'openAPI');
        $this->setMethod('POST');
    }

    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;
        $this->queryParameters['SourceUrl'] = $sourceUrl;
    }

    public function getAppName()
    {
        return $this->appName;
    }

    public function setAppName($appName)
    {
        $this->appName = $appName;
        $this->queryParameters['AppName'] = $appName;
    }

    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    public function setSecurityToken($securityToken)
    {
        $this->securityToken = $securityToken;
        $this->queryParameters['SecurityToken'] = $securityToken;
    }

    public function getDomainName()
    {
        return $this->domainName;
    }

    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
        $this->queryParameters['DomainName'] = $domainName;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
        $this->queryParameters['EndTime'] = $endTime;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
        $this->queryParameters['StartTime'] = $startTime;
    }

    public function getOwnerId()
    {
        return $this->ownerId;
    }

    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        $this->queryParameters['OwnerId'] = $ownerId;
    }

    public function getStreamName()
    {
        return $this->streamName;
    }

    public function setStreamName($streamName)
    {
        $this->streamName = $streamName;
        $this->queryParameters['StreamName'] = $streamName;
    }
}
