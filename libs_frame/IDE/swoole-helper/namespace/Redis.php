<?php
namespace Swoole;

/**
 * @since 4.2.12
 */
class Redis
{
    const STATE_CONNECT = 0;
    const STATE_READY = 1;
    const STATE_WAIT_RESULT = 2;
    const STATE_SUBSCRIBE = 3;
    const STATE_CLOSED = 4;

    /**
     * @param $setting[optional]
     * @return mixed
     */
    public function __construct($setting = null)
    {
    }

    /**
     * @return mixed
     */
    public function __destruct()
    {
    }

    /**
     * @param $command[required]
     * @param $params[required]
     * @return mixed
     */
    public function __call($command, $params)
    {
    }

    /**
     * @param $event_name[required]
     * @param $callback[required]
     * @return mixed
     */
    public function on($event_name, $callback)
    {
    }

    /**
     * @param $host[required]
     * @param $port[required]
     * @param $callback[required]
     * @return mixed
     */
    public function connect($host, $port, $callback)
    {
    }

    /**
     * @return mixed
     */
    public function close()
    {
    }

    /**
     * @return mixed
     */
    public function getState()
    {
    }
}
