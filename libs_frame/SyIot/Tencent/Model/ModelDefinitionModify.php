<?php
/**
 * 修改产品数据模板
 * User: 姜伟
 * Date: 2019/7/24 0024
 * Time: 14:07
 */
namespace SyIot\Tencent\Model;

use Constant\ErrorCode;
use Exception\Iot\TencentIotException;
use SyIot\IotBaseTencent;
use Tool\Tool;

class ModelDefinitionModify extends IotBaseTencent
{
    /**
     * 产品ID
     * @var string
     */
    private $ProductId = '';
    /**
     * 数据模板定义
     * @var array
     */
    private $ModelSchema = [];

    public function __construct()
    {
        parent::__construct();
        $this->reqHeader['X-TC-Action'] = 'ModifyModelDefinition';
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
     * @param array $modelSchema
     * @throws \Exception\Iot\TencentIotException
     */
    public function setModelSchema(array $modelSchema)
    {
        if (empty($modelSchema)) {
            throw new TencentIotException('数据模板定义不合法', ErrorCode::IOT_PARAM_ERROR);
        }
        $this->reqData['ModelSchema'] = Tool::jsonEncode($modelSchema, JSON_UNESCAPED_UNICODE);
    }

    public function getDetail() : array
    {
        if (!isset($this->reqData['ProductId'])) {
            throw new TencentIotException('产品ID不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        if (!isset($this->reqData['ModelSchema'])) {
            throw new TencentIotException('数据模板定义不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        $this->addReqSign();
        return $this->getContent();
    }
}
