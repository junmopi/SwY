<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/2/27
 * Time: 9:32
 */

namespace DesignPatterns\Singletons;

use Constant\ErrorCode;
use Exception\SyPrint\YlyException;
use SyPrint\ConfigYly;
use Tool\Tool;
use Traits\SingletonTrait;

class PrintYlyConfigSingleton
{
    use SingletonTrait;

    /**
     * 易联云配置列表
     * @var array
     */
    private $YlyConfigs = null;

    private function __construct()
    {
        $configs = Tool::getConfig('print.' . SY_ENV . SY_PROJECT);

        $ylyConfig = new ConfigYly();
        $ylyConfig->setClientId((string)Tool::getArrayVal($configs, 'yly.client.id', '', true));
        $ylyConfig->setClientSecret((string)Tool::getArrayVal($configs, 'yly.client.secret', '', true));
        $this->YlyConfigs[$ylyConfig->getClientId()] = $ylyConfig;
    }

    /**
     * @return \DesignPatterns\Singletons\PrintYlyConfigSingleton
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $clintId
     */
    public function removeYlyConfig(string $clintId)
    {
        unset($this->YlyConfigs[$clintId]);
    }

    /**
     * @param string $clintId
     * @return \SyPrint\ConfigYly
     * @throws \Exception\SyPrint\YlyException
     */
    public function getYlyConfig(string $clintId)
    {
        if (isset($this->YlyConfigs[$clintId])) {
            return $this->YlyConfigs[$clintId];
        } else {
            throw new YlyException('易联云配置不存在', ErrorCode::PRINT_PARAM_ERROR);
        }
    }
}