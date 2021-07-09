<?php
/**
 * Created by PhpStorm.
 * User: jw
 * Date: 17-4-3
 * Time: 下午3:13
 */
namespace DB\Models\NotORM;

abstract class NotORM_Abstract
{
    protected $connection;
    protected $driver;
    protected $structure;
    protected $cache;
    protected $notORM;
    protected $table;
    protected $primary;
    protected $rows;
    protected $referenced = [];

    protected $debug = false;
    protected $debugTimer;
    protected $freeze = false;
    protected $rowClass = '\DB\Models\NotORM\NotORM_Row';
    protected $jsonAsArray = false;

    protected function access($key, $delete = false)
    {
    }
}
