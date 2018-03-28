<?php 
namespace Home\Controller;
use Org\Util\Excel;
class OrderController extends CommonController {
/*
 * 订单管理
 * 2016/10/19
 * zhangrui
 * 
 * */	
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dOrder=D('Order');
	}
	/*
	 * 
	 * 写进管理员订单日志表
	 * */
	public function markTradeLog($tid,$dealType,$remarks,$oidsString){
		//写进管理员操作日志表
		$logData['admin_username']=$this->realName;
		$logData['admin_userid']=$this->adminId;
		$logData['deal_type']=$dealType;
		$logData['tid']=$tid;
		$logData['memo']=$remarks;
		$logData['oids']=$oidsString;
		$this->dOrder->markTradeLog($logData);		
	}	
	//取出条件的名字
	public function serachName($id,$type,$level){
		//搜索条件的对应名字
		//订单状态
		if($type=="orderStatus"){
			$staus=array(
				"WAIT_BUYER_PAY" =>array(
					"status" => "WAIT_BUYER_PAY",
					"name"   => "待付款"
				),
				"TRADE_CLOSED_BY_SYSTEM" =>array(
					"status" => "TRADE_CLOSED_BY_SYSTEM",
					"name"   => "已取消"
				),
				"TRADE_FINISHED" =>array(
					"status" => "TRADE_FINISHED",
					"name"   => "已完成"
				),								
				"WAIT_SELLER_SEND_GOODS" =>array(
					"status" => "WAIT_SELLER_SEND_GOODS",
					"name"   => "待发货"
				),				
				"WAIT_BUYER_CONFIRM_GOODS" =>array(
					"status" => "WAIT_BUYER_CONFIRM_GOODS",
					"name"   => "待收货"
				),	
				"WAIT_COMMENT" =>array(
					"status" => "WAIT_COMMENT",
					"name"   => "待评价"
				),	
				"IN_STOCK" =>array(
					"status" => "IN_STOCK",
					"name"   => "备货中"
				),											
			);
			$info=$staus[$id];
		}
		if($type=="serviceType"){
			$stausl=array(
				"NO_APPLY"=>array(
					"type" => "NO_APPLY",
					"name" => "无售后"
				),
				"REFUND" =>array(
					"type" => "REFUND",
					"name"   => "退款"
				),	
				"CANCEL_REFUND" =>array(
					"type" => "CANCEL_REFUND",
					"name"   => "取消退款"
				),	
				"RETURN" =>array(
					"type" => "RETURN",
					"name"   => "退货"
				),							
				"CANCEL_RETURN" =>array(
					"type" => "CANCEL_RETURN",
					"name"   => "取消退货"
				),	
				"EXCHANGE" =>array(
					"type" => "EXCHANGE",
					"name"   => "换货"
				),				
				"CANCEL_EXCHANGE" =>array(
					"type" => "CANCEL_EXCHANGE",
					"name"   => "取消换货"
				),	
				"REPAIR" =>array(
					"type" => "REPAIR",
					"name"   => "维修"
				),					
				"CANCEL_REPAIR" =>array(
					"type" => "CANCEL_REPAIR",
					"name"   => "取消维修"
				)
			);		
			$info=$stausl[$id];
		}
		return $info;
	}	
	//订单状态翻译
	public function orderStatus($status){
		switch($status){
			case "WAIT_BUYER_PAY":
				$status="待付款";
				break;
			case "TRADE_CLOSED_BY_SYSTEM":
				$status="已取消(系统)";
				break;
			case "TRADE_CLOSED_BY_ADMIN":
				$status="已取消(管理员)";
				break;				
			case "TRADE_CLOSED_BY_USER":
				$status="已取消(用户)";
				break;				
			case "TRADE_FINISHED":
				$status="已完成";
				break;
			case "WAIT_SELLER_SEND_GOODS":
				$status="待发货";
				break;	
			case "WAIT_BUYER_CONFIRM_GOODS":
				$status="待收货";
				break;	
			case "IN_STOCK":
				$status="备货中";
				break;																								
		}
		return $status;
	}		
