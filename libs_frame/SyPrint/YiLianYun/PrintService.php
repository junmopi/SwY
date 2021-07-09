<?php

namespace SyPrint\YiLianYun;

class PrintService
{

    /**
     * 打印接口
     * @param $machineCode string 机器码
     * @param $content string 打印内容
     * @param $originId string 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母
     * @return mixed
     */
    public function index(array $data)
    {
        $action = 'print/index';
        $params = [
            'machine_code' => $data['machine_code'],
            'content' => $data['content'],
            'origin_id' => $data['origin_id'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }
}