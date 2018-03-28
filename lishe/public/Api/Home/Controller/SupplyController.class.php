<?php
/**
  +------------------------------------------------------------------------------
 * SupplyController
  +------------------------------------------------------------------------------
 * @author   	Gaolong <1025264711@qq.com>
 * @version  	$Id: SupplyController.class.php v001 2017-04-21
 * @description 集配接口       
  +------------------------------------------------------------------------------
 */

namespace Home\Controller;
use Think\Controller;
use Think\Exception;

class SupplyController extends Controller {
	
	private static $_requestId = 0; //请求id，请求标志，仅供参考，（不能当做唯一标志，可能会串号）
	//private $_requestTime = '1970-01-01 00:00:00';
	private static $_ip = '0.0.0.0';
	
	/**
	 * 初始化
	 */
	protected function _initialize(){
		self::$_ip = get_client_ip(0, true);
		self::$_requestId = $this->requestId(); //ip.time.
		//日志记录
		$data = json_encode(I('param.','null',''));
		$this->log('method:'.REQUEST_METHOD.' data:'.$data, 'mark');
	}
	
	/**
	 * 空操作
	 */
	public function _empty(){
		echo 'error request';
	}
	
	/**
	 * 集配类型
	 * @var number
	 */
	const ACTIVE_TYPE_SUPPLY = 6;
	
	public function query(){
		$acid = I('get.acid', -1, 'intval');
		if(!is_numeric($acid)){
			$msg = '参数错误（acid错误）';
			self::log("msg:{$msg} acid={$acid}");
			$this->retError('1001', $msg);
		}
		
		if ($acid === -1) {
			$this->queryAllActive();
		} else if (is_numeric($acid) && $acid > 0) {
			$this->queryActive($acid);
		} else {
			//返回错误
			$msg = '参数错误（comId类型错误）';
			self::log("msg:{$msg} acid={$acid}");
			$this->retError('1002', $msg);
		}
		
	}
	