//售后类型翻译
	public function orderStatusReturn($serviceStatus){
		switch($serviceStatus){
			case "NO_APPLY":
				$serviceStatus="无售后";
				break;
			case "REFUND":
				$serviceStatus="有退款";
				break;
			case "CANCEL_REFUND":
				$serviceStatus="取消退款";
				break;				
			case "RETURN":
				$serviceStatus="有退货";
				break;
			case "CANCEL_RETURN":
				$serviceStatus="取消退货";
				break;				
			case "EXCHANGE":
				$serviceStatus="有换货";
				break;	
			case "CANCEL_EXCHANGE":
				$serviceStatus="取消换货";
				break;					
			case "REPAIR":
				$serviceStatus="有维修";
				break;		
			case "CANCEL_REPAIR":
				$serviceStatus="取消维修";
				break;																							
		}		
		return $serviceStatus;
	} 
//售后状态翻译
	public function orderStatusLastReturn($serviceStatus){
		switch($serviceStatus){
			case "NO_APPLY":
				$serviceStatus="无操作";
				break;	
			case "CANCEL_APPLY":
				$serviceStatus="取消售后申请";
				break;					
			case "WAIT_EARLY_PROCESS":
				$serviceStatus="待初审";
				break;						
			case "WAIT_PROCESS":
				$serviceStatus="待审核";
				break;
			case "SELLER_REFUSE":
				$serviceStatus="商家拒绝";
				break;
			case "REFUND_PROCESS":
				$serviceStatus="待退款";
				break;				
			case "SUCCESS":
				$serviceStatus="已完成";
				break;
			case "WAIT_BUYER_SEND_GOODS":
				$serviceStatus="等待用户回寄";
				break;				
			case "WAIT_SELLER_CONFIRM_GOODS":
				$serviceStatus="等待商家收货";
				break;	
			case "WAIT_REFUND":
				$serviceStatus="待退款(商家已收到货)";
				break;					
			case "SELLER_SEND_GOODS":
				$serviceStatus="商家已回寄";
				break;		
		}		
		return $serviceStatus;
	} 
/*
 * 代发订单
 * */	
	public function index(){
		$adminId=$this->adminId;
		$condition=array(
			'supplier_id'=>$adminId,
			'status'=>array('not in','TRADE_CLOSED_BY_SYSTEM,TRADE_CLOSED_BY_USER,TRADE_CLOSED_BY_ADMIN,WAIT_BUYER_PAY')
		);
		$allTids=$this->dOrder->getOrderItemsToNum($condition);
		$this->showDeal($allTids);
		$this->display();
	}
/*
 * 未发货订单
 * */	
 	public function waitSendIndex(){
 		$adminId=$this->adminId;
		$condition=array(
			'supplier_id'=>$adminId,
			'status'=>array('in','WAIT_SELLER_SEND_GOODS,IN_STOCK'),
			'aftersales_status'=>'NO_APPLY',
		);		
		$allTids=$this->dOrder->getOrderItemsToNum($condition);
		$this->showDeal($allTids,'nosend');
		$this->display();		
 	}
/*
 * 已发订单
 * */	
 	public function sendedIndex(){
 		$adminId=$this->adminId;
		$condition['supplier_id']=$adminId;
		$condition['status']=array('in','WAIT_BUYER_CONFIRM_GOODS,WAIT_COMMENT,TRADE_FINISHED');
		$allTids=$this->dOrder->getOrderItemsToNum($condition);
		$this->showDeal($allTids,'sended');
		$this->display();	 		
 	}
/*
 * 售后订单
 * */	
 	public function aftersaleIndex(){
  		$adminId=$this->adminId;
		$condition=array(
			'supplier_id'=>$adminId,
			'aftersales_status'=>array('neq','NO_APPLY'),
		);		
		$allTids=$this->dOrder->getOrderItemsToNum($condition);
		$this->showDeal($allTids,'aftersale');
		$this->display();		
 	}
