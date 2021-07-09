<?php
/**
 * Created by PhpStorm.
 * User: junmopi
 * Date: 2021/4/14
 * Time: 15:32
 */
namespace DouYin\UserManagement;

use Constant\ErrorCode;
use DouYin\DouYinBaseOpen;
use DouYin\DouYinUtilBase;
use Exception\DouYin\DouYinException;
use Log\Log;
use Tool\Tool;

class FollowList extends DouYinBaseOpen {
    public function __construct(string $open_id, string $access_token = '')
    {
        parent::__construct();
        $this->reqData['open_id'] = $open_id;
        $this->reqData['access_token'] = $access_token;
    }

    private function __clone()
    {
    }

    /**
     * 设置范围
     * @param int $cursor 分页游标, 第一页请求cursor是0, response中会返回下一页请求用到的cursor, 同时response还会返回has_more来表明是否有更多的数据。
     * @param int $count 每页数量
     */
    public function setRange(int $cursor, int $count)
    {
        $this->reqData['cursor'] = $cursor;
        $this->reqData['count'] = $count;
    }

    public function getDetail() : array
    {
        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/following/list?' . http_build_query($this->reqData);
        $sendRes = DouYinUtilBase::sendGetReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if ($sendData['data']['error_code'] > 0) {
            Log::log('DouYinFollowList:' . $sendData['data']['description']);
            throw new DouYinException($sendData['data']['description'], ErrorCode::PRINT_GET_ERROR);
        }

        return $sendData;
    }
}