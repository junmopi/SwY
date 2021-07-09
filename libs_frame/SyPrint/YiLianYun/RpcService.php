<?php

namespace SyPrint\YiLianYun;


use SyPrint\ConfigYly;

class RpcService{

    protected $client;

    public function __construct($token, ConfigYly $config)
    {
        $this->client = new YlyRpcClient($token, $clientId = '', $action = '', $params = []);
    }
}