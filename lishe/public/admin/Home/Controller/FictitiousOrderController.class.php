<?php  
namespace Home\Controller;
use Org\Util\Excel;
class FictitiousOrderController extends CommonController {
/*
 * 订单管理
 * 2016/8/23
 * zhangrui
 * 
 * */	
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dFOrder=D('Fictitious');
		$this->dOrder=D('Order');
		$this->dCategory=D('Category');
		$this->dShop=D('Shopinfo');	
		$this->dActivityOrder=D('Activityorder');
		$this->dCategory=D('Category');
		$this->dGoods=D('Goods');
		$this->dAftersales=D('Aftersales');				
		
	}
	//取出条件的名字
	public function serachName($id,$type,$level){
		//搜索条件的对应名字
		//企业
		if($type=="company"){
			$company=$this->dFOrder->getAllCompany();
			foreach($company as $key=>$value){
				if($value['com_id']==$id){
					$info=$value;
				}
			}
		}
		//店铺
		if($type=="shop"){
			$shop=$this->dShop->getAllShopName();
			foreach($shop as $key=>$value){
				if($value['shop_id']==$id){
					$info=$value;
				}
			}
		}	
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
			);
			$info=$staus[$id];
		}
		if($type=="serviceType"){
			$stausl=array(
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
		//商品分类
		if($type=="category"){
			$info=$this->dFOrder->getThisCategoryInfo($id,$level);
			
		}	
		return $info;
	}	
	//条件
	public function condition(){
		$data=I('');
		//输出搜索条件显示
		$searchData=$data;
		//企业条件
		if(!empty($data['comId'])){
			//start公司名字显示用
				$searchData['company']=$this->serachName($data['comId'],"company");
			//end
			$condition['t.com_id']=$data['comId'];
		}
		//店铺条件
		if(!empty($data['shopId'])){
			//start公司名字显示用
				$searchData['shop']=$this->serachName($data['shopId'],"shop");
			//end
						
			$condition['t.shop_id']=$data['shopId'];
		}
		//时间条件
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$data['start']=strtotime($data['startTime']);
			$data['end']=strtotime($data['endTime']." +24 hours");
			$str=$data['start'].','.$data['end'];
			$condition['t.created_time']=array('between',$str);
		}
		//状态条件
		if(!empty($data['status'])){
			//start订单状态
				$searchData['status']=$this->serachName($data['status'],"orderStatus");
			//end	
			if($data['status']=="TRADE_CLOSED_BY_SYSTEM"){
				$condition['t.status']=array('in','TRADE_CLOSED_BY_SYSTEM,TRADE_CLOSED_BY_USER,TRADE_CLOSED_BY_ADMIN');
			}else{
				$condition['t.status']=$data['status'];
			}		
		}
		//售后状态选择
		if(!empty($data['service'])){
			$searchData['service']=$this->serachName($data['service'],"serviceType");
			$condition['t.order_status']=$data['service'];
			
		}
		//员工手机号
		if(!empty($data['mobile'])){
			$userId=$this->dFOrder->getUserId(trim($data['mobile']));
			if(!empty($userId)){
				$condition['t.user_id']=array('in',$userId);
			}
		}
		//订单号
		if(!empty($data['tid'])){
			$condition['t.tid']=trim($data['tid']);
		}
		//商品名条件
		if(!empty($data['goods'])){
			$goodWhere['title']=array('like','%'.$data['goods'].'%');
			$goodsTids=$this->dFOrder->getOrderItemsToNum($goodWhere);
			if(!empty($goodsTids)){
				$condition['t.tid']=array('in',$goodsTids);
			}
		}
		//商品分类条件
		$catPath=$this->dCategory->getcatPath();
		foreach($catPath as $key => $value){
			$catPath[$key]['cat_path'] = explode(",", $value['cat_path']);
		}
		if(!empty($data['catone'])){
			//start一级分类
				$searchData['catone']=$this->serachName($data['catone'],"category",1);
			//end				
			if(!empty($data['cattwo'])){
			//start二级分类
				$searchData['cattwo']=$this->serachName($data['cattwo'],"category",2);
			//end					
				if(!empty($data['catthree'])){
					//分类项到3级
					//start三级分类
						$searchData['catthree']=$this->serachName($data['catthree'],"category",3);
					//end					
						$searchCatIDs=$data['catthree'];
				}else{
					//分类项到两级
					foreach($catPath as $key => $value){
						if(in_array($data['cattwo'], $value['cat_path'])){
								$searchCatIDs[]=$value['cat_id'];
						}
					}
				}				
			}else{
				//分类在一级
				foreach($catPath as $key => $value){
					if(in_array($data['catone'], $value['cat_path'])){
							$searchCatIDs[]=$value['cat_id'];
					}
				}		
									
			}		
			if(!empty($searchCatIDs)){
				//取出所有属于这个分类下的商品id
				$itemIds=$this->dFOrder->getAllCartItenIds($searchCatIDs);
				if(!empty($itemIds)){
					$cartgoryWhere['item_id']=array('in',$itemIds);	
				}
				$cartgoryTids=$this->dFOrder->getOrderItemsToNum($cartgoryWhere);
				if(!empty($cartgoryTids)){
					$condition['t.tid']=array('in',$cartgoryTids);
				}else{
					$condition['t.tid']=0;
				}
			}
				
		}		
		
		$this->assign('searchData',$searchData);
		return 	$condition;
	}
	public function vrIndex(){
		$condition=$this->condition();
		$size = 20;
		if(I('execlType') && I('execlType')=="exportExcel"){
			//导出execl
			$orderExecl=$this->dFOrder->getOrderExecl($condition);
			if(!empty($orderExecl)){
				$execlData=$this->OrderDeal($orderExecl,"execl");	
				$this->vrExportExcel($execlData);
			}
		}			
		$number=$this->dFOrder->getOrderCount($condition);
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$orderRes=$this->dFOrder->getOrder($condition,$limit);
		if(!empty($orderRes)){
			$orderRes=$this->OrderDeal($orderRes,1);
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this -> assign('pagestr',$pagestr);	
		$this->assign('list',$orderRes);
		$this->assign('count',$number);
		$this->display();
	}
	public function OrderDeal($orderRes,$type){
		if(empty($orderRes)){
			exit;
		}
		foreach($orderRes as $key=>$value){
			$tids[]=$value['tid'];   //订单号
			$comIds[]=$value['com_id']; //comid
			$userIds[]=$value['user_id'];
		}
		if(!empty($tids)){
			if($type==1){
				$orderItem=$this->dFOrder->getOrderItems($tids);
			}
			$JdOrderNumber=$this->dFOrder->getJdOrderNumber($tids);
		}
		if(!empty($comIds)){
			$companys=$this->dFOrder->getThisCompany($comIds);
		}
		if($type=="execl"){
			$users=$this->dFOrder->getThiUserInfo($userIds);
		}
		foreach($orderRes as $key=>$value){
			//订单内商品
			if($type==1){
				foreach($orderItem as $keys=>$values){
					if($value['tid']==$values['tid']){
						$orderRes[$key]['items'][]=$values;	
					}				
				}
			}
			//用户名
			if($type=="execl"){
				foreach($users as $keyu=>$valueu){
					if($value['user_id']==$valueu['user_id']){
						$orderRes[$key]['user_id']=$valueu['mobile'];	
					}				
				}				
			}
			//所属公司
			foreach($companys as $keyc=>$valuec){
				if($value['com_id']==$valuec['com_id']){
					$orderRes[$key]['company']=$valuec['com_name'];	
				}					
			}
			//京东订单号
			foreach($JdOrderNumber as $keyj=>$valuej){
				if($value['tid']==$valuej['tid']){
					$orderRes[$key]['JdnNmber']=$valuej['sync_order_id'];	
				}					
			}			
			//地址
			$orderRes[$key]['address']=$value['receiver_state'].$value['receiver_city'].$value['receiver_district'].$value['receiver_address'];
			//时间
			$orderRes[$key]['created_time']=date('Y-m-d H:i:s',$value['created_time']);
			if($orderRes[$key]['pay_time']){
				$orderRes[$key]['pay_time']=date('Y-m-d H:i:s',$value['pay_time']);
			}else{
				$orderRes[$key]['pay_time']="未支付";
			}
			$orderRes[$key]['orderStatus']=$this->orderStatus($value['status']);
			$orderRes[$key]['serviceStatus']=$this->orderStatusReturn($value['order_status']);
		}
		return $orderRes;

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
//导出execl
	public function vrExportExcel($execlData){
		header("Content-type:text/html;charset=utf-8");
		foreach($execlData as $key=>$value){
			$itemIds[]=$value['item_id'];
		}
		if(!empty($itemIds)){
			$itemInfo=$this->dFOrder->getAllItem($itemIds);
		}
		foreach($itemInfo as $key=>$value){
			$catIds[]=$value['cat_id'];
		}		
		if(!empty($catIds)){
			$catInfo=$this->dFOrder->getInCategoryInfo($catIds);
		}
		foreach($itemInfo as $key=>$value){
			foreach($catInfo as $keys=>$values){
				if($value['cat_id']==$values['cat_id']){
					$itemInfo[$key]['cat_name']=$values['cat_name'];
				}
			}	
		}			
		foreach($execlData as $key =>$value){
			foreach($itemInfo as $keys=>$values){
				if($value['item_id']==$values['item_id']){
					$execlData[$key]['cat_name']=$values['cat_name'];
					$execlData[$key]['total_price']=$values['price']*$value['num'];
					$execlData[$key]['total_cost_price']=$values['cost_price']*$value['num'];
				}
			}
		}		
		foreach($execlData as $key =>$value){
			$LastExeclData[$key]['vtid']=" ".$value['vtid'];
			$LastExeclData[$key]['tid']=" ".$value['tid'];
			$LastExeclData[$key]['jdnumber']=" ".$value['JdnNmber'];
			$LastExeclData[$key]['user_id']=$value['user_id'];
			$LastExeclData[$key]['cat_name']=$value['cat_name'];
			$LastExeclData[$key]['title']=$value['title'];
			$LastExeclData[$key]['spec_nature_info']=$value['spec_nature_info'];
			$LastExeclData[$key]['price']=" ".$value['price']."元";
			$LastExeclData[$key]['num']=$value['num'];
			$LastExeclData[$key]['total_price']=" ".$value['total_price']."元";
			$LastExeclData[$key]['total_cost_price']=$value['total_cost_price'];
			$LastExeclData[$key]['receiver_name']=$value['receiver_name'];
			$LastExeclData[$key]['receiver_mobile']=$value['receiver_mobile'];
			$LastExeclData[$key]['created_time']=$value['created_time'];
			if($execlData[$key]['consign_time']){
				$LastExeclData[$key]['consign_time']=date('Y-m-d H:i:s',$LastExeclData[$key]['consign_time']);		
			}else{
				$LastExeclData[$key]['consign_time']="无";		
			}	
			$LastExeclData[$key]['payment']=" ".$value['payment']."元";
			$LastExeclData[$key]['payed_fee']=" ".$value['payed_fee']."元";
			$LastExeclData[$key]['totalCostPrices']=" ";
			$LastExeclData[$key]['pay_time']=$value['pay_time'];
			$LastExeclData[$key]['company']=$value['company'];
			$LastExeclData[$key]['address']=$value['receiver_state'].$value['receiver_city'].$value['receiver_district'].$value['address'];
			$LastExeclData[$key]['orderStatus']=$value['orderStatus'];
			$result[$key]=$value['tid'];
		}
		$result=array_unique($result);
		foreach($LastExeclData as $k => $v){
			foreach($result as $keys=>$values){
				if($v['tid']==$values){
					$totalCostPrice[$values]['totalCostPrices']+=$v['total_cost_price'];
				}
			}
		}	
		//取出k值
		foreach($result as $k1=>$v1){
			$result[$k1]=$k1;
		}		
		foreach($LastExeclData as $k => $v){
			foreach($totalCostPrice as $keys=>$values){
				if($LastExeclData[$k]['tid']==$keys){
					$LastExeclData[$k]['totalCostPrices']=$values['totalCostPrices'];
				}
			}
			if(!array_key_exists($k,$result)){
				$LastExeclData[$k]['tid']="";
				$LastExeclData[$k]['vtid']="";
				$LastExeclData[$k]['jdnumber']="";
				$LastExeclData[$k]['user_id']="";
				$LastExeclData[$k]['receiver_name']="";
				$LastExeclData[$k]['receiver_mobile']="";
				$LastExeclData[$k]['payment']="";
				$LastExeclData[$k]['total_fee']="";
				$LastExeclData[$k]['pay_time']="";
				$LastExeclData[$k]['created_time']="";
				$LastExeclData[$k]['consign_time']="";
				$LastExeclData[$k]['payed_fee']="";
				$LastExeclData[$k]['company']="";
				$LastExeclData[$k]['address']="";
				$LastExeclData[$k]['orderStatus']="";
				$LastExeclData[$k]['totalCostPrices']="";
			}
		}
		$ex=new Excel;
		$columnName=array('虚拟订单号','订单号','京东单号','用户名/手机号','商品所属分类','商品名称','商品属性','商品价格','商品数量','商品总价格','商品总成本价','收货人','手机','下单时间','发货时间','订单总额','支付金额','订单总成本价','支付时间','所属企业','地址','订单状态');
		$ex->getExcel($LastExeclData,$columnName,"虚拟订单列表(".date('Y年m月d日H时i分',time()).")");		
		
	}
	//订单详情
	public function vrDetail(){
		$tid=I('tid');
		if($tid){
			$detail=$this->dFOrder->getOrderDetail($tid);
			//店铺信息
			$detail['shopinfo']=$this->dFOrder->getShopInfo($detail['shop_id']);
			//京东单号
			$detail['JdOrderNumber']=$this->dFOrder->getThisJdOrderNumber($detail['tid']);
			//状态
			$detail['OrderStatus']=$this->orderStatus($detail['status']);
			//用户信息
			$detail['userInfo']=$this->dFOrder->getUserInfo($detail['user_id']);
			//时间
			$detail['created_time']=date('Y-m-d H:i:s',$detail['created_time']);
			if(!empty($detail['pay_time'])){
				$detail['pay_time']=date('Y-m-d H:i:s',$detail['pay_time']);
			}
			if(!empty($detail['modified_time'])){
				$detail['modified_time']=date('Y-m-d H:i:s',$detail['modified_time']);
			}
			if(!empty($detail['userInfo']['birthday'])){
				$detail['userInfo']['birthday']=date('Y年m月d日',$detail['userInfo']['birthday']);
			}
			if(!empty($detail['userInfo']['regtime'])){
				$detail['userInfo']['regtime']=date('Y-m-d H:i:s',$detail['userInfo']['regtime']);
			}			
			$detail['shopinfo']['open_time']=date('Y-m-d H:i:s',$detail['shopinfo']['open_time']);
			//所属公司
			$detail['companyName']=$this->dFOrder->getUserCompany($detail['com_id']);
			//物流信息
			$detail['express']=$this->dFOrder->getExpress($detail['tid']);
			$detail['express']['logi_no']=explode(',', $detail['express']['logi_no']);
			if(!empty($detail['express']['t_begin'])){
				$detail['express']['t_begin']=date('Y-m-d H:i:s',$detail['express']['t_begin']);
			}	
			//售后类型
			$detail['serviceStatus']=$this->orderStatusReturn($detail['order_status']);
			//售后状态
			foreach($detail['more'] as $key=>$value){
				$itemIds[]=$value['item_id'];
				$detail['more'][$key]['serviceLastStatus']=$this->orderStatusLastReturn($value['aftersales_status']);
			}
			if($detail['shop_id']==10){
				$jdskus=$this->dFOrder->getItemJdsku($itemIds);
				foreach($detail['more'] as $key=>$value){
					foreach($jdskus as $keys=>$values){
						if($value['item_id']==$values['item_id']){
							$detail['more'][$key]['jd_sku']=$values['jd_sku'];
						}
					}
				}				
			}
			$this->assign('info',$detail);
		}
		
		$this->display();
	}	
	//订单->查合单
	public function single(){
		$tid=I('tid');
		if($tid){
			$data=$this->dFOrder->getSingleOrderNums($tid);
			foreach($data as $key=>$value){
				$tids[]=$value['tid'];
				$paymentId=$value['payment_id'];
			}
			if(!empty($tids)){
				$ordersInfo=$this->dFOrder->getOrderDetails($tids);
				$expressInfo=$this->dFOrder->getInExpress($tids);
				$JdOrderNumbers=$this->dFOrder->getJdOrderNumber($tids);
				foreach($ordersInfo as $key=>$value){
					//物流信息
					foreach($expressInfo as $keys=>$values){
						if($value['tid']==$values['tid']){
							$ordersInfo[$key]['express']=$values;
						}
					}
					//京东单号
					foreach($JdOrderNumbers as $keyn=>$valuen){
						if($value['tid']==$valuen['tid']){
							$ordersInfo[$key]['JdOrderNumber']=$valuen['sync_order_id'];
						}
					}	
					//状态
					$ordersInfo[$key]['status']=$this->orderStatus($value['status']);
					//售后类型
					$ordersInfo[$key]['serviceStatus']=$this->orderStatusReturn($value['order_status']);
								
				}
				//时间戳转换
				foreach($ordersInfo as $key=>$value){
					$ordersInfo[$key]['created_time']=date('Y-m-d H:i:s',$value['created_time']);
					if(!empty($value['pay_time'])){
						$ordersInfo[$key]['pay_time']=date('Y-m-d H:i:s',$value['pay_time']);
					}
					$ordersInfo[$key]['modified_time']=date('Y-m-d H:i:s',$value['modified_time']);
					if(!empty($value['express']['t_begin'])){
						$ordersInfo[$key]['express']['t_begin']=date('Y-m-d H:i:s',$value['express']['t_begin']);
					}
					
				}
				//售后状态
				foreach($value['more'] as $keyi=>$valuei){
					$ordersInfo[$key]['more'][$keyi]['serviceLastStatus']=$this->orderStatusLastReturn($valuei['aftersales_status']);
				}
			}
			$this->assign('orders',$ordersInfo);
			//支付单号payment_id
			$this->assign('paymentId',$paymentId);
		}
		$this->display();
	}
	public function timeDeal($time){
		if(!empty($time)){
			$time=date('Y-m-d H:i:s',$time);
		}
		return $time;
	}
	
	//订单退款
	public function refund(){
		$tid=I('tid');
		if($tid){
			$paymentId=$this->dOrder->getTradePaymentId($tid);
			$items=$this->dFOrder->getOrderItems($tid);
			$this->assign('tid',$tid);
			$this->assign('paymentId',$paymentId);	
			$this->assign('items',$items);
			$this->assign('payType',I('payType'));
			$this->display();
		}else{
			echo 'Can not find pay bill!';
		}
		
	}
//物流信息
	public function expressProgress(){
		$expressNum=I('logiNo');
		if(I('shop_id')==10){
			//京东快递
			$url=C('COMMON_API')."Api/getJdExpress/";
			$data=array(
				'orderId'=>$expressNum
			);
			$res=$this->requestPost($url,$data);
			$res=json_decode($res,true);
			$expressInfo=$res['data']['orderTrack'];
				//当前页订单
		}else{
			//普通快递
		}			
		$this->assign('expressInfo',$expressInfo);
		$this->assign('logiNo',$expressNum);
		$this->display();
	}
//同步京东订单前取消同步指定商品
	public function cancelSyncGoods(){
		$oid=I('get.oid');
		if(!empty($oid)){
			$res=$this->dFOrder->cancelThisGoods($oid);
			$this->ajaxReturn($res);
		}
	}
//申请退款
	public function applyRefund(){
		$tid=I('get.tid');
		if($tid){
			$res=$this->dFOrder->editOrderStatus($tid);
			echo json_encode($res);
		}
	}
//创建虚拟订单
	public function creatFictitiousOrder(){
		
		$this->display();
	}	
//创建虚拟单处理
	public function creatDeal(){
		$tid=trim(I('tid'));
		$payType=I('payType');
		if($tid){
			$res=$this->dFOrder->getThisOrderInfo($tid);		
			if(!$res){
				$trade=$this->dOrder->getThisOrderInfo($tid);		
				if($trade){
					$tradeData=$trade;
					$tradeData['created_time']=time();
					if($payType==0){
						$tradeData['vtid']=$trade['tid'].'0';
						$tradeData['payed_fee']=0;
						$tradeData['pay_time']=0;
					}else if($payType==1){
						$tradeData['vtid']=$trade['tid'].'1';
						if(empty($trade['payed_fee'])==0){
							$tradeData['payed_fee']=$trade['payment'];
							$tradeData['pay_time']=time();
						}
					}
					$addres=$this->dFOrder->addTradeVrInfo($tradeData);
					if($addres){
						$order=$this->dOrder->getThisTradeInfo($tid);
						foreach($order as $key => $value){
							$this->dFOrder->addOrderVrInfo($value);
						}
						echo json_encode(array(1,'虚拟订单创建成功!虚拟单号:'.$tradeData['vtid']));				
					}
					
				}else{
					echo json_encode(array(0,'输入的订单号不存在!'));				
				}
			}else{
				echo json_encode(array(0,'该虚拟订单已存在,无需重新创建     !'));				
			}
		}else{
			echo json_encode(array(0,'请输入订单号!'));				
		}
	}
	public function saveRefund(){
		$tid=I('tid');
		$paymentId=I('paymentId');
		$fee=I('fee');
		$mark=I('mark');
		$type=I('type');
		$order=I('order');
		$orderType=I('orderType');
//		$dealRes=I('dealRes'); //处理结果 1-同意退款  2-拒绝退款
		$dealRes=1;
		$userName=I('userName');  //退款账户
		if($type==0){
			$refundLoad="积分";
		}else if($type==1){
			$refundLoad="E卡通";
		}	
		$orderArr=explode("#", $order);
		array_pop($orderArr);
		foreach($orderArr as $key=>$value){
			$valueArr=explode("-", $value);
			$item[$key]['oid']=$valueArr[0];
			$item[$key]['num']=$valueArr[1];
			$oids[]=$valueArr[0];
		}
		if($dealRes==1){
			//同意退款
			$items = json_encode($item);		
			if($type==0){
				//积分退款
				$refundType="point";
			}else if($type==1){
				//E卡退款
				$refundType="ecard";
			}
			$url=C('COMMON_API')."Order/apiDoRefundOrder/";
			$data=array(
				'paymentId'=>$paymentId,
				'fee'=>$fee,
				'tid'=>$tid,
				'items'=>$items,
				'mark'=>$mark,
				'refundType'=>$refundType
			);
			if(!empty($orderType)){
				$data['orderType']=$orderType;
			}
			$return=$this->requestPost($url,$data);
			$resu=json_decode(trim($return,chr(239).chr(187).chr(191)),true);
			if($resu['result']==100 && $resu['errcode']==0){
				//退款成功
				$result=1;
				$refundId=$resu['data']['refundId'];
				$status=2;
				$remarks="退款结果：通过,<br/>退款账户：".$userName."<br/>退款路径：".$refundLoad."<br/>退款金额：￥".$fee."<br/>处理备注：".$mark;
				$dealRes='SUCCESS';
				$refundStatus=1;
				$datas['refund_fee']=$fee;
				$datas['refund_time']=time();
				$datas['cancel_reason']=$mark;
				$datas['refund_mark']=$mark;
		 		$datas['order_status']='REFUND';
				$this->dFOrder->editOrderStatusData($tid,$datas);
				//退款成功更改几个表的状态为已处理
			}else{
				//退款失败
				echo json_encode(array(0,$resu['msg']));
				exit;
			}			
		}else if($dealRes==2){
			//拒绝退款
			$result=2;
			$status=3;
			$remarks="退款结果：不通过,处理备注：".$mark;
			$dealRes='SELLER_REFUSE';
			$refundStatus=2;	
			$resu['msg']="拒绝退款成功";	
		}
		//写进管理员操作订单日志表
		$oidsString=implode(',',$oids);
		$dealType="退款(虚拟订单)";
		A('Order')->markTradeLog($tid,$dealType,$remarks,$oidsString);
		//该订单分表的状态
		$data['aftersales_status']=$dealRes;
		$data['modified_time']=time();
		$statusRes=$this->dFOrder->editThisConditionOrderInfo(array('oid'=>array('in',$oids)),$data,'aftersales_status,modified_time');	
		//修改申请退款表状态
		$refundData['status']=$refundStatus;
		$refundData['modified_time']=time();
		//写进去退款申请表
		$refundData['total_price']=$refundData['total_fee'];
		$refundData['tid']=$tid;
		foreach($oids as $key=>$value){
			$refundData['refund_bn']=date('ymdHis').rand(1000000,9999999);
			$refundData['aftersales_bn']="00000000";
			$refundData['oid']=$value;
			$refundData['refunds_reason']="虚拟单退款:".$mark;
			$refundData['created_time']=time();
			$refundData['trade_source']='vr';
			$this->dAftersales->addreFundData($refundData);
		}			
		echo json_encode(array($result,$resu['msg'],$refundId));
	}


	
	
}