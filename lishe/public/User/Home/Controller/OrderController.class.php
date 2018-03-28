<?php
/**
 +------------------------------------------------------------------------------
* OrderController
+------------------------------------------------------------------------------
* @author   	赵尊杰 <10199720@qq.com>
* @version  	$Id: OrderController.class.php v001 2016-5-22
* @description 订单中心
+------------------------------------------------------------------------------
*/
namespace Home\Controller;
class OrderController extends CommonController {
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			header("Location:".__SHOP__."/Sign/index");
			exit;
		}
		$this->modelOrder=D('Order');
		$this->groupOrder = M('gift_group_order');
		$this->orderRecever = M('gift_group_order_recever');
		$this->activityCategory = M('company_activity_category');
		$this->activityTrade = M('company_activity_trade');

		$orderStatus=array(
				'TRADE_CLOSED_BY_SYSTEM'=>'已取消（系统）',
				'TRADE_CLOSED_BY_USER'=>'已取消（用户）',
				'TRADE_CLOSED_BY_ADMIN'=>'已取消（管理员）',
				'WAIT_BUYER_PAY'=>'待付款',
				'WAIT_SELLER_SEND_GOODS'=>'待发货',
				'WAIT_BUYER_CONFIRM_GOODS'=>'待收货',
				'WAIT_COMMENT'=>'待评论',
				'TRADE_FINISHED'=>'已完成',
				'IN_STOCK'=>'备货中'
		);
		$this->assign('orderStatus',$orderStatus);
	}

	//用户订单
	public function orderList(){
		$status=I('get.status','');
		if (empty($status)) {
			$condition =array('user_id'=>$this->uid,'disabled'=>0);
		}elseif($status == 'NO_APPLY'){
			$condition =array('user_id'=>$this->uid,'order_status'=>array('neq','NO_APPLY'),'disabled'=>0);
		}else{
			$condition =array('user_id'=>$this->uid,'status'=>$status,'disabled'=>0);
		}
		$orderList = $this->modelOrder->getOrderList($condition);
		if (empty($orderList)) {
			$this->error("信息不存在！");
		}
		$alltids = array();
		if ($orderList) {
			foreach ($orderList as $key => $value) {
				$alltids[$key] = $value['tid'];
			}
		}
		if(!empty($alltids)){
			//查询支付子表信息
			$alltidsStr=implode(',',$alltids);
			$allconditioPayId['tid'] = array('in',$alltidsStr);
			$allconditioPayId['user_id'] = $this->uid;
			$allpaymentList = $this->modelOrder->getPaymentBillList($allconditioPayId);
			if (empty($allpaymentList)) {
				$this->error("信息不存在！");
			}
			foreach ($allpaymentList as $key => $value) {
				$resultPaymentListAll[$value['tid']]= $value['payment_id'];
			}
			$count = count($resultPaymentListAll);
			//实例化分页类
			$size=10;
			$page = new \Think\Page($count,$size);
			$rollPage = 5; //分页栏显示的页数个数；
			$page -> setConfig('first' ,'首页');
			$page -> setConfig('last' ,'尾页');
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page->firstRow;  //起始行数
			$pagesize = $page->listRows;   //每页显示的行数
			$limit = "$start,$pagesize";
			$style = "badge";
			$onclass = "pageon";
			$pagestr = $page -> show($style,$onclass);  //组装分页字符串
			$pidsStr = implode(',',$resultPaymentListAll);
			$conditionPids['payment_id'] = array('in',$pidsStr);
			$paymentList = $this->modelOrder->getPaymentBillList($conditionPids,$limit);
			if (!$paymentList) {
				$this->error("信息不存在！");
			}
			foreach ($paymentList as $key => $value) {
					$tids[$key] = $value['tid'];
				}
			$conditionTidsStr = implode(',',$tids);
			$cond['tid'] = array('in',$conditionTidsStr);
			$orderList = $this->modelOrder->getOrderList($cond);

			if ($orderList) {
				foreach ($orderList as $key => $value) {
					$shopIds[$key] = $value['shop_id'];
					//$tids[$key] = $value['tid'];
					$trade[$value['tid']]=$value;
				}
				//查询店铺信息
				$shopIdStr=implode(',',$shopIds);
				$conditionShop['shop_id']=array('in',$shopIdStr);
				$shopList = $this->modelOrder->getShopList($conditionShop);
				foreach ($trade as $key => $value) {
					foreach ($shopList as $k => $val) {
						if ($value['shop_id']  == $val['shop_id']) {
							$trade[$key]['shopInfo'] = $val;
						}
					}
				}
				//查询商品信息
				$tidsStr=implode(',',$tids);
				$conditionTid = " tid IN (".$tidsStr.")";
				$orderItemList = $this->modelOrder->getOrderItemList($conditionTid);
				foreach ($orderItemList as $key => $value) {
					$order[$value['tid']][$value['oid']]=$value;
				}
				//查询支付子表信息
				$tidsStr=implode(',',$tids);
				$conditioPayId['tid'] = array('in',$tidsStr);
				$conditioPayId['user_id'] = $this->uid;
				$paymentList = $this->modelOrder->getPaymentBillList($conditioPayId);

				$payIds = array();
				foreach ($paymentList as $key => $value) {
					$resultPaymentList[$value['tid']]['payId']= $value['payment_id'];
					$resultPaymentList[$value['tid']]['status']= $value['status'];
					$resultPaymentList[$value['tid']]['ctime']= ($value['created_time']+60*60*24) < time() ? 0 : 1 ;
					$payIds[] = $value['payment_id'];
				}
				//查询支付主表信息
				$payIdsStr=implode(',',array_unique($payIds));
				$conditionPayId['payment_id'] = array('in',$payIdsStr);
				$field = array('payment_id','cash_fee','point_fee','payed_cash','payed_point');
				$paymentListInfo = $this->modelOrder->getPaymentList($conditionPayId);
				$paymentIdList = array();
				if (!empty($paymentListInfo)) {
					foreach ($paymentListInfo as $key => $value) {
						$paymentIdList[$value['payment_id']] = $value;
					}
				}
				$paymentTrade = array();
				foreach ($resultPaymentList as $krpl => $vrpl) {
					$paymentTrade[$vrpl['payId']]['paymentId'] = $vrpl['payId'];
					$paymentTrade[$vrpl['payId']]['payStatus'] = $vrpl['status'];
					$paymentTrade[$vrpl['payId']]['ctime'] = $vrpl['ctime'];
					$paymentTrade[$vrpl['payId']]['tradeInfo'][$krpl]['trade']=$trade[$krpl];
					$paymentTrade[$vrpl['payId']]['tradeInfo'][$krpl]['order']=$order[$krpl];
					$paymentTrade[$vrpl['payId']]['paymentInfo'] = $paymentIdList[$vrpl['payId']];					
				}
				$this->assign('paymentTrade',$paymentTrade);
			}
		}
		$this->assign('pagestr',$pagestr);
		$this->assign('status',$status);
		$this->assign('count',empty($count)? 0 : $count);
		$this->display();
	}

	// 订单状态的修改
	public function orderChgStatus(){
		$paymentId=I('get.paymentId','','trim');
		$status=I('get.status','','trim');
		if (!$paymentId || !$status) {
			echo json_encode(array(0,'系统繁忙，请刷新重试！'));
			exit();
		}

		//用户取消
		if ($status == 'CANCEL') {
			$condition=array(
				'payment_id'=>$paymentId,
				'user_id'=>$this->uid
			);
			$paymentBillList = $this->modelOrder->getPaymentBillList($condition);
			if (!$paymentBillList) {
				echo json_encode(array(0,'系统繁忙，请刷新重试！'));
				exit();
			}
			$tidarry = array();
			foreach ($paymentBillList as $key => $value) {
				$tidarry[$key] = $value['tid'];
			}
			//取消paymentId下所有订单
			$conditionTO = array('tid'=>array('in',implode(',',$tidarry)),'user_id'=>$this->uid);
			//如果出现已经支付的订单，则无法取消订单
			$resTrade = $this->modelOrder->checkTrade($conditionTO);
			if (!$resTrade) {
				echo json_encode(array(0,'存在已支付的订单，无法取消！'));
				exit();
			}
			
			//如果用户支付了积分那么退还已扣除的积分
			$field = array('payment_id','cash_fee','point_fee','payed_cash','payed_point');
			$paymentInfo = $this->modelOrder->getPaymentInfo($condition,$field);
			if ($paymentInfo['payed_point']) {
				$data = array('paymentId'=>$paymentId);
				$url=C('LISHE_URL').'/admin.php/Task/mixedPayPointRet';       
        		$return=$this->requestPost($url,$data);		
            	$syncReturn = trim($return, "\xEF\xBB\xBF");//去除BOM头
            	$syncRes = json_decode($syncReturn,true);		
			}
			if (($paymentInfo['payed_point'] && $syncRes['code'] == 1) ||  !$paymentInfo['payed_point']) {
				//更新支付主表status字段状态为取消
				$resPayment = $this->modelOrder->updatePaymentStatus($condition,array('status'=>'cancel'));
				if (!$resPayment) {
					echo json_encode(array(0,'系统繁忙，请刷新重试！'));
					exit();
				}
				//更新支付副表status字段为取消
				$resPaymentBill = $this->modelOrder->updatePaymentBillStatus($condition,array('status'=>'cancel'));
				if (!$resPaymentBill) {
					echo json_encode(array(0,'系统繁忙，请刷新重试！'));
					exit();
				}
				//查询购买数量，还原库存
				$orderItemNums = $this->modelOrder->getOrderItemNums($conditionTO,'item_id,sku_id,num');
				if (!$orderItemNums) {				
					echo json_encode(array(0,'系统繁忙，请刷新重试！'));
					exit();
				}
				foreach ($orderItemNums as $key => $value) {
					$resSku = $this->modelOrder->updateSku('sku_id='.$value['sku_id'],$value['num']);
					if (!$resSku) {
						$this->makeLog('orderChgStatus',"sku_id:".$value['sku_id']."数量：".$value['num']."还原失败！\r\n");
					}
				}

				$data['status']='TRADE_CLOSED_BY_USER';
				$data['modified_time']=time();
				$res = $this->modelOrder->updateTrade($conditionTO,$data);
				$result = $this->modelOrder->updateOrder($conditionTO,$data);
				//用户取消操作记录
				$this->userName = empty($this->userName) ? '会员' : $this->userName;
				foreach ($tidarry as $key => $value) {
					$dataList[$key] = array('admin_userid'=>$this->uid,'admin_username'=>$this->userName,'created_time'=>time(),'deal_type'=>'取消订单','tid'=>$value,'memo'=>'用户取消成功','ip'=>$_SERVER["REMOTE_ADDR"]);
				}			
				$resLog = $this->modelOrder->addTradeLog($dataList);
				if ($res && $result) {
					echo json_encode(array(1,'订单取消成功！'));
					exit();
				}else{
					echo json_encode(array(0,'订单取消失败！'));
					exit();
				}
			}else{				
				echo json_encode(array(0,'订单取消失败！'));
				exit();
			}
		}elseif($status == 'CONFIRM'){
			//确认收货
			//注意：paymentId获取到的是tid
			$conditionConfirm=array('tid'=>$paymentId,'user_id'=>$this->uid);
			$dataTrade=array(
					'status'=>'TRADE_FINISHED',
					'trade_status'=>'TRADE_FINISHED',
					'modified_time'=>time()
			);
			$dataOrder=array(
					'status'=>'TRADE_FINISHED',
					'modified_time'=>time()
			);
			$res = $this->modelOrder->updateTrade($conditionConfirm,$dataTrade);
			$result = $this->modelOrder->updateOrder($conditionConfirm,$dataOrder);

			//用户取消操作记录
			$this->userName = empty($this->userName) ? '会员' : $this->userName;
			$dataList[]= array('admin_userid'=>$this->uid,'admin_username'=>$this->userName,'created_time'=>time(),'deal_type'=>'确认收货','tid'=>$paymentId,'memo'=>'用户确认收货成功','ip'=>$_SERVER["REMOTE_ADDR"]);

			$resLog = $this->modelOrder->addTradeLog($dataList);
			if ($res && $result) {
				echo json_encode(array(1,'确认收货成功！'));
				exit();
			}else{
				echo json_encode(array(0,'确认收货失败！'));
				exit();
			}
		}else{
			echo json_encode(array(0,'系统繁忙，请刷新重试！'));
			exit();
		}
	}
	//记录用户操作订单
	public function addAdminTradeLog($data){
		//批量插入用户操作记录
		$this->userName = empty($this->userName) ? '会员' : $this->userName;
		foreach ($tids as $key => $value) {
			$dataList[] = array('admin_userid'=>$this->uid,'admin_username'=>$this->userName,'created_time'=>time(),'deal_type'=>'取消订单','tid'=>$value,'memo'=>'用户取消','ip'=>$_SERVER["REMOTE_ADDR"]);
		}
		$resLog = $this->modelOrder->addTradeLog($dataList);
		if (!$resLog) {
			return false;
		}else{
			return $resLog;
		}
	}
	//订单行详情
	public function orderInfo(){
		$orderId = I('orderId');
		if (empty($orderId)) {
			$this->error("缺少订单编号！");
		}
		$condition = array('tid'=>$orderId,'user_id'=>$this->uid);
		$orderInfo = $this->modelOrder->getOrderInfo($condition);
		if (empty($orderInfo)) {
			$this->error("未查询到该订单！");
		}
		//根据订单查询支付id
		$conditionPayId['tid'] = $orderInfo['tid'];
		$paymentId = $this->modelOrder->getPaymentId($conditionPayId);
		//查询地址级联
		if ($orderInfo['buyer_area']) {
			$areas = str_replace('/',',',$orderInfo['buyer_area']);
			$conditionArea['jd_id'] = array('in',$areas);
			$areaList = $this->modelOrder->getAreaNames($conditionArea);
			if ($areaList) {
				foreach ($areaList as $key => $value) {
					$orderInfo['areaNames'] .= $value['name'];
				}
			}
		}
		//查询快递信息
		$logisticsList = $this->modelOrder->getLogistics('tid ='.$orderId);
		foreach ($logisticsList as $key => $value) {
			if ($value['corp_id'] == 3) {
				$orderInfo['jdlogisList'] = $this->expressProgress($value['logi_no']);
			}
			if ($value['corp_id'] == 2) {
				$orderInfo['sflogisList'] = $this->sfLogistics($paymentId['payment_id']);
			}
		}
		//查询店铺信息
		$shopInfo= $this->modelOrder->getShopList('shop_id='.$orderInfo['shop_id']);
		//查询商品信息
		$orderItemList = $this->modelOrder->getOrderItemList('tid='.$orderId);
		$this->assign('orderInfo',$orderInfo);
		$this->assign('shopInfo',$shopInfo);
		$this->assign('orderItemList',$orderItemList);
		$this->assign('logisticsList',$logisticsList);
		$this->display();
	}
	//京东物流信息
	public function expressProgress($expressNum){
		if($expressNum){
			//京东快递
			$url=C('COMMON_API')."Api/getExpress/";
			$data=array(
					'orderId'=>$expressNum
			);
			$res=$this->requestPost($url,$data);
			$resu=json_decode(trim($res,chr(239).chr(187).chr(191)),true);
			$expressInfo=$resu['data']['orderTrack'];
			return $expressInfo;
		}
			
	}
	//顺丰物流信息
	public function sfLogistics($payId){
		if (!$payId) {
			return false;
		}
		//顺丰物流
		$url=C('COMMON_API')."Sf/sendOrderStatus/";
		$data=array(
				'paymentId'=>$payId
		);
		$res=$this->requestPost($url,$data);
		$resu=json_decode(trim($res,chr(239).chr(187).chr(191)),true);
		$res = json_decode($resu['data'],true);
		$expressInfo=$res['data']['steps'];
		return $expressInfo;

	}
	//活动订单列表
	public function activityOrderList(){
		$status=I('get.status','');
		if (empty($status)) {
			$condition =array('user_id'=>$this->uid);
		}elseif($status == 'NO_APPLY'){
			$condition =array('user_id'=>$this->uid,'order_status'=>array('neq','NO_APPLY'));
		}else{
			$condition =array('user_id'=>$this->uid,'status'=>$status);
		}
		$count = $this->modelOrder->getActivityOrderCount($condition);
		//实例化分页类
		$size=10;
		$page = new \Think\Page($count,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page->firstRow;  //起始行数
		$pagesize = $page->listRows;   //每页显示的行数
		$limit = "$start,$pagesize";
		$style = "badge";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$orderList = $this->modelOrder->getActivityOrderList($condition,$limit);
		if ($orderList) {
			foreach ($orderList as $key => $value) {
				$tids[$key] = $value['atid'];
			}
			//查询商品信息
			$tidsStr=implode(',',$tids);
			$conditionTid['atid'] = array('in',$tidsStr);
			$conditionTid['splitOrder_id'] = array('elt',0);
			$orderItemList = $this->modelOrder->getActivityOrderItemList($conditionTid);
			//查询支付表信息
			$conditioPayId['tid'] = array('in',$tidsStr);
			$conditioPayId['user_id'] = $this->uid;
			$paymentList = $this->modelOrder->getActivityPaymentBillList($conditioPayId);
			foreach ($orderList as $key => $value) {
				foreach ($orderItemList as $k => $v) {
					if ($value['atid'] == $v['atid']) {
						$orderList[$key]['orderList'][$k]= $v;
					}
				}
				foreach ($paymentList as $ke => $val) {
					if ($value['atid'] == $val['tid']) {
						$orderList[$key]['paymentId']= $val['payment_id'];
					}
				}
			}
			$this->assign('orderList',$orderList);
		}
		$this->assign('pagestr',$pagestr);
		$this->assign('status',$status);
		$this->assign('count',empty($count)? 0 : $count);
		$this->display();
	}
	// 活动订单状态的修改
	public function activityOrderChgStatus(){
		$tid=I('get.tid','','trim');
		$status=I('get.status','','trim');
		if (!$tid || !$status) {
			echo json_encode(array(0,'系统繁忙，请刷新重试！'));
			exit();
		}
		$condition=array(
				'atid'=>$tid,
				'user_id'=>$this->uid
		);
		//用户取消
		if ($status == 'CANCEL') {
			$data['status']='TRADE_CLOSED_BY_USER';
			$data['modified_time']=time();
			$res = $this->modelOrder->updateActivityTrade($condition,$data);
			$result = $this->modelOrder->updateActivityOrder($condition,$data);
			if ($res && $result) {
				echo json_encode(array(1,'订单取消成功！'));
				exit();
			}else{
				echo json_encode(array(0,'订单取消失败！'));
				exit();
			}
		}elseif($status == 'CONFIRM'){
			//确认收货
			$data=array(
					'status'=>'TRADE_FINISHED',
					'modified_time'=>time()
			);
			$res = $this->modelOrder->updateActivityTrade($condition,$data);
			$result = $this->modelOrder->updateActivityOrder($condition,$data);
			if ($res && $result) {
				echo json_encode(array(1,'确认收货成功！'));
				exit();
			}else{
				echo json_encode(array(0,'确认收货失败！'));
				exit();
			}
		}else{
			echo json_encode(array(0,'系统繁忙，请刷新重试！'));
			exit();
		}
	}
	//退换货(退款)页面展示
	public function refund(){
		$tid = I("tid");
		$type = I('type');
		if (empty($tid)) {
			$this->error("系统繁忙，请刷新重试！");
		}
		if (empty($type)) {
			$this->error("系统繁忙，请刷新重试！");
		}
		$condition = array('tid'=>$tid,'user_id'=>$this->uid);
		$orderInfo = $this->modelOrder->getOrderInfo($condition);
		if (empty($orderInfo)) {
			$this->error("未查询到该订单！");
		}
		//获取店铺信息
		if (!$orderInfo['shop_id']) {
			$this->error("系统繁忙，请刷新重试！");
		}
		//查询地址级联
		if ($orderInfo['buyer_area']) {
			$areas = str_replace('/',',',$orderInfo['buyer_area']);
			$conditionArea['jd_id'] = array('in',$areas);
			$areaList = $this->modelOrder->getAreaNames($conditionArea);
			if ($areaList) {
				foreach ($areaList as $key => $value) {
					$orderInfo['areaNames'] .= $value['name'];
				}
			}
		}
		//查询店铺信息
		$shopInfo= $this->modelOrder->getShopList('shop_id='.$orderInfo['shop_id']);
		//查询商品信息
		$orderItemList = $this->modelOrder->getOrderItemList('tid='.$tid);
		$this->assign('orderInfo',$orderInfo);
		$this->assign('shopInfo',$shopInfo);
		$this->assign('orderItemList',$orderItemList);
		if ($type == 'refund') {
			$this->display('refundGoods');
		}else if($type == 'refundMoney'){
			$this->display('refundMoney');
		}else{
			$this->error("系统繁忙，请刷新重试！");
		}
	}
	//退款申请
	public function refundMoney(){
		$tid = I('tid',0,intval);
		$reason = I("reason");
		$mark = I("mark");
		if (empty($tid) || empty($reason) || empty($mark)) {
			$this->error('系统繁忙，请刷新重试！');
		}
		$url=C('COMMON_API')."OrderHandle/applyRefund/";
		$data=array(
				'tid'=>$tid,
				'reason'=>$reason,
				'mark'=>$mark
		);
		$res=$this->requestPost($url,$data);
		$res = trim($res, "\xEF\xBB\xBF");//去除BOM头
		$return = json_decode($res,true);
		if ($return['result'] != 100) {
			$this->error('系统繁忙，请刷新重试！');
		}
		if($return['errcode'] > 0){
			$this->error($return['msg']);
		}
		echo 1;

	}
	//退款进度
	public function refundMoneyProgress($tid){
		if (empty($tid)) {
			$tid = I('tid');
		}if (empty($tid)) {
			$this->error("系统繁忙，请刷新重试！");
		}
		//订单信息
		$condition = array('tid'=>$tid,'user_id'=>$this->uid);
		$orderInfo = $this->modelOrder->getOrderInfo($condition);
		//快递列表
		$this->assign('orderInfo',$orderInfo);
		$this->display();
	}
	//退货申请
	public function refundDo(){
		$tid = I('tid',0,intval);
		$type = I('refundtype');
		$oids = I("oid");
		$reason = I("reason");
		$mark = I("mark");
		$pathImages = I('goodsfile');
		if (empty($tid) || empty($type) || empty($oids) || empty($reason) || empty($mark)) {
			$this->error('系统繁忙，请刷新重试！');
		}
		$evidencePic = '';
		foreach ($pathImages as $key => $value) {
			$evidencePic .= $value.',';
		}
		// if(!empty($_FILES['img'])){
		// 		//上传凭证
		//     $upload = new \Think\Upload();// 实例化上传类
		//     $upload->maxSize   =  3145728 ;// 设置附件上传大小
		//     $upload->exts      =  array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		//     $upload->rootPath  =  './Upload/images/evidencePic/'; // 设置附件上传根目录
		//     // 上传单个文件 
		//     $info = $upload->uploadOne($_FILES['img']);
		//     $evidencePic = '';
		//     if(!$info){// 上传错误提示错误信息
		// 		$this->error("图片上传失败！");
		//     }else{// 上传成功 获取上传文件信息
		//         $evidencePic='/Upload/images/evidencePic/'.$info['savepath'].$info['savename'];
		//     }			
		// }	
		$oidsJson = json_encode($oids);
		$url=C('COMMON_API')."OrderHandle/applyReturnOrChange/";
		$data=array(
				'tid'=>$tid,
				'type'=>$type,
				'oids'=>$oidsJson,
				'reason'=>$reason,
				'mark'=>$mark,
				'evidencePic'=>$evidencePic
		);
		$res=$this->requestPost($url,$data);
		$res = trim($res, "\xEF\xBB\xBF");//去除BOM头
		$return = json_decode($res,true);
		if ($return['result'] != 100) {
			$this->error('系统繁忙，请刷新重试！!');
		}
		if($return['errcode'] > 0){
			$this->error($return['msg']);
		}
		$this->redirect('Order/refundLogistics/tid/'.$tid);

	}
	//退货物流信息页面
	public function refundLogistics($tid){
		if (empty($tid)) {
			$tid = I('tid');
		}
		if (empty($tid)) {
			$this->error("系统繁忙，请刷新重试！");
		}
		//订单信息
		$condition = array('tid'=>$tid,'user_id'=>$this->uid);
		$orderInfo = $this->modelOrder->getOrderInfo($condition);
		//快递列表
		$syslogDlyList = $this->modelOrder->getSyslogDlyList();
		//回寄信息		
		$aftersales = $this->modelOrder->getAftersales('tid='.$tid);
		$this->assign('aftersales',json_decode($aftersales['shop_explanation'],true));
		$this->assign('syslogDlyList',$syslogDlyList);
		$this->assign('orderInfo',$orderInfo);
		$this->display('refundLogistics');
	}
	//提交退货物流信息
	public function refundLogisDo(){
		$tid = I('tid');
		$corpName = I('corpName');
		$corpNum = I('corpNum');
		if (empty($tid)) {
			echo json_encode(array(0,'系统繁忙，请刷新重试！'));
			exit();
		}
		if (empty($corpName)) {
			echo json_encode(array(0,'请选择物流！'));
			exit();
		}
		if (empty($corpNum)) {
			echo json_encode(array(0,'请填写物流单号！'));
			exit();
		}
		//查询oids
		$orderList = $this->modelOrder->getOrderOidList('tid='.$tid.' and aftersales_status="WAIT_BUYER_SEND_GOODS"');
		foreach ($orderList as $key => $value) {
			$oids[] = $value['oid'];
		}
		$oidStr=implode(',',$oids);
		//修改退货物流信息表
		$condition=array('tid'=>$tid,'oid'=>array('in',$oidStr));
		$data['sendback_data'] = json_encode(array('logi'=>$corpName,'logi_no'=>$corpNum));
		try {
			$res = $this->modelOrder->updateAftersales($condition,$data);
			$this->makeLog('refundLogisDo',' message:res:'.$res."\r\n");
			//修改主订单表
			$resTrade=$this->modelOrder->updateTrade(array('tid'=>$tid),array('status'=>'WAIT_SELLER_CONFIRM_GOODS'));
			$this->makeLog('refundLogisDo',' message:resTrade:'.$resTrade."\r\n");
			//修改子订单表
			$resOrder = $this->modelOrder->updateOrder($condition,array('aftersales_status'=>'WAIT_SELLER_CONFIRM_GOODS'));	
			$this->makeLog('refundLogisDo',' message:resOrder:'.$resOrder."\r\n");		
		} catch (\Exception $e) {
			$this->makeLog('refundLogisDo',' message:error:'.$e->getMessage()."\r\n");
		}
		if ($res && $resTrade && $resOrder) {			
			echo json_encode(array(1,'提交成功！'));
			exit();
		}else{	
			echo json_encode(array(0,'提交失败！'));
			exit();
		}
	}

	//企业礼包订单
	public function comOrder(){
		$page = I('get.p', 1, 'intval');
		$listRows = 10;
		//分页
		$where = array();
		$where['user_id'] = $this->uid;
		$where['receive_status'] = 1;
		//订单列表
		$receverOrderList = $this->orderRecever->where($where)->page($page, $listRows)->order('receive_time DESC')->select();
		if(empty($receverOrderList)){
			$this->display('emptyHint');
			exit();
		}
		//总订单数
		$receverOrderCount = $this->orderRecever->where($where)->count('order_id');
		 
		$orderIdArr = array();
		$actConfigIdArr = array();
		$comIdArr = array();
		$atidArr = array();
		foreach ($receverOrderList as $order){
			$actConfigId = $order['activity_config_id'];
			$comId = $order['com_id'];
			$orderIdArr[] = $order['order_id'];
			$actConfigIdArr[$actConfigId] = $actConfigId;
			$comIdArr[$comId] = $comId;
			$atidArr[] = $order['atid'];
		}
		 
		//加载订单收货人信息
		$where = array();
		$where['order_id'] = array('in', $orderIdArr);
		$orderList = $this->groupOrder->where($where)->select();
		$orderArr = array();
		foreach ($orderList as $order){
			$stateArr = explode(':', $order['receiver_state']);
			$cityArr = explode(':', $order['receiver_city']);
			$disArr = explode(':', $order['receiver_district']);
			$order['receiver_state'] = $stateArr[0];
			$order['receiver_city'] = $cityArr[0];
			$order['receiver_district'] = $disArr[0];
			$orderArr[$order['order_id']] = $order;
		}
		unset($orderList);
		//加载订单礼包信息
		$where = array();
		$where['activity_config_id'] = array('in', $actConfigIdArr);
		$categoryList = $this->activityCategory->field('activity_config_id, cat_name')->where($where)->select();
		$categoryArr = array();
		foreach ($categoryList as $cat){
			$categoryArr[$cat['activity_config_id']] = $cat;
		}
		unset($categoryList);
		//加载企业信息
		$where = array();
		$where['com_id'] = array('in', $comIdArr);
		$comList = M('company_config')->field('com_id, com_name')->where($where)->select();
		$comArr = array();
		foreach ($comList as $com){
			$comArr[$com['com_id']] = $com;
		}
		unset($comList);
		//加载订单信息
		$where = array();
		$where['atid'] = array('in', $atidArr);
		$tradeList = $this->activityTrade->field('atid,status')->where($where)->select();
		$tradeArr = array();
		foreach ($tradeList as $trade){
			$tradeArr[$trade['atid']] = $trade['status'];
		}
		 
		//实例化分页类
		$page = new \Think\Page($receverOrderCount, $listRows);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page->firstRow;  //起始行数
		$limit = "$start,$size";
		$style = "badge";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		 
		$this->assign('pagestr', $pagestr);
		$this->assign('categoryArr', $categoryArr);
		$this->assign('comArr', $comArr);
		$this->assign('orderArr', $orderArr);
		$this->assign('tradeArr', $tradeArr);
		$this->assign('receverOrderList', $receverOrderList);
		$this->display('comOrder');
	}
	
	//企业福利订单-获取答谢虚拟礼物
	public function thxImg(){
		$ret = array('','msg'=>'unkown error','data'=>array());
		$url = C('API').'/welfareMall/getDataBaseList';
		$param = array();
		$param['status'] = 1;
		$param['dataType'] = '虚拟礼物';
		$param['sign'] = apiSign($param);
		$result = $this->requestJdPost($url, $param);
		$resultArr = json_decode($result, true);
		 
		if(0 === intval($resultArr['errcode'])){
			//过滤数据
			$data = array();
			$imgHost = C('API_SERVER');
			array_walk($resultArr['data']['info'], function ($val) use (&$data,$imgHost){
				$data[] = array(
						'id'=>$val['id'],
						'url'=>$imgHost.$val['dataValue']
				);
			});
				$ret['code'] = 1;
				$ret['msg'] = 'success';
				$ret['data'] = array_slice($data, 0, 8);
		}else{
			$ret['msg'] = 'fail';
		}
		$this->ajaxReturn($ret);
	}
	
	//答谢
	public function thx(){
		$receverId = I('post.receverId', -1, 'intval');
		$giftid = I('post.giftid', -1, 'intval');
		$thxContent = I('post.thxContent','','trim,strip_tags,stripslashes');
		$ret = array('code'=>-1,'msg'=>'unkown error');
		 
		//参数过滤
		if(!is_numeric($receverId) || $receverId < 1){
			$ret['msg'] = '参数错误';
			$this->ajaxReturn($ret);
		}
		 
		if(!is_numeric($giftid) || $giftid < 1){
			$ret['msg'] = '请选择礼物';
			$this->ajaxReturn($ret);
		}
		if(empty($thxContent)){
			$ret['msg'] = '答谢内容为空';
			$this->ajaxReturn($ret);
		}
		 
		if(mb_strlen($thxContent,'UTF8') > 50){
			$ret['msg'] = '答谢内容50字以内';
			$this->ajaxReturn($ret);
		}
		 
		//核对数据信息
		$where = array();
		$where['recever_id'] = $receverId;
		$recever = $this->orderRecever->field('atid,phone,user_id,receive_status,is_thx')->where($where)->find();
		if($recever['user_id'] != $this->uid){
			$ret['msg'] = '没有答谢权限';
			$this->ajaxReturn($ret);
		}
		 
		if($recever['receive_status'] != 1){
			$ret['msg'] = '领取状态错误';
			$this->ajaxReturn($ret);
		}
		 
		if($recever['is_thx'] != 0){
			$ret['msg'] = '请不要重复答谢';
			$this->ajaxReturn($ret);
		}
		 
		//调用请求接口
		$atid = $recever['atid'];
		$param = array();
		$param['shopSupplyNo'] = $atid;
		$param['phoneNum'] = $recever['phone'];
		$param['dataBaseId'] = $giftid;
		$param['replyContent'] = $thxContent;
		$param['sign'] = apiSign($param);
		$url = C('API').'welfareMall/thanksHR';
		$result = $this->requestJdPost($url, $param);
		$resultArr = json_decode($result, true);
		if(isset($resultArr['errcode']) && $resultArr['errcode'] == 0){
			$where = array();
			$where['recever_id'] = $receverId;
			$this->orderRecever->where($where)->setField('is_thx',1);
			//添加记录
			$data = array();
			$data['thx_giftid'] = $giftid;
			$data['thx_content'] = $thxContent;
			$data['atid'] = $atid;
			$data['recever_id'] = $receverId;
			M('gift_group_order_thx')->add($data);
	
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = '提交失败';
		}
		$this->ajaxReturn($ret);
	}
	
	//空页面
	public function emptyHint(){
		$this->display();
	}
}