	/**
	 * 创建集配订单
	 */
	public function createOrder() {
		
// 		$_POST = $this->test();
		
		$comId 		   = I('post.comId', -1, 'intval');
		$comName 	   = I('post.comName');
		$supplyOrderId = I('post.supplyOrderId');
		$deliveryDate  = I('post.distributionDate');
		$receiverName  = I('post.receiverName');
		$receiverPhone = I('post.receiverPhone');
		$receiverState = I('post.receiverState');
		$receiverCity  = I('post.receiverCity');
		$receiverDistrict = I('post.receiverDistrict');
		$receiverAddr  = I('post.receiverAddr');
		$receiveDate  = I('post.receiveDate');
		$sign  		  = I('post.sign');
		$supplyOrderList   = I('post.supplyOrderList','','htmlspecialchars_decode');
		
		//校验签名
// 		if (!$this->signature($_POST)) {
// 			$msg = '签名校验错误（sign错误）';
// 			self::log("msg:{$msg} sign={$sign}");
// 			$this->retError('4031', $msg);
// 		}
		/*数据校验*/
		if (!is_numeric($comId)) {
			$msg = '企业id错误（comId错误）';
			self::log("msg:{$msg} comId={$comId}");
			$this->retError('4001', $msg);
		}
		if(empty($comName)){
			$msg = '企业名称为空（comName错误）';
			self::log("msg:{$msg} comName={$comName}");
			$this->retError('4002', $msg);
		}
		if(empty($supplyOrderId)){
			$msg = '订单id错误（supplyOrderId错误）';
			self::log("msg:{$msg} supplyOrderId={$supplyOrderId}");
			$this->retError('4012', $msg);
		}
		if(empty($deliveryDate)){
			$msg = '配送日期错误（deliveryDate错误）';
			self::log("msg:{$msg} deliveryDate={$deliveryDate}");
			$this->retError('4013', $msg);
		}
		if(empty($receiverName)){
			$msg = '收货人姓名为空（receiverName错误）';
			self::log("msg:{$msg} receiverName={$receiverName}");
			$this->retError('4003', $msg);
		}
		if(empty($receiverPhone)){
			$msg = '收货人手机为空（receiverPhone错误）';
			self::log("msg:{$msg} receiverPhone={$receiverPhone}");
			$this->retError('4004', $msg);
		}
		if(empty($receiverState)){
			$msg = '收货地址错误（省份 receiverState 错误）';
			self::log("msg:{$msg} receiverState={$receiverState}");
			$this->retError('4005', $msg);
		}
		if(empty($receiverCity)){
			$msg = '收货地址错误（城市 receiverCity 错误）';
			self::log("msg:{$msg} receiverCity={$receiverCity}");
			$this->retError('4006', $msg);
		}
		if(empty($receiverDistrict)){
			$msg = '收货地址错误（区/县receiverDistrict错误 ）';
			self::log("msg:{$msg} receiverDistrict={$receiverDistrict}");
			$this->retError('4007', $msg);
		}
		if(empty($receiverAddr)){
			$msg = '详细地址错误（receiverAddr错误 ）';
			self::log("msg:{$msg} receiverAddr={$receiverAddr}");
			$this->retError('4008', $msg);
		}
// 		if(empty($orderTime)){
// 			$msg = '下单时间为空（orderTime错误 ）';
// 			self::log("msg:{$msg} orderTime={$orderTime}");
// 			$this->retError('4009', $msg);
// 		}
		$supplyOrderArr = json_decode($supplyOrderList, true);
		if(empty($supplyOrderArr) || !is_array($supplyOrderArr)){
			$msg = '礼包信息为空（supplyOrderList错误 ）';
			self::log("msg:{$msg} supplyOrderList=$supplyOrderList");
			$this->retError('4010', $msg);
		}
		
		$supplyItemsArr = array();
		foreach ($supplyOrderArr as $package) {
			$supplyItemsArr[] = array(
				//'availableSum'  => $package['availableSum'],
				'buySum' 		=> $package['buySum'],
				//'createTime' 	=> $package['createTime'],
				'packageAreaId' => $package['packageAreaId'],
				//'packageAreaImg'  => $package['packageAreaImg'],
				//'packageAreaName' => $package['packageAreaName'],
				//'packageAreaPrice'=> $package['packageAreaPrice'],
				//'supplyOrderId'   => $package['supplyOrderId'],
				//'totalPrice' => $package['totalPrice'],
				//'updateTime' => $package['updateTime']
			);
		}
		
		$paymentId = date('ymdHis').rand(1000,100000);//支付单号	
		
		$data = array(
			'payment_id'	 => $paymentId,
			'com_id' 		 => $comId,
			'comName' 		 => $comName,
			'supply_order_id'=> $supplyOrderId,
			//'supply_items'	 => json_encode($supplyItemsArr),
			'supply_packages'=> json_encode($supplyItemsArr),
			'delivery_date'  => $deliveryDate,
			'receiver_name'  => $receiverName,
			'receiver_phone' => $receiverPhone,
			'receiver_state' => $receiverState,
			'receiver_city'  => $receiverCity,
			'receiver_district' => $receiverDistrict,
			'receiver_addr'  => $receiverAddr,
			'receive_date'   => $receiveDate,
			'created_at' 	 => date('Y-m-d H:i:s', NOW_TIME)
		);
		
		try {
			$result = M('supply_order')->add($data);
			if(!$result){
				$msg = '接口调用失败';
				self::log("msg:{$msg} supplyItems={$supplyItems}");
				$this->retError('4021', $msg);
			}
			$orderId = $result;
			$msg = '下单成功';
			self::log("msg:{$msg} orderId={$orderId} supplyOrderId=$supplyOrderId",'success');
			
			$data = array(
				'orderId' => $orderId,
			);
			
			$this->retSuccess($data, 'success');
			
		} catch (Exception $e) {
			$msg = '系统错误';
			self::log("msg:{$msg}");
			$this->retError('4041', $msg);
		}
	}
	
