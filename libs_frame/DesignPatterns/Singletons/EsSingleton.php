<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2020/7/10
 * Time: 10:57
 */

namespace DesignPatterns\Singletons;

use Constant\ErrorCode;
use Exception\Es\EsException;
use Log\Log;
use Tool\Tool;
use Traits\SingletonTrait;

class EsSingleton {
    use SingletonTrait;

    /**
     * @var string
     */
    private $server = '';
    /**
     * @var string
     */
    private $authToken = '';

    private function __construct()
    {
        $configs = Tool::getConfig('es.' . SY_ENV . SY_PROJECT);

        $connectUrl = (string)Tool::getArrayVal($configs, 'connect.url', '', true);
        if (preg_match('/^(http|https)\:\/\/\S+$/', $connectUrl) == 0) {
            throw new EsException('服务地址不合法', ErrorCode::SOLR_PARAM_ERROR);
        }


        $this->server = $connectUrl;
    }

    /**
     * @return \DesignPatterns\Singletons\EsSingleton
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 添加或更新数据
     * 注:字符串类型的数据,如果是以汉字一开头,需要将在数据之前加上字符串&nbsp;
     * @param array $data 数据内容
     * @return array
     */
    public function addOrUpdate(array $data) : array
    {
        $resArr = [
            'code' => 0,
        ];

        $result = $this->httpPut($data['es_index'] . '/' . $data['es_type'] . '/' . $data['id'], $data);
        Log::log('RESULT:' . print_r($result,true));
        if (isset($result['_id']) && ($result['_id'] == $data['id'])) {
            $resArr['data'] = $result;
        } else {
            Log::error(Tool::jsonEncode($result, JSON_UNESCAPED_UNICODE), ErrorCode::SOLR_ADD_ERROR);
            $resArr['code'] = ErrorCode::SOLR_ADD_ERROR;
            $resArr['message'] = '添加或更新失败';
        }

        return $resArr;
    }

    /**
     * 删除数据
     * @param array $data 删除的数据
     * @return array
     */
    public function delete(array $data) : array
    {
        $resArr = [
            'code' => 0,
        ];
        $esType = $data['es_type'];
        unset($data['es_type']);

        $result = $this->httpDelete($data['es_index'] . '/' . $esType. '/' . $data['id']);
        Log::log('RESULT:' . print_r($result,true));
        if (isset($result['_id']) && ($result['_id'] == $data['id'])) {
            $resArr['data'] = $result;
        } else {
            Log::error(Tool::jsonEncode($result, JSON_UNESCAPED_UNICODE), ErrorCode::SOLR_DELETE_ERROR);
            $resArr['code'] = ErrorCode::SOLR_DELETE_ERROR;
            $resArr['message'] = '删除失败';
        }

        return $resArr;
    }

    /**
     * 搜索
     * @param array $data 配置参数数组
     * @return array
     */
    public function select(array $data) : array
    {
        $resArr = [
            'code' => 0,
        ];
        $esIndex = $data['es_index'];
        unset($data['es_index']);

        $result = $this->httpPost($esIndex . '/_search' , $data);
        if (isset($result['hits'])) {
            $resArr['data'] = $result['hits'];
        } else {
            Log::error(Tool::jsonEncode($result, JSON_UNESCAPED_UNICODE), ErrorCode::SOLR_SELECT_ERROR);
            $resArr['code'] = ErrorCode::SOLR_SELECT_ERROR;
            $resArr['message'] = '查询失败';
        }

        return $resArr;
    }

    /**
     * 搜索
     * @param array $data 配置参数数组
     * @return array
     */
    public function findOne(array $data) : array
    {
        $resArr = [
            'code' => 0,
        ];
        $esType = $data['es_type'];
        unset($data['es_type']);

        $result = $this->httpGet($data['es_index'] . '/' . $esType . '/' . $data['id'], $data);
        if (isset($result['_id']) && $result['_id'] == $data['id']) {
            $resArr['data'] = $result;
        } else {
            Log::error(Tool::jsonEncode($result, JSON_UNESCAPED_UNICODE), ErrorCode::SOLR_SELECT_ERROR);
            $resArr['code'] = ErrorCode::SOLR_SELECT_ERROR;
            $resArr['message'] = '查询失败';
        }

        return $resArr;
    }

    /**
     * 分词
     * @param array $data 配置参数
     *   analysis_key: string 分词器名称
     *   keyword: string 待分词字符串
     * @return array
     */
    /*public function analysis(array $data) : array
    {
        $resArr = [
            'code' => 0,
        ];

        $result = $this->httpGet('analysis/field', [
            'analysis.showmatch' => 'true',
            'analysis.fieldtype' => $data['analysis_key'],
            'analysis.fieldvalue' => urlencode($data['keyword']),
        ]);
        if (isset($result['responseHeader']['status']) && ($result['responseHeader']['status'] == 0)) {
            $resArr['data'] = $result;
        } else {
            Log::error(Tool::jsonEncode($result, JSON_UNESCAPED_UNICODE), ErrorCode::SOLR_ANALYSIS_ERROR);
            $resArr['code'] = ErrorCode::SOLR_ANALYSIS_ERROR;
            $resArr['message'] = '分词失败';
        }

        return $resArr;
    }*/

