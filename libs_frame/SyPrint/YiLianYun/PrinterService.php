<?php

namespace SyPrint\YiLianYun;

class PrinterService
{
    /**
     * 自有型应用授权终端
     * @param $machineCode string 机器码
     * @param $mSign string 机器密钥
     * @param string $printName 打印机昵称
     * @param string $phone gprs卡号
     * @return mixed
     */
    public function addPrinter(array $data)
    {
        $action = 'printer/addprinter';
        $params = [
            'machine_code' => $data['machine_code'],
            'msign' => $data['msign'],
        ];
        if (!empty($data['phone'])) {
            $params['phone'] = $data['phone'];
        }
        if (!empty($data['print_name'])) {
            $params['print_name'] = $data['print_name'];
        }
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 设置内置语音接口
     * 注意: 仅支持K4-WA、K4-GAD、K4-WGEAD型号
     * @param $machineCode string 机器码
     * @param $content string 在线语音地址链接 or 自定义语音内容
     * @param bool $isFile true or false
     * @param string $aid int 0~9 , 定义需设置的语音编号,若不提交,默认升序
     * @return mixed
     */
    public function setVoice(array $data)
    {
        $action = 'printer/setvoice';
        $params = [
            'machine_code' => $data['machine_code'],
            'content' => $data['content'],
            'is_file' => $data['is_file'],
        ];
        if (!empty($data['aid'])) {
            $params['aid'] = $data['aid'];
        }
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 删除内置语音接口
     * 注意: 仅支持K4-WA、K4-GAD、K4-WGEAD型号
     * @param $machineCode string 机器码
     * @param $aid int 0 ~ 9 编号
     * @return mixed
     */
    public function deleteVoice($data)
    {
        $action = 'printer/deletevoice';
        $params = [
            'machine_code' => $data['machine_code'],
            'aid' => $data['aid'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 删除终端授权接口
     * @param $machineCode string 机器码
     * @return mixed
     */
    public function deletePrinter($data)
    {
        $action = 'printer/deleteprinter';
        $params = [
            'machine_code' => $data['machine_code'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 关机重启接口
     * @param $machineCode string 机器码
     * @param $responseType string restart or shutdown
     * @return mixed
     */
    public function shutdownRestart($data)
    {
        $action = 'printer/shutdownrestart';
        $params = [
            'machine_code' => $data['machine_code'],
            'response_type' => $data['response_type'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 声音调节接口
     * @param $machineCode string 机器码
     * @param $voice string 音量 0 or 1 or 2 or 3
     * @param $responseType string buzzer (蜂鸣器) or horn (喇叭)
     * @return mixed
     */
    public function setsound($data)
    {
        $action = 'printer/setsound';
        $params = [
            'machine_code' => $data['machine_code'],
            'voice' => $data['voice'],
            'response_type' => $data['response_type'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 获取机型打印宽度接口
     * @param $machineCode string 机器码
     * @return mixed
     */
    public function printInfo($data)
    {
        $action = 'printer/printinfo';
        $params = [
            'machine_code' => $data['machine_code'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 获取机型软硬件版本接口
     * @param $machineCode string 机器码
     * @return mixed
     */
    public function getVersion($data)
    {
        $action = 'printer/getversion';
        $params = [
            'machine_code' => $data['machine_code'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 取消所有未打印订单接口
     * @param $machineCode string 机器码
     * @return mixed
     */
    public function cancelAll($data)
    {
        $action = 'printer/cancelall';
        $params = [
            'machine_code' => $data['machine_code'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 取消单条未打印订单接口
     * @param $machineCode string 机器码
     * @param $orderId string 未打印的易联云ID
     * @return mixed
     */
    public function cancelOne($data)
    {
        $action = 'printer/cancelone';
        $params = [
            'machine_code' => $data['machine_code'],
            'order_id' => $data['order_id'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 设置logo接口
     * @param $machineCode string 机器码
     * @param $imgUrl string logo链接地址
     * @return mixed
     */
    public function setIcon($data)
    {
        $action = 'printer/seticon';
        $params = [
            'machine_code' => $data['machine_code'],
            'img_url' => $data['img_url'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 取消logo接口
     * @param $machineCode string 机器码
     * @return mixed
     */
    public function deleteIcon($data)
    {
        $action = 'printer/deleteicon';
        $params = [
            'machine_code' => $data['machine_code'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 打印方式接口
     * @param $machineCode string 机器码
     * @param $responseType string btnopen or btnclose
     * @return mixed
     */
    public function btnPrint($data)
    {
        $action = 'printer/deleteicon';
        $params = [
            'machine_code' => $data['machine_code'],
            'response_type' => $data['response_type'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 接单拒单设置接口
     * @param $machineCode string 机器码
     * @param $responseType string open or close
     * @return mixed
     */
    public function getOrder($data)
    {
        $action = 'printer/getorder';
        $params = [
            'machine_code' => $data['machine_code'],
            'response_type' => $data['response_type'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 获取订单状态接口
     * @param $machineCode string 机器码
     * @param $orderId  string 易联云订单id
     * @return mixed
     */
    public function getOrderStatus($data)
    {
        $action = 'printer/getorder';
        $params = [
            'machine_code' => $data['machine_code'],
            'order_id' => $data['order_id'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 获取订单列表接口
     * @param $machineCode string 机器码
     * @param $pageIndex int 第几页
     * @param $pageSize int 查询条数
     * @return mixed
     */
    public function getOrderPagingList($data)
    {
        $action = 'printer/getorderpaginglist';
        $params = [
            'machine_code' => $data['machine_code'],
            'page_index' => $data['page_index'] > 0 ? $data['page_index'] : 1,
            'page_size' => $data['page_size'] > 0 ? $data['page_size'] : 10,
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

    /**
     * 获取终端状态接口
     * @param $machineCode string 机器码
     * @return mixed
     */
    public function getPrintStatus($data)
    {
        $action = 'printer/getprintstatus';
        $params = [
            'machine_code' => $data['machine_code'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }


}