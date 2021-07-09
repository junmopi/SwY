<?php
/**
 * 钉钉配置单例类
 * User: 姜伟
 * Date: 2017/6/17 0017
 * Time: 11:18
 */
namespace DesignPatterns\Singletons;

use DingDing\TalkConfigProvider;
use Tool\Tool;
use Traits\DingTalkConfigTrait;
use Traits\SingletonTrait;

class DingTalkConfigSingleton
{
    use SingletonTrait;
    use DingTalkConfigTrait;

    /**
     * 企业服务商公共配置
     * @var \DingDing\TalkConfigProvider
     */
    private $corpProviderConfig = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return \DesignPatterns\Singletons\DingTalkConfigSingleton
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 获取企业服务商公共配置
     * @return \DingDing\TalkConfigProvider
     */
    public function getCorpProviderConfig()
    {
        if (is_null($this->corpProviderConfig)) {
            $configs = Tool::getConfig('dingtalk.' . SY_ENV . SY_PROJECT);
            $corpProviderConfig = new TalkConfigProvider();
            $corpProviderConfig->setCorpId((string)Tool::getArrayVal($configs, 'provider.corp.id', '', true));
            $corpProviderConfig->setSsoSecret((string)Tool::getArrayVal($configs, 'provider.sso.secret', '', true));
            $corpProviderConfig->setToken((string)Tool::getArrayVal($configs, 'provider.token', '', true));
            $corpProviderConfig->setAesKey((string)Tool::getArrayVal($configs, 'provider.aeskey', '', true));
            $corpProviderConfig->setSuiteId((int)Tool::getArrayVal($configs, 'provider.suite.id', 0, true));
            $corpProviderConfig->setSuiteKey((string)Tool::getArrayVal($configs, 'provider.suite.key', '', true));
            $corpProviderConfig->setSuiteSecret((string)Tool::getArrayVal($configs, 'provider.suite.secret', '', true));
            $corpProviderConfig->setLoginAppId((string)Tool::getArrayVal($configs, 'provider.login.app.id', '', true));
            $corpProviderConfig->setLoginAppSecret((string)Tool::getArrayVal($configs, 'provider.login.app.secret', '', true));
            $corpProviderConfig->setLoginUrlCallback((string)Tool::getArrayVal($configs, 'provider.login.url.callback', '', true));
            $this->corpProviderConfig = $corpProviderConfig;
        }

        return $this->corpProviderConfig;
    }
}