	/**
	 * 订单礼包接口
	 */
	public function opack(){
		$comId = I('post.comId');
		$orderId = I('post.orderNo');
		$packageAreaId = I('post.packageAreaId');
		$defaultPackageId = I('post.defaultPackageId');
		$redbagPoolId = I('post.redbagPoolId');
		$userList = I('post.userList', '', 'htmlspecialchars_decode');
		
		/*校验数据*/
		if (!is_numeric($packageAreaId)) {
			$msg = '礼包专区id错误（packageAreaId错误）';
			self::log("msg:{$msg} packageAreaId={$packageAreaId}");
			$this->retError('5001', $msg);
		}
		
		if (!is_numeric($defaultPackageId)) {
			$msg = '默认礼包专区id错误（defaultPackageId错误）';
			self::log("msg:{$msg} defaultPackageId={$defaultPackageId}");
			$this->retError('5002', $msg);
		}
		
		if (!is_numeric($redbagPoolId)) {
			$msg = '一企一舍订单id错误（redbagPoolId错误）';
			self::log("msg:{$msg} redbagPoolId={$redbagPoolId}");
			$this->retError('5003', $msg);
		}
		$userArr = json_decode($userList, true);
		if (empty($userArr) || !is_array($userArr)) {
			$msg = '用户列表数据错误（userList错误）';
			self::log("msg:{$msg} userList={$userList}");
			$this->retError('5004', $msg);
		}
		
		$data = array(
			'com_id' => $comId,
			'order_id' => $orderId,
			'package_area_id' => $packageAreaId,
			'defautl_package_id' => $defaultPackageId,
			'redbag_pool_id' => $redbagPoolId,
			'user_count' => count($userArr),
			'created_at' => date('Y-m-d H:i:s'),
		);
		$SupplyOrderPackage = M('supply_order_package');
		$SupplyOrderPackage->startTrans();
		try {
			$result = $SupplyOrderPackage->add($data);
			if(!$result){
				$SupplyOrderPackage->rollback();
				$msg = '添加数据错误（supply_order_package）';
				self::log("msg:{$msg}");
				$this->retError('5008', $msg);
			}
			$opid = $result;
			
			$dataList = array();
			$data = array(
				'opid' 		=> $opid,
				'com_id' 	=>$comId,
				'order_id'  => $orderId,
				'package_area_id' => $packageAreaId,
				'redbag_pool_id'  => $redbagPoolId
			);
			
			foreach ($userArr as $user) {
				$data['phone_num'] = $user['phoneNum'];
				$data['emp_name'] = $user['empName'];
				$dataList[] = $data;
			}
			
			$result = M('supply_package_users')->addAll($dataList);
			
			if (!$result) {
				$SupplyOrderPackage->rollback();
				$msg = '添加数据错误（supply_order_package）';
				self::log("msg:{$msg}");
				$this->retError('5010', $msg);
			}
			
			$SupplyOrderPackage->commit(); //提交事务
			
			$msg = '调用成功';
			self::log("msg:{$msg} opid={$opid} redbagPoolId=$redbagPoolId",'success');
			
			$data = array(
				'opid' => $opid
			);
			$this->retSuccess($data);
			
		} catch (Exception $e) {
			$SupplyOrderPackage->rollback();
			$msg = '系统错误';
			self::log("msg:{$msg}");
			$this->retError('5010', $msg);
		}
		
	}
	
	/**
	 * 领取接口
	 */
	public function receive() {
		$sign = I('post.sign');
		$packageid = I('post.packageid', -1 ,'intval');
		$mobile= I('post.mobile');
		$poolId = I('post.poolId', -1 ,'intval');
		if (empty($sign)) {
			$msg = '参数错误（sign错误）';
			self::log("msg:{$msg} sign={$sign}");
			$this->retError('6001', $msg);
		}
		if (!$this->signature($_POST)) {
			$msg = '签名错误（sign）';
			self::log("msg:{$msg} sign={$sign}");
			$this->retError('6002', $msg);
		}
		if (!is_numeric($packageid) || $packageid < 1){
			$msg = '参数错误（packageid错误）';
			self::log("msg:{$msg} packageid={$packageid}");
			$this->retError('6003', $msg);
		}
		$reg = '/^1\d{10}$/';
		if (!preg_match($reg, $mobile)){
			$msg = '参数错误（mobile错误）';
			self::log("msg:{$msg} mobile={$mobile}");
			$this->retError('6004', $msg);
		}
		if (!is_numeric($poolId) || $packageid < 1) {
			$msg = '参数错误（poolId错误）';
			self::log("msg:{$msg} poolId={$poolId}");
			$this->retError('6005', $msg);
		}
		//检索礼包数据
		$where = array(
			'aitem_id' => $packageid
		);
		$activityConfigId = M('company_activity_item')->where($where)->getField('activity_config_id');
		if (empty($activityConfigId)){
			$msg = '未检索到礼包数据（packageid）';
			self::log("msg:{$msg} packageid={$packageid}");
			$this->retError('6006', $msg);
		}
		//检索发放数据
		$where = array(
			'redbag_pool_id' => $poolId,
			'phone_num' => $mobile,
		);
		$SupplyUsers = M('supply_package_users');
		$userPackage = $SupplyUsers
							->field('puid,com_id,order_id,package_area_id,user_id,username,is_receive')
							->where($where)
							->find();
		if (empty($userPackage)) {
			$msg = '未检索到礼包信息';
			self::log("msg:{$msg} phone_num={$mobile} redbag_pool_id={$poolId}");
			$this->retError('6010', $msg);
		}
		
		$puid = $userPackage['puid'];
		$comId = $userPackage['com_id'];
		$orderId = $userPackage['order_id'];
		$packageAreaId = $userPackage['package_area_id'];
		$userId = $userPackage['user_id'];
		$username = $userPackage['username'];
		$isReceive = $userPackage['is_receive'];
		
		if(!is_numeric($isReceive) || $isReceive != 0){
			$msg = '该礼包已被领取';
			self::log("msg:{$msg} puid={$puid} phone_num={$mobile} redbag_pool_id={$poolId}");
			$this->retError('6011', $msg);
		}
		
		if ($packageAreaId != $activityConfigId){
			$msg = '礼包专区数据核对有误';
			self::log("msg:{$msg} packageid={$packageid} activityConfigId={$activityConfigId} packageAreaId={$packageAreaId}");
			$this->retError('6012', $msg);
		}
		
		$where = array(
			'order_id' => $orderId
		);
		$lisheSupplyOrderNo = M('supply_order')->where($where)->getField('supply_order_id');
		if(empty($lisheSupplyOrderNo)){
			$msg = '未检索到礼包订单';
			self::log("msg:{$msg} order_id={$orderId} puid={$puid}");
			$this->retError('6013', $msg);
		}
		$param = array(
			'phoneNum' => $mobile,
			//'shopSupplyOrderNo' => $orderId,
			//'selectPackageId' => $packageid,
			//'lisheSupplyOrderNo' => $lisheSupplyOrderNo,
			'redbagPoolId' => $poolId
		);
		$param['sign'] = $this->signature($param,'CREATE');
		//开始下单，并且更新状态
		$result = $this->addTrade($puid, $orderId, $packageid, $userId, $username, $comId);
		if(!is_numeric($result)){
			$msg = '更新数据失败';
			self::log("msg:{$msg} packageid={$packageid} order_id={$orderId} puid={$puid}");
			$this->retError('6015', $msg);
		}
		$tradeRefId = $result;
		$url = C('API').'welfareEmp/updateReceiveStatus';
		self::log("msg:一企一舍接口（request）url:{$url} param:".json_encode($param), 'info');
		$result = $this->curl($url, $param);
		self::log("msg:一企一舍接口（response）result:".$result,'info');
		
		$resultArr = json_decode($result, true);
		if ($resultArr['result'] != 100 || $resultArr['errcode'] != 0) {
			$msg = '调用一企一舍接口失败';
			self::log("msg:{$msg} order_id={$orderId} puid={$puid}");
			$this->retError('6014', $msg);
		}
		
		self::log("msg:领取成功  orderId={$orderId} tradeRefId={$tradeRefId}",'success');
		
		$data = array(
			'tradeRefId' => $tradeRefId,
		);
		$this->retSuccess($data,'success');
	}
	
