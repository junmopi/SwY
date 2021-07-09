<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2018/9/8 0008
 * Time: 16:07
 */
namespace DesignPatterns\Singletons;

use TaoBao\ConfigDaYu;
use Tool\Tool;
use Traits\SingletonTrait;

class TaoBaoSingleton
{
    use SingletonTrait;

    /**
     * 大鱼配置
     * @var \TaoBao\ConfigDaYu
     */
    private $dayuConfig = null;

    private function __construct()
    {
        $configs = Tool::getConfig('taobao.' . SY_ENV . SY_PROJECT);

        //设置大鱼配置
        $dayuConfig = new ConfigDaYu();
        $dayuConfig->setAppKey((string)Tool::getArrayVal($configs, 'alidayu.app.key', '', true));
        $dayuConfig->setAppSecret((string)Tool::getArrayVal($configs, 'alidayu.app.secret', '', true));
        $this->dayuConfig = $dayuConfig;
    }

    /**
     * @return \DesignPatterns\Singletons\TaoBaoSingleton
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return \TaoBao\ConfigDaYu
     */
    public function getDayuConfig()
    {
        return $this->dayuConfig;
    }
}
