<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2018/9/7 0007
 * Time: 9:45
 */
namespace TaoBao;

use Constant\ErrorCode;
use Log\Log;
use Tool\Tool;
use Traits\SimpleTrait;

abstract class TaoBaoUtilBase
{
    use SimpleTrait;

    protected static $urlHttp = 'http://gw.api.taobao.com/router/rest';

    /**
     * 生成签名字符串
     * @param array $data 参数数组
     * @param string $appSecret 应用密钥
     * @return void
     */
    public static function createSign(array &$data, string $appSecret)
    {
        unset($data['sign']);
        ksort($data);
        $needStr = $appSecret;
        foreach ($data as $key => $value) {
            $needStr .= $key . $value;
        }
        $needStr .= $appSecret;
        $data['sign'] = strtoupper(md5($needStr));
    }

    /**
     * 发送服务请求
     * @param \TaoBao\TaoBaoBase $taoBaoBase
     * @return array
     */
    public static function sendServiceRequest(TaoBaoBase $taoBaoBase)
    {
        $resArr = [
            'code' => 0
        ];

        $data = $taoBaoBase->getDetail();
        $responseTag = $taoBaoBase->getResponseTag();
        $sendRes = self::sendPostReq(self::$urlHttp, $data);
        $rspData = Tool::jsonDecode($sendRes);
        if (isset($rspData[$responseTag])) {
            $resArr['data'] = $rspData[$responseTag];
        } elseif (isset($rspData['error_response'])) {
            $resArr['code'] = ErrorCode::SMS_POST_ERROR;
            $resArr['msg'] = $rspData['error_response']['sub_msg'] ?? $rspData['error_response']['msg'];
        } else {
            Log::error($sendRes, ErrorCode::SMS_POST_ERROR);
            $resArr['code'] = ErrorCode::SMS_POST_ERROR;
            $resArr['msg'] = '解析服务数据出错';
        }

        return $resArr;
    }

    /**
     * 发送POST请求
     * @param string $url 请求地址
     * @param array $data 请求参数
     * @param array $curlConfig curl配置数组
     * @return mixed
     */
    private static function sendPostReq(string $url, array $data, array $curlConfig = [])
    {
        $curlConfig[CURLOPT_URL] = $url;
        $curlConfig[CURLOPT_NOSIGNAL] = true;
        $curlConfig[CURLOPT_SSL_VERIFYPEER] = false;
        $curlConfig[CURLOPT_SSL_VERIFYHOST] = false;
        $curlConfig[CURLOPT_POST] = true;
        $curlConfig[CURLOPT_POSTFIELDS] = http_build_query($data);
        $curlConfig[CURLOPT_RETURNTRANSFER] = true;
        $curlConfig[CURLOPT_HTTPHEADER] = [
            'Expect:',
        ];
        if (!isset($curlConfig[CURLOPT_TIMEOUT_MS])) {
            $curlConfig[CURLOPT_TIMEOUT_MS] = 1000;
        }
        $sendRes = Tool::sendCurlReq($curlConfig);
        if ($sendRes['res_no'] > 0) {
            Log::error('短信请求失败,curl错误码为' . $sendRes['res_no'], ErrorCode::SMS_POST_ERROR);
        }

        return $sendRes['res_content'];
    }
}
