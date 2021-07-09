<?php
/**
 * BaseWechat.php
 *
 * Created by PhpStorm.
 * author: yandy  <yandycom@126.com>
 * DateTime: 2019-05-07
 */

namespace Wx;


use Exception\Wx\WxBusinessException;


/**
 * Class BaseWechat
 * @package App\WechatXiaowei\V1\Services\wechat
 */
class BaseWechat
{
    //请求API
    const WXAPIHOST = 'https://api.mch.weixin.qq.com/';

    //根目录
    const DIR = __DIR__;

    //小程序appid
    protected  $appid;

    //小程序secret
    protected $secret;

    // 服务商商户号
    protected $mch_id;

    // 商户证书序列号
    protected $serial_no;

    // 加密秘钥
    protected $aes_key;

    // 商户自定义key
    protected $diy_key;

    // 最新的证书完整响应体存放位置
    protected $newResponseDataAddr;

    // 解密后证书地址
    protected $publicKeyAddr;

    // 私钥地址
    protected $privateKeyAddr;

    //公钥地址
    protected $sslCertAddr;


    protected $uid;

    public function __construct($wechatConfig = array())
    {
        // 获取配置

        $this->mch_id    = $wechatConfig['mch_id'];
        $this->serial_no = $wechatConfig['serial_no'];
        $this->aes_key   = $wechatConfig['aes_key'];
        $this->appid   = $wechatConfig['appid'];
        $this->secret   = $wechatConfig['secret'];
        $this->diy_key   = $wechatConfig['diy_key'];
        $this->uid       = 0;

        $this->privateKeyAddr = self::DIR . '/Certificate/apiclient_key.pem';
        $this->sslCertAddr = self::DIR . '/Certificate/apiclient_cert.pem';
        $this->newResponseDataAddr = self::DIR . '/Certificate/jiemi.json';
        $this->publicKeyAddr = self::DIR . '/Certificate/jiemi.pem';
    }


    /**
     * httpsRequest  https请求（支持GET和POST）
     * @param $url
     * @param null $data
     * @return mixed
     */
    protected function httpsRequest($url, $data = '', array $headers = [], $userCert = false, $timeout = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        //设置超时
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if ($userCert) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);//严格校验
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            list($sslCertPath, $sslKeyPath) = $this->getSSLCertPath();
            curl_setopt($curl, CURLOPT_SSLCERT, $sslCertPath);
            curl_setopt($curl, CURLOPT_SSLKEY, $sslKeyPath);
        } else {
            if (substr($url, 0, 5) == 'https') {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
            }
        }
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($curl, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
            // $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT); //官方文档描述是“发送请求的字符串”，其实就是请求的header。这个就是直接查看请求header，因为上面允许查看
        }
        curl_setopt($curl, CURLOPT_HEADER, true);    // 是否需要响应 header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output          = curl_exec($curl);
        $header_size     = curl_getinfo($curl, CURLINFO_HEADER_SIZE);    // 获得响应结果里的：头大小
        $response_header = substr($output, 0, $header_size);    // 根据头大小去获取头信息内容
        $http_code       = curl_getinfo($curl, CURLINFO_HTTP_CODE);    // 获取响应状态码
        $response_body   = substr($output, $header_size);
        $error           = curl_error($curl);
        curl_close($curl);


        return [$response_body, $http_code, $response_header, $error];
    }

    /**
     * parseHeaders    处理curl响应头
     * @param $header
     * @return array
     */
    protected function parseHeaders($header)
    {
        $headers = explode("\r\n", $header);
        $head    = array();
        array_map(function($v) use (&$head) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $head[trim($t[0])] = trim($t[1]);
            } else {
                if (!empty($v)) {
                    $head[] = $v;
                    if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out)) {
                        $head['reponse_code'] = intval($out[1]);
                    }
                }
            }
        }, $headers);
        return $head;
    }

    /**
     * getRandChar 获取随机字符串
     * @param int $length
     * @return null|string
     */
    protected function getRandChar($length = 32)
    {
        $str    = NULL;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $newStr = str_shuffle($strPol);
        $max    = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $newStr[mt_rand(0, $max)];    // rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    /**
     * MakeSign 生成签名
     * @param $data
     * @param string $signType
     * @return string
     */
    protected function makeSign(array $data, $signType = 'HMAC-SHA256')
    {

        //签名步骤一：按字典序排序参数
        ksort($data);

        $string = $this->toUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->diy_key;

        //签名步骤三：MD5加密或者HMAC-SHA256
        if ($signType == 'md5') {
            //如果签名小于等于32个,则使用md5验证
            $string = md5($string);
        } else {
            //是用sha256校验
            $string = hash_hmac("sha256", $string, $this->diy_key);
        }
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * ToUrlParams     格式化参数格式化成url参数
     * @param $data
     * @return string
     */
    protected function toUrlParams(array $data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v !== "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * @param WxPayConfigInterface $config 配置对象
     * 检测签名
     */
    protected function checkSign($data)
    {
        strlen($data['sign']) <= 32 && $sign_type = 'md5';
        if ($this->makeSign($data, $sign_type ?? '') == $data['sign']) {
            return true;
        }
        throw new WxBusinessException(20000);
    }

    protected function getSSLCertPath()
    {
        return [
            $this->sslCertAddr,
            $this->privateKeyAddr
        ];
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    protected function toXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new WxBusinessException(30001);
        }

        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    protected function fromXml($xml)
    {
        if (!$xml) {
            throw new WxBusinessException(30000);
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $xml, true)) {
            xml_parser_free($xml_parser);
            throw new WxBusinessException(30000);
        } else {
            $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        }
        return $arr;
    }

    /**
     * getMillisecond 获取毫秒级别的时间戳
     * @return float
     */
    protected function getMillisecond()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * disposeReturn 处理微信小微商户接口返回值
     * @param $res  httpsRequest 方法调用接口返回的数组
     * @param array $need_fields 需要接口返回的字段（当return_code 、result_code都为SUCCESS时返回的字段的key）
     * @param array $arr 自定义的参数返回出去，例如入驻接口生成的业务编号
     * @return array 微信返回系统级错误不暴露出去，直接返回操作失败，业务级别错误返回具体错误消息
     */
    protected function disposeReturn($res, array $need_fields = [], array $arr = [])
    {

        if ($res[1] == 200) {
            $rt = $this->fromXml($res[0]);
            // var_dump($rt); die;
            if ($rt['return_code'] != 'SUCCESS') {
//                    \Log::error($rt['return_msg']);
                throw new WxBusinessException(0, $rt['return_msg']);
            }
            if ($rt['result_code'] != 'SUCCESS') {
                throw new WxBusinessException(0, $rt['err_code_des'] ?? $rt['err_code_msg']);
            }
            if ($this->checkSign($rt)) {
                if (!empty($need_fields)) {
                    $need = [];
                    array_map(function($v) use ($rt, &$need) {
                        $need[$v] = $rt[$v] ?? '';
                    }, $need_fields);
                    return array_merge($need, $arr);
                }
                return $arr;
            }
        }
        throw new WxBusinessException(30002);
    }

}