<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 11:27
 */
namespace DouYin;

abstract class DouYinBaseOpen extends DouYinBase
{
    /**
     * 服务域名
     * @var string
     */
    protected $serviceDomain = '';

    public function __construct()
    {
        parent::__construct();
        $this->serviceDomain = 'https://open.douyin.com';
    }
}