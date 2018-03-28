<?php

namespace Home\Controller;

use Think\Controller;

class ZzgxController extends CommonController {
	private $accountModel;
	private $exchApply;
	private $exchApplyCoupon;
	public function __construct() {
		parent::__construct ();
		$this->accountModel = M ( 'sysuser_account' ); // 用户登录信息
		$this->exchApply = M ( 'zzgx_exch_apply' );
		$this->exchApplyCoupon = M ( 'zzgx_exch_apply_coupon' );
	}
	
	// 空操作
	public function _empty() {
		echo '→_→ 404';
	}
	
	// 兑换干洗券
	public function exchCoupon() {
		$chargeType = I ( 'post.chargeType', -1, 'intval' );
		$vercode = I ( 'post.vercode', '', 'trim,strip_tags,stripslashes' );
		$mobile = I ( 'post.mobile', '', 'trim,strip_tags,stripslashes' );
		$ret = array (
				'code' => - 1,
				'msg' => 'unkown error' 
		);
		
		// 过滤数据
		// 类型
		if ($chargeType !== 1 && $chargeType !== 2) {
			$ret ['msg'] = '参数有误';
			$this->ajaxReturn ( $ret );
		}
		
		//验证码
		if(empty($vercode)){
			$ret ['msg'] = '验证码错误';
			$this->ajaxReturn ( $ret );
		}
		$reg = '/^\w{4}$/';
		if (!preg_match($reg, $vercode)) {
			$ret ['msg'] = '验证码错误';
			$this->ajaxReturn ( $ret );
		}
		$verify = new \Think\Verify();
		if(!$verify->check($vercode)){
			$ret ['msg'] = '验证码错误';
			$this->ajaxReturn($ret);
		}
		// 手机号码
		$reg = '/^1\d{10}$/';
		if (!preg_match($reg,$mobile)) {
			$ret ['msg'] = '手机号错误';
			$this->ajaxReturn ( $ret );
		}
		$where = array ();
		$where ['mobile'] = $mobile;
		$userId = $this->accountModel->where ( $where )->getField ( 'user_id' );
		if (! is_numeric ( $userId )) {
			$ret ['msg'] = '该手机未注册';
			$this->ajaxReturn ( $ret );
		}
		// 检测兑换比率
		$exchRate = C ( 'ZZGX_EXCH_RATE' );
		if (! is_numeric ( $exchRate ) || ($exchRate >= 1)) {
			$ret ['msg'] = '系统错误（rate）';
			$this->ajaxReturn ( $ret );
		}
		
		if ($chargeType == 1) {
			// 干洗券
			$this->exchCouponList ( $userId, $mobile );
		} else if ($chargeType == 2) {
			// 干洗卡
			$this->exchCouponCard ( $userId, $mobile );
		}
	}
	private function exchCouponList($userId, $mobile) {
		$cpList = I ( 'post.cpList', '', 'trim,strip_tags,stripslashes' );
		$cpList = rtrim ( $cpList, '|' );
		$cpStrArr = explode ( '|', $cpList );
		if (empty ( $cpStrArr )) {
			$ret ['msg'] = '参数异常（cpList）';
			$this->ajaxReturn ( $ret );
		}
		
		$exchRate = C ( 'ZZGX_EXCH_RATE' );
		
		$where = array();
		$where['user_id'] = $this->uid;
		$applyUsername = $this->accountModel->where($where)->getField ('mobile');
		if(empty($applyUsername)){
			$ret ['msg'] = '未获取到您的手机号';
			$this->ajaxReturn ( $ret );
		}
		
		$dataList = array (); // 干洗券
		$data = array ();
		$data ['user_id'] = $userId;
		$data ['mobile'] = $mobile;
		$data ['exch_rate'] = $exchRate;
		$data ['coupon_type'] = 1;
		
		$couponAmt = 0; // 总额
		$couponNum = 0; // 总数量
		
		foreach ( $cpStrArr as $cpStr ) {
			$cpArr = explode ( '_', $cpStr );
			$cpPirce = $cpArr [0];
			$cpNum = $cpArr [1];
			// 检测数据
			$data ['coupon_price'] = $cpPirce; // 单张面额
			$data ['coupon_number'] = $cpNum; // 对应面额数量
			$couponAmt += ($cpPirce * $cpNum);
			$couponNum += $cpNum;
			$dataList [] = $data;
		}
		
		$data = array ();
		$data ['apply_userid'] = $this->uid;
		$data ['apply_username'] = $applyUsername;
		$data ['user_id'] = $userId;
		$data ['mobile'] = $mobile;
		$data ['coupon_amount'] = $couponAmt;
		$data ['coupon_num'] = $couponNum;
		$data ['coupon_type'] = 1;
		$data ['applay_amount'] = $couponAmt * $exchRate;
		$data ['exch_rate'] = $exchRate;
		
		$this->exchApply->startTrans (); // 开启事物
		
		$applyId = $this->exchApply->add ( $data );
		if (! $applyId) {
			$this->exchApply->rollback ();
			$ret ['msg'] = '系统错误（addcp）';
			$this->ajaxReturn ( $ret );
		}
		// 更新apply_id
		foreach ( $dataList as &$data ) {
			$data ['apply_id'] = $applyId;
		}
		$result = $this->exchApplyCoupon->addAll ( $dataList ); // 添加数据
		if ($result) {
			$this->exchApply->commit ();
			$ret ['code'] = 1;
			$ret ['msg'] = 'success';
			$this->ajaxReturn ( $ret );
		} else {
			$this->exchApply->rollback ();
			$ret ['msg'] = '系统错误（addlist）';
			$this->ajaxReturn ( $ret );
		}
	}
	private function exchCouponCard($userId, $mobile) {
		$cpCard = I ( 'post.cpCard', '', 'trim,strip_tags,stripslashes' );
		$cpCardPrice = I ( 'post.cpCardPrice', '', 'trim,strip_tags,stripslashes' );
		
		if (empty ( $cpCard )) {
			$ret ['msg'] = '洗衣卡为空';
			$this->ajaxReturn ( $ret );
		}
		
		if (empty ( $cpCardPrice )) {
			$ret ['msg'] = '洗衣卡面额为空';
			$this->ajaxReturn ( $ret );
		}
		
		$reg = '/^\w{6,32}$/';
		if (! preg_match ( $reg, $cpCard )) {
			$ret ['msg'] = '洗衣卡号有误';
			$this->ajaxReturn ( $ret );
		}
		$reg = '/(^[0-9]+$)|(^[0-9]+.{1}[0-9]{1,2}$)/';
		if (! preg_match ( $reg, $cpCardPrice )) {
			$ret ['msg'] = '洗衣卡面额有误';
			$this->ajaxReturn ( $ret );
		}
		if ($cpCardPrice <= 1 || $cpCardPrice > 9999) {
			$ret ['msg'] = '洗衣卡面额有误';
			$this->ajaxReturn ( $ret );
		}
		
		$exchRate = C ( 'ZZGX_EXCH_RATE' );
		$where = array();
		$where['user_id'] = $this->uid;
		$applyUsername = $this->accountModel->where($where)->getField ('mobile');
		if(empty($applyUsername)){
			$ret ['msg'] = '未获取到您的手机号';
			$this->ajaxReturn ( $ret );
		}
		
		$data = array ();
		$data ['apply_userid'] = $this->uid;
		$data ['apply_username'] = $applyUsername;
		$data ['user_id'] = $userId;
		$data ['mobile'] = $mobile;
		$data ['coupon_amount'] = $cpCardPrice;
		$data ['coupon_num'] = 1;
		$data ['coupon_type'] = 2;
		$data ['applay_amount'] = $cpCardPrice * $exchRate;
		$data ['exch_rate'] = $exchRate;
		
		$this->exchApply->startTrans (); // 开启事物
		
		$applyId = $this->exchApply->add ( $data );
		if (! $applyId) {
			$this->exchApply->rollback ();
			$ret ['msg'] = '系统错误（addcp）';
			$this->ajaxReturn ( $ret );
		}
		
		$data = array ();
		$data ['apply_id'] = $applyId;
		$data ['user_id'] = $userId;
		$data ['mobile'] = $mobile;
		$data ['exch_rate'] = $exchRate;
		$data ['coupon_type'] = 2;
		$data ['coupon_price'] = $cpCardPrice; // 单张面额
		$data ['coupon_number'] = 1; // 对应面额数量
		$data ['coupon_code'] = $cpCard;
		$result = $this->exchApplyCoupon->add ( $data ); // 添加数据
		if ($result) {
			$this->exchApply->commit ();
			$ret ['code'] = 1;
			$ret ['msg'] = 'success';
			$this->ajaxReturn ( $ret );
		} else {
			$this->exchApply->rollback ();
			$ret ['msg'] = '系统错误（addlist）';
			$this->ajaxReturn ( $ret );
		}
	}
	
// 	// 获取验证码
// 	public function getCode() {
// 		if (! IS_AJAX)
// 			exit ();
// 		$mobile = I ( 'post.mobile', '', 'trim,strip_tags,stripslashes' );
// 		$ret = array (
// 				'code' => - 1,
// 				'msg' => 'unkown error' 
// 		);
// 		if (empty ( $mobile )) {
// 			$ret ['msg'] = '手机号为空';
// 			$this->ajaxReturn ( $ret );
// 		}
// 		$reg = '/^1\d{10}$/';
// 		if (! preg_match ( $reg, $mobile )) {
// 			$ret ['msg'] = '手机号错误';
// 			$this->ajaxReturn ( $ret );
// 		}
// 		// 检测账户是否存在
// 		$where = array ();
// 		$where ['mobile'] = $mobile;
// 		$userId = $this->accountModel->where ( $where )->getField ( 'user_id' );
// 		if (empty ( $userId )) {
// 			$ret ['code'] = 1101;
// 			$ret ['msg'] = '手机号未注册';
// 			$this->ajaxReturn ( $ret );
// 		}
// 		// 发送验证码
// 		$code = rand ( 1000, 9999 );
// 		session ( 'code_' . $mobile, $code );
// 		// $sres = A('Sms')->send($mobile,'您的验证码为：'.$code);
// 		$ret ['code'] = 1;
// 		$ret ['msg'] = 'success-' . $code;
// 		$this->ajaxReturn ( $ret );
// 	}
	
// 	// 检测验证码
// 	public function checkCode() {
// 		$mobile = I ( 'post.mobile', '', 'trim,strip_tags,stripslashes' );
// 		$code = I ( 'post.code', - 1, 'intval' );
// 		$ret = array (
// 				'code' => '-1',
// 				'msg' => 'unkown error' 
// 		);
// 		// 过滤参数
// 		// 手机号码
// 		$reg = '/^1\d{10}$/';
// 		if (! preg_match ( $reg, $mobile )) {
// 			$ret ['msg'] = '手机号错误（param）';
// 			$this->ajaxReturn ( $ret );
// 		}
// 		// 验证码
// 		if (! is_numeric ( $code ) || ($code < 1000 || $code > 9999)) {
// 			$ret ['msg'] = '验证码错误（param）';
// 			$this->ajaxReturn ( $ret );
// 		}
// 		$targetCode = session ( 'code_' . $mobile );
// 		if ($targetCode == $code) {
// 			$ret ['code'] = 1;
// 			$ret ['msg'] = 'success';
// 		} else {
// 			$ret ['msg'] = '验证码错误（fail）-' . $targetCode;
// 		}
// 		$this->ajaxReturn ( $ret );
// 	}
	
	//获取验证码
	public function vercode() {
		$Verify = new \Think\Verify();
		$Verify->fontSize = 20;
		$Verify->useCurve = false;
		$Verify->length   = 4;
		$Verify->expire   = 1800;
		$Verify->useNoise = false;
		$Verify->bg = array(255,255,255);
		$Verify->entry();
	}
}
