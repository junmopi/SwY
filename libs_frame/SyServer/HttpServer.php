<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-3-5
 * Time: 16:42
 */
namespace SyServer;

use Constant\ErrorCode;
use Constant\Project;
use Constant\Server;
use Exception\Swoole\HttpServerException;
use Log\Log;
use Request\RequestSign;
use Response\Result;
use Response\SyResponseHttp;
use SyModule\ModuleContainer;
use Tool\SessionTool;
use Tool\SyPack;
use Tool\Tool;
use Traits\HttpServerTrait;
use Traits\PreProcessHttpFrameTrait;
use Traits\PreProcessHttpProjectTrait;
use Traits\Server\BasicHttpTrait;
use Yaf\Registry;
use Yaf\Request\Http;

class HttpServer extends BaseServer
{
    use BasicHttpTrait;
    use HttpServerTrait;
    use PreProcessHttpFrameTrait;
    use PreProcessHttpProjectTrait;

    const RESPONSE_RESULT_TYPE_FORBIDDEN = 0; //响应结果类型-拒绝请求
    const RESPONSE_RESULT_TYPE_ACCEPT = 1; //响应结果类型-允许请求执行业务
    const RESPONSE_RESULT_TYPE_ALLOW = 2; //响应结果类型-不执行业务，直接返回响应

    /**
     * 跨域共享资源数组
     * @var array
     */
    protected $_cors = [];
    /**
     * swoole请求cookie域名数组
     * @var array
     */
    private $_reqCookieDomains = [];
    /**
     * @var \Tool\SyPack
     */
    private $_messagePack = null;
    /**
     * @var \SyModule\ModuleContainer
     */
    private $_moduleContainer = null;
    /**
     * HTTP响应
     * @var \swoole_http_response
     */
    private static $_response = null;
    /**
     * 请求标识
     * @var bool true:外部请求 false:内部请求
     */
    private static $_reqTag = true;
    /**
     * 响应消息
     * @var string
     */
    private static $_rspMsg = '';
    /**
     * swoole请求头信息数组
     * @var array
     */
    private static $_reqHeaders = [];
    /**
     * swoole服务器信息数组
     * @var array
     */
    private static $_reqServers = [];
    /**
     * swoole task请求数据
     * @var string
     */
    private static $_reqTask = null;

    public function __construct(int $port)
    {
        parent::__construct($port);
        $projectLength = strlen(SY_PROJECT);
        $serverType = Tool::getConfig('project.' . SY_ENV . SY_PROJECT . '.modules.' . substr(SY_MODULE, $projectLength) . '.type');
        if (!in_array($serverType, [Server::SERVER_TYPE_API_GATE, Server::SERVER_TYPE_FRONT_GATE], true)) {
            exit('服务端类型不支持' . PHP_EOL);
        }
        define('SY_SERVER_TYPE', $serverType);
        $this->_configs['server']['cachenum']['hc'] = 1;
        $this->_configs['server']['cachenum']['modules'] = (int)Tool::getArrayVal($this->_configs, 'server.cachenum.modules', 0, true);
        $this->_configs['server']['cachenum']['local'] = (int)Tool::getArrayVal($this->_configs, 'server.cachenum.local', 0, true);
        if ($serverType == Server::SERVER_TYPE_API_GATE) {
            $this->_configs['server']['cachenum']['wx'] = (int)Tool::getArrayVal($this->_configs, 'server.cachenum.wx', 0, true);
        } else {
            $this->_configs['server']['cachenum']['wx'] = 1;
        }
        if (SY_SESSION == Server::SESSION_TYPE_CACHE) {
            $this->_configs['server']['cachenum']['users'] = (int)Tool::getArrayVal($this->_configs, 'server.cachenum.users', 0, true);
        } else {
            $this->_configs['server']['cachenum']['users'] = 1;
        }
        $this->checkServerHttp();
        $this->_cors = Tool::getConfig('cors.' . SY_ENV . SY_PROJECT);
        $this->_cors['allow']['headerStr'] = isset($this->_cors['allow']['headers']) ? implode(', ', $this->_cors['allow']['headers']) : '';
        $this->_cors['allow']['methodStr'] = isset($this->_cors['allow']['methods']) ? implode(', ', $this->_cors['allow']['methods']) : '';
        $this->_messagePack = new SyPack();
        $this->_moduleContainer = new ModuleContainer();
        $this->_reqCookieDomains = Tool::getConfig('project.' . SY_ENV . SY_PROJECT . '.domain.cookie');
    }

