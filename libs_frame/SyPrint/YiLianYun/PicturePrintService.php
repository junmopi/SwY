<?php

namespace SyPrint\YiLianYun;

class PicturePrintService
{
    /**
     * 图形打印接口
     * 不支持机型: k4-wh, k4-wa, m1
     * @param $machineCode string 机器码
     * @param $pictureUrl string 图片链接地址
     * @param $originId string 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母
     * @return mixed
     */
    public function index(array $data)
    {
        $action = 'pictureprint/index';
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