	/**
	 * 接收收货人信息
	 */
	private function addTrade($puid, $orderId, $packageid, $userId, $userName, $comId) {
		//检索礼包数据
		$where = array(
			'aitem_id' => $packageid
		);
		$activityItem = M('company_activity_item')->where($where)->field('item_info,price')->find();
		
		$itemInfo = $activityItem['item_info'];
		$pakPrice = $activityItem['price'];
		$pakItemArr = json_decode($itemInfo, true);
		if(empty($pakItemArr) || !is_array($pakItemArr)){
			$msg = '未检索到礼包商品';
			self::log("msg:{$msg} packageid={$packageid}");
			$this->retError('6051', $msg);
		}
		//获取收货人信息
		$where = array(
			'order_id' => $orderId,
		);
		$supplyOrder = M('supply_order')->where($where)->find();
		$paymentId = $supplyOrder['payment_id'];
		$receiverName  = $supplyOrder['receiver_name'];
		$receiverPhone = $supplyOrder['receiver_phone'];
		$receiverStateId = $supplyOrder['receiver_state'];
		$receiverCityId  = $supplyOrder['receiver_city'];
		$receiverDistrictId = $supplyOrder['receiver_district'];
		$receiverAddr = $supplyOrder['receiver_addr'];
		$deliveryDate = $supplyOrder['delivery_date'];
		
		$buyerArea = $receiverStateId.'/'.$receiverCityId.'/'.$receiverDistrictId;
		$receiverStateName = ''; //省
		$receiverCityName = ''; //市
		$receiverDistrictName = ''; //区
		$where = array('jd_id'=> array('in',
			array($receiverStateId,$receiverCityId,$receiverDistrictId)
		));
		$siteAreaList = M('site_area')->field('jd_id,name')->where($where)->select();
		foreach ($siteAreaList as $area) {
			$areaId = $area['jd_id'];
			$areaName = $area['name'];
			if($areaId == $receiverStateId) {
				$receiverStateName = $areaName;
			}else if ($areaId == $receiverCityId) {
				$receiverCityName = $areaName;
			}else if($areaId == $receiverDistrictId) {
				$receiverDistrictName = $areaName;
			}
		}
		$itemIdArr = array();
		$skuIdArr = array();
		$itemNumMap = array();
		$skurNumMap = array();
		foreach ($pakItemArr as $item) {
			$itemId = $item['item_id'];
			$skuId = $item['sku_id'];
			$num = $item['num'];
			
			$itemIdArr[] = $itemId;
			$skuIdArr[] = $skuId;
			if (isset($itemNumMap[$itemId])) {
				$itemNumMap[$itemId] += $num;
			}else{
				$itemNumMap[$itemId] = $num;
			}
			if (isset($skurNumMap[$skuId])) {
				$skurNumMap[$skuId] += $num;
			}else{
				$skurNumMap[$skuId] = $num;
			}
		}
		
		//检索商品信息
		$itemArr = array();
		$skuArr = array();
		$where = array(
			'item_id'=> array('in', $itemIdArr)
		);
		$itemList = M('sysitem_item')->where($where)->select();
		if(empty($itemList) || !is_array($itemList)){
			$msg = '未检索到商品信息';
			self::log("msg:{$msg} packageid={$packageid}");
			$this->retError('6061', $msg);
		}
		
		foreach ($itemList as $item) {
			$itemArr[$item['item_id']] = $item;
		}
		unset($itemList);
		//检索sku信息
		$where = array(
			'sku_id'=> array('in', $skuIdArr)
		);
		$skuList = M('sysitem_sku')->where($where)->select();
		if(empty($skuList) || !is_array($skuList)){
			$msg = '未检索到商品（SKU）信息';
			self::log("msg:{$msg} packageid={$packageid}");
			$this->retError('6062', $msg);
		}
		foreach ($skuList as $sku) {
			$skuArr[$sku['sku_id']] = $sku;
		}
		unset($skuList);
		
		//生成订单号
		$tid = substr(date('YmdHis'),2).$userId.rand(0,9);
		$orderList = array();
		
		$paytime = time();
		$tradeTotalWeight = 0;
		$itemnum = 0;
		
		foreach ($pakItemArr as $key => $item) {
			$oid = substr(date('YmdHis'),2).$userId.$key;
			$itemId = $item['item_id'];
			$skuId = $item['sku_id'];
			$num = $item['num'];
			$itemnum += $num;
			$weight = $skuArr[$skuId]['weight'] * $num;
			$tradeTotalWeight += $weight;
			$order = array(
				'oid' => $oid,
				'tid' => $tid,
				'supplier_id' => $itemArr[$itemId]['supplier_id'],
				'send_type' => $itemArr[$itemId]['send_type'],
				'cat_id'  => $itemArr[$itemId]['cat_id'],
				'shop_id' => $itemArr[$itemId]['shop_id'],
				'user_id' => $userId,
				'item_id' => $itemId,
				'sku_id'  => $skuId,
				'bn'	  => $skuArr[$skuId]['bn'],
				'title'   => $skuArr[$skuId]['title'],
				'spec_nature_info' => $skuArr[$skuId]['spec_info'],
				'price' => $skuArr[$skuId]['price'],
				'cost_price' => $skuArr[$skuId]['cost_price'],
				'num' => $num,
				'cash' => $skuArr[$skuId]['price'],
				'point' => $skuArr[$skuId]['price'] * 100,
				'total_cash' => $skuArr[$skuId]['price'],
				'total_point' => $skuArr[$skuId]['price'] * 100,
				'pay_time' => $paytime,
				'total_fee' => $skuArr[$skuId]['price'],
				'total_weight' => $tradeTotalWeight * $num,
				'order_from' => 'ws',
				'pic_path' => $itemArr[$itemId]['image_default_id'],
				'status' => 'WAIT_SELLER_SEND_GOODS', 
			);
			$orderList[] = $order;
		}
		//
		$data = array();
		$data['tid'] 		= $tid;
		$data['shop_id'] 	= 1;
		$data['user_id'] 	= $userId;
		$data['com_id'] 	= $comId;
		$data['dlytmpl_id'] = 0;
		$data['payment'] 	= $pakPrice;
		$data['total_fee'] 	= $pakPrice;
		$data['post_fee'] 	= 0;
		$data['point_fee'] 	= $pakPrice * 100;
		$data['receiver_name'] 	  = $receiverName;
		$data['created_time'] 	  = time();
		$data['receiver_state']   = $receiverStateName;
		$data['receiver_city'] 	  = $receiverCityName;
		$data['receiver_district']= $receiverDistrictName;
		$data['receiver_address'] = $receiverAddr;
		$data['receiver_zip'] 	  = '';
		$data['receiver_mobile']  = $receiverPhone;
		$data['title'] 			  = '订单明细介绍';
		$data['buyer_message'] 	  = '';
		$data['receiver_phone']   = $receiverPhone;
		$data['itemnum'] 		  = $itemnum;
		$data['buyer_area'] 	  = $buyerArea;
		$data['total_weight']     = $tradeTotalWeight;
		$data['status'] 		  = 'WAIT_SELLER_SEND_GOODS';
		$data['payed_fee'] 		  = $pakPrice;
		$data['pay_time'] 		  = $paytime;
		$data['trade_type']		  = 6;
		$data['trade_from'] 	  = 'ws';
		
		$SystradeTrade = M('systrade_trade');
		$SystradeTrade->startTrans();
		
		try {
			$result = $SystradeTrade->add($data);
			if (!$result) {
				$SystradeTrade->rollback();
				$msg = '添加订单（trade）信息失败';
				self::log("msg:{$msg} packageid={$packageid} SQL:".$SystradeTrade->getLastSql());
				$this->retError('6071', $msg);
			}
			$SystradeOrder = M('systrade_order');
			$result = $SystradeOrder->addAll($orderList);
			if (!$result) {
				$SystradeTrade->rollback();
				$msg = '添加订单子表（order）信息失败';
				self::log("msg:{$msg} packageid={$packageid} SQL:".$SystradeOrder->getLastSql());
				$this->retError('6072', $msg);
			}
			//订单号
			if (empty($paymentId)) {
				$paymentId = date('ymdHis').$uid.rand(0,9);//支付单号;
			}
			$data = array(
				'payment_id' => $paymentId,
				'money' => $pakPrice,
				'cur_money' => $pakPrice,
				'status' => 'succ',
				'user_id' => $userId,
				'user_name' => $userName,
				'created_time' => $paytime,
				'modified_time' => $paytime,
				'memo' => '集配订单虚拟支付',
			);
			$EctoolsPayments = M('ectools_payments');
			$result = $EctoolsPayments->add($data);
			if (!$result) {
				$SystradeTrade->rollback();
				$msg = '添加支付主表信息失败';
				self::log("msg:{$msg} packageid={$packageid} SQL:".$EctoolsPayments->getLastSql());
				$this->retError('6073', $msg);
			}
			//支付副单号
			$data = array(
				'payment_id' => $paymentId,
				'tid' => $tid,
				'status' => 'succ',
				'payment' => $pakPrice,
				'created_time' => time(),
				'payed_time' => $paytime,
				'modified_time' => time(),
			);
			$EctoolsTradePaybill = M('ectools_trade_paybill');
			$result = $EctoolsTradePaybill->add($data);
			if (!$result) {
				$SystradeTrade->rollback();
				$msg = '添加支付主表信息失败';
				self::log("msg:{$msg} packageid={$packageid} SQL:".$EctoolsTradePaybill->getLastSql());
				$this->retError('6074', $msg);
			}
			$paybillId = $result;
			//订单关系表
			$data = array(
				'puid' => $puid,
				'order_id' => $orderId,
				'trade_id' => $tid,
				'payment_id' => $paymentId,
				'paybill_id' => $paybillId,
				'user_id' => $userId,
				'delivery_date' => $deliveryDate,
				'created_at' => date('Y-m-d H:i:s'),
			);
			$SupplyTradeRef = M('supply_trade_ref');
			$result = $SupplyTradeRef->add($data);
			if (!$result) {
				$SystradeTrade->rollback();
				$msg = '添加支付主表信息失败';
				self::log("msg:{$msg} packageid={$packageid} SQL:".$SupplyTradeRef->getLastSql());
				$this->retError('6075', $msg);
			}
			$tradeRefId = $result;
			//用户领取状态表
			$where = array(
				'puid' => $puid
			);
			$data = array(
				'is_receive' => 1,
				'package_id' => $packageid,
				'receive_time' => date('Y-m-d H:i:s'),	
			);
			$result = M('supply_package_users')->where($where)->save($data);
			if (!$result) {
				$SystradeTrade->rollback();
				$msg = '更新用户领取状态失败';
				self::log("msg:{$msg} packageid={$packageid} puid={$puid}");
				$this->retError('6075', $msg);
			}
			
			$SystradeTrade->commit();
			try {
				//更新库存，销量
				foreach ($itemNumMap as $itemId => $num){
					//购买减item_store库存
					M('sysitem_item_store')->where("item_id=$itemId")->setDec('store', $num);
					//购买增加item销量
					M('sysitem_item_count')->where("item_id=$itemId")->setInc('sold_quantity', $num);
				}
				foreach ($skurNumMap as $skuId => $num){
					//减sku_store库存
					M('sysitem_sku_store')->where("sku_id=$skuId")->setDec('store', $num);
					//购买增加sku销量
					M('sysitem_sku')->where("sku_id =$skuId")->setInc('sold_quantity', $num);
				}
			} catch (Exception $e) {
				$msg = '更新库存销量错误';
				self::log("msg:{$msg} packageid={$packageid}",'warning');
			}
			return $tradeRefId;
			
		} catch (Exception $e) {
			$SystradeTrade->rollback();
			$msg = '系统错误';
			self::log("msg:{$msg} packageid={$packageid}");
			$this->retError('6101', $msg);
		}
	}
	
