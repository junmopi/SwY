<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-04-16
 * Time: 2:25
 */
namespace Exception\TaoBao\AliDaYu;

use Exception\BaseException;

class SmsException extends BaseException
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
        $this->tipName = '阿里大于短信异常';
    }
}
