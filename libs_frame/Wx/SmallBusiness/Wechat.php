<?php
/**
 * wechat.php
 *
 * Created by PhpStorm.
 * author: yandy  <yandycom@126.com>
 * DateTime: 2019-04-19
 */

namespace Wx;

use Exception\Wx\WxBusinessException;

class Wechat extends BaseWechat implements WechatApi {
	use \Wx\Certificate;
	use \Wx\UploadMedia;

	/**
	 * applyEnter 申请入驻小微商户
	 * @return mixed
	 */
    public function applyEnter(array $params) {
        //校验银行卡号前缀是否支持
		if ($this->accountNumberIsSupport(isset($params['account_number'])?$params['account_number']:'')) {
			throw new WxBusinessException(20003);
		}

		if(!$this->checkParams($params))
		    throw new WxBusinessException(20004);


		$data = [
			'version' => '3.0',
			'cert_sn' => $this->newResponseData()['serial_no'],
			'mch_id' => $this->mch_id,
			'nonce_str' => $this->getRandChar(),
			'sign_type' => 'HMAC-SHA256',
			'sign' => '',
			'business_code' => $params['business_code'], // 业务申请编号,服务商自定义的商户唯一编号
			'id_card_copy' => $params['id_card_copy'], // 身份证人像面照片  media_id
			'id_card_national' => $params['id_card_national'], // 身份证国徽面照片
			'id_card_name' => $this->publicKeyEncrypt($params['id_card_name']),
			'id_card_number' => $this->publicKeyEncrypt($params['id_card_number']),
			'id_card_valid_time' => htmlspecialchars_decode($params['id_card_valid_time']), // '["1970-01-01","长期"]' string(50)
			'account_name' => $this->publicKeyEncrypt($params['account_name']),
			'account_bank' => $params['account_bank'],
			'bank_address_code' => $params['bank_address_code'],
			'bank_name' => isset($params['bank_name'])?$params['bank_name']:'',
			'account_number' => $this->publicKeyEncrypt($params['account_number']),
			'store_name' => $params['store_name'],
			'store_address_code' => $params['store_address_code'],
			'store_street' => $params['store_street'],
			'store_longitude' => isset($params['store_longitude'])?$params['store_longitude']:'',
			'store_latitude' => isset($params['store_longitude'])?$params['store_longitude']:'',
			'store_entrance_pic' => $params['store_entrance_pic'],
			'indoor_pic' => $params['indoor_pic'],
			'address_certification' => isset($params['address_certification'])?$params['address_certification']:'',
			'merchant_shortname' => $params['merchant_shortname'],
			'service_phone' => $params['service_phone'],
			//'business' => $params['business'],
			'product_desc' => isset($params['product_desc'])?$params['product_desc']:'',
			//'qualifications' =>isset($params['qualifications'])?$params['qualifications']:'',
			'rate' => isset($params['rate'])?$params['rate']:'0.6%',
			'business_addition_desc' => isset($params['business_addition_desc'])?$params['business_addition_desc']:'',
			'business_addition_pics' => isset($params['business_addition_pics'])?$params['business_addition_pics']:'', // ["123","456"] 最多可上传5张照片，请填写已预先上传图片生成好的MediaID
			'contact' => $this->publicKeyEncrypt($params['contact']),
			'contact_phone' => $this->publicKeyEncrypt($params['contact_phone']),
			'contact_email' => isset($params['contact_email']) && !empty($params['contact_email']) ? $this->publicKeyEncrypt($params['contact_email']) : '',
		];

		// 签名
		$data['sign'] = $this->makeSign($data, $data['sign_type']);
		$url = self::WXAPIHOST . 'applyment/micro/submit';
		// 数组转xml
		$xml = $this->toXml($data);

		//var_dump($data); die;

		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);

		// 处理返回值
		$rt = $this->disposeReturn($res, ['applyment_id'], ['business_code' => $data['business_code']]);

