<?php
/**
 * 获取设备属性数据
 * User: 姜伟
 * Date: 2019/7/24 0024
 * Time: 14:07
 */
namespace SyIot\Tencent\Device;

use Constant\ErrorCode;
use Exception\Iot\TencentIotException;
use SyIot\IotBaseTencent;

class DeviceDataDescribe extends IotBaseTencent
{
    /**
     * 产品ID
     * @var string
     */
    private $ProductId = '';
    /**
     * 设备名称
     * @var string
     */
    private $DeviceName = '';

    public function __construct()
    {
        parent::__construct();
        $this->reqHeader['X-TC-Action'] = 'DescribeDeviceData';
        $this->reqHeader['X-TC-Version'] = '2019-04-23';
    }

    private function __clone()
    {
    }

    /**
     * @param string $productId
     * @throws \Exception\Iot\TencentIotException
     */
    public function setProductId(string $productId)
    {
        if (ctype_alnum($productId)) {
            $this->reqData['ProductId'] = $productId;
        } else {
            throw new TencentIotException('产品ID不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param string $deviceName
     * @throws \Exception\Iot\TencentIotException
     */
    public function setDeviceName(string $deviceName)
    {
        if (strlen($deviceName) > 0) {
            $this->reqData['DeviceName'] = $deviceName;
        } else {
            throw new TencentIotException('设备名称不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        if (!isset($this->reqData['ProductId'])) {
            throw new TencentIotException('产品ID不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        if (!isset($this->reqData['DeviceName'])) {
            throw new TencentIotException('设备名称不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        $this->addReqSign();
        return $this->getContent();
    }
}