	/**
	 * 检索礼包活动
	 */
	private function queryActive($acid) {
		$where = array(
			'activity_config_id' => $acid,
		);
		$ActivityCategory = M('company_activity_category');
		$activityCategory = $ActivityCategory
								->where($where)
								->field('activity_config_id,aid,cat_name,cat_banner')
								->find();
		
		if(empty($activityCategory)){
			$msg = "礼包商品信息为空 acid={$acid}";
			self::log("msg:{$msg}");
			$this->retError('3001', $msg);
		}
		
		$activityConfigId 	= $activityCategory['activity_config_id'];
		$activityName		= $activityCategory['cat_name'];
		$catBanner			= $activityCategory['cat_banner'];
		
		$where = array(
			'activity_config_id' => $activityConfigId,
		);
		$ActivityItem = M('company_activity_item');
		$activityItemList = $ActivityItem->where($where)->field('aitem_id,price,shop_price,item_info')->select();
		
		if (empty($activityItemList)) {
			$msg = "礼包专区信息为空 activityConfigId={$activityConfigId}";
			self::log("msg:{$msg}");
			$this->retError('3001', $msg);
		}
		
		$itemIdArr = array();
		foreach ($activityItemList as &$activityItem) {
			$itemList = json_decode($activityItem['item_info'], true);
			if (!is_array($itemList) || empty($itemList)){
				continue;
			}
			$activityItem['items'] = $itemList;
			foreach ($itemList as $item){
				$itemIdArr[] = $item['item_id'];
			}
		}
		
		$where = array(
			'item_id' => array('in', $itemIdArr)
		);
		$SysitemItem = M('sysitem_item');
		$itemList = $SysitemItem
						->field('item_id,point,title,image_default_id')
						->where($where)
						->select();
		
		$itemArr = array();
		foreach ($itemList as $item) {
			$itemArr[$item['item_id']] = $item;
		}
		unset($itemList);
		
		$data = array(
			'packageAreaName' => $activityName,
			'packageAreaPrice' => 0,
			'packageAreaImg' => $catBanner,
			'packageAreaId' => $activityConfigId,
			'packList'		=> array(),
		);
		
		foreach ($activityItemList as $val) {
			$data['packageAreaPrice'] = $val['price'];
			$tmpItem = array(
				'packageId' => $val['aitem_id'],
				'packagePrice' => $val['price'],
				'packageOldPrice' => 0,
			);
			$point = 0;
			foreach ($val['items'] as $item){
				$itemdata = $itemArr[$item['item_id']];
				$point += $itemdata['point'];
				$tmpItem['commodityList'][] = array(
					'commodityName'=> $itemdata['title'],
					'commodityImg' => $item['num'],
					'itemId'  => $itemdata['item_id'],
					'skuId'   => $item['sku_id'],
					'commodityImg' => $itemdata['image_default_id'],
				);
			}
			$tmpItem['packageOldPrice'] = $point / 100;
			$data['packList'][] = $tmpItem;
		}
		
		$this->retSuccess($data);
	}
	
