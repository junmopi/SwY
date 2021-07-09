<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/1/14
 * Time: 15:04
 */

namespace SyPrint;

use DesignPatterns\Factories\CacheSimpleFactory;
use SyPrint\YiLianYun\AccessToken;
use Tool\Tool;
use Traits\SimpleTrait;

final class PrintUtilYly extends PrintUtilBase
{
    use SimpleTrait;

    public static function getAccessToken(string $clientId, $code = '') : string
    {
        $nowTime = Tool::getNowTime();
        $redisKey = Project::REDIS_PREFIX_PRINT_FEYIN_ACCOUNT . $clientId;
        $redisData = CacheSimpleFactory::getRedisInstance()->hGetAll($redisKey);
        if (isset($redisData['unique_key']) && ($redisData['unique_key'] == $redisKey) && ($redisData['expire_time'] >= $nowTime)) {
            return $redisData['access_token'];
        }

        $accessTokenObj = new AccessToken($clientId);
        if(!empty($code)){
            $accessTokenDetail = $accessTokenObj->getDetail($code);
        }else{
            $accessTokenDetail = $accessTokenObj->getDetail();
        }
        unset($accessTokenObj);

        $expireTime = (int)($accessTokenDetail['expires_in'] + $nowTime);
        $activeTime = (int)($accessTokenDetail['expires_in'] + 100);
        CacheSimpleFactory::getRedisInstance()->hMset($redisKey, [
            'access_token' => $accessTokenDetail['access_token'],
            'expire_time' => $expireTime,
            'unique_key' => $redisKey,
        ]);
        CacheSimpleFactory::getRedisInstance()->expire($redisKey, $activeTime);

        return $accessTokenDetail['access_token'];
    }
}