/*
 * 页面显示处理
 * 
 * */
 	public function showDeal($allTids,$type){
		$adminId=$this->adminId;
		if($_GET['goods']){
			$_GET['goods']=urldecode($_GET['goods']);
		}
		$data=I('');
		$searchData=$data;
		//输出搜索条件显示
        $page=empty($_GET['p'])?1:$_GET['p'];
		if(count($allTids)>1){
			$allTids=array_unique($allTids);
		}
		if(!empty($data['tid'])){
			//订单号条件搜索
			if(in_array(trim($data['tid']), $allTids)){
				$allTids=trim($data['tid']);
			}else{
				$allTids=0;
			}
		}
		//商品名条件
		$goodWhere=array();
		if(!empty($data['goods'])){
			$goodWhere['title']=array('like','%'.$data['goods'].'%');
			$goodWhere['tid']=array('in',$allTids);
			$allTids=$this->dOrder->getOrderItemsToNum($goodWhere);
		}
		//时间条件
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$data['start']=strtotime($data['startTime']);
			$data['end']=strtotime($data['endTime']." +24 hours");
			$str=$data['start'].','.$data['end'];
			$orderWhere['pay_time']=array('between',$str);
		}
		//状态条件
		if(!empty($data['status'])){
			//start订单状态
				$searchData['status']=$this->serachName($data['status'],"orderStatus");
			//end	
			if(!empty($allTids)){
				$where['tid']=array('in',$allTids);
			}else{
				$where['tid']=0;
			}			
			if($data['status']=="TRADE_CLOSED_BY_SYSTEM"){
				$where['status']=array('in','TRADE_CLOSED_BY_SYSTEM,TRADE_CLOSED_BY_USER,TRADE_CLOSED_BY_ADMIN');
			}else{
				$where['status']=$data['status'];
			}
			$allTids=$this->dOrder->getOrderItemsToNum($where);
		}
		//售后状态选择
		if(!empty($data['service'])){
			$searchData['service']=$this->serachName($data['service'],"serviceType");
			if($data['service'] != 'NO_APPLY'){
				if(!empty($allTids)){
					$serviceWhere['tid']=array('in',$allTids);
				}else{
					$serviceWhere['tid']=0;
				}
				$serviceWhere['aftersales_status']=array('neq','NO_APPLY');		
				$allTids=$this->dOrder->getOrderItemsToNum($serviceWhere);
			}
			$orderWhere['order_status']=$data['service'];
			
		}
		if(count($allTids)>1){
			$allTids=array_unique($allTids);
		}
		if((!empty($data['startTime']) && !empty($data['endTime'])) || !empty($data['service'])){
			if(!empty($allTids)){
				$orderWhere['tid']=array('in',$allTids);
			}else{
				$orderWhere['tid']=0;
			}
			$allTids=$this->dOrder->getConditionOrderTid($orderWhere);
		}
	
		$size=20;
		$page = new \Think\Page(count($allTids),$size);
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		//按订单创建时间排下序
		if(!empty($allTids)){
			$map['tid']=array('in',$allTids);
		}else{
			$map['tid']=0;
		}
		$allTids=$this->dOrder->getConditionOrderTid($map);
		if(I('execlType') && I('execlType')=="exportExcel"){
			//导出execl
			$orderExecl=$this->dOrder->getOrder(array('tid'=>array('in',$allTids)));
			if(!empty($orderExecl)){
				$execlData=$this->OrderDeal($orderExecl,$type);	
				$this->orderExportExcel($execlData,$type);         
			}
		}			
		//按订单创建时间排下序
		if(count($allTids)>=$size){
			$OrderTids=array_slice($allTids,$start,$pagesize);	
		}else{
			$OrderTids=$allTids;
		}
		if(!empty($OrderTids)){
			$condition['t.tid']=array('in',$OrderTids);
		}else{
			$condition['t.tid']=0;
		}	
		$orderRes=$this->dOrder->getOrder($condition);
		if(!empty($orderRes)){
			$orderRes=$this->OrderDeal($orderRes,$type);
		}	
        $this->assign('page',$page->show());	
		$this->assign('list',$orderRes);
		$this->assign('number',count($allTids));
		//搜索条件转码
		$this->assign('searchData',$searchData); 		
		
 	}