	/**
	 * 检索所有活动
	 */
	private function queryAllActive() {
		$where = array(
			'type' => self::ACTIVE_TYPE_SUPPLY,
		);
		$Activity = M('company_activity');
		$activityList = $Activity->where($where)->field('aid,activity_name')->select();
		if (empty($activityList) || !is_array($activityList)){
			$msg = '集配信息为空';
			self::log("msg:{$msg}");
			$this->retError('2001', $msg);
		}
		
		$aidArr = array();
		$activityArr = array();
		foreach ($activityList as $activity) {
			$aid = $activity['aid'];
			$aidArr[] = $aid;
			$activityArr[$aid] = $activity;
		}
		
		$where = array(
			'aid' => array('in', $aidArr),
		); 
		$ActivityCategory = M('company_activity_category');
		$activityCategoryList = $ActivityCategory
									->where($where)
									->field('activity_config_id,aid,cat_name,cat_banner')
									->select();
		
		if(empty($activityCategoryList) || !is_array($activityCategoryList)){
			$msg = '礼包专区信息为空';
			self::log("msg:{$msg}");
			$this->retError('2002', $msg);
		}
		
		$activityCategoryIdArr = array();
		$activityCategoryArr = array();
		foreach ($activityCategoryList as $category) {
			$configId = $category['activity_config_id'];
			$activityCategoryIdArr[] = $configId;
			$activityCategoryArr[$category['aid']][$configId] = array(
				'packageAreaName'  => $category['cat_name'],
				'packageAreaPrice' => 0,
				'packageAreaImg'   => $category['cat_banner'],
				'packageAreaId'    => $configId,
			);
		}
		unset($activityCategoryList);
		
		$where = array(
			'activity_config_id' => array('in', $activityCategoryIdArr),
		);
		$ActivityItem = M('company_activity_item');
		$activityItemList = $ActivityItem
								->where($where)
								->field('aitem_id,aid,activity_config_id,price,shop_price,item_info')
								->select();
		
		if (empty($activityItemList) || !is_array($activityItemList)) {
			$msg = '礼包商品信息为空';
			self::log("msg:{$msg}");
			$this->retError('2003', $msg);
		}
		
		$itemIdArr = array();
		foreach ($activityItemList as &$activityItem) {
			$itemList = json_decode($activityItem['item_info'], true);
			if (!is_array($itemList) || empty($itemList)){
				continue;
			}
			$activityItem['items'] = $itemList;
			foreach ($itemList as $item){
				$itemIdArr[] = $item['item_id'];
			}
		}
		
		$where = array(
			'item_id' => array('in', $itemIdArr)
		);
		$SysitemItem = M('sysitem_item');
		$itemList = $SysitemItem->field('item_id,point,title,image_default_id')->where($where)->select();
		
		$itemArr = array();
		foreach ($itemList as $item) {
			$itemArr[$item['item_id']] = $item;
		}
		unset($itemList);
		
		array_walk($activityItemList, function ($activityItem) use (&$activityCategoryArr,$itemArr){
			$aid = $activityItem['aid'];
			$activityConfigId = $activityItem['activity_config_id'];
			$packageAreaPrice = $activityItem['price'];
			$tmpItem = array(
				'packageId' => $activityItem['aitem_id'],
				'packagePrice' => $activityItem['price'],
				'packageOldPrice' => 0,
			);
			$point = 0;
			foreach ($activityItem['items'] as $item){
				$itemdata = $itemArr[$item['item_id']];
				$point += $itemdata['point'];
				$tmpItem['commodityList'][] = array(
					'commodityName'	=> $itemdata['title'],
					'commoditySum'	=> $item['num'],
					'skuId'   => $item['sku_id'],
					'itemId'  => $itemdata['item_id'],
					'commodityImg' => $itemdata['image_default_id'],
				);
			}
			$tmpItem['packageOldPrice'] = $point / 100;
			$activityCategoryArr[$aid][$activityConfigId]['packageAreaPrice'] = $packageAreaPrice;
			$activityCategoryArr[$aid][$activityConfigId]['packList'][] = $tmpItem;
		});

		$data = array();
		foreach ($activityCategoryArr as $aid => $activityCategory) {
			$aid = $activityArr[$aid]['aid'];
			
			$aidMap = array(
				25 => 20
			);
			
			$data[] = array(
				'senceId' 	=> $aidMap[$aid],
				'senceName' => $activityArr[$aid]['activity_name'],
				'packageAreaList' => array_values($activityCategory),
			);
		}
		
		$this->retSuccess($data);
	}
	
