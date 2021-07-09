<?php
namespace AliOpen\Live;

use AliOpen\Core\RpcAcsRequest;

class LiveStreamCountDescribeRequest extends RpcAcsRequest
{
    private $domainName;
    private $ownerId;

    public function __construct()
    {
        parent::__construct('live', '2016-11-01', 'DescribeLiveStreamCount', 'live', 'openAPI');
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

    public function getOwnerId()
    {
        return $this->ownerId;
    }

    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        $this->queryParameters['OwnerId'] = $ownerId;
    }
}