    private function __clone()
    {
    }

    public function start()
    {
        $this->initTableHttp();
        //初始化swoole服务
        $this->_server = new \swoole_websocket_server($this->_host, $this->_port);
        $this->baseStart([
            'start' => 'onStart',
            'managerStart' => 'onManagerStart',
            'workerStart' => 'onWorkerStart',
            'workerStop' => 'onWorkerStop',
            'workerError' => 'onWorkerError',
            'workerExit' => 'onWorkerExit',
            'shutdown' => 'onShutdown',
            'request' => 'onRequest',
            'task' => 'onTask',
            'finish' => 'onFinish',
            'handshake' => 'onHandshake',
            'message' => 'onMessage',
            'close' => 'onClose',
        ]);
    }

    /**
     * 生成web socket服务端签名
     * @param string $socketKey 客户端密钥
     * @return bool|string
     */
    public static function createSocketAccept(string $socketKey)
    {
        if (is_null($socketKey)) {
            return false;
        } elseif (preg_match('/^[0-9a-zA-Z\+\/]{21}[AQgw]\={2}$/', $socketKey) == 0) {
            return false;
        } elseif (strlen(base64_decode($socketKey, true)) != 16) {
            return false;
        }

        return base64_encode(sha1($socketKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    }

    /**
     * 校验服务端签名是否正确
     * @param string $socketKey 客户端密钥
     * @param string $socketAccept 服务端签名
     * @return bool
     * @throws \Exception\Swoole\HttpServerException
     */
    public static function checkSocketAccept(string $socketKey, string $socketAccept) : bool
    {
        if (is_null($socketAccept)) {
            throw new HttpServerException('服务端签名不能为空', ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
        }

        $nowAccept = self::createSocketAccept($socketKey);
        if ($nowAccept === false) {
            return false;
        } else {
            return $nowAccept === $socketAccept;
        }
    }

    public function onStart(\swoole_server $server)
    {
        $this->basicStart($server);
        $this->addTaskBase($server);
        $this->_messagePack->setCommandAndData(SyPack::COMMAND_TYPE_SOCKET_CLIENT_SEND_TASK_REQ, [
            'task_module' => SY_MODULE,
            'task_command' => Project::TASK_TYPE_REFRESH_TOKEN_EXPIRE,
            'task_params' => [],
        ]);
        $taskDataToken = $this->_messagePack->packData();
        $this->_messagePack->init();

        $server->tick(Project::TIME_TASK_REFRESH_TOKEN_EXPIRE, function () use ($server, $taskDataToken) {
            $server->task($taskDataToken);
        });
        $this->addTaskHttpTrait($server);
    }

    public function onWorkerStop(\swoole_server $server, int $workerId)
    {
        $this->basicWorkStop($server, $workerId);
    }

    public function onWorkerError(\swoole_server $server, $workId, $workPid, $exitCode)
    {
        $this->basicWorkError($server, $workId, $workPid, $exitCode);

        if (self::$_response) {
            $this->setRspCookies(self::$_response, Registry::get(Server::REGISTRY_NAME_RESPONSE_COOKIE));
            $this->setRspHeaders(self::$_response, Registry::get(Server::REGISTRY_NAME_RESPONSE_HEADER));

            $json = new Result();
            $json->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, ErrorCode::getMsg(ErrorCode::COMMON_SERVER_ERROR));
            if (self::$_reqTag) {
                self::$_response->end($json->getJson());
            } else {
                self::$_response->end($json->getJson() . Server::SERVER_HTTP_TAG_RESPONSE_EOF);
            }
        }
    }

    /**
     * web socket握手
     * @param \swoole_http_request  $request
     * @param \swoole_http_response $response
     * @return bool
     */
    public function onHandshake(\swoole_http_request $request, \swoole_http_response $response)
    {
        $socketAccept = self::createSocketAccept(Tool::getArrayVal($request->header, 'sec-websocket-key', null));
        if ($socketAccept === false) {
            $response->end();
            return false;
        }

        $origin = isset($request->header['origin']) ? trim($request->header['origin']) : '';
        $origins = Tool::getArrayVal($this->_cors, 'allow.origins', [], true);
        if ((strlen($origin) > 0) && !empty($origins)) { //校验origin是否允许
            $checkRes = false;
            foreach ($origins as $eOrigin) {
                $startIndex = -1 * strlen($eOrigin);
                if (substr($origin, $startIndex) === $eOrigin) {
                    $checkRes = true;
                    break;
                }
            }

            if (!$checkRes) {
                $response->end();
                return false;
            }
        }

        $response->header('Upgrade', 'websocket');
        $response->header('Connection', 'Upgrade');
        $response->header('Sec-WebSocket-Accept', $socketAccept);
        $response->header('Sec-WebSocket-Version', '13');
        $response->header('Keep-Alive', 'off');
        $response->status(101);
        $response->end();

        $fd = $request->fd;
        $server = $this->_server;
        $this->_server->defer(function () use ($fd, $server) {
            $server->push($fd, 'hello, welcome', WEBSOCKET_OPCODE_TEXT);
        });

        return true;
    }

    /**
     * 接受socket消息
     * 消息格式：abcde
     * <pre>
     * 格式说明：
     *     a:消息头长度，值固定为16
     *     b:消息内容长度，无符号整数
     *     c:消息执行命令标识，4位字符串
     *     d:保留字段，值固定为0000
     *     e:消息内容，json格式
     * </pre>
     * @param \swoole_websocket_server $server
     * @param \swoole_websocket_frame $frame
     */
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        $result = new Result();
        if ($frame->opcode != WEBSOCKET_OPCODE_BINARY) {
            $result->setCodeMsg(ErrorCode::COMMON_PARAM_ERROR, '只接受二进制数据');
            $server->push($frame->fd, $result->getJson(), WEBSOCKET_OPCODE_TEXT, true);
            return;
        } elseif (!$frame->finish) { //数据未发送完
            return;
        }

        $message = $this->_messagePack->unpackData($frame->data);
        $command = $this->_messagePack->getCommand();
        $commandData = $this->_messagePack->getData();
        $this->_messagePack->init();
        if ($message === false) {
            $result->setCodeMsg(ErrorCode::COMMON_PARAM_ERROR, '消息格式不正确');
            $server->push($frame->fd, $result->getJson(), WEBSOCKET_OPCODE_TEXT, true);
            return;
        }

        switch ($command) {
            case SyPack::COMMAND_TYPE_SOCKET_CLIENT_CLOSE:
                $server->close($frame->fd);
                break;
            case SyPack::COMMAND_TYPE_SOCKET_CLIENT_CHECK_STATUS:
                $result->setData([
                    'status' => $server->exist($frame->fd) ? 1 : 0,
                    'detail' => $server->exist($frame->fd) ? $server->connection_info($frame->fd, null, true) : [],
                ]);
                $server->push($frame->fd, $result->getJson(), WEBSOCKET_OPCODE_TEXT, true);
                break;
            case SyPack::COMMAND_TYPE_SOCKET_CLIENT_GET_SERVER:
                $result->setData([
                    'sy_version' => SY_VERSION,
                    'server_type' => 'swoole-http-server',
                    'swoole_version' => SWOOLE_VERSION,
                    'yaf_version' => \YAF\VERSION,
                ]);
                $server->push($frame->fd, $result->getJson(), WEBSOCKET_OPCODE_TEXT, true);
                break;
            case SyPack::COMMAND_TYPE_SOCKET_CLIENT_SEND_API_REQ:
                $module = $this->_moduleContainer->getObj($commandData['api_module']);

                try {
                    if (is_null($module)) {
                        $handleRes = false;
                    } elseif (($commandData['api_module'] == Project::MODULE_NAME_API) && ($commandData['api_method'] == 'GET')) {
                        $handleRes = $module->sendGetReq($commandData['api_uri'], $commandData['api_params']);
                    } elseif ($commandData['api_module'] == Project::MODULE_NAME_API) {
                        $handleRes = $module->sendPostReq($commandData['api_uri'], $commandData['api_params']);
                    } else {
                        $handleRes = $module->sendApiReq($commandData['api_uri'], $commandData['api_params']);
                    }
                    if ($handleRes === false) {
                        $result->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, '服务处理失败');
                    } else {
                        $result = $handleRes;
                    }
                } catch (\Exception $e) {
                    Log::error($e->getMessage(), $e->getCode(), $e->getTraceAsString());

                    $result->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, '服务出错');
                } finally {
                    if ($result instanceof Result) {
                        $server->push($frame->fd, $result->getJson(), WEBSOCKET_OPCODE_TEXT, true);
                    } else {
                        $server->push($frame->fd, $result, WEBSOCKET_OPCODE_TEXT, true);
                    }
                }
                break;
            case SyPack::COMMAND_TYPE_SOCKET_CLIENT_SEND_TASK_REQ:
                $module = $this->_moduleContainer->getObj($commandData['task_module']);

                try {
                    if (is_null($module)) {
                        $handleRes = false;
                    } else {
                        $handleRes = $module->sendTaskReq($commandData['task_command'], $commandData['task_params']);
                    }
                    if ($handleRes === false) {
                        $result->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, '服务处理失败');
                    } else {
                        $result->setData([
                            'result' => 'send task success',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error($e->getMessage(), $e->getCode(), $e->getTraceAsString());

                    $result->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, '服务出错');
                } finally {
                    $server->push($frame->fd, $result->getJson(), WEBSOCKET_OPCODE_TEXT, true);
                }
                break;
            default:
                $result->setCodeMsg(ErrorCode::COMMON_PARAM_ERROR, '命令不存在');
                $server->push($frame->fd, $result->getJson(), WEBSOCKET_OPCODE_TEXT, true);
                break;
        }
    }

