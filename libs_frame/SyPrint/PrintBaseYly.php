<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/1/14
 * Time: 14:57
 */

namespace SyPrint;

abstract class PrintBaseYly extends PrintBase{
    /**
     * 服务域名
     * @var string
     */
    protected $serviceDomain = '';

    public function __construct()
    {
        parent::__construct();
        $this->serviceDomain = 'https://open-api.10ss.net';
    }
}