    /**
     * 发送PUT请求
     * @param string $method 操作类型
     * @param array $data 数据数组
     * @return array
     * @throws \Exception\Es\EsException
     */
    private function httpPut(string $method, array $data)
    {
        $dataStr = Tool::jsonEncode($data, JSON_FORCE_OBJECT);
        $url = $this->server . $method;

        $httpHeaders = [
            'Content-Type: application/json',
        ];
        if (strlen($this->authToken) > 0) {
            $httpHeaders[] = 'Authorization: ' . $this->authToken;
        }
        $sendRes = Tool::sendCurlReq([
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $dataStr,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $httpHeaders,
        ]);

        if ($sendRes['res_no'] == 0) {
            $resData = Tool::jsonDecode($sendRes['res_content']);
            if (is_array($resData)) {
                return $resData;
            } else {
                Log::error('解析PUT响应失败,响应数据=' . $sendRes['res_content'], ErrorCode::SOLR_POST_ERROR);
                throw new EsException('解析PUT响应失败', ErrorCode::SOLR_POST_ERROR);
            }
        } else {
            Log::error('curl发送ES PUT请求出错,错误码=' . $sendRes['res_no'] . ',错误信息=' . $sendRes['res_msg'], ErrorCode::SOLR_POST_ERROR);
            throw new EsException('PUT请求出错', ErrorCode::SOLR_POST_ERROR);
        }
    }

    /**
     * 发送POST请求
     * @param string $method 操作类型
     * @param array $data 数据数组
     * @return array
     * @throws \Exception\Es\EsException
     */
    private function httpPost(string $method, array $data)
    {
        $dataStr = empty($data) ? (object)[] : Tool::jsonEncode($data,JSON_FORCE_OBJECT);
        $url = $this->server . $method;

        $httpHeaders = [
            'Content-Type: application/json',
        ];
        if (strlen($this->authToken) > 0) {
            $httpHeaders[] = 'Authorization: ' . $this->authToken;
        }
        $sendRes = Tool::sendCurlReq([
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $dataStr,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $httpHeaders,
        ]);

        if ($sendRes['res_no'] == 0) {
            $resData = Tool::jsonDecode($sendRes['res_content']);
            if (is_array($resData)) {
                return $resData;
            } else {
                Log::error('解析POST响应失败,响应数据=' . $sendRes['res_content'], ErrorCode::SOLR_POST_ERROR);
                throw new EsException('解析POST响应失败', ErrorCode::SOLR_POST_ERROR);
            }
        } else {
            Log::error('curl发送ES POST请求出错,错误码=' . $sendRes['res_no'] . ',错误信息=' . $sendRes['res_msg'], ErrorCode::SOLR_POST_ERROR);
            throw new EsException('POST请求出错', ErrorCode::SOLR_POST_ERROR);
        }
    }

    /**
     * 发送GET请求
     * @param string $method 操作类型
     * @param array $data 数据数组
     * @return array
     * @throws \Exception\Es\EsException
     */
    private function httpGet(string $method, array $data)
    {
        $url = $this->server . $method;

        $httpHeaders = [
            'Content-Type: application/json',
        ];
        if (strlen($this->authToken) > 0) {
            $httpHeaders[] = 'Authorization: ' . $this->authToken;
        }

        $sendRes = Tool::sendCurlReq([
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $httpHeaders,
        ]);
        if ($sendRes['res_no'] == 0) {
            $resData = Tool::jsonDecode($sendRes['res_content']);
            if (is_array($resData)) {
                return $resData;
            } else {
                Log::error('解析GET响应失败,响应数据=' . $sendRes['res_content'], ErrorCode::SOLR_GET_ERROR);
                throw new EsException('解析GET响应失败', ErrorCode::SOLR_GET_ERROR);
            }
        } else {
            Log::error('curl发送es get请求出错,错误码=' . $sendRes['res_no'] . ',错误信息=' . $sendRes['res_msg'], ErrorCode::SOLR_GET_ERROR);
            throw new EsException('GET请求出错', ErrorCode::SOLR_GET_ERROR);
        }
    }

    /**
     * 发送DELETE请求
     * @param string $method 操作类型
     * @return array
     * @throws \Exception\Es\EsException
     */
    private function httpDelete(string $method)
    {
        $dataStr = (object)[];
        $url = $this->server . $method;

        $httpHeaders = [
            'Content-Type: application/json',
        ];
        if (strlen($this->authToken) > 0) {
            $httpHeaders[] = 'Authorization: ' . $this->authToken;
        }
        $sendRes = Tool::sendCurlReq([
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => $dataStr,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $httpHeaders,
        ]);

        if ($sendRes['res_no'] == 0) {
            $resData = Tool::jsonDecode($sendRes['res_content']);
            if (is_array($resData)) {
                return $resData;
            } else {
                Log::error('解析DELETE响应失败,响应数据=' . $sendRes['res_content'], ErrorCode::SOLR_POST_ERROR);
                throw new EsException('解析DELETE响应失败', ErrorCode::SOLR_POST_ERROR);
            }
        } else {
            Log::error('curl发送ES DELETE请求出错,错误码=' . $sendRes['res_no'] . ',错误信息=' . $sendRes['res_msg'], ErrorCode::SOLR_POST_ERROR);
            throw new EsException('DELETE请求出错', ErrorCode::SOLR_POST_ERROR);
        }
    }
}