    public function onTask(\swoole_server $server, int $taskId, int $fromId, string $data)
    {
        $baseRes = $this->handleTaskBase($server, $taskId, $fromId, $data);
        if (is_array($baseRes)) {
            $taskCommand = Tool::getArrayVal($baseRes['params'], 'task_command', '');
            switch ($taskCommand) {
                default:
                    $this->handleTaskHttpTrait($server, $taskId, $fromId, $baseRes);
            }
        }
    }

    /**
     * 处理请求
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        self::$_response = $response;
        $initRes = $this->initReceive($request);
        if (strlen($initRes) > 0) {
            self::$_rspMsg = $initRes;
        } elseif (is_null(self::$_reqTask)) {
            $rspHeaders = [];
            $handleHeaderRes = $this->handleReqHeader($rspHeaders);
            if ($handleHeaderRes == self::RESPONSE_RESULT_TYPE_ACCEPT) {
                self::$_rspMsg = $this->handleReqService($request, $rspHeaders);
                $rspData = Tool::jsonDecode(self::$_rspMsg);
                $nowRspHeaders = Registry::get(Server::REGISTRY_NAME_RESPONSE_HEADER);
                if (isset($rspData['code']) && ($rspData['code'] > 0)) {
                    $nowRspHeaders['Syresp-Status'] = SY_HTTP_RSP_CODE_ERROR;
                }
                $this->setRspCookies($response, Registry::get(Server::REGISTRY_NAME_RESPONSE_COOKIE));
                $this->setRspHeaders($response, $nowRspHeaders);
            } elseif ($handleHeaderRes == self::RESPONSE_RESULT_TYPE_ALLOW) {
                $rspHeaders['Content-Type'] = 'application/json; charset=utf-8';
                $this->setRspHeaders($response, $rspHeaders);
            } else {
                $rspHeaders['Content-Type'] = 'text/plain; charset=utf-8';
                $rspHeaders['Syresp-Status'] = 403;
                $this->setRspHeaders($response, $rspHeaders);
            }
        } else {
            self::$_syServer->incr(self::$_serverToken, 'request_times', 1);
            $this->_server->task(self::$_reqTask, random_int(1, $this->_taskMaxId));
            $res = new Result();
            $res->setData([
                'msg' => 'task received',
            ]);
            self::$_rspMsg = $res->getJson();
        }

        $syTag = BaseServer::getProjectCache('sytag', 'value', '1');
        if($syTag != '1'){
            $response->end('');
        } else if (self::$_reqTag) {
            $response->end(self::$_rspMsg);
        } else {
            $response->end(self::$_rspMsg . Server::SERVER_HTTP_TAG_RESPONSE_EOF);
        }

        $this->clearRequest();
    }

    /**
     * 设置响应头信息
     * @param \swoole_http_response $response
     * @param array|bool $headers
     */
    private function setRspHeaders(\swoole_http_response $response, $headers)
    {
        if (is_array($headers)) {
            if (!isset($headers['Content-Type'])) {
                $response->header('Content-Type', 'application/json; charset=utf-8');
            }

            foreach ($headers as $headerName => $headerVal) {
                $response->header($headerName, $headerVal);
            }

            if (isset($headers['Location'])) {
                $response->status(302);
            } elseif (isset($headers['Syresp-Status'])) {
                $response->status($headers['Syresp-Status']);
            }
        } else {
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Content-Type', 'application/json; charset=utf-8');
        }
    }

