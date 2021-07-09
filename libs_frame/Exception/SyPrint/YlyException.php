<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/1/14
 * Time: 15:43
 */

namespace Exception\SyPrint;

use Exception\BaseException;

class YlyException extends BaseException
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
        $this->tipName = '易联云打印异常';
    }
}