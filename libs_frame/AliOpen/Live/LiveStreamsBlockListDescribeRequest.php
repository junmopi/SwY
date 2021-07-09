<?php
namespace AliOpen\Live;

use AliOpen\Core\RpcAcsRequest;

class LiveStreamsBlockListDescribeRequest extends RpcAcsRequest
{
    private $securityToken;
    private $domainName;
    private $pageSize;
    private $ownerId;
    private $pageNum;

    public function __construct()
    {
        parent::__construct('live', '2016-11-01', 'DescribeLiveStreamsBlockList', 'live', 'openAPI');
        $this->setMethod('POST');
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

    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        $this->queryParameters['PageSize'] = $pageSize;
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

    public function getPageNum()
    {
        return $this->pageNum;
    }

    public function setPageNum($pageNum)
    {
        $this->pageNum = $pageNum;
        $this->queryParameters['PageNum'] = $pageNum;
    }
}