    /**
     * 设置响应cookie信息
     * @param \swoole_http_response $response
     * @param array|bool $cookies
     */
    private function setRspCookies(\swoole_http_response $response, $cookies)
    {
        if (is_array($cookies)) {
            foreach ($cookies as $cookie) {
                $value = Tool::getArrayVal($cookie, 'value', null);
                $expires = Tool::getArrayVal($cookie, 'expires', 0);
                $path = Tool::getArrayVal($cookie, 'path', '/');
                $domain = Tool::getArrayVal($cookie, 'domain', '');
                $secure = Tool::getArrayVal($cookie, 'secure', false);
                $httpOnly = Tool::getArrayVal($cookie, 'httponly', false);
                $response->cookie($cookie['key'], $value, $expires, $path, $domain, $secure, $httpOnly);
            }
        }
    }

    /**
     * 初始化公共数据
     * @param \swoole_http_request $request
     * @return string
     */
    private function initReceive(\swoole_http_request $request)
    {
        $_POST = $request->post ?? [];
        $_SESSION = [];
        Registry::del(Server::REGISTRY_NAME_SERVICE_ERROR);
        self::$_reqHeaders = $request->header ?? [];
        self::$_reqServers = $request->server ?? [];
        self::$_reqTag = !isset(self::$_reqHeaders[Server::SERVER_HTTP_TAG_REQUEST_HEADER]);
        self::$_rspMsg = '';

        if (isset($request->header['content-type']) && ($request->header['content-type'] == 'application/json')) {
            $_POST = Tool::jsonDecode($request->rawContent());
            if (!is_array($_POST)) {
                $res = new Result();
                $res->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, 'JSON格式不正确');
                return $res->getJson();
            }
        }