/*
  * 订单处理
  * */
	public function OrderDeal($orderRes,$status){
		$adminId=$this->adminId;
		if(empty($orderRes)){
			exit;
		}
		foreach($orderRes as $key=>$value){
			$tids[]=$value['tid'];   //订单号
			$userIds[]=$value['user_id'];
		}
		if(!empty($tids)){
			$condition['tid']= array('in',$tids);
			if($status=='nosend'){
				//待发货页面
				$condition['aftersales_status']='NO_APPLY';
				$condition['status']=array('in',array('WAIT_SELLER_SEND_GOODS','IN_STOCK'));
			}else if($status=="sended"){
				//已发货页面
				$condition['status']=array('in',array('WAIT_BUYER_CONFIRM_GOODS','WAIT_COMMENT','TRADE_FINISHED'));
			}else if($status=="aftersale"){
				//售后页面
				$condition['aftersales_status']=array('neq','NO_APPLY');
			}
			$orderItem=$this->dOrder->getOrderItems($condition,$adminId);
		}
		foreach($orderRes as $key=>$value){
			//订单内商品
			foreach($orderItem as $keys=>$values){
				if($value['tid']==$values['tid']){
					$orderRes[$key]['items'][]=$values;	
				}				
			}
			$orderRes[$key]['orderStatus']=$this->orderStatus($value['status']);
			$orderRes[$key]['serviceStatus']=$this->orderStatusReturn($value['order_status']);
		}
		return $orderRes;

	}	
	//订单详情
	public function detail(){
		$adminId=$this->adminId;
		$tid=I('tid');
		if($tid){
			$detail=$this->dOrder->getOrderDetail($tid,$adminId);
			//状态
			$detail['OrderStatus']=$this->orderStatus($detail['status']);
			//时间
			if(!empty($detail['pay_time'])){
				$detail['pay_time']=date('Y-m-d H:i:s',$detail['pay_time']);
			}
			if(!empty($detail['modified_time'])){
				$detail['modified_time']=date('Y-m-d H:i:s',$detail['modified_time']);
			}
			//物流信息
			$detail['express']=$this->dOrder->getExpress($detail['tid'],$adminId);
			//售后状态
			foreach($detail['more'] as $key=>$value){
				$itemIds[]=$value['item_id'];
				$detail['more'][$key]['serviceLastStatus']=$this->orderStatusLastReturn($value['aftersales_status']);
				if($value['aftersales_status'] != 'NO_APPLY'){
					$hasAfter=1;
				}
			}
			if($hasAfter==1){
				//售后类型
				$detail['serviceStatus']=$this->orderStatusReturn($detail['order_status']);
			}
//			订单处理进度end
			$this->assign('info',$detail);
		}
		
		$this->display();
	}		
/*
 * 标记订单状态为备货中页面
 * */	
	public function toStock(){
		$adminId=$this->adminId;
		$tid=I('tid');
		if($tid){
			$items=$this->dOrder->getOrderItems($tid,$adminId);
			$this->assign('tid',$tid);
			$this->assign('items',$items);
			$this->display();
			
		}else{
			echo 'Can not find pay bill!';
		}
	}	
/*
 * 标记订单状态为备货中处理
 * */
	public function toStockDeal(){
		$adminId=$this->adminId;
		$tid=I('tid');
		$oids=I('oids');
		$mark=I('mark');
		if(empty($oids)){
			$this->error("请选择可备货的商品");
		}
		//加入管理员订单日志表
		$dealType="标记备货";
		$remarks="备货中,备注：".$mark;
		$oidsString=$oids;
		$logData['tid']=$tid;
		$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
		$nowstatus=$this->dOrder->getThisOrderInfo($tid,'status');
		if($nowstatus['status'] == "WAIT_SELLER_SEND_GOODS"){
			$data['status']='IN_STOCK';
			$res=$this->dOrder->editTradeInfo($tid,$data);//添加备注，订单状态改为备货中
		}		
		$condition['oid']=array('in',$oids);
		$SendData['status']='IN_STOCK';
		$SendData['to_stork_mark']=$mark."&nbsp;&nbsp".date('Y-m-d H:i:s',time());
		$SendData['modified_time']=time();
		$condition['supplier_id']= $adminId;
		$statusRes=$this->dOrder->editThisConditionOrderInfo($condition,$SendData,'status,modified_time,to_stork_mark');		
		if($statusRes){
			$this->success('标记备货成功！');
		}
	}	
