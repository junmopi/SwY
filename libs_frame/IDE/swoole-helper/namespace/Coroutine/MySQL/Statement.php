<?php
namespace Swoole\Coroutine\MySQL;

/**
 * @since 4.2.12
 */
class Statement
{
    /**
     * @return mixed
     */
    public function __destruct()
    {
    }

    /**
     * @param $params[optional]
     * @param $timeout[optional]
     * @return mixed
     */
    public function execute($params = null, $timeout = null)
    {
    }

    /**
     * @return mixed
     */
    public function fetch()
    {
    }

    /**
     * @return mixed
     */
    public function fetchAll()
    {
    }

    /**
     * @return mixed
     */
    public function nextResult()
    {
    }
}
