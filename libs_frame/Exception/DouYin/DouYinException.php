<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 11:17
 */
namespace Exception\DouYin;

use Exception\BaseException;

class DouYinException extends BaseException {
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
        $this->tipName = '抖音异常';
    }
}