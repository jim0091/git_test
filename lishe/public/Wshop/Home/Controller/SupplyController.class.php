<?php

namespace Home\Controller;

/**
 * 礼包领取
 * @author LT
 *
 */
class SupplyController extends CommonController {
	
	private $uid;
	private $userName;
	
// 	public function __construct() {
// 		parent::__construct();
		
// 	}
	
	protected function _initialize() {
		
		$account = session('account');
		$account = json_decode($account, true);
		$this->uid = $account['id'];
		$this->userName = $account['userName'];
 		if (empty($this->uid)) {
 			$refer = 'http://'.$_SERVER['HTTP_HOST'].__SELF__;
 			$refer = urlencode($refer);
 			redirect(__APP__ . "/Login/login/index?entry=no&refer={$refer}");
 		}
	}
	
	/**
	 * 领取页面
	 */
	public function index () {
		$mobile = I('get.m'); //手机号
		$poolId = I('get.pid'); //红包池id
		$code = I('get.code'); //校验码
		
		$data = array(
			'm' => $mobile,
			'pid' => $poolId,
			'sign' => $code
		);
		$result = $this->signature($data);
		if(!$result) {
			exit('领取链接有误');
		}
		//校验红包池id
		if(!is_numeric($poolId)){
			exit('链接有误');
		}
		//手机号
		$reg = '/^1\d{10}$/';
		if(!preg_match($reg, $mobile)){
			exit('链接有误');
		}
		
		//检索发放数据
		$where = array(
			'redbag_pool_id' => $poolId,
			'phone_num' => $mobile,
		);
		$SupplyUsers = M('supply_package_users');
		$userPackage = $SupplyUsers->field('puid,package_area_id,is_receive')->where($where)->find();
		if(!isset($userPackage['puid'])){
			exit('礼包不存在');
		}
		
		$puid = $userPackage['puid'];
		$packageAreaId = $userPackage['package_area_id'];
		$isReceive = $userPackage['is_receive'];
// 		//判断是否已经领取
// 		if(!is_numeric($isReceive) || $isReceive != 0) {
// 			exit('already receive');
// 		}
		$where = array(
			'activity_config_id' => $packageAreaId,
		);
		$activeItemList = M('company_activity_item')
							->field('aitem_id,item_name,item_info,price')
							->where($where)
							->select();
		$price = $activeItemList[0]['price'];
		
		
		$itemIdArr = array();
		foreach ($activeItemList as &$activeItem) {
			$itemArr = json_decode($activeItem['item_info'], true);
			$activeItem['items'] = $itemArr;
			foreach ($itemArr as $item) {
				$itemIdArr[] = $item['item_id'];
			}
		}
		
		$where = array(
			'item_id' => array('in', $itemIdArr),
		);
		$SysitemItem = M('sysitem_item');
		$itemList = $SysitemItem->field('item_id,title,image_default_id')->where($where)->select();
		
		$itemInfoArr = array();
		foreach ($itemList as $item) {
			$itemInfoArr[$item['item_id']] = $item;
		}
		$this->assign('price', $price);
		$this->assign('activeItemList', $activeItemList);
		$this->assign('itemInfoArr', $itemInfoArr);
		$this->assign('mobile', $mobile);
		$this->assign('poolId', $poolId);
		$this->assign('code', $code);
		$this->display('index');
	}
	
