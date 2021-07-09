<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/7/10
 * Time: 11:32
 */
namespace Exception\Es;

use Exception\BaseException;

class EsException extends BaseException
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
        $this->tipName = 'Es异常';
    }
}