<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 11:32
 */

namespace DesignPatterns\Singletons;

use Constant\ErrorCode;
use DouYin\DouYinConfig;
use Exception\DouYin\DouYinException;
use Tool\Tool;
use Traits\SingletonTrait;

class DouYinConfigSingleton {
    use SingletonTrait;

    /**
     * 抖音配置列表
     * @var array
     */
    private $douYinConfigs = null;

    private function __construct()
    {
        $configs = Tool::getConfig('douyin.' . SY_ENV . SY_PROJECT);

        $dyConfigs = new DouYinConfig();
        $dyConfigs->setClientKey((string)Tool::getArrayVal($configs, 'douyin.client.key', '', true));
        $dyConfigs->setClientSecret((string)Tool::getArrayVal($configs, 'douyin.client.secret', '', true));
        $dyConfigs->setRedirectUri((string)Tool::getArrayVal($configs, 'douyin.client.redirect', '', true));
        $this->douYinConfigs = $dyConfigs;
    }

    /**
     * @return \DesignPatterns\Singletons\DouYinConfigSingleton
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
    public function removeConfig()
    {
        unset($this->douYinConfigs);
    }

    /**
     * @param string $clintKey
     * @return
     * @throws \Exception\DouYin\DouYinException
     */
    public function getConfig()
    {
        if (!empty($this->douYinConfigs)) {
            return $this->douYinConfigs;
        } else {
            throw new DouYinException('抖音配置不存在', ErrorCode::PRINT_PARAM_ERROR);
        }
    }
}