	/**
	 * 领取
	 */
	public function receive () {
		$mobile = I('post.m','','trim'); //手机号
		$poolId = I('post.pid'); //红包池id
		$code = I('post.code'); //校验码
		$packageid = I('post.packageid',-1, 'intval');
		$ret = array('code' => 0, 'msg' => 'unkown error');
		
		$data = array(
			'm' => $mobile,
			'pid' => $poolId,
			'sign' => $code
		);
		$result = $this->signature($data);
		if(!$result) {
			$ret['msg'] = '领取链接有误';
			$this->ajaxReturn($ret);
		}
		//校验红包池id
		if(!is_numeric($poolId)){
			$ret['msg'] = '参数错误(pid)';
			$this->ajaxReturn($ret);
		}
		//手机号
		$reg = '/^1\d{10}$/';
		if(!preg_match($reg, $mobile)){
			$ret['msg'] = '参数错误(m)';
			$this->ajaxReturn($ret);
		}
		
		$where = array(
			'user_id' => $this->uid
		);
		$SysuserAccount = M('sysuser_account');
		$userMobile = $SysuserAccount->where($where)->getField('mobile');
		if(empty($userMobile)){
			$ret['msg'] = '您的账号没有绑定手机号';
			$this->ajaxReturn($ret);
		}
		if ($userMobile !== $mobile){
			$ret['msg'] = '您的注册手机号和领取手机号不一致，无法领取';
			$this->ajaxReturn($ret);
		}
		if(!is_numeric($packageid) || $packageid < 1){
			$ret['msg'] = '参数错误(packageid)';
			$this->ajaxReturn($ret);
		}
		//检索发放数据
		$where = array(
			'redbag_pool_id' => $poolId,
			'phone_num' => $mobile,
		);
		$SupplyUsers = M('supply_package_users');
		$userPackage = $SupplyUsers->field('puid,is_receive')->where($where)->find();
		if(!isset($userPackage['puid'])){
			$ret['msg'] = '您没有需要领取的礼包';
			$this->ajaxReturn($ret);
		}
		
		$puid = $userPackage['puid'];
		$isReceive = $userPackage['is_receive'];
		//判断是否已经领取
		if(!is_numeric($isReceive) || $isReceive != 0) {
			$ret['msg'] = '已经被领取';
			$this->ajaxReturn($ret);
		}
		$data = array(
			'user_id' => $this->uid,
			'username' => $this->userName,
		);
		$where = array(
			'puid' => $puid
		);
		$result = M('supply_package_users')->where($where)->save($data);
		if(!is_numeric($result)) {
			$ret['msg'] = '更新用户信息失败，请重新领取';
			$this->ajaxReturn($ret);
		}
		$param = array(
			'packageid' => $packageid,
			'mobile' => $mobile,
			'poolId' => $poolId
		);
		$param['sign'] = $this->signature($param, 'CREATE');
		//调用接口
		$apiURL = C('COMMON_API') . 'Supply/receive';
		$result = $this->requestPost($apiURL, $param);
		$resultArr = json_decode($result, true);
		
		if($resultArr['result'] !== 100 ||$resultArr['errcode'] !== 0 ) {
			$ret['msg'] = '领取失败';
			$this->ajaxReturn($ret);
		}
		
		$ret['code'] = 1;
		$ret['msg'] = 'success';
		$ret['url'] = "http://v.lishe.cn/rt/rt.html?m={$mobile}&pid={$poolId}&code={$code}"; //生产环境地址
		//$ret['url'] = "http://120.76.159.44:8080/lshe.framework.web/rt/rt.html?m={$mobile}&pid={$poolId}&code={$code}";
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 建议该方法放到function.php中
	 * 校验签名，签名为post过来的参数集合，具体算法看源代码，注意，必须包含sign字段，否则签名不通过
	 * @param array $param
	 * @option string 操作选项，取值范围，CHECK检测，CREATE生成, 默认'CHECK'
	 * @return boolean
	 */
	protected function signature($param = array(), $option='CHECK'){
	
		if(empty($param) || !is_array($param)){
			return false;
		}
		$sign = '';
		if($option == 'CHECK'){
			//判断sign字段
			if(empty($param['sign'])){
				return false;
			}
			$sign = $param['sign'];
			unset($param['sign']);
		}
		//排序，按ASCII升序
		ksort($param, SORT_REGULAR);
		$signStr = '';
		//拼接成 a=123&b=123格式
		array_walk($param, function($value, $key) use (&$signStr){
			$signStr .= "$key=$value&";
		});
			if(empty($signStr)){
				return false;
			}
			//删除最后一个‘&’，并加上key
			$signStr = rtrim($signStr, '&') . C('API_KEY');
			//self::log("signature:$signStr mysign=".md5($signStr) . 'sign='.$sign);
			$md5Str = md5($signStr);
			if($option == 'CHECK'){
				return $md5Str === $sign; //返回校验结果
			}else if($option == 'CREATE'){
				return $md5Str;
			}else{
				return false;
			}
	}
}