        $taskData = $_POST[Server::SERVER_DATA_KEY_TASK] ?? '';
        self::$_reqTask = is_string($taskData) && (strlen($taskData) > 0) ? $taskData : null;

        $_SERVER = [];
        foreach (self::$_reqServers as $key => $val) {
            $_SERVER[strtoupper($key)] = $val;
        }
        foreach (self::$_reqHeaders as $key => $val) {
            $_SERVER[strtoupper($key)] = $val;
        }
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = $this->_host . ':' . $this->_port;
        }
        if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }

        $nowTime = time();
        $_SERVER[Server::SERVER_DATA_KEY_TIMESTAMP] = $nowTime;
        $_SERVER['SYREQ_ID'] = hash('md4', $nowTime . Tool::createNonceStr(8));
        return '';
    }

    private function initRequest(\swoole_http_request $request, array $rspHeaders)
    {
        self::$_reqStartTime = microtime(true);
        self::$_syServer->incr(self::$_serverToken, 'request_times', 1);
        $_GET = $request->get ?? [];
        $_FILES = $request->files ?? [];
        $_COOKIE = $request->cookie ?? [];
        $GLOBALS['HTTP_RAW_POST_DATA'] = $request->rawContent();
        $_POST[RequestSign::KEY_SIGN] = $_GET[RequestSign::KEY_SIGN] ?? '';
        unset($_GET[RequestSign::KEY_SIGN]);
        //注册全局信息
        Registry::set(Server::REGISTRY_NAME_REQUEST_HEADER, self::$_reqHeaders);
        Registry::set(Server::REGISTRY_NAME_REQUEST_SERVER, self::$_reqServers);
        Registry::set(Server::REGISTRY_NAME_RESPONSE_HEADER, $rspHeaders);
        Registry::set(Server::REGISTRY_NAME_RESPONSE_COOKIE, []);
        if (SY_SESSION == Server::SESSION_TYPE_JWT) {
            SessionTool::initSessionJwt();
        }
    }

    /**
     * 清理请求数据
     */
    private function clearRequest()
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_COOKIE = [];
        $_SERVER = [];
        $_SESSION = [];
        $GLOBALS['HTTP_RAW_POST_DATA'] = '';
        self::$_reqTag = true;
        self::$_reqTask = null;
        self::$_reqHeaders = [];
        self::$_reqServers = [];
        self::$_response = null;
        self::$_rspMsg = '';

        //清除yaf注册常量
        Registry::del(Server::REGISTRY_NAME_REQUEST_HEADER);
        Registry::del(Server::REGISTRY_NAME_REQUEST_SERVER);
        Registry::del(Server::REGISTRY_NAME_RESPONSE_HEADER);
        Registry::del(Server::REGISTRY_NAME_RESPONSE_COOKIE);
        if (SY_SESSION == Server::SESSION_TYPE_JWT) {
            Registry::del(Server::REGISTRY_NAME_RESPONSE_JWT_SESSION);
            Registry::del(Server::REGISTRY_NAME_RESPONSE_JWT_DATA);
        }

        self::$_syServer->set(self::$_serverToken, [
            'memory_usage' => memory_get_usage(),
        ]);
    }

    /**
     * 处理请求头
     * @param array $headers 响应头配置
     * @return int
     */
    private function handleReqHeader(array &$headers) : int
    {
        $headers['Access-Control-Allow-Origin'] = $_SERVER['ORIGIN'] ?? '*';
        $headers['Access-Control-Allow-Credentials'] = 'true';
        if (isset($_SERVER['ACCESS-CONTROL-REQUEST-METHOD'])) { //校验请求方式
            $methodStr = ', ' . strtoupper(trim($_SERVER['ACCESS-CONTROL-REQUEST-METHOD']));
            if (strpos(', ' . $this->_cors['allow']['methodStr'], $methodStr) === false) {
                return self::RESPONSE_RESULT_TYPE_FORBIDDEN;
            }
        }
        if (isset($_SERVER['ACCESS-CONTROL-REQUEST-HEADERS'])) { //校验请求头
            $controlReqHeaders = explode(',', strtolower($_SERVER['ACCESS-CONTROL-REQUEST-HEADERS']));
            foreach ($controlReqHeaders as $eHeader) {
                $headerName = trim($eHeader);
                if ((strlen($headerName) > 0) && !in_array($headerName, $this->_cors['allow']['headers'], true)) {
                    return self::RESPONSE_RESULT_TYPE_FORBIDDEN;
                }
            }
        }
        if (isset($_SERVER['SY-DOMAIN'])) {
            $cookieDomain = trim($_SERVER['SY-DOMAIN']);
            if (!in_array($cookieDomain, $this->_reqCookieDomains, true)) {
                return self::RESPONSE_RESULT_TYPE_FORBIDDEN;
            }
            $_SERVER['SY-DOMAIN'] = $cookieDomain;
        } else {
            $_SERVER['SY-DOMAIN'] = $this->_reqCookieDomains[0];
        }

        $reqMethod = strtoupper(Tool::getArrayVal($_SERVER, 'REQUEST_METHOD', 'GET'));
        if ($reqMethod == 'OPTIONS') {
            //预请求OPTIONS的响应结果有效时间
            $headers['Access-Control-Max-Age'] = $this->_cors['options']['maxage'];
            $headers['Access-Control-Allow-Methods'] = $this->_cors['allow']['headerStr'];
            $headers['Access-Control-Allow-Headers'] = $this->_cors['allow']['methodStr'];
            return self::RESPONSE_RESULT_TYPE_ALLOW;
        }
        return self::RESPONSE_RESULT_TYPE_ACCEPT;
    }

    /**
     * 处理请求业务
     * @param \swoole_http_request $request
     * @param array $initRspHeaders 初始化响应头
     * @return string
     */
    private function handleReqService(\swoole_http_request $request, array $initRspHeaders) : string
    {
        $uri = Tool::getArrayVal(self::$_reqServers, 'request_uri', '/');
        if($uri == '/killself'){
            BaseServer::setProjectCache('sytag', [
                'value' => '0'
            ]);
            $result = 'kill success';
        }elseif ($uri == '/recoverself'){
            BaseServer::setProjectCache('sytag', [
                'value' => '1'
            ]);
            $result = 'recover success';
        }else{
            $uriCheckRes = $this->checkRequestUri($uri);
            if (strlen($uriCheckRes['error']) > 0) {
                return $uriCheckRes['error'];
            }
            $uri = $uriCheckRes['uri'];
            self::$_reqServers['request_uri'] = $uriCheckRes['uri'];

            $funcName = $this->getPreProcessFunction($uri, $this->preProcessMapFrame, $this->preProcessMapProject);
            if (is_bool($funcName)) {
                $error = new Result();
                $error->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, '预处理函数命名不合法');
                $result = $error->getJson();
                unset($error);
                return $result;
            } elseif (strlen($funcName) > 0) {
                return $this->$funcName($request);
            }

            $this->initRequest($request, $initRspHeaders);

            $error = null;
            $result = '';
            $httpObj = new Http($uri);
            try {
                self::checkRequestCurrentLimit();
                $result = $this->_app->bootstrap()->getDispatcher()->dispatch($httpObj)->getBody();
                if (strlen($result) == 0) {
                    $error = new Result();
                    $error->setCodeMsg(ErrorCode::SWOOLE_SERVER_NO_RESPONSE_ERROR, '未设置响应数据');
                }
            } catch (\Exception $e) {
                SyResponseHttp::header('Content-Type', 'application/json; charset=utf-8');
                if (SY_REQ_EXCEPTION_HANDLE_TYPE) {
                    $error = $this->handleReqExceptionByFrame($e);
                } else {
                    $error = $this->handleReqExceptionByProject($e);
                }
            } finally {
                self::$_syServer->decr(self::$_serverToken, 'request_handling', 1);
                $this->reportLongTimeReq($uri, array_merge($_GET, $_POST), Project::TIME_EXPIRE_SWOOLE_CLIENT_HTTP);
                unset($httpObj);
                if (is_object($error)) {
                    $result = $error->getJson();
                    unset($error);
                }
            }
        }

        return $result;
    }
}
