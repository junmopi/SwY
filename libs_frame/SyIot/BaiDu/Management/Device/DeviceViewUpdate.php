<?php
/**
 * 修改设备View信息
 * User: 姜伟
 * Date: 2019/7/18 0018
 * Time: 23:59
 */
namespace SyIot\BaiDu\Management\Device;

use Constant\ErrorCode;
use Exception\Iot\BaiDuIotException;
use SyIot\IotBaseBaiDu;
use SyIot\IotUtilBaiDu;
use Tool\Tool;

class DeviceViewUpdate extends IotBaseBaiDu
{
    /**
     * 设备名称
     * @var string
     */
    private $deviceName = '';
    /**
     * reported信息
     * @var array
     */
    private $reported = [];
    /**
     * desired信息
     * @var array
     */
    private $desired = [];
    /**
     * 版本号
     * @var int
     */
    private $profileVersion = 0;

    public function __construct()
    {
        parent::__construct();
    }

    private function __clone()
    {
    }

    /**
     * @param string $deviceName
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setDeviceName(string $deviceName)
    {
        if (ctype_alnum($deviceName)) {
            $this->deviceName = $deviceName;
            $this->serviceUri = '/v3/iot/management/deviceView/' . $deviceName;
        } else {
            throw new BaiDuIotException('设备名称不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param array $reported
     */
    public function setReported(array $reported)
    {
        $this->reported = $reported;
    }

    /**
     * @param array $desired
     */
    public function setDesired(array $desired)
    {
        $this->desired = $desired;
    }

    /**
     * @param int $profileVersion
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setProfileVersion(int $profileVersion)
    {
        if ($profileVersion > 0) {
            $this->reqData['profileVersion'] = $profileVersion;
        } else {
            throw new BaiDuIotException('版本号不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        if (strlen($this->deviceName) > 0) {
            throw new BaiDuIotException('设备名称不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        if (empty($this->reported) && empty($this->desired)) {
            throw new BaiDuIotException('reported信息和desired信息不能都为空', ErrorCode::IOT_PARAM_ERROR);
        }
        if (!isset($this->reqData['profileVersion'])) {
            throw new BaiDuIotException('版本号不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        if (!empty($this->reported)) {
            $this->reqData['reported'] = $this->reported;
        }
        if (!empty($this->desired)) {
            $this->reqData['desired'] = $this->desired;
        }

        $this->reqHeader['Authorization'] = IotUtilBaiDu::createSign([
            'req_method' => self::REQ_METHOD_PUT,
            'req_uri' => $this->serviceUri,
            'req_params' => [
                'updateView' => '',
            ],
            'req_headers' => [
                'host',
            ],
        ]);
        $this->curlConfigs[CURLOPT_URL] = $this->serviceProtocol . '://' . $this->serviceDomain . $this->serviceUri . '?updateView';
        $this->curlConfigs[CURLOPT_CUSTOMREQUEST] = self::REQ_METHOD_PUT;
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        return $this->getContent();
    }
}
