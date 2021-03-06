<?php
/**
 * 查询设备Profile列表
 * User: 姜伟
 * Date: 2019/7/17 0017
 * Time: 17:14
 */
namespace SyIot\BaiDu\Management\Device;

use Constant\ErrorCode;
use Exception\Iot\BaiDuIotException;
use SyIot\IotBaseBaiDu;
use SyIot\IotUtilBaiDu;

class DeviceList extends IotBaseBaiDu
{
    /**
     * 页码
     * @var int
     */
    private $pageNo = 1;
    /**
     * 每页个数
     * @var int
     */
    private $pageSize = 0;
    /**
     * 排序字段
     * @var string
     */
    private $orderBy = '';
    /**
     * 排序方式
     * @var string
     */
    private $order = '';
    /**
     * 属性名
     * @var string
     */
    private $name = '';
    /**
     * 属性名对应值
     * @var string
     */
    private $value = '';
    /**
     * 收藏标识
     * @var string
     */
    private $favourite = '';

    public function __construct()
    {
        parent::__construct();
        $this->serviceUri = '/v3/iot/management/device';
        $this->reqData['pageNo'] = 1;
        $this->reqData['pageSize'] = 10;
        $this->reqData['orderBy'] = 'name';
        $this->reqData['order'] = 'asc';
        $this->reqData['favourite'] = 'all';
    }

    private function __clone()
    {
    }

    /**
     * @param int $pageNo
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setPageNo(int $pageNo)
    {
        if ($pageNo > 0) {
            $this->reqData['pageNo'] = $pageNo;
        } else {
            throw new BaiDuIotException('页码不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param int $pageSize
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setPageSize(int $pageSize)
    {
        if (($pageSize > 0) && ($pageSize <= 200)) {
            $this->reqData['pageSize'] = $pageSize;
        } else {
            throw new BaiDuIotException('每页个数不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param string $orderBy
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setOrderBy(string $orderBy)
    {
        if (in_array($orderBy, ['createTime', 'name', 'lastActiveTime'])) {
            $this->reqData['orderBy'] = $orderBy;
        } else {
            throw new BaiDuIotException('排序字段不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param string $order
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setOrder(string $order)
    {
        if (in_array($order, ['asc','desc'])) {
            $this->reqData['order'] = $order;
        } else {
            throw new BaiDuIotException('排序方式不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param string $name
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setName(string $name)
    {
        if (strlen($name) > 0) {
            $this->reqData['name'] = $name;
        } else {
            throw new BaiDuIotException('属性名不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param string $value
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setValue(string $value)
    {
        if (strlen($value) > 0) {
            $this->reqData['value'] = $value;
        } else {
            throw new BaiDuIotException('属性名对应值不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param string $favourite
     * @throws \Exception\Iot\BaiDuIotException
     */
    public function setFavourite(string $favourite)
    {
        if (in_array($favourite, ['true', 'false', 'all'])) {
            $this->reqData['favourite'] = $favourite;
        } else {
            throw new BaiDuIotException('收藏标识不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        $this->reqHeader['Authorization'] = IotUtilBaiDu::createSign([
            'req_method' => self::REQ_METHOD_GET,
            'req_uri' => $this->serviceUri,
            'req_params' => $this->reqData,
            'req_headers' => [
                'host',
            ],
        ]);
        $this->curlConfigs[CURLOPT_URL] = $this->serviceProtocol . '://' . $this->serviceDomain . $this->serviceUri . '?' . http_build_query($this->reqData);
        return $this->getContent();
    }
}
