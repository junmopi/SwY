<?php
/**
 * 拒绝重复请求插件
 * User: junmopi
 * Date: 2020/10/27 0024
 * Time: 09:34
 */
namespace SyFrame\Plugins;

use Constant\ErrorCode;
use Constant\Project;
use DesignPatterns\Factories\CacheSimpleFactory;
use Exception\Swoole\ServerException;
use Request\SyRequest;
use Tool\Tool;
use Yaf\Plugin_Abstract;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;

class RejectDuplicatePlugin extends Plugin_Abstract
{
    public function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     * @return void
     * @throws \Exception\Swoole\ServerException
     */
    public function preDispatch(Request_Abstract $request, Response_Abstract $response)
    {
        $params = SyRequest::getParams();
        if (isset($params['session_id'])) {
            $token = trim($params['session_id']);
        } else {
            $token = '';
        }
        if ((strlen($token) > 0) && ($request->isPost())) {
            if(isset($params['_sign'])){
                unset($params['_sign']);
            }
            $paramsJson = Tool::jsonEncode($params);
            list($mse, $sec) = explode(' ', microtime());
            $mseTime = (float)sprintf('%.0f', (floatval($mse) + floatval($sec)) * 1000); //毫秒级时间戳
            $tag = md5(SY_MODULE . '_' . strtolower($request->getMethod() . '_' . $request->getModuleName() . $request->getControllerName() . $request->getActionName()) . '_' . $token . '_' . $paramsJson);
            $cacheKey = Project::REDIS_PREFIX_DUPLICATE_REQUEST . '_' . $tag;
            $cacheData = CacheSimpleFactory::getRedisInstance()->get($cacheKey);
            if(!empty($cacheData)){//不为空说明3秒内请求了同一接口
                $cacheArr = explode('_', $cacheData);
                //500毫秒内有相同请求则抛异常
                $def = (int)($mseTime) - (int)($cacheArr[0]);
                if($def <= 500){
                    throw new ServerException('请求重复,请刷新重试', ErrorCode::COMMON_SERVER_ERROR);
                }
            }
            $value = $mseTime . '_' . $tag;
            CacheSimpleFactory::getRedisInstance()->set($cacheKey,$value,3);
        }
    }
}