/*
 * 
 * 发货
 * */
	public function sendGoods(){
		$adminId=$this->adminId;
		$tid=I('tid');
		if($tid){
			//取出该订单的地址信息
			$res=$this->dOrder->getThisOrderInfo($tid,'shop_id,tid,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address,buyer_message,ziti_memo,pay_time');
			$itemInfo=$this->dOrder->getThisTradeInfo($tid,$adminId,'oid,title,num,spec_nature_info,sendnum,to_stork_mark,supplier_id,send_type,status,disabled,aftersales_status');
			$express=$this->dOrder->getAllexpressInfo();
			$this->assign('express',$express);
			$this->assign('info',$res);
			$this->assign('item',$itemInfo);
			$this->display();
		}
	}
/*
 * 发货处理
 * */	
	public function sendGoodsDeal(){
		$adminId=$this->adminId;
		$logId=I('logId');
		$tid=I('tid');
		$logiNo=trim(I('logiNo'));
		$oids=I('oid'); 
		if(empty($oids)){
			$this->error('请选择发货的商品!');
		}		
		if(!empty($logId) && !empty($logiNo) && !empty($tid)){
			//快递公司信息
			$expressCom=$this->dOrder->getThisExpressInfo($logId);
			$data=$this->dOrder->getThisOrderInfo($tid);
			$data['supplier_id']=$adminId;
			$data['logi_no']=$logiNo;
			$data['logi_id']=$expressCom['corp_id'];
			$data['logi_name']=$expressCom['corp_name'];
			$data['logi_code']=$expressCom['logi_no'];
			$data['seller_id']=$this->dOrder->getThisShopInfo($data['shop_id']);
			$data['t_begin']=time();
			$data['t_send']=time();
			$data['t_confirm']=time();
			$data['status']='succ';
			$data['delivery_id']=date('ymdHis').rand(1000000,9999999);
			//发货添加发货表
			$res=$this->dOrder->sendGoodsaddExpree($data);
			if($res){
				//发货添加发货详情表
				$orderInfo=$this->dOrder->getOidsOrderInfo($oids,$adminId,'oid,num,item_id,title');
				$SendData['status']='WAIT_BUYER_CONFIRM_GOODS';
				$SendData['consign_time']=time();
				$SendData['modified_time']=time();				
				foreach($orderInfo as $key=>$value){
				//更改order表发货商品数量
					$SendData['sendnum']=$value['num'];
					$this->dOrder->editThisOrderInfo($value['oid'],$SendData,'sendnum,status,consign_time,modified_time');
					$itemIds[]=$value['item_id'];
					$sendOids[]=$value['oid'];
				} 				
				$itemInfos=$this->dOrder->getAllItem($itemIds,'jd_sku,item_id,bn');
				foreach($orderInfo as $key=>$value){
					foreach($itemInfos as $keys=>$values){
						if($value['item_id']==$values['item_id']){
							$orderInfo[$key]['sku_id']=$values['jd_sku'];
							$orderInfo[$key]['sku_bn']=$values['bn'];
						}
					}
					$orderInfo[$key]['number']=$value['num'];
					$orderInfo[$key]['sku_title']=$value['title'];
					$orderInfo[$key]['delivery_id']=$data['delivery_id'];
				}
				foreach($orderInfo as $key=>$value){
					$this->dOrder->addExpressDeatils($orderInfo[$key]);
				} 	
				//改变订单状态为待收货
				$nowstatus=$this->dOrder->getThisOrderInfo($tid,'status');
				if(in_array($nowstatus['status'], array('IN_STOCK','WAIT_SELLER_SEND_GOODS'))){
					//改变订单状态为待收货
					$dataStatus['status']='WAIT_BUYER_CONFIRM_GOODS';
					$orderRes=$this->dOrder->editTradeInfo($tid,$dataStatus);	
				}					
				//子订单状态改为待收货
				if(!empty($oids)){
					$condition['oid']=array('in',$oids);
				}else{
					$condition['tid']=$tid;
				}
				//写进管理员操作订单日志表
				$oidsString=implode(',',$oids);				
				$dealType="发货";
				$remarks="配送快递：".$expressCom['corp_name'].",快递单号：".$logiNo;
				$logData['tid']=$tid;
				$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
				if($res){
					echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>订单:".$tid."标记发货！<br/>配送流水号:".$data['delivery_id']."</div>";
				}
			}
		}else{
			$this->error('提交参数不完整！请重新检测输入');
		}
		
	}
