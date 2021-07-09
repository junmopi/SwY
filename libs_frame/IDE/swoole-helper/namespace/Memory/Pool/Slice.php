<?php
namespace Swoole\Memory\Pool;

/**
 * @since 4.2.12
 */
class Slice
{
    /**
     * @return mixed
     */
    public function __destruct()
    {
    }

    /**
     * @param $size[optional]
     * @param $offset[optional]
     * @return mixed
     */
    public function read($size = null, $offset = null)
    {
    }

    /**
     * @param $data[required]
     * @param $offset[optional]
     * @return mixed
     */
    public function write($data, $offset = null)
    {
    }
}