		return $rt;
	}

	/**
	 * submitUpGrade 小微商户升级接口
	 * @return array
	 * @throws WxBusinessException
	 * @author   liuml  <liumenglei0211@163.com>
	 * @DateTime 2019-02-28  18:04
	 */
	public function submitUpGrade(array $params) {

		// 查询此商户是否已经升级
        // 可以自己实现逻辑判断

		$data = [
			'version' => '1.0',
			'mch_id' => $this->mch_id,
			'nonce_str' => $this->getRandChar(),
			'sign_type' => 'HMAC-SHA256',
			'sign' => '',
			'cert_sn' => $this->newResponseData()['serial_no'],
			'sub_mch_id' => $params['sub_mch_id'] ?? '', // 小微商户号
			'organization_type' => $params['organization_type'] ?? '', // 主体类型
			'business_license_copy' => $params['business_license_copy'] ?? '', // 营业执照扫描件
			'business_license_number' => $params['business_license_number'] ?? '', // 营业执照注册号
			'merchant_name' => $params['merchant_name'] ?? '', // 商户名称
			'company_address' => $params['company_address'] ?? '', // 注册地址
			'legal_person' => $this->publicKeyEncrypt($params['legal_person']), // 经营者姓名 / 法定代表人
			'business_time' => $params['business_time'] ?? '', // 营业期限
			'business_licence_type' => $params['business_licence_type'] ?? '', // 营业执照类型
			'organization_copy' => $params['organization_copy'] ?? '', // 组织机构代码证照片
			'organization_number' => $params['organization_number'] ?? '', // 组织机构代码
			'organization_time' => $params['organization_time'] ?? '', // 组织机构代码有效期限
			'account_name' => isset($params['contact_email']) && !empty($params['account_name']) ? $this->publicKeyEncrypt($params['account_name']) : '', // 开户名称
			'account_bank' => $params['account_bank'] ?? '', // 开户银行
			'bank_address_code' => $params['bank_address_code'] ?? '', // 开户银行省市编码
			'bank_name' => $params['bank_name'] ?? '', // 开户银行全称（含支行）
			'account_number' => isset($params['contact_email']) && !empty($params['account_number']) ? $this->publicKeyEncrypt($params['account_number']) : '', // 银行卡号
			'merchant_shortname' => $params['merchant_shortname'] ?? '', // 商户简称
			'business' => $params['business'] ?? '', // 费率结算规则 ID
			'qualifications' => $params['qualifications'] ?? '', // 特殊资质
			'business_scene' => $params['business_scene'] ?? '', // 经营场景
			'business_addition_desc' => $params['business_addition_desc'] ?? '', // 补充说明
			'business_addition_pics' => $params['business_addition_pics'] ?? '', // 补充材料
			// 以下字段在 business_scene 为线下场景，既值为 "[1721]" 时无需填写，若包含其它场景，请按以下规则填写
			'mp_appid' => $params['mp_appid'] ?? '', // 公众号 APPID
			'mp_app_screen_shots' => $params['mp_app_screen_shots'] ?? '', // 公众号页面截图
			'miniprogram_appid' => $params['miniprogram_appid'] ?? '', // 小程序 APPID
			'miniprogram_screen_shots' => $params['miniprogram_screen_shots'] ?? '', // 小程序页面截图
			'app_appid' => $params['app_appid'] ?? '', // 应用 APPID
			'app_screen_shots' => $params['app_screen_shots'] ?? '', // APP 截图
			'app_download_url' => $params['app_download_url'] ?? '', // APP 下载链接
			'web_url' => $params['web_url'] ?? '', // PC 网站域名
			'web_authoriation_letter' => $params['web_authoriation_letter'] ?? '', // 网站授权函
			'web_appid' => $params['web_appid'] ?? '', // PC 网站对应的公众号 APPID
		];

		// 签名
		$data['sign'] = $this->makeSign($data, $data['sign_type']);
		$url = self::WXAPIHOST . 'applyment/micro/submitupgrade';
		// 数组转xml
		$xml = $this->toXml($data);
		// 发起接口升级申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		$rt = $this->disposeReturn($res);

		return $rt;
	}

	/**
	 * upGradeIsThrough 小微商户升级接口状态查询
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 * @author   liuml  <liumenglei0211@163.com>
	 * @DateTime 2019-03-04  11:50
	 */
	public function upGradeIsThrough(array $params) {
		if (!isset($params['sub_mch_id'])) {
			throw new WxBusinessException(20004);
		}

		$data = [
			'version' => '1.0',
			'mch_id' => $this->mch_id,
			'nonce_str' => $this->getRandChar(),
			'sign_type' => 'HMAC-SHA256',
			'sign' => '',
			'sub_mch_id' => $params['sub_mch_id'] ?? '',
		];

		$data['sign'] = $this->makeSign($data, $data['sign_type']);
		$url = self::WXAPIHOST . 'applyment/micro/getupgradestate32';
		$xml = $this->toXml($data);

		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		$rt = $this->disposeReturn($res, [
			'sub_mch_id',
			'applyment_state',
			'applyment_state_desc',
			'sign_qrcode',
			'sign_url',
			'audit_detail',
			// 以下字段当 applyment_state 为 ACCOUNT_NEED_VERIFY 时有返回，请商户按照以下信息进行汇款，以完成账户验证
			// 注：1、未填写对公账户的个体户，无此账户验证环节；2、验证结束后，汇款金额将全额退还至汇款账户。
			'account_name',
			'pay_amount',
			'destination_account_number',
			'destination_account_name',
			'destination_account_bank',
			'city',
			'remark',
			'deadline_time',
		]);
		$audit_detail = $rt['audit_detail'];
		$rt['audit_detail'] = json_decode($audit_detail, true);

		return $rt;
	}

    /**
     * 企业付款到用户零钱
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     * @param string $timestamp 支付时间
     * @return array
     */
    public function createJsBizPackage($openid, $totalFee, $outTradeNo)
    {
        $config = array(
            'mch_id' => $this->mch_id,
            'appid' => $this->appid,
            'key' => $this->aes_key,
        );
        $unified = array(
            'mch_appid' => $config['appid'],
            'mchid' => $config['mch_id'],
            'nonce_str' => $this->getRandChar(),
            'openid' => $openid,
            'check_name'=>'NO_CHECK',        //校验用户姓名选项。NO_CHECK：不校验真实姓名，FORCE_CHECK：强校验真实姓名
            //'re_user_name'=>$trueName,                 //收款用户真实姓名（不支持给非实名用户打款）
            'partner_trade_no' => $outTradeNo,
            'spbill_create_ip' => '127.0.0.1',
            'amount' => intval($totalFee * 100),       //单位 转为分
            'desc'=>'提现',            //企业付款操作说明信息
        );
        $unified['sign'] = $this->makeSign($unified, 'md5');

        //$responseXml = $this->curlPost('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', self::arrayToXml($unified));
        $url = self::WXAPIHOST . 'mmpaymkttransfers/promotion/transfers';

        $xml = $this->toXml($unified);
        // 发起请求
        $res = $this->httpsRequest($url, $xml, [], true);

        $rt = $this->disposeReturn($res, [
            'partner_trade_no',
            'payment_no',
            'payment_time',
        ]);

        return $rt;
    }


	/**
	 * getBusinessCode 生成业务申请编号
	 * @return mixed|null|string|string[]
	 */
	public function getBusinessCode() {
		$millisecond = $this->getMillisecond();
		return mb_strtoupper(md5(uniqid($millisecond . mt_rand())));
	}

	/**
	 * accountNumberIsSupport 判断银行卡账号是否支持
	 * @param $account_number
	 * @return bool
	 */
	private function accountNumberIsSupport(string $account_number) {
		$account_prefix_6 = substr($account_number, 0, 6);
		$account_prefix_8 = substr($account_number, 0, 8);

		$not_support = ['623501', '621468', '620522', '625191', '622384', '623078', '940034', '622150', '622151', '622181', '622188', '955100', '621095', '620062', '621285', '621798', '621799', '621797', '622199', '621096', '62215049', '62215050', '62215051', '62218849', '62218850', '62218851', '621622', '623219', '621674', '623218', '621599', '623698', '623699', '623686', '621098', '620529', '622180', '622182', '622187', '622189', '621582', '623676', '623677', '622812', '622810', '622811', '628310', '625919', '625368', '625367', '518905', '622835', '625603', '625605', '518905'];
		if (array_search($account_prefix_6, $not_support)) {
			return true;
		}
		if (array_search($account_prefix_8, $not_support)) {
			return true;
		}
		return false;
	}

	/**
	 * checkParams 校验入驻接口必填字段信息
	 * @param array $params
	 * @return bool
	 */
	private function checkParams(array $params) {
		$data = ['id_card_copy', 'id_card_national', 'id_card_name', 'id_card_number', 'id_card_valid_time', 'account_name', 'account_bank', 'bank_address_code', 'account_number', 'store_name', 'store_address_code', 'store_street', 'store_entrance_pic', 'indoor_pic', 'merchant_shortname', 'service_phone', 'contact', 'contact_phone'];
		$result = true;
		foreach ($data as $key => $value) {
			if (!isset($params[$value]) || empty($params[$value])) {
				$result = false;
				break;
			}
		}
		return $result;
	}

	/**
	 * applyIsExist 查询申请是否存在且申请状态是成功
	 * @param $id_card_number
	 * @param $id_card_name
	 * @return bool
	 */
	public function applyIsExist($id_card_number, $id_card_name) {

    }

	/**
	 * enquiryOfApplyStatus 入驻申请状态查询
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 */
	public function enquiryOfApplyStatus(array $params) {
		if (!isset($params['applyment_id']) && !isset($params['business_code'])) {
			throw new WxBusinessException(20004);
		}

		$data = [
			'version' => '1.0',
			'mch_id' => $this->mch_id,
			'nonce_str' => $this->getRandChar(),
			'sign_type' => 'HMAC-SHA256',
			'sign' => '',
			'applyment_id' => $params['applyment_id'] ?? '',
			'business_code' => $params['business_code'] ?? '',
		];

		$data['sign'] = $this->makeSign($data, $data['sign_type']);
		$url = self::WXAPIHOST . 'applyment/micro/getstate';
		$xml = $this->toXml($data);

		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		$rt = $this->disposeReturn($res, [
			'applyment_id',
			'applyment_state',
			'applyment_state_desc',
			'sub_mch_id',
			'sign_url',
			'audit_detail',
		], ['business_code' => $data['business_code']]);
		$audit_detail = $rt['audit_detail'];
		$rt['audit_detail'] = json_decode($audit_detail, true);

		//var_dump($rt); die;

		return $rt;
	}

	/**
	 * tenantConfig  关注配置  小微商户关注功能配置API
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 */
	public function tenantConfig(array $params) {
		if (!isset($params['sub_mch_id'])) {
			throw new WxBusinessException(20004, '小微商户号必填');
		}
		$data = [
			'mch_id' => $this->mch_id,
			'sub_mch_id' => $params['sub_mch_id'],
			'nonce_str' => $this->getRandChar(),
			'sign_type' => 'HMAC-SHA256',
			'sign' => '',
			'sub_appid' => $params['sub_appid'] ?? 'NULL',
			'subscribe_appid' => $params['subscribe_appid'] ?? '',
			'receipt_appid' => $params['receipt_appid'] ?? '',
		];
		$data['sign'] = $this->makeSign($data, $data['sign_type']);
		$url = self::WXAPIHOST . 'secapi/mkt/addrecommendconf';
		$xml = $this->toXml($data);
		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		return $this->disposeReturn($res, ['subscribe_appid', 'receipt_appid']);
	}

	/**
	 * payTheDirectoryConfig  支付目录配置   小微商户开发配置新增支付目录API
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 */
	public function payTheDirectoryConfig(array $params) {
		if (!isset($params['sub_mch_id'])) {
			throw new WxBusinessException(20004, '小微商户号必填');
		}
		if (!isset($params['jsapi_path'])) {
			throw new WxBusinessException(20004, '授权目录必填');
		}
		if (!isset($params['appid'])) {
			throw new WxBusinessException(20004, '服务商的公众账号 ID 必填');
		}
		$data = [
			'appid' => $params['appid'],
			'mch_id' => $this->mch_id,
			'sub_mch_id' => $params['sub_mch_id'],
			'jsapi_path' => $params['jsapi_path'],
			'sign' => '',
		];
		$data['sign'] = $this->makeSign($data, 'md5');
		$url = self::WXAPIHOST . 'secapi/mch/addsubdevconfig';
		$xml = $this->toXml($data);
		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		return $this->disposeReturn($res);
	}

	/**
	 * bindAppIdConfig 绑定appid配置  小微商户新增对应APPID关联API
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 */
	public function bindAppIdConfig(array $params) {
		if (!isset($params['sub_mch_id'])) {
			throw new WxBusinessException(20004, '小微商户号必填');
		}
		if (!isset($params['sub_appid'])) {
			throw new WxBusinessException(20004, '关联 APPID 必填');
		}
		if (!isset($params['appid'])) {
			throw new WxBusinessException(20004, '服务商的公众账号 ID 必填');
		}
		$data = [
			'appid' => $params['appid'],
			'mch_id' => $this->mch_id,
			'sub_mch_id' => $params['sub_mch_id'],
			'sub_appid' => $params['sub_appid'],
			'sign' => '',
		];
		$data['sign'] = $this->makeSign($data, 'md5');
		$url = self::WXAPIHOST . 'secapi/mch/addsubdevconfig';
		$xml = $this->toXml($data);
		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		return $this->disposeReturn($res);
	}

	/**
	 * inquireConfig 查询配置
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 */
	public function inquireConfig(array $params) {
		if (!isset($params['sub_mch_id'])) {
			throw new WxBusinessException(20004, '小微商户号必填');
		}
		if (!isset($params['appid'])) {
			throw new WxBusinessException(20004, '服务商的公众账号 ID 必填');
		}
		$data = [
			'mch_id' => $this->mch_id,
			'sub_mch_id' => $params['sub_mch_id'],
			'sign' => '',
			'appid' => $params['appid'],
		];
		$data['sign'] = $this->makeSign($data, 'md5');
		$url = self::WXAPIHOST . 'secapi/mch/querysubdevconfig';
		$xml = $this->toXml($data);
		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		$rt = $this->disposeReturn($res, ['jsapi_path_list', 'appid_config_list']);
		$rt['jsapi_path_list'] = json_decode($rt['jsapi_path_list'], true);
		$rt['appid_config_list'] = json_decode($rt['appid_config_list'], true);
		return $rt;
	}

	/**
	 * modifyArchives 小微商户修改资料接口-修改结算银行卡
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 */
	public function modifyArchives(array $params) {
		if (!isset($params['sub_mch_id'])) {
			throw new WxBusinessException(20004, '小微商户号必填');
		}

		$data = [
			'version' => '1.0',
			'mch_id' => $this->mch_id,
			'nonce_str' => $this->getRandChar(),
			'sign_type' => 'HMAC-SHA256',
			'sub_mch_id' => $params['sub_mch_id'],
			'sign' => '',
			'account_number' => isset($params['account_number']) ? $this->publicKeyEncrypt($params['account_number']) : '',
			'bank_name' => $params['bank_name'] ?? '',
			'account_bank' => $params['account_bank'] ?? '',
			'bank_address_code' => $params['bank_address_code'] ?? '',
			'cert_sn' => $this->newResponseData()['serial_no'],
		];
		$data['sign'] = $this->makeSign($data, $data['sign_type']);
		$url = self::WXAPIHOST . 'applyment/micro/modifyarchives';
		$xml = $this->toXml($data);
		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		return $this->disposeReturn($res, ['sub_mch_id']);
	}

	/**
	 * withdrawalState 服务商帮小微商户查询自动提现 - 查询提现状态
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 */
	public function withdrawalState(array $params) {
		if (!isset($params['sub_mch_id'])) {
			throw new WxBusinessException(20004, '小微商户号必填');
		}
		if (!isset($params['date'])) {
			throw new WxBusinessException(20004, '日期必填');
		}

		$data = [
			'mch_id' => $this->mch_id,
			'nonce_str' => $this->getRandChar(),
			'sign_type' => 'HMAC-SHA256',
			'sub_mch_id' => $params['sub_mch_id'],
			'sign' => '',
			'date' => $params['date'],
		];
		$data['sign'] = $this->makeSign($data, $data['sign_type']);
		$url = self::WXAPIHOST . 'fund/queryautowithdrawbydate';
		$xml = $this->toXml($data);
		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		$rt = $this->disposeReturn($res, [
			'date',
			'sub_mch_id',
			'withdraw_status',
			'fail_reason',
			'withdraw_id',
			'amount',
			'create_time',
			'success_time',
			'refund_time',
		]);
		$rt['withdraw_status_desc'] = $this->withdrawStatusDesc($rt['withdraw_status']);
		return $rt;
	}

	/**
	 * withdrawStatusMsg 提现状态单据状态字段的中文描述
	 * @param $key
	 * @return mixed|string
	 */
	private function withdrawStatusDesc($key) {
		$status = [
			'PROCESSING' => '提现处理中',
			'SUCCESS' => '提现操作成功',
			'REFUNDED' => '银行处理失败，提现操作退票',
			'SUPPORT_RE_WITHDRAW' => '可重新发起提现',
			'SUPPORT_WITHDRAW' => '当日无提现单，并且当日净交易额大于0，因此支持发起该日自动提现',
			'NOT_SUPPORT_WITHDRAW' => '当日距今超过30天；或当日无提现单，并且当日净交易额不大于0，因此不支持发起该日自动提现',
			'NO_WITHDRAW_AUTH' => '商户无提现权限',
		];
		return $status[$key] ?? '';
	}

	/**
	 * reAutoWithdrawByDate 重新发起提现 - 服务商帮小微商户重新发起自动提现
	 * @param array $params
	 * @return array
	 * @throws WxBusinessException
	 */
	public function reAutoWithdrawByDate(array $params) {
		if (!isset($params['sub_mch_id'])) {
			throw new WxBusinessException(20004, '小微商户号必填');
		}
		if (!isset($params['date'])) {
			throw new WxBusinessException(20004, '日期必填');
		}

		$data = [
			'mch_id' => $this->mch_id,
			'nonce_str' => $this->getRandChar(),
			'sign_type' => 'HMAC-SHA256',
			'sub_mch_id' => $params['sub_mch_id'],
			'sign' => '',
			'date' => $params['date'],
		];
		$data['sign'] = $this->makeSign($data, $data['sign_type']);
		$url = self::WXAPIHOST . 'fund/reautowithdrawbydate';
		$xml = $this->toXml($data);
		// 发起入驻申请请求
		$res = $this->httpsRequest($url, $xml, [], true);
		// 处理返回值
		return $this->disposeReturn($res, [
			'date',
			'sub_mch_id',
			'withdraw_id',
			'amount',
			'create_time',
		]);
	}

	/**
	 * getApplyEnterInfo 返回申请入驻详情
	 * @param $id
	 * @return array
	 * @author   liuml  <liumenglei0211@163.com>
	 * @DateTime 2018/9/19  15:29
	 */
	public function getApplyEnterInfo($id) {

	}

	/**
	 * setShowImgUrl 设置图片链接
	 * @param $imgs
	 * @return false|\Illuminate\Contracts\Routing\UrlGenerator|string
	 * @author   liuml  <liumenglei0211@163.com>
	 * @DateTime 2018/9/19  15:53
	 */
	public function setShowImgUrl($imgs) {
		if (is_string($imgs)) {
			$imgs ?? 'default';
			$imgs = url("/api/showimg/{$imgs}");
		}
		if (is_array($imgs)) {
			array_map(function ($v) {
				return url("/api/showimg/{$v}");
			}, $imgs);
			$imgs = json_encode($imgs);
		}
		return $imgs;
	}

	/**
	 * getBusiness 获取类目中文意思
	 * @param $business
	 * @return mixed
	 * @author   liuml  <liumenglei0211@163.com>
	 * @DateTime 2018/9/20  12:10
	 */
	public function getBusiness($business) {
		$str = '{ "116": "运动户外(线下零售)", "123": "计生用品(线下零售)", "148": "运动健身场馆(休闲娱乐)", "209": "便利店(线下零售)", "292": "食品生鲜(线下零售)[需要特殊资质:1.销售食用农产品：无需特殊资质2.销售加工非食用农产品需提供：《食品流通许可证》或《食品卫生许可证》或《食品经营许可证》或《保健食品经营卫生许可证》（四选一）]", "293": "家具建材/家居厨具(线下零售)", "294": "美妆个护(线下零售)", "295": "礼品鲜花/农资绿植(线下零售)", "296": "汽车用品(线下零售)", "297": "服饰箱包(线下零售)", "298": "钟表眼镜(线下零售)", "299": "婚庆/摄影(居民生活/商业服务)", "300": "俱乐部/休闲会所(休闲娱乐)", "301": "旅馆/酒店/度假区(交通运输/票务旅游)", "305": "宠物/宠物用品(线下零售)", "306": "装饰/设计(居民生活/商业服务)", "307": "娱乐票务(交通运输/票务旅游)", "319": "数码电器/电脑办公(线下零售)", "320": "家政/维修服务(居民生活/商业服务)", "321": "广告/会展/活动策划(居民生活/商业服务)", "323": "图书音像/文具乐器(线下零售)", "324": "苗木种植/园林绿化(居民生活/商业服务)", "551": "其他中餐(餐饮)", "552": "西餐(餐饮)", "553": "日韩/东南亚菜(餐饮)", "554": "咖啡厅(餐饮)", "555": "火锅(餐饮)", "556": "烧烤(餐饮)", "557": "快餐(餐饮)", "560": "小吃/熟食(餐饮)", "561": "烘焙糕点(餐饮)", "562": "甜品饮品(餐饮)", "577": "酒吧(休闲娱乐)", "580": "美发/美容/美甲店(休闲娱乐)[需要特殊资质:如涉及医疗美容技术内容需提供《医疗机构执业许可证》]", "586": "批发业(线下零售)[需要特殊资质:一、食盐批发：食盐批发许可证+国务院盐业主管机构备案证明文件 二、医疗器械批发：批发第二类医疗器械需取得食品药品监督管理部门备案证明；批发第三类医疗器械需取得《医疗器械经营许可证》 三、批发报纸、期刊、图书、音像制品、电子出版物等：《出版物经营许可证》]" }';
		$arr = json_decode($str, true);
		return $arr[$business];
	}

	/**
	 * getStoreAddress 传code获取中文地址
	 * @param $store_address_code
	 * @return mixed
	 * @author   liuml  <liumenglei0211@163.com>
	 * @DateTime 2018/9/20  12:11
	 */
	public function getStoreAddress($store_address_code) {
		$jsonStr = file_get_contents( '/json/cityCode.json');
		$arr = json_decode($jsonStr, true);
		return $arr[$store_address_code];
	}

}