	/**
	 * 日志记录
	 * @param string $msg
	 * @author Gaolong
	 */
	protected static function log($msg = '', $logFlag='error', $logName = ACTION_NAME){
 		//$logPath = APP_PATH.'Logs/Supply/'.date('Ymd').'_'.$logName.'.log'; //设置Home目录同级 /////////////////////////////////////测试代码///////////////////////////////////////
		$logPath = C('DIR_LOG').'Supply/'.date('Ymd').'_'.$logName.'.log'; //设置Home目录同级
		//格式
		$msg = 'time:'.date('Y-m-d H:i:s') . " {$logFlag} requestId:".self::$_requestId.' ip:'.self::$_ip." {$msg}";
		//写入
		@file_put_contents($logPath, "{$msg}\r\n", FILE_APPEND);
	}
	
	//接口返回错误信息
	protected function retError($errCode=1, $msg='操作失败'){
		header('Content-Type:application/json; charset=utf-8');
		$ret = array(
				'result'=>100,
				'errcode'=>$errCode,
				'msg'=>$msg,
				'requestId'=>self::$_requestId
		);
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		//echo preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $code);
		exit();
	}
	
	//接口返回结果
	protected function retSuccess($data=array(), $msg='操作成功'){
		header('Content-Type:application/json; charset=utf-8');
		$ret = array(
				'result'=>100,
				'errcode'=>0,
				'msg'=>$msg,
				'data'=>$data,
				'requestId'=>self::$_requestId
		);
		//echo json_encode($ret);
		echo json_encode($ret, JSON_UNESCAPED_UNICODE);
		//echo preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $code);
		exit();
		//$this->ajaxReturn($ret);
	}
	
	/**
	 * 获取请求id<br/>
	 * @return string
	 * @author Gaolong
	 */
	protected static function requestId(){
		return substr(microtime(true) * 10000, 6) . substr(ip2long(self::$_ip), rand(0, 6), 4);
	}
	
	/**
	 * curl请求
	 * @param string $url
	 * @param array $param
	 * @return mixed
	 */
	function curl($url, $param){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (is_array($param)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($param));
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
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
				return $md5Str == $sign; //返回校验结果
			}else if($option == 'CREATE'){
				return $md5Str;
			}else{
				return false;
			}
	}
}