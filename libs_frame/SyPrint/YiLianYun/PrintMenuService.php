<?php

namespace SyPrint\YiLianYun;

class PrintMenuService
{
    /**
     * 添加应用菜单接口
     * @param $machineCode string 机器码
     * @param $content  string 菜单详情(json)
     * @return mixed
     */
    public function addPrintMenu(array $data)
    {
        $action = 'printmenu/addprintmenu';
        $params = [
            'machine_code' => $data['machine_code'],
            'content' => $data['content'],
        ];
        $rpcClient = new YlyRpcClient($data['access_token'], $data['client_id'], $action, $params);
        $res = $rpcClient->getDetail();

        return $res;
    }

}