/*
 * 导出execl
 * */
	public function orderExportExcel($execlData,$type){
		header("Content-type:text/html;charset=utf-8");
		foreach($execlData as $key => $value){
			foreach($value['items'] as $keys=>$values){
				$itemIds[]=$values['item_id'];
				$skuIds[]=$values['sku_id'];
			}
		}	
		if(!empty($skuIds)){
			$skuInfo=$this->dOrder->getAllSkuItem($skuIds);
		}
		foreach($execlData as $key => $value){
			$oldTids[$value['tid']]=$value['tid'];
			foreach($value['items'] as $keys=>$values){
				$itemIds[]=$value['item_id'];
				if($keys==0){
					//订单第一条
					$newTids[$value['tid']]=$value['tid'];
					$LastExeclData[$values['oid']]['tid']=" ".$value['tid'];
				}else{
					$LastExeclData[$values['oid']]['tid']=" ";
				}
					$LastExeclData[$values['oid']]['title']=$values['title'];
					$LastExeclData[$values['oid']]['barcode']=$values['sku_id'];
					$LastExeclData[$values['oid']]['bn']="";
					$LastExeclData[$values['oid']]['spec_nature_info']=$values['spec_nature_info'];
					$LastExeclData[$values['oid']]['price']=" ".$values['cost_price'];
					$LastExeclData[$values['oid']]['num']=$values['num'];
				if($keys==0){
					$LastExeclData[$values['oid']]['receiver_name']=$value['receiver_name'];
					$LastExeclData[$values['oid']]['receiver_mobile']=$value['receiver_mobile'];
					$LastExeclData[$values['oid']]['address']=$value['receiver_state'].$value['receiver_city'].$value['receiver_district'].$value['receiver_address'];
					$LastExeclData[$values['oid']]['created_time']=date('Y-m-d H:i:s',$value['created_time']);
					if($value['pay_time']){
						$LastExeclData[$values['oid']]['pay_time']=date('Y-m-d H:i:s',$value['pay_time']);
					}else{
						$LastExeclData[$values['oid']]['pay_time']="---";
					}
					$LastExeclData[$values['oid']]['buyer_message']=$value['buyer_message'];
				}else{
					$LastExeclData[$values['oid']]['receiver_name']="";
					$LastExeclData[$values['oid']]['receiver_mobile']="";
					$LastExeclData[$values['oid']]['address']="";
					$LastExeclData[$values['oid']]['created_time']="";
					$LastExeclData[$values['oid']]['pay_time']="";	
					$LastExeclData[$values['oid']]['buyer_message']="";
				}
			}
		}
		foreach($LastExeclData as $key =>$value){
			$newTids[]=$value['tid'];
			foreach($skuInfo as $keyk=>$valuek){
				if($value['barcode']==$valuek['sku_id']){
					//条形码
					$LastExeclData[$key]['barcode']=" ".$valuek['barcode'];
					//商品编号
					$LastExeclData[$key]['bn']=" ".$valuek['bn'];
				}
			}
		}
//		看哪个订单没导出start
//		$newTids=array_unique($newTids);
//		$oldTids=array_unique($oldTids);
//		foreach($oldTids as $k1 => $v1){
//			if(!array_key_exists($v1, $newTids)){
//				$noTid[]=$v1;
//			}
//			
//		}
//		看哪个订单没导出end  
		$ex=new Excel;
		if($type=='nosend'){
			$title="未发货清单";
		}else if($type=='sended'){
			$title="已发货清单";
		}else{
			$title="订单列表";
		}
		$columnName=array('订单号','商品名称','商品条形码','商品编号','商品属性','商品价格/元','商品数量','收货人','手机','地址','下单时间','支付时间','买家留言');
		$ex->getExcel($LastExeclData,$columnName,$title.date('YmdHis'));			
	}
}