<?php  
namespace Home\Controller;
use Org\Util\Excel;
class OrderController extends CommonController {
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
		$this->dOrder=D('Order');
		$this->dActivityOrder=D('Activityorder');
		$this->dCategory=D('Category');
		$this->dShop=D('Shopinfo');	
		$this->dGoods=D('Goods');
		$this->dAftersales=D('Aftersales');	
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
		//企业
		if($type=="company"){
			$company=$this->dOrder->getAllCompany();
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
		//退换货类型	
		if($type=="afterType"){
			$staust=array(
				"ONLY_REFUND"=>array(
					"type" => "ONLY_REFUND",
					"name" => "仅退款"
				),
				"REFUND_GOODS" =>array(
					"type" => "REFUND_GOODS",
					"name"   => "退货退款"
				),	
				"EXCHANGING_GOODS" =>array(
					"type" => "EXCHANGING_GOODS",
					"name"   => "换货"
				),	
				"REPAIRING_GOODS" =>array(
					"type" => "REPAIRING_GOODS",
					"name"   => "维修"
				)						
			);		
			$info=$staust[$id];
		}
		//售后进度
		if($type=="afterProcess"){
			$stausProcess=array(
				"WAIT_EARLY_PROCESS"=>array(
					"type" => "WAIT_EARLY_PROCESS",
					"name" => "待初审"
				),
				"WAIT_PROCESS" =>array(
					"type" => "WAIT_PROCESS",
					"name"   => "待审核"
				),	
				"WAIT_BUYER_SEND_GOODS" =>array(
					"type" => "WAIT_BUYER_SEND_GOODS",
					"name"   => "待买家回寄"
				),	
				"WAIT_SELLER_CONFIRM_GOODS" =>array(
					"type" => "WAIT_SELLER_CONFIRM_GOODS",
					"name"   => "待确认收货"
				),	
				"REFUND_PROCESS" =>array(
					"type" => "REFUND_PROCESS",
					"name"   => "待退款"
				),					
				"SELLER_SEND_GOODS" =>array(
					"type" => "SELLER_SEND_GOODS",
					"name"   => "商家已回寄"
				),	
				"SELLER_REFUSE" =>array(
					"type" => "SELLER_REFUSE",
					"name"   => "审核不通过"
				),	
				"SUCCESS" =>array(
					"type" => "SUCCESS",
					"name"   => "已完成"
				),													
			);		
			$info=$stausProcess[$id];
		}					
		//商品分类
		if($type=="category"){
			$info=$this->dOrder->getThisCategoryInfo($id,$level);
			
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
	//条件
	public function condition(){
		if($_GET['goods']){
			$_GET['goods']=urldecode($_GET['goods']);
		}
		if($_GET['tid']){
			$_GET['tid']=urldecode($_GET['tid']);
		}		
		$data=I('');
		//输出搜索条件显示
		$searchData=$data;
		//企业条件
		if(!empty($data['comId'])){
			//start公司名字显示用
				$searchData['company']=$this->serachName($data['comId'],"company");
			//end
			$condition['com_id']=$data['comId'];
		}
		//店铺条件
		if(!empty($data['shopId'])){
			//start公司名字显示用
				$searchData['shop']=$this->serachName($data['shopId'],"shop");
			//end
						
			$condition['shop_id']=$data['shopId'];
		}
		//时间条件(支付时间)
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$data['start']=strtotime($data['startTime']);
			$data['end']=strtotime($data['endTime']." +24 hours");
			$str=$data['start'].','.$data['end'];
			$condition['pay_time']=array('between',$str);
		}
		//时间条件(下单时间)
		if(!empty($data['creatStartTime']) && !empty($data['creatEndTime'])){
			$data['cstart']=strtotime($data['creatStartTime']);
			$data['cend']=strtotime($data['creatEndTime']." +24 hours");
			$creatStr=$data['cstart'].','.$data['cend'];
			$condition['created_time']=array('between',$creatStr);
		}		
		//状态条件
		if(!empty($data['status'])){
			//start订单状态
				$searchData['status']=$this->serachName($data['status'],"orderStatus");
			//end	
			if($data['status']=="TRADE_CLOSED_BY_SYSTEM"){
				$condition['status']=array('in','TRADE_CLOSED_BY_SYSTEM,TRADE_CLOSED_BY_USER,TRADE_CLOSED_BY_ADMIN');
			}else{
				$condition['status']=$data['status'];
			}		
		}
		//售后状态选择
		if(!empty($data['service'])){
			$searchData['service']=$this->serachName($data['service'],"serviceType");
			$condition['order_status']=$data['service'];
			
		}
		//商品名条件
		if(!empty($data['goods'])){
			$goodWhere['title']=array('like','%'.$data['goods'].'%');
			$goodsTids=$this->dOrder->getOrderItemsToNum($goodWhere);
			if(!empty($goodsTids)){
				$condition['tid']=array('in',$goodsTids);
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
				$itemIds=$this->dOrder->getAllCartItenIds($searchCatIDs);
				if(!empty($itemIds)){
					$cartgoryWhere['item_id']=array('in',$itemIds);	
				}
				$cartgoryTids=$this->dOrder->getOrderItemsToNum($cartgoryWhere);
				if(!empty($cartgoryTids)){
					$condition['tid']=array('in',$cartgoryTids);
				}else{
					$condition['tid']=0;
				}
			}
		}
		//发货类型
		if(!empty($data['sendType'])){
			$sendTids=$this->dOrder->getOrderItemsToNum(array('send_type'=>$data['sendType']));
			if(!empty($sendTids)){
				$condition['tid']=array('in',$sendTids);
			}else{
				$condition['tid']=0;
			}			
		}
		//活动类型
		if(!empty($data['activityType'])){
			if($data['activityType'] == -1){
				$data['activityType'] = 0;
			}
			$condition['trade_type']=$data['activityType'];
		}
		//支付单号
		if(!empty($data['paymentId'])){
			$tids=$this->dOrder->getPaymentToTids(trim($data['paymentId']));
			if(!empty($tids)){
				$condition['tid']=array('in',$tids);
			}else{
				$condition['tid']=0;
			}
		}
		//员工手机号
		if(!empty($data['mobile'])){
			$userId=$this->dOrder->getUserId(trim($data['mobile']));
			$condition['user_id']=$userId;
		}
		//订单号
		if(!empty($data['tid'])){
			$condition['tid|receiver_name|receiver_mobile']= array('like','%'.trim($data['tid']).'%');
		}
		//vip用户
		if(!empty($data['vip'])){
			$condition['is_vip']=1;
		}				
		//搜索条件转码
		$this->assign('searchData',$searchData);
		return 	$condition;
	}
/*
 * 商城订单首页
 * */
	public function index(){
		$condition=$this->condition();
		$size = 20;
		if(I('execlType') && I('execlType')=="exportExcel"){
			//导出execl
//			$orderExecl=$this->dOrder->getOrderExecl($condition);  //其中一份份导出execl  方法exportExcel
			$orderExecl=$this->dOrder->getOrder($condition);   //另一份导出execl  方法orderExportExcel
			if(!empty($orderExecl)){
				$execlData=$this->OrderDeal($orderExecl,"execl");	
//				$this->exportExcel($execlData);                 //其中一份份导出execl  方法exportExcel
				$this->orderExportExcel($execlData);           //另一份导出execl   方法orderExportExcel
			}
		}	
		$number=$this->dOrder->getOrderCount($condition);
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$orderRes=$this->dOrder->getOrder($condition,$limit);
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
	//条件
	public function sendCondition(){
		if($_GET['goods']){
			$_GET['goods']=urldecode($_GET['goods']);
		}
		if($_GET['tid']){
			$_GET['tid']=urldecode($_GET['tid']);
		}		
		$data=I('');
		//输出搜索条件显示
		$searchData=$data;
		//企业条件
		if(!empty($data['comId'])){
			//start公司名字显示用
				$searchData['company']=$this->serachName($data['comId'],"company");
			//end
			$condition['com_id']=$data['comId'];
		}
		//店铺条件
		if(!empty($data['shopId'])){
			//start公司名字显示用
				$searchData['shop']=$this->serachName($data['shopId'],"shop");
			//end
						
			$condition['shop_id']=$data['shopId'];
		}
		//时间条件(支付时间)
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$data['start']=strtotime($data['startTime']);
			$data['end']=strtotime($data['endTime']." +24 hours");
			$str=$data['start'].','.$data['end'];
			$condition['pay_time']=array('between',$str);
		}
		//时间条件(下单时间)
		if(!empty($data['creatStartTime']) && !empty($data['creatEndTime'])){
			$data['cstart']=strtotime($data['creatStartTime']);
			$data['cend']=strtotime($data['creatEndTime']." +24 hours");
			$creatStr=$data['cstart'].','.$data['cend'];
			$condition['created_time']=array('between',$creatStr);
		}
		//员工手机号
		if(!empty($data['mobile'])){
			$userId=$this->dOrder->getUserId(trim($data['mobile']));
			$condition['user_id']=$userId;
		}
		//订单号
		if(!empty($data['tid'])){
			$condition['tid|receiver_name|receiver_mobile']= array('like','%'.trim($data['tid']).'%');
		}  
		//商品名条件
		if(!empty($data['goods'])){
			$goodWhere['title']=array('like','%'.$data['goods'].'%');
			$goodsTids=$this->dOrder->getOrderItemsToNum($goodWhere);
			if(!empty($goodsTids)){
				$condition['tid']=array('in',$goodsTids);
			}
		}
		//搜索条件转码
		$this->assign('searchData',$searchData);
		return 	$condition;
	}
/*
 * 发货订单
 * */
 	public function sendOrderIndex(){
		//操作条件（备货、发货）
		$data=I('');
		if(!empty($data['status'])){
			//start订单状态
			$condition['status']=$data['status'];
			if($data['status']=='WAIT_SELLER_SEND_GOODS'){
				$statusData =array(
					"status" => "WAIT_SELLER_SEND_GOODS",
					"name"   => "备货"
				);
			}else if($data['status']=='IN_STOCK'){
				$statusData =array(
					"status" => "IN_STOCK",
					"name"   => "发货"
				);				
			}
			$this->assign('statusData',$statusData);
		}else{
			$condition['status']=array('in','WAIT_SELLER_SEND_GOODS,IN_STOCK');
		} 
		//自发代发
		if(!empty($data['sendType'])){
			//start订单状态
			$condition['send_type']=$data['sendType'];
			if($data['sendType']==1){
				$sendTypeData =array(
					"status" => 1,
					"name"   => "自发"
				);
			}else if($data['sendType']==2){
				$sendTypeData =array(
					"status" => 2,
					"name"   => "代发"
				);				
			}
			$this->assign('sendTypeData',$sendTypeData);
		}				
 		$condition['sendnum']=0;
 		$condition['aftersales_status']='NO_APPLY';
 		$condition['disabled']=0;
 		$tids=$this->dOrder->getOrderItemsToNum($condition);
		$tids=array_unique($tids);
		//搜索条件符合tid
		$OrderCondition=$this->sendCondition();
		if(!empty($OrderCondition) && !empty($tids)){
			$OrderCondition['tid']=array('in',$tids);
			$tids=$this->dOrder->getConditionOrderTid($OrderCondition);	
		}
		if($tids){
			$paymentInfos=$this->dOrder->getSomeSingleNums($tids,'created_time desc');
		}
		foreach($paymentInfos as $key=>$value){
			$paymentIds[]=$value['payment_id'];
		}
		$paymentIds=array_unique($paymentIds);
		foreach($paymentIds as $key=>$value){
			foreach($paymentInfos as $keys=>$values){
				if($value==$values['payment_id']){
					$list[$values['payment_id']][]=$values['tid'];
				}
			}			
		}
		if(I('execlType') && I('execlType')=="exportExcel"){
			//导出execl
			if(!empty($tids)){
				$orderExecl=$this->dOrder->getOrder(array('tid'=>array('in',$tids)));   
			}else{
				$this->error("暂无数据!");
			}
			if(!empty($orderExecl)){
				$this->SendGoodsExecl($orderExecl);        
			}
		}			
		$number=count($list);
		$page = new \Think\Page($number,20);
		$rollPage = 20; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$lastList=array_slice($list,$start,$pagesize);
		foreach($lastList as $key=>$value){
			foreach($value as $keys=>$values){
				$LastTids[]=$values;
			}
		}
		if(!empty($LastTids)){
			$orderRes=$this->dOrder->getOrder(array('tid'=>array('in',$LastTids)));
		}
		if(!empty($orderRes)){
			$orderRes=$this->OrderDeal($orderRes,1);
		}	
		foreach($lastList as $key=>$value){
			foreach($value as $keys=>$values){
				foreach($orderRes as $keyt=>$valuet){
					if($values==$valuet['tid']){
						unset($lastList[$key][$keys]);
						$lastList[$key][$values]=$valuet;
					}
				}
			}
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this -> assign('pagestr',$pagestr);	
		$this->assign('lastList',$lastList);
		$this->assign('count',$number);		
		$this->display();
 	}
/*
 *发货备货单从发货备货列表中移除（之前的未该规则和异常的订单） 
 * */
 	public function removeSendGoods(){
		$tid=I('tid');
		$ret = array('code'=>0, 'msg'=>'unkown error');
		if(empty($tid)){
			$ret['msg']='移除失败,缺少订单号!';
			$this->ajaxReturn($ret);
		}
		//取出该订单的主状态
		$status=$this->dOrder->getThisOrderInfo($tid,'status');
		$res = false;
		$ststusArr=array(
			'WAIT_BUYER_CONFIRM_GOODS',
			'WAIT_COMMENT',
			'TRADE_FINISHED'
		);
		if(!in_array($status, $ststusArr) || !$status){
			//异常单//售后
			$status='TRADE_FINISHED';
		}
		$condition['tid']=$tid;
		$data['status']=$status;
		$res=$this->dOrder->editThisConditionOrderInfo($condition,$data,'status');
		if($res){
			$ret['code']=1;
			$ret['msg']='移除成功!';
			$this->ajaxReturn($ret);
		}else{
			$ret['msg']='移除失败!';
			$this->ajaxReturn($ret);
		}		
 	}
/*
 * 订单导出快递所需单
 * */
	public function SendGoodsExecl($execlData){
		header("Content-type:text/html;charset=utf-8");
		foreach($execlData as $key => $value){
			$shopIds[]=$value['shop_id'];
			$tids[]=$value['tid'];
			$comIds[]=$value['com_id'];
			$execlData[$key]['num']=0;
		}
		if(!empty($shopIds)){
			$condition['shop_id'] = array('in',$shopIds);
			$shopname=$this->dShop->getShopInfo($condition,"shop_id,shop_name");
		}
		if(!empty($comIds)){
			$companys=$this->dOrder->getThisCompany($comIds);
		}
		foreach($execlData as $key => $value){
			foreach($shopname as $keys=>$values){					
				if($value['shop_id'] == $values['shop_id']){
					$execlData[$key]['shop_name']=$values['shop_name'];
				}
			}
			//所属公司
			foreach($companys as $keyc=>$valuec){
				if($value['com_id']==$valuec['com_id']){
					$execlData[$key]['company']=$valuec['com_name'];	
				}					
			}			
		}			
		if(!empty($tids)){
			$condition=array(
				'tid'=>array('in',$tids),
				'sendnum'=>0,
				'aftersales_status'=>'NO_APPLY',
				'disabled'=>0,
				'status' => 'IN_STOCK',
				'send_type' => 1
				
			);
			$orderItem=$this->dOrder->getThisConditionOrderInfo($condition,'tid,num');
			foreach($execlData as $key=>$value){
				foreach($orderItem as $keys=>$values){
					if($value['tid']==$values['tid']){
						$execlData[$key]['num']+=$values['num'];
					}
				}
			}
		}	
		foreach($execlData as $key => $value){
			$LastExeclData[$key]['tid']=" ".$value['tid'];
			$LastExeclData[$key]['expressCom']=$value['company'];
			$LastExeclData[$key]['receiver_name']=$value['receiver_name'];
			$LastExeclData[$key]['receiver_mobile']=$value['receiver_mobile'];
			$LastExeclData[$key]['phone']="";
			$LastExeclData[$key]['addr']=$value['receiver_state'].$value['receiver_city'].$value['receiver_district'].$value['receiver_address'];
			$LastExeclData[$key]['payType']="";
			$LastExeclData[$key]['monthCard']="";
			$LastExeclData[$key]['shopname']=$value['shop_name'];
			$LastExeclData[$key]['num']=$value['num'];
		}
		$ex=new Excel;
		$columnName=array('订单号','收件公司','联系人','联系电话','手机号码','收件详细地址','付款方式','第三方付月结卡号','托寄物内容','托寄物数量');
		$ex->getExcel($LastExeclData,$columnName,"自发商品顺丰发货快递单(".date('Y年m月d日H时i分',time()).")");			
	}
/*
 *售后订单页搜索条件条件类型
 * */
 	public function aftersaleCondition(){
 		$aftersaleType=I('aftersaleType');
 		if($aftersaleType!=0){
			$condition['aftersales_type']=$aftersaleType;
 		}
		$condition['trade_source']="mall";
		return $condition;
 	}
/*
 *售后订单页（退、换、货） 处理订单
 * */
	public function aftersaleDealIndex(){
		$aftersaleTypeCondition=$this->aftersaleCondition();
		$aftersaleTids=$this->dAftersales->getAllThisAftersalesOrder($aftersaleTypeCondition);
 		$aftersalesStatus=I('aftersalesStatus');
		$roleId=$this->roleId;
		if(empty($aftersalesStatus) && $roleId!=0){
			if($this->checkPowerNode("refundearlyprocess")){
				//待初审权限
				$aftersalesStatus="WAIT_EARLY_PROCESS";
			}else if($this->checkPowerNode("refundprocess") || $this->checkPowerNode("returnprocess")){
				//待审核权限
				$aftersalesStatus="WAIT_PROCESS";
			}else if($this->checkPowerNode("returnsendfororder") || $this->checkPowerNode("waitreturnfororder")){
				//待确认收货
				$aftersalesStatus="WAIT_SELLER_CONFIRM_GOODS";
			}
		}
 		$onlyType=I('onlyType');
		$this->aftersaleOrderPageDeal($condition,$aftersaleTids,$aftersalesStatus,"aftersale",$onlyType);
		$this->display();
	}	
/*
 *售后订单页（退、换、货） 
 * */
	public function aftersaleOrderIndex(){
		$aftersaleTypeCondition=$this->aftersaleCondition();
		$aftersaleTids=$this->dAftersales->getAllThisAftersalesOrder($aftersaleTypeCondition);
 		$aftersalesStatus=I('aftersalesStatus');
		$this->aftersaleOrderPageDeal($condition,$aftersaleTids,$aftersalesStatus,"aftersale");
		$this->display();
	}
/*
 *退款管理页 
 * */
 	public function refundOrderIndex(){
 		$aftersalesStatus=I('aftersalesStatus');
		$aftersaleTypeCondition=$this->aftersaleCondition();
 		$aftersaleTids=$this->dAftersales->getAllThisRefundOrder($aftersaleTypeCondition);
		$this->aftersaleOrderPageDeal($condition,$aftersaleTids,$aftersalesStatus,"redund");
		$this->display();
 	}
/*
 *售后高级搜索条件
 * */
 	public function searchMoreConditionData($tids,$aftersalesStatus){
		if($_GET['goods']){
			$_GET['goods']=urldecode($_GET['goods']);
		} 		
 		$data=I('');
		if($data && !empty($tids)){
	 		$searchData=$data;
			//售后单号
			if(!empty($data['aftersalesBn'])){
				$condition['aftersales_bn']=trim($data['aftersalesBn']);
			}
			//时间条件(售后申请时间)
			$condition['tid']=array('in',$tids);
			if(!empty($data['startTime']) && !empty($data['endTime'])){
				$data['start']=strtotime($data['startTime']);
				$data['end']=strtotime($data['endTime']." +24 hours");
				$str=$data['start'].','.$data['end'];
				$condition['created_time']=array('between',$str);
			}
			//最后处理时间
			if(!empty($data['dealStartTime']) && !empty($data['dealEndTime'])){
				$data['dealStart']=strtotime($data['dealStartTime']);
				$data['dealEnd']=strtotime($data['dealEndTime']." +24 hours");
				$dealStr=$data['dealStart'].','.$data['dealEnd'];
				$condition['modified_time']=array('between',$dealStr);
			}			
			//退换货类型
			if(!empty($data['aftersaleType'])){
				$condition['aftersales_type']=$data['aftersaleType'];
				$searchData['thisAfterType']=$this->serachName($data['aftersaleType'],"afterType");
			}
			if(!empty($condition['created_time']) || !empty($condition['modified_time']) || !empty($condition['aftersales_type']) || !empty($condition['aftersales_bn'])){
				$condition['trade_source']="mall";
				$tids=array_unique($this->dAftersales->searchMoreAfterOrder($condition,$tids));
			}
			if(!empty($tids)){
				//退款订单页退款创建时间
				if(!empty($data['refundStartTime']) && !empty($data['refundEndTime'])){
					$refundCondition['tid']=array('in',$tids);
					$data['refundStart']=strtotime($data['refundStartTime']);
					$data['refundEnd']=strtotime($data['refundEndTime']." +24 hours");
					$refundStr=$data['refundStart'].','.$data['refundEnd'];
					$refundCondition['created_time']=array('between',$refundStr);
					$refundCondition['trade_source']="mall";
					$tids=array_unique($this->dAftersales->searchMoreRefundOrder($refundCondition,$tids));
				}					
			}else{
				$tids=0;
			}
			if(!empty($tids)){
				$orderCondition['tid']=array('in',$tids);
				//订单号
				if(!empty($data['tid'])){
					$orderCondition['tid']=trim($data['tid']);
				}
				//审核进度
				if(!empty($data['aftersaleProcess']) || !empty($aftersalesStatus)){
					if(!empty($aftersalesStatus) && empty($data['aftersaleProcess'])){
						$data['aftersaleProcess']=$aftersalesStatus;
					}
					$orderCondition['aftersales_status']=$data['aftersaleProcess'];
					$searchData['thisProcess']=$this->serachName($data['aftersaleProcess'],"afterProcess");
				}
				//商品名条件
				if(!empty($data['goods'])){
					$orderCondition['title']=array('like','%'.$data['goods'].'%');
				}
				if(!empty($orderCondition['tid']) || !empty($orderCondition['aftersaleProcess']) || !empty($orderCondition['title'])){
					$tids=array_unique($this->dOrder->getFieldConditionOrderTids($orderCondition,$tids));
				}	
				if(!empty($tids)){
					//下单时间
					if(!empty($data['creatStartTime']) && !empty($data['creatEndTime'])){
						$data['creatStart']=strtotime($data['creatStartTime']);
						$data['creatEnd']=strtotime($data['creatEndTime']." +24 hours");
						$creatStr=$data['creatStart'].','.$data['creatEnd'];
						$orderCondition['created_time']=array('between',$creatStr);
					}
					if(!empty($orderCondition['created_time'])){
						$tids=array_unique($this->dOrder->getFieldConditionTradeTids($orderCondition,$tids));
					}												
				}else{
					$tids=0;
				}				
				
			}else{
				$tids=0;
			}
			$this->assign('searchData',$searchData);
		}
		return 	$tids;
 	}
/*
 *售后订单页面处理 
 * */
 	public function aftersaleOrderPageDeal($condition,$aftersaleTids,$aftersalesStatus,$type,$onlyType){
		//售后的进度
		if($onlyType){
			$detailTids=$this->dOrder->getFieldThisConditionTradeInfo($afterStatusTids,$onlyType);
		}
		if(!empty($aftersalesStatus) && !empty($aftersaleTids)){
			$afterTid=$this->dOrder->getFieldOrderInfoOrder($aftersaleTids,$aftersalesStatus,'tid');
			$afterStatusTids=array_unique($afterTid);
		}else{
			$afterStatusTids=array_unique($aftersaleTids);
		} 
		if(I('serachIdentity')==1){
			$afterStatusTids=$this->searchMoreConditionData(array_unique($aftersaleTids),$aftersalesStatus);	
		}
		//比较导出及搜索数据的差值begin
//		foreach(array_unique($aftersaleTids) as $k=>$v){
//			if(!in_array($v, $afterStatusTids)){
//				$diffTids[]=$v;
//			}
//		}
		//比较导出及搜索数据的差值end
 		$size = 20;
		if(I('execlType') && I('execlType')=="exportExcel"){
			//导出execl
			$condition['tid']=array('in',$afterStatusTids);
			$orderExecl=$this->dOrder->getOrder($condition);   //另一份导出execl  方法orderExportExcel
			if(!empty($orderExecl)){
				$execlData=$this->OrderDeal($orderExecl,"execl","aftersale");	
				$this->orderExportExcel($execlData);   				
			}
		}
		$number=count($afterStatusTids);	
		//该状态下的个数
		$allTids=array_unique($aftersaleTids);
		$count['all']=count($allTids);
		$count['earlyProcess']=$this->getafterCount("WAIT_EARLY_PROCESS",$allTids);
		$count['process']=$this->getafterCount("WAIT_PROCESS",$allTids);
		$count['buyerSend']=$this->getafterCount("WAIT_BUYER_SEND_GOODS",$allTids);
		$count['getGoods']=$this->getafterCount("WAIT_SELLER_CONFIRM_GOODS",$allTids);
		$count['sendGoods']=$this->getafterCount("SELLER_SEND_GOODS",$allTids);
		$count['pass']=$this->getafterCount("SELLER_REFUSE",$allTids);
		$count['waitRefund']=$this->getafterCount("REFUND_PROCESS",$allTids);
		$count['sucess']=$this->getafterCount("SUCCESS",$allTids);
		$page = new \Think\Page($number,$size);
		$rollPage = 20; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$OrderTids=array_slice($afterStatusTids,$start,$pagesize);
		if(!empty($OrderTids)){
			$condition['tid']=array('in',$OrderTids);
		}else{
			$condition['tid']=0;
		}
		$orderRes=$this->dOrder->getAftersaleOrder($condition,$OrderTids);
		if(!empty($orderRes)){
			$orderRes=$this->OrderDeal($orderRes,1);
		}
		if(!empty($OrderTids)){
			$afterBnCondition['tid']=array('in',$OrderTids);
			$afterBnCondition['trade_source']="mall";
		}
		if($type=="aftersale"){
			$afterInfo=$this->dAftersales->getAllThisAftersales($afterBnCondition,'aftersales_bn,tid,created_time,modified_time');
		}else if($type=="redund"){
			$afterInfo=$this->dAftersales->getAllRefundOrder($afterBnCondition,'tid,created_time,modified_time');
		}
		if($afterInfo){
			foreach($orderRes as $key=>$value){
				foreach($afterInfo as $keys=>$values){
					if($value['tid']==$values['tid']){
						$orderRes[$key]['afterInfo']=$values;
					}
				}			
			}			
		}
		foreach($orderRes as $key=>$value){
			foreach($value['items'] as $keys=>$values){
				$orderRes[$key]['items'][$keys]['aftersalesStatus']=$this->orderStatusLastReturn($values['aftersales_status']);
			}
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this -> assign('pagestr',$pagestr);	
		$this->assign('list',$orderRes);
		$this->assign('count',$number);
		$this->assign('nowLocal',$aftersalesStatus);
		$this->assign('number',$count);
		
 	}
/*计算改状态下的数量*/
	public function getafterCount($afterProcess,$tids){
		if($tids){
			$condition['aftersales_status']=$afterProcess;
			$condition['tid']=array('in',$tids);
			$res=$this->dOrder->getOrderItemsToNum($condition);
			$afterStatusTids=array_unique($res);
			return count($afterStatusTids);
		}else{
			return 0;
		}
	}
 /*
  * 订单处理
  * */
	public function OrderDeal($orderRes,$type,$aftersale){
		if(empty($orderRes)){
			exit;
		}
		foreach($orderRes as $key=>$value){
			$tids[]=$value['tid'];   //订单号
			$comIds[]=$value['com_id']; //comid
			$userIds[]=$value['user_id'];
			if($value['order_status']== 'REFUND' || $value['order_status']== 'RETURN' ){
				$refundTids[]=$value['tid'];  //退款的订单tid
			}
		}
//		已退款金额
		if(!empty($refundTids)){
			//退款积分
			$refundOrders=$this->dAftersales->getRefundOrderInfo($refundTids);
			foreach($refundOrders as $v){
			  $refundPoint[$v['tid']] += $v['refund_fee'];			
			}
			//退款现金
			$refundOrderCash = $this->dOrder->getRefundCash($tids);
			foreach($refundOrderCash as $v){
				$refundCash[$v['tid']] += $v['refund_fee'];
			}
			if($type != "execl"){
				$this->assign('refundPoint', $refundPoint);
				$this->assign('refundCash', $refundCash);
			}
		}
		if(!empty($tids)){
			$paymentInfo=$this->dOrder->getSomeSingleNums($tids,'created_time desc');
			if(!empty($paymentInfo)){
				foreach($paymentInfo as $key=>$value){
					$paymentIds[]=$value['payment_id'];
				}
				$payOrderInfo=$this->dOrder->getSingleForOrder($paymentIds);
				$payNames=$this->dOrder->getOrderPayName($paymentIds);
				if($payOrderInfo){
					foreach($paymentInfo as $key=>$value){
						foreach($payOrderInfo as $keys=>$values){
							if($value['payment_id']==$values['payment_id']){
								$paymentInfo[$key]['moreOrder'][]=$values['tid'];
							}
						}
						foreach($payNames as $keyn=>$valuen){
							if($value['payment_id']==$valuen['payment_id']){
								$paymentInfo[$key]['pay_name']=$valuen['pay_name'];
								$paymentInfo[$key]['account']=$valuen['account'];
								$paymentInfo[$key]['bank']=$valuen['bank'];
							}
						}						
					}					
				}
			}
		}
		if(!empty($tids)){
//			if($type==1){   //使用另一份导出execl时注释      方法orderExportExcel
			if($aftersale=="aftersale"){
				$orderItem=$this->dOrder->getOrderAfterItems($tids);
			}else{
				$orderItem=$this->dOrder->getOrderItems($tids);
			}
//			}
			$JdOrderNumber=$this->dOrder->getJdOrderNumber($tids);
		}
		if(!empty($comIds)){
			$companys=$this->dOrder->getThisCompany($comIds);
		}
		if($type=="execl"){
			$users=$this->dOrder->getThiUserInfo($userIds);
		}
		foreach($orderRes as $key=>$value){
			//订单内商品
//			if($type==1){    //使用另一份导出execl时注释     方法orderExportExcel时    
				foreach($orderItem as $keys=>$values){
					if($value['tid']==$values['tid']){
						$orderRes[$key]['items'][]=$values;	
					}				
				}
//			}
			//用户名
			if($type=="execl"){
				foreach($users as $keyu=>$valueu){
					if($value['user_id']==$valueu['user_id']){
						$orderRes[$key]['user_id']=$valueu['mobile'];	
					}				
				}				
			}
			//合单-所有子订单
			foreach($paymentInfo as $keyso=>$valueso){
				if($value['tid']==$valueso['tid']){
					$orderRes[$key]['sigleOrder']=$valueso;	
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
		if($type == "execl"){
			return array(
				'orderRes' => $orderRes,
				'refundPoint' => $refundPoint,
				'refundCash'  => $refundCash
			);
			
		}else{
			return $orderRes;
		}

	}
//导出execl  另一份
	public function orderExportExcel($execl){
		header("Content-type:text/html;charset=utf-8");
		$execlData = $execl['orderRes'];
		$refundPoint = $execl['refundPoint'];
		$refundCash = $execl['refundCash'];
		foreach($execlData as $key => $value){
			foreach($value['items'] as $keys=>$values){
				$itemIds[]=$values['item_id'];
				$supplierIds[]=$values['supplier_id']; //供应商id			
				$skuIds[]=$values['sku_id'];
			}
		}	
		$itemIds=array_filter($itemIds);
		$supplierIds=array_filter($supplierIds);
		//所属供应商
		if(!empty($supplierIds)){
			$supplierInfo=$this->dGoods->getConditionsupplierUser($supplierIds);
		}
		if(!empty($itemIds)){
			$itemInfo=$this->dOrder->getAllItem($itemIds);
		}
		foreach($itemInfo as $key=>$value){
			$catIds[]=$value['cat_id'];
		}		
		if(!empty($catIds)){
			$catInfo=$this->dOrder->getInCategoryInfo($catIds);
		}
		foreach($itemInfo as $key=>$value){
			foreach($catInfo as $keys=>$values){
				if($value['cat_id']==$values['cat_id']){
					$itemInfo[$key]['cat_name']=$values['cat_name'];
				}
			}	
		}			
		if(!empty($skuIds)){
			$skuInfo=$this->dOrder->getAllSkuItem($skuIds);
		}
		foreach($execlData as $key => $value){
			$oldTids[$value['tid']]=$value['tid'];
			foreach($value['items'] as $keys=>$values){
				$itemIds[]=$value['item_id'];
				$supplierIds[]=$value['supplier_id']; //供应商id		
				if($keys==0){
					//订单第一条
					$newTids[$value['tid']]=$value['tid'];
					$firstOids[$values['oid']]=$values['oid'];
					$LastExeclData[$values['oid']]['tid']=" ".$value['tid'];
					$LastExeclData[$values['oid']]['payment_id']=" ".$value['sigleOrder']['payment_id'];
					$LastExeclData[$values['oid']]['jdnumber']=" ".$value['JdnNmber'];
					$LastExeclData[$values['oid']]['user_id']=$value['user_id'];
				}
				else{
					$LastExeclData[$values['oid']]['tid']="";
					$LastExeclData[$values['oid']]['payment_id']="";
					$LastExeclData[$values['oid']]['jdnumber']="";
					$LastExeclData[$values['oid']]['user_id']="";
				}
					$LastExeclData[$values['oid']]['cat_name']=$values['item_id'];	 //三级分类
					$LastExeclData[$values['oid']]['title']=$values['title'];
					$LastExeclData[$values['oid']]['barcode']=$values['sku_id'];
					$LastExeclData[$values['oid']]['bn']="";
					$LastExeclData[$values['oid']]['spec_nature_info']=$values['spec_nature_info'];
					$LastExeclData[$values['oid']]['price']=" ".$values['price'];
					$LastExeclData[$values['oid']]['num']=$values['num'];
					$LastExeclData[$values['oid']]['total_price']="".$values['price']*$values['num'];
					$LastExeclData[$values['oid']]['total_cost_price']="".$values['cost_price']*$values['num'];					
					if(empty($values['supplier_id'])){
						$LastExeclData[$values['oid']]['supperName']="---";
					}else{
						$LastExeclData[$values['oid']]['supperName']=$values['supplier_id'];
					}
					if($values['send_type']==1){
						$LastExeclData[$values['oid']]['send_type']="自发";
					}else if($values['send_type']==2){
						$LastExeclData[$values['oid']]['send_type']="代发";
					}else if($values['send_type']==3){
						$LastExeclData[$values['oid']]['send_type']="顺丰发货";
					}else{
						$LastExeclData[$values['oid']]['send_type']="---";
					}				
				if($keys==0){
					$LastExeclData[$values['oid']]['receiver_name']=$value['receiver_name'];
					$LastExeclData[$values['oid']]['receiver_mobile']=$value['receiver_mobile'];
					$LastExeclData[$values['oid']]['created_time']=$value['created_time'];
					if($value['consign_time']){
						$LastExeclData[$values['oid']]['consign_time']=date('Y-m-d H:i:s',$value['consign_time']);		
					}else{
						$LastExeclData[$values['oid']]['consign_time']="---";		
					}
					$LastExeclData[$values['oid']]['post_fee']=" ".$value['post_fee'];	
					$LastExeclData[$values['oid']]['payment']=" ".$value['payment'];
					$LastExeclData[$values['oid']]['payed_fee']=" ".$value['payed_fee'];
					$LastExeclData[$values['oid']]['pay_time']=$value['pay_time'];
					$LastExeclData[$values['oid']]['company']=$value['company'];
					$LastExeclData[$values['oid']]['address']=$value['receiver_state'].$value['receiver_city'].$value['receiver_district'].$value['address'];
					$LastExeclData[$values['oid']]['orderStatus']=$value['orderStatus'];	
					$LastExeclData[$values['oid']]['afterType']=$this->orderStatusReturn($value['order_status']);	//售后
					if(!empty($refundPoint[$value['tid']])){
						$LastExeclData[$values['oid']]['refundPoint']=" ".$refundPoint[$value['tid']];	//已退款积分				
					}else{
						$LastExeclData[$values['oid']]['refundPoint'] = '---';	//已退款积分				
					}
					if(!empty($refundCash[$value['tid']])){
						$LastExeclData[$values['oid']]['refundCash']=" ".$refundCash[$value['tid']];	//已退款现金			
					}else{
						$LastExeclData[$values['oid']]['refundCash'] = '---';	//已退款现金
					}					
					$LastExeclData[$values['oid']]['pay_name']=$value['sigleOrder']['pay_name'];
					$LastExeclData[$values['oid']]['account']="".$value['sigleOrder']['account'];
					$LastExeclData[$values['oid']]['bank']=$value['sigleOrder']['bank'];
				}else{
					$LastExeclData[$values['oid']]['receiver_name']="";
					$LastExeclData[$values['oid']]['receiver_mobile']="";
					$LastExeclData[$values['oid']]['total_fee']="";
					$LastExeclData[$values['oid']]['pay_time']="";
					$LastExeclData[$values['oid']]['created_time']="";
					$LastExeclData[$values['oid']]['payed_fee']="";
					$LastExeclData[$values['oid']]['consign_time']="";
					$LastExeclData[$values['oid']]['payment']="";
					$LastExeclData[$values['oid']]['company']="";
					$LastExeclData[$values['oid']]['address']="";
					$LastExeclData[$values['oid']]['post_fee']="";	
					$LastExeclData[$values['oid']]['orderStatus']="";
					$LastExeclData[$values['oid']]['afterType']=" ";
					$LastExeclData[$values['oid']]['refundPoint'] = " ";	//已退款积分				
					$LastExeclData[$values['oid']]['refundCash'] = " ";	//已退款现金
					$LastExeclData[$values['oid']]['pay_name']="";
					$LastExeclData[$values['oid']]['account']="";
					$LastExeclData[$values['oid']]['bank']="";									
				}	
			}
		}
		foreach($LastExeclData as $key =>$value){
			$newTids[]=$value['tid'];
			foreach($itemInfo as $keys=>$values){
				if($value['cat_name']==$values['item_id']){
					$LastExeclData[$key]['cat_name']=$values['cat_name'];
				}
			}
			foreach($skuInfo as $keyk=>$valuek){
				if($value['barcode']==$valuek['sku_id']){
					//条形码
					$LastExeclData[$key]['barcode']=" ".$valuek['barcode'];
					//商品编号
					$LastExeclData[$key]['bn']=" ".$valuek['bn'];
				}
			}
			foreach($supplierInfo as $keyp=>$valuep){
				if($value['supperName'] == $valuep['supplier_id']){
					$LastExeclData[$key]['supperName']=$valuep['company_name'];
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
		$columnName=array('订单号','支付单号','京东单号','用户名/手机号','所属分类3','商品名称','商品条形码','商品编号','商品属性','商品价格/元','商品数量','商品总价格/元','商品总成本价/元','商品所属供应商','发货类型','收货人','手机','下单时间','发货时间','邮费','订单总额/元','支付金额/元','支付时间','所属企业','地址','订单状态','订单售后','已退款(积分)/元','已退款(现金)/元','支付方式','收款账号','收款方式');
		$ex->getExcel($LastExeclData,$columnName,"订单列表(".date('Y年m月d日H时i分',time()).")");	
	}
//获取所有企业名
	public function getCompany(){
		$res=$this->dOrder->getAllCompany();
		echo json_encode($res);
	}		
	//订单详情
	public function detail(){
		$tid=I('tid');
		if($tid){
			$detail=$this->dOrder->getOrderDetail($tid);
			//店铺信息
			$detail['shopinfo']=$this->dOrder->getShopInfo($detail['shop_id']);
			//京东单号
			$detail['JdOrderNumber']=$this->dOrder->getThisJdOrderNumber($detail['tid']);
			//状态
			$detail['OrderStatus']=$this->orderStatus($detail['status']);
			//用户信息
			$detail['userInfo']=$this->dOrder->getUserInfo($detail['user_id']);
			//时间
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
			$detail['companyName']=$this->dOrder->getUserCompany($detail['com_id']);
			//物流信息
			$detail['express']=$this->dOrder->getExpress($detail['tid']);
			//售后类型
			$detail['serviceStatus']=$this->orderStatusReturn($detail['order_status']);
			//售后状态
			foreach($detail['more'] as $key=>$value){
				$itemIds[]=$value['item_id'];
				$detail['more'][$key]['serviceLastStatus']=$this->orderStatusLastReturn($value['aftersales_status']);
			}
			if($detail['shop_id']==10){
				$jdskus=$this->dOrder->getItemJdsku($itemIds);
				foreach($detail['more'] as $key=>$value){
					foreach($jdskus as $keys=>$values){
						if($value['item_id']==$values['item_id']){
							$detail['more'][$key]['jd_sku']=$values['jd_sku'];
						}
					}
				}				
			}
//			订单处理进度start
			$orderDealProcess=$this->dOrder->getThisOrderLogInfo($tid);
	//		已退款金额
			//退款积分
			$refundOrders=$this->dAftersales->getRefundOrderInfo(array($tid));
			foreach($refundOrders as $v){
			  $refundPoint[$v['tid']] += $v['refund_fee'];			
			}
			//退款现金
			$refundOrderCash = $this->dOrder->getRefundCash(array($tid));
			foreach($refundOrderCash as $v){
				$refundCash[$v['tid']] += $v['refund_fee'];
			}
			$this->assign('refundPoint', $refundPoint);
			$this->assign('refundCash', $refundCash);
			$this->assign('orderDealProcess',$orderDealProcess);
//			订单处理进度end
			$this->assign('info',$detail);
		}
		
		$this->display();
	}	
	//订单->查合单
	public function single(){
		$tid=I('tid');
		if($tid){
			$data=$this->dOrder->getSingleOrderNums($tid);
			foreach($data as $key=>$value){
				$tids[]=$value['tid'];
				$paymentId=$value['payment_id'];
			}
			if(!empty($tids)){
				$ordersInfo=$this->dOrder->getOrderDetails($tids);
				$expressInfo=$this->dOrder->getInExpress($tids);
				$JdOrderNumbers=$this->dOrder->getJdOrderNumber($tids);
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
	//活动订单-》条件
	public function activityCondition(){
		$data=I('');
		//输出搜索条件显示
		$searchData=$data;
		//企业条件
		if(!empty($data['comId'])){
			//start公司名字显示用
				$searchData['company']=$this->serachName($data['comId'],"company");
			//end
			$condition['com_id']=$data['comId'];
		}
		//时间条件
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$data['start']=strtotime($data['startTime']);
			$data['end']=strtotime($data['endTime']." +24 hours");
			$str=$data['start'].','.$data['end'];
			$condition['creat_time']=array('between',$str);
		}
		//时间条件(支付时间)
		if(!empty($data['payStartTime']) && !empty($data['payEndTime'])){
			$data['cstart']=strtotime($data['payStartTime']);
			$data['cend']=strtotime($data['payEndTime']." +24 hours");
			$creatStr=$data['cstart'].','.$data['cend'];
			$condition['pay_time']=array('between',$creatStr);
		}		
		//售后状态选择
		if(!empty($data['service'])){
			$searchData['service']=$this->serachName($data['service'],"serviceType");
			$condition['order_status']=$data['service'];
			
		}			
		//状态条件
		if(!empty($data['status'])){
			//start订单状态
				$searchData['status']=$this->serachName($data['status'],"orderStatus");
			//end	
			if($data['status']=="TRADE_CLOSED_BY_SYSTEM"){
				$condition['status']=array('in','TRADE_CLOSED_BY_SYSTEM,TRADE_CLOSED_BY_USER,TRADE_CLOSED_BY_ADMIN');
			}else{
				$condition['status']=$data['status'];
			}						
		}
		//员工手机号
		if(!empty($data['mobile'])){
			$userId=$this->dOrder->getUserId(trim($data['mobile']));
			if(!empty($userId)){
				$condition['user_id']=array('in',$userId);
			}else{
				$condition['user_id']=0;
			}
		}
		//审核
		if(!empty($data['process'])){
			if($data['process']==1){
				$where['splitOrder_id']=array('in','0,-2');
			}else if($data['process']==2){
				$where['splitOrder_id']=-1;
			}
			$where['status']='WAIT_SELLER_SEND_GOODS';
			$where['aid']=array('neq',25);
			$tids=$this->dOrder->getOrderSomeField($where,'atid');
			if(!empty($tids)){
				$condition['atid']=array('in',$tids);
			}else{
				$condition['atid']=0;
			}
		}	
		//支付单号
		if(!empty($data['paymentId'])){
			$tids=$this->dOrder->getPaymentToTids(trim($data['paymentId']));
			if(!empty($tids)){
				$condition['atid']=array('in',$tids);
			}else{
				$condition['atid']=0;
			}
		}
		//订单号
		if(!empty($data['tid'])){
			$condition['atid|receiver_name|receiver_mobile']= array('like','%'.trim($data['tid']).'%');
		}		
		//活动类型
		if(!empty($data['aid'])){
			$condition['aid']=$data['aid'];
			$searchData['activityTypes']=$this->dOrder->getActivityType($data['aid']);
		}
		$this->assign('searchData',$searchData);
		return 	$condition;		
	}
	//订单->活动订单 rui 2016-09-05
	public function activity(){
		$condition=$this->activityCondition();
		$size = 20;
		if(I('execlType') && I('execlType')=="exportExcel"){
			//导出execl
			$orderExecl=$this->dOrder->getActivityOrder($condition);
			if(!empty($orderExecl)){
				$execlData=$this->activityOrderDeal($orderExecl);	
				$this->ActivityexportExcel($execlData);
			}	
		}	
		//活动类型
		$this->activityAllType();		
		$number=$this->dOrder->getAcitityOrderCount($condition);
		$page = new \Think\Page($number,$size);
		$rollPage = 5; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";		
		$orderRes=$this->dOrder->getActivityOrder($condition,$limit);
		if($orderRes){
			$orderRes=$this->activityOrderDeal($orderRes);
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this -> assign('pagestr',$pagestr);	
		$this->assign('list',$orderRes);
		$this->assign('count',$number);
		$this->display();
	}
/*
 *所有活动类型 
 * */
 	public function activityAllType(){
 		$res=$this->dOrder->getAllActivityType();
		$this->assign('activityTypes',$res);
 	}
//活动订单导出execl
	public function ActivityexportExcel($execlData){
		header("Content-type:text/html;charset=utf-8");
		foreach($execlData as $key =>$value){
			$data[$key]['tid']=" ".$value['atid'];
			$data[$key]['name']=$value['account'];
			$data[$key]['aid']=$value['aid'];
			$data[$key]['activity_name']=$value['activity_name'];
			$data[$key]['title']=$value['title'];
			$data[$key]['market_price']=' '.$value['market_price'];//商城价格
			$data[$key]['receiver_name']=' '.$value['receiver_name'];
			$data[$key]['address']=$value['receiver_state'].$value['receiver_city'].$value['receiver_district'].$value['receiver_address'];
			$data[$key]['receiver_mobile']=$value['receiver_mobile'];
			$data[$key]['creat_time']=$value['creat_time'];
			$data[$key]['payment']=$value['payment'];
			$data[$key]['payed_fee']=$value['payed_fee'];
			$data[$key]['pay_time']=$value['"pay_time'];
			$data[$key]['company']=$value['company'];
			$data[$key]['orderStatus']=$value['orderStatus'];
		}
		$ex=new Excel;
		$columnName=array('订单号','用户名','套餐ID','活动名称','套餐信息','商城价格/元','收货人','收货地址','手机','下单时间','订单总额','支付金额/元','支付时间','所属企业','订单状态');
		$ex->getExcel($data,$columnName,"活动订单列表(".date('Y年m月d日H时i分',time()).")");		
		
	}		
	//订单信息处理
	public function activityOrderDeal($orderRes){
		if(empty($orderRes)){
			exit;
		}
		foreach($orderRes as $key=>$value){
			$tids[]=$value['atid'];   //订单号
			$comIds[]=$value['com_id']; //comid
			$userIds[]=$value['user_id'];
			if($value['order_status']== 'REFUND' || $value['order_status']== 'RETURN' ){
				$refundTids[]=$value['atid'];  //退款的订单tid
			}			
		}
//		已退款金额
		if(!empty($refundTids)){
			$refundOrders=$this->dAftersales->getRefundOrderInfo($refundTids);
		}
		for($i=0;$i<count($refundOrders);$i++){
			for($j=$i+1;$j<count($refundOrders);$j++){
				if($refundOrders[$i]['tid']==$refundOrders[$j]['tid']){
					$refundOrders[$i]['refund_fee']=$refundOrders[$i]['refund_fee']+$refundOrders[$j]['refund_fee'];
					unset($refundOrders[$j]);
				}
			}
		}
		if(!empty($comIds)){
			$companys=$this->dOrder->getThisCompany($comIds);
		}
		if($type=="execl"){
			$users=$this->dOrder->getThiUserInfo($userIds);
		}
		if($tids){
			$orderItem=$this->dOrder->getAllActivityItems($tids);
			$orderItem=A('ActivityOrder')->repairItemTitle($orderItem);
			$JdOrderNumber=$this->dOrder->getJdOrderNumber($tids);
			$paymentIds=$this->dOrder->getSomeSingleNums($tids);
		}
		foreach($orderRes as $key=>$value){
			//订单内商品
			foreach($orderItem as $keys=>$values){
				if($value['atid']==$values['atid']){
					$orderRes[$key]['items'][]=$values;	
				}				
			}
			if(!empty($refundOrders)){
				foreach($refundOrders as $keyf=>$valuef){
					if($value['atid']==$valuef['tid']){
						$orderRes[$key]['refundFeed']=$valuef['refund_fee'];	
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
				if($value['atid']==$valuej['tid']){
					$orderRes[$key]['JdnNmber']=$valuej['sync_order_id'];	
				}	
			}
			//支付单号
			foreach($paymentIds as $keyp=>$valuep){
				if($value['atid']==$valuep['tid']){
					$orderRes[$key]['paymentId']=$valuep['payment_id'];	
				}	
			}			
			//时间
			$orderRes[$key]['creat_time']=date('Y-m-d H:i:s',$value['creat_time']);
			$orderRes[$key]['payTime']=$orderRes[$key]['pay_time'];
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
	//活动订单详情
	public function activityDetail(){
		$tid=I('tid');
		if($tid){
			$detail=$this->dOrder->getThisAOrderDetail($tid);
			$detail['more']=A('ActivityOrder')->repairItemTitle($detail['more']);
			$detail['status']=$this->orderStatus($detail['status']);			
			//用户信息
			$detail['userInfo']=$this->dOrder->getUserInfo($detail['user_id']);
			//时间
			$detail['creat_time']=date('Y-m-d H:i:s',$detail['creat_time']);
			$detail['pay_time']=$this->timeDeal($detail['pay_time']);
			$detail['consign_time']=$this->timeDeal($detail['consign_time']);
			$detail['modified_time']=$this->timeDeal($detail['modified_time']);
			if(!empty($detail['userInfo']['birthday'])){
				$detail['userInfo']['birthday']=date('Y年m月d日',$detail['userInfo']['birthday']);
			}
			$detail['userInfo']['regtime']=$this->timeDeal($detail['userInfo']['regtime']);
			//所属公司
			$detail['companyName']=$this->dOrder->getUserCompany($detail['com_id']);
			//套餐信息
			$activityInfo=$this->dOrder->getActivityItems($tid,'aitem_id');
			$detail['moreInfo']=$this->dOrder->getThisActivityInfo($activityInfo[0]['aitem_id']);
			//物流信息
			$detail['express']=$this->dOrder->getExpress($detail['atid']);	
			$this->assign('info',$detail);
//			订单处理进度start
			$orderDealProcess=$this->dOrder->getThisOrderLogInfo($tid);
			$this->assign('orderDealProcess',$orderDealProcess);			
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
		if(!$tid || !is_numeric($tid)){
			exit('Can not find pay bill!');
		}
		$paymentId=$this->dOrder->getTradePaymentId($tid);
		$condition['tid']=$tid;
		$condition['aftersales_status']='REFUND_PROCESS';
		$items=$this->dOrder->getThisConditionOrderInfo($condition);
		if(empty($items)){
			exit('该订单暂不存在待退款商品!');
		}
		foreach($items as $key=>$value){
			$oids[]=$value['oid'];
		}
		if(!empty($oids)){
			$aftersaleCondition['oid']=array('in',$oids);
			$aftersaleCondition['trade_source']="mall";
			$aftersaleInfo=$this->dAftersales->getAllThisAftersales($aftersaleCondition,'oid,shop_explanation');
			//取出上一步审核备注
			foreach($items as $key=>$value){
				foreach($aftersaleInfo as $keys=>$values){
					if($value['oid']==$values['oid']){
						$items[$key]['reason']=$values['shop_explanation'];
					}
				}
			}	
		}
		if($items[0]['user_id']){
			$userName=$this->dOrder->getThisUserName($items[0]['user_id']);
		}
		$this->assign('userName',$userName);
		$field = 'refund_fee,order_status,refund_point,refund_cash,payed_fee,payed_cash,payed_point';
		$res=$this->dOrder->getThisOrderInfo($tid,$field);
		$res['serviceStatus']=$this->orderStatusReturn($res['order_status']);
		//以前系统的积分退款
		if($res['refund_cash'] == 0 && $res['refund_point'] == 0){
			$res['refund_point'] = $res['refund_fee']*100;
		}
		if($res['refund_cash']>0){
			//取出支付宝支付流水号
			$payInfo = $this->dOrder->getPayTradeNo($paymentId,'pay_app_id,trade_no,pay_name');
			$payType=array(
				'alipay'=>'支付宝',
				'wxpay' => '微信',
			);
			$this->assign('payInfo',$payInfo);
		}
		$this->assign('payedType',$payType);
		$this->assign('orderInfo',$res);
		$this->assign('tid',$tid);
		$this->assign('paymentId',$paymentId);	
		$this->assign('items',$items);
		$this->assign('payType',I('payType'));
		$this->display();
	}
	//活动订单退款
	public function activityRefund(){
		$tid=I('tid');
		if($tid){
			$paymentId=$this->dOrder->getTradePaymentId($tid);
			$condition['aftersales_status']='REFUND_PROCESS';
			$condition['atid']=$tid;
			$items=$this->dActivityOrder->getThisConditionOrderInfo($condition);
			$items=A('ActivityOrder')->repairItemTitle($items);
			foreach($items as $key=>$value){
				$oids[]=$value['order_id'];
			}
			if(!empty($oids)){
				$aftersaleCondition['oid']=array('in',$oids);
				$aftersaleCondition['trade_source']="activity";
				$aftersaleInfo=$this->dAftersales->getAllThisAftersales($aftersaleCondition,'oid,shop_explanation');
				//取出上一步审核备注
				foreach($items as $key=>$value){
					foreach($aftersaleInfo as $keys=>$values){
						if($value['order_id']==$values['oid']){
							$items[$key]['reason']=$values['shop_explanation'];
						}
					}
				}	
			}
			$activityInfo=$this->dOrder->getThisActivityOrderInfo($tid,'activity_name,title');
			$res=$this->dActivityOrder->getThisOrderInfo($tid,'refund_fee,order_status,user_id');
			$res['activity_name']=$activityInfo['activity_name'];
			$res['title']=$activityInfo['title'];
			$userName=$this->dOrder->getThisUserName($res['user_id']);
			$this->assign('userName',$userName);						
			$res['serviceStatus']=$this->orderStatusReturn($res['order_status']);
			$this->assign('orderInfo',$res);
			$this->assign('tid',$tid);
			$this->assign('paymentId',$paymentId);	
			$this->assign('items',$items);	
			$this->assign('payType',I('payType'));
			$this->display();
		}else{
			echo 'Can not find pay bill!';
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
		$dealRes=I('dealRes'); //处理结果 1-同意退款  2-拒绝退款
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
				'tid'=>$tid,
				'items'=>$items,
				'mark'=>$mark,
				'refundType'=>$refundType
			);
			if(!empty($orderType)){
				$data['orderType']=$orderType;
				$data['fee'] = $fee;
			}else{
				//积分转钱
				$data['fee'] = $fee/100;
				$trade = $this->dOrder->getThisOrderInfo($tid,'refund_fee,refund_point,refund_cash,refund_time');
				if($trade['refund_time']>0){
					$this->ajaxReturn(array(0,'该笔退款订单已经退过款'));
				}
				//以前系统的积分退款
				if($trade['refund_cash'] == 0 && $trade['refund_point'] == 0){
					$data['fee'] = $trade['refund_fee'];
				}else{
					$data['fee'] = $trade['refund_point']/100;
				}				
			}
			$result=1;
			$status=2;
			$dealRes='SUCCESS';
			$refundStatus=1;
			if($data['fee'] > 0){
				//退积分
				$return=$this->requestPost($url,$data);
				$resu=json_decode(trim($return,chr(239).chr(187).chr(191)),true);
				if($resu['result']==100 && $resu['errcode']==0){
					//退款成功
					$refundId=$resu['data']['refundId'];
					$remarks="退款结果：通过,<br/>退款账户：".$userName."<br/>退款路径：".$refundLoad."<br/>退款金额：￥".$fee."<br/>处理备注：".$mark;
					//退款成功更改几个表的状态为已处理
				}else{
					//退款失败
					$this->ajaxReturn(array(0,$resu['msg']));
				}				
			}
			if($trade['refund_cash'] > 0){
				//只退现金
				$cachData = array(
					'items' => str_replace('&quot;','"',$items),
					'tid' => $tid,
					'refund_fee' => $trade['refund_cash'],
					'mark' => $mark,
					'modified_time' => time(),
				);
				//退现金记录进退款现金记录表
				if($refundId > 0){
					$cachData['refund_id'] = $refundId;
				}
				$cashAdd = $this->dOrder->addRefundCash($cachData);
				if($cashAdd){
					$resu['msg']="退款记录添加成功";	
				}
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
		//改变售后申请表状态
		$condition['oid']=array('in',$oids);
		$statusData['status']=$status;
		$statusData['admin_explanation']=$mark;
		$statusData['modified_time']=time();
		$aftersaleRes=$this->dAftersales->editAftersales($condition,$statusData,'status,shop_explanation,modified_time');	
		//写进管理员操作订单日志表
		$oidsString=implode(',',$oids);
		$dealType="退款处理";
		$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
		//订单主表状态
		$mdTrade = array(
			'refund_time' => time(),
			'refund_cash' => 0,
			'refund_point' => 0
		);
		$this->dOrder->editTradeInfo($tid,$mdTrade,'refund_time,refund_cash,refund_point');		
		//该订单分表的状态
		$data['aftersales_status']=$dealRes;
		$data['modified_time']=time();
		$statusRes=$this->dOrder->editThisConditionOrderInfo($condition,$data,'aftersales_status,modified_time');	
		//修改申请退款表状态
		$refundData['status']=$refundStatus;
		$refundData['modified_time']=time();
		$this->dAftersales->editrefundInfo($condition,$refundData,'status,modified_time');	
		if($trade['refund_cash'] > 0 && empty($refundId) && $cashAdd > 0){
			//只退现金
			$refundId = $cashAdd;
		}
		$this->ajaxReturn(array($result,$resu['msg'],$refundId));
	}	
//物流信息
	public function expressProgress(){
		$expressNum=I('logiNo');
		$corpId=I('corpId');//快递标识
		$tid=I('tid');//快递标识
		if(!$tid){
			exit('Error:tid No Fund!');
		}
		if(I('shop_id')==10){
			//京东快递
			$url=C('COMMON_API')."Api/getExpress/";
			$data=array(
				'orderId'=>$expressNum
			);
			$res=$this->requestPost($url,$data);
			$resu=json_decode(trim($res,chr(239).chr(187).chr(191)),true);	
			$expressInfo=$resu['data']['orderTrack'];
				//当前页订单
		}else{
			if($corpId == 2){
				//顺丰
				$paymentId=$this->dOrder->getTradePaymentId($tid);
				$url=C('COMMON_API')."Sf/sendOrderStatus/";
				$data=array(
					'paymentId'=>$paymentId
				);	
				$res=$this->requestPost($url,$data);
				$resu=json_decode(trim($res,chr(239).chr(187).chr(191)),true);	
				$expree=json_decode($resu['data'],TRUE);
				foreach($expree['data']['steps'] as $key=>$val){
					$expressInfo[$key]['content']=$val['note'];
					$expressInfo[$key]['msgTime']=$val['eventTime'];
				}
			}
		}			
		$this->assign('expressInfo',$expressInfo);
		$this->assign('logiNo',$expressNum);
		$this->display();
	}
/*
 * 申请退款页面
 * */	
	public function applyRefund(){
		$tid=I('tid');
		if($tid){
			$this->assign('tid',$tid);
			$this->display();		
		}
	}
//申请退款
	public function applyRefundDeal(){
		$tid=I('tid');
		$reason=I('reason');
		$mark=I('mark');		
		if($tid){
			if($reason==='0'){
				$this->error('请选择售后原因！');
			}
			if(empty($mark)){
				$this->error('请填写取消理由！');
			}				
			$data['cancel_reason']=$mark;
			$data['status']='SUCCESS';
			$res=$this->dOrder->editOrderStatus($tid,$data);
			if($res){
				//写进管理员操作日志表
				$dealType="申请退款";
				$remarks='申请退款,原因：'.$reason.',描述：'.$mark;
				$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
				//写进申请售后表
				$tradeInfo=$this->dOrder->findThisTradeInfo($tid,'user_id,shop_id,tid');
				$tradeInfo['description']="管理员:".$this->adminName."申请退款,描述：".$mark;
				$tradeInfo['aftersales_type']='ONLY_REFUND';
				$tradeInfo['modified_time']=time();	
				$tradeInfo['created_time']=time();				
				$tradeInfo['reason']=$reason;			
				$itemInfo=$this->dOrder->getThisTradeInfo($tid,'oid,title,num');
				foreach($itemInfo as $key=>$value){
					$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
					$tradeInfo['oid']=$value['oid'];
					$tradeInfo['title']=$value['title'];
					$tradeInfo['num']=$value['num'];
					$this->dOrder->editOrderInfo($value['oid'],$value['num']);
					$this->dAftersales->addAftersales($tradeInfo);				
				}
				if($res){
					echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>商品取消成功，等待审核退款！</div>";
				}				
			}
		}
	}
/**
 * 申请售后时当是全单申请时status改为SUCCESS
 */
 	private function checkOrderChange($tid){
 		if(empty($tid)){
 			return false;
 		}
		$map = array(
			'tid' => $tid,
			'aftersales_status'    => array('in', array('CANCEL_APPLY','NO_APPLY'))
		);
 		$isAll = $this->dOrder->getOrderInfo($map);
		if(empty($isAll)){
			$data['status'] = 'SUCCESS';
			$this->dOrder->editTradeInfo($tid,$data,'status');				
		}
 	}
/*
 * 取消商品（可做单个退款用）
 * 
 * */
	public function cancelGoods(){
		$tid=I('tid');
		if($tid){
			$items=$this->dOrder->getOrderItems($tid);
			$res=$this->dOrder->getThisOrderInfo($tid,'cancel_reason');
			$this->assign('mark',$res['cancel_reason']);
			$this->assign('tid',$tid);
			$this->assign('items',$items);
			$this->display();
		}else{
			echo 'Can not find pay bill!';
		}
	}
//同步京东订单前取消同步指定商品
	public function cancelSyncGoods(){
		$oids=I('oids');
		$tid=I('tid');
		$reason=I('reason');
		$mark=I('mark');
		if(empty($oids)){
			$this->error('请选择要取消的商品！');
		}
		if($reason==='0'){
			$this->error('请选择售后原因！');
		}
		if(empty($mark)){
			$this->error('请填写取消理由！');
		}		
		$orderItemInfo=$this->dOrder->getOidsOrderInfo($oids,'oid,num,sku_id,title');
		//改变订单状态为申请退款
		$data['order_status']='REFUND';
		$data['cancel_reason']=$mark;
		$res=$this->dOrder->editTradeInfo($tid,$data);
		//写进管理员操作日志表
		$oidsString=implode(',',$oids);		
		$dealType="申请退款";
		$remarks='商品取消,原因：'.$reason.',描述：'.$mark;
		$this->markTradeLog($tid,$dealType,$remarks,$oidsString);	
		//写进申请售后表
		$tradeInfo=$this->dOrder->findThisTradeInfo($tid,'user_id,shop_id,tid');
		$tradeInfo['reason']=$reason;
		$tradeInfo['description']="管理员:".$this->adminName."申请(取消商品)";
		$tradeInfo['aftersales_type']='ONLY_REFUND';
		$tradeInfo['modified_time']=time();		
		$tradeInfo['created_time']=time();		
		foreach($orderItemInfo as $key=>$value){
			$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
			$tradeInfo['oid']=$value['oid'];
			$tradeInfo['title']=$value['title'];
			$tradeInfo['num']=$value['num'];
			$this->dOrder->cancelThisGoods($value['oid'],$value['num']);  //商品置disabled
			$this->dAftersales->addAftersales($tradeInfo);				
		}
		//全单申请时改变trade表status为SUCCESS
		$this->checkOrderChange($tid);
		
		if($res){
			echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>商品取消成功，等待审核退款！</div>";
		}	
	}	
//申请退货
	public function returnItem(){
		$tid=I('tid');
		if($tid){
			$items=$this->dOrder->getOrderItems($tid);
			$res=$this->dOrder->getThisOrderInfo($tid,'cancel_reason');
			$this->assign('mark',$res['cancel_reason']);
			$this->assign('tid',$tid);
			$this->assign('items',$items);
			$this->display();
		}else{
			echo 'Can not find pay bill!';
		}
	}
//申请退货添加
	public function saveReturnItem(){
		$tid=I('tid');
		$mark=I('mark');
		$order=I('order');
		$orderStatus=I('orderStatus');
		$reason=I('reason');
		$logi=trim(I('logi'));
		$logiNo=trim(I('logiNo'));
		$orderArr=explode("#", $order);
		array_pop($orderArr);
		foreach($orderArr as $key=>$value){
			$valueArr=explode("-", $value);
			$item[$key]['oid']=$valueArr[0];
			$item[$key]['num']=$valueArr[1];
			$item[$key]['title']=$valueArr[2];
			$oids[]=$valueArr[0];
			$details[]=$valueArr[1].'x'.$valueArr[2];
		}
		switch($orderStatus){
			case 1:
				$situation="未收到货";
				break;
			case 2:
				$situation="已收到货";
				break;
			case 3:
				$situation="已拒收";
				break;								
		}		
		if($tid){
			$condition['oid']=array('in',$oids);
			$tradeInfo=$this->dOrder->findThisTradeInfo($tid,'user_id,shop_id,tid');
			$tradeInfo['description']=$mark;
			$tradeInfo['aftersales_type']='REFUND_GOODS';
			$tradeInfo['modified_time']=time();
			$tradeInfo['created_time']=time();
			$data['order_status']="RETURN";
			$data['cancel_reason']="收货情况：".$situation.",管理员:".$this->realName."代用户申请<br/>描述：".$mark;
			$res=$this->dOrder->editTradeInfo($tid,$data);
		}
		//写进售后申请表、改变订单子表
		$tradeInfo['order_status']=$orderStatus;
		if($orderStatus != 2){
			$logiArr['content']="管理员:".$this->realName."代用户申请";
		}else{
			$logiArr['logi']=$logi;
			$logiArr['logi_no']=$logiNo;
			$logiArr['content']="收货情况：".$situation.",管理员".$this->realName."代用户申请";
		}
		$logiJson=json_encode($logiArr);
		$tradeInfo['sendback_data']=$logiJson;
		$tradeInfo['reason']=$reason;
		foreach($item as $key=>$value){
			if($value['oid']){
				$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
				$tradeInfo['oid']=$value['oid'];
				$tradeInfo['title']=$value['title'];
				$tradeInfo['num']=$value['num'];
				$tradeInfo['status']=1;
				$this->dOrder->editOrderForConfirmGoods($value['oid'],$value['num']);
				$this->dAftersales->addAftersales($tradeInfo);
			}
		}
		//写进管理员操作日志表
		$oidsString=implode(',',$oids);		
		$dealType="申请退货";
		$logData['tid']=$tid;
		$remarks="收货情况：".$situation.'原因:'.$reason.'描述:'.$mark.'  详情:'.implode(',', $details).'回寄信息：'.$logi.':'.$logiNo;
		$this->markTradeLog($tid,$dealType,$remarks,$oidsString);	
		//全单申请时改变trade表status为SUCCESS
		$this->checkOrderChange($tid);			
		echo json_encode($res);
	}	
/*
 * 添加备注、该订单
 * */
	public function addMemo(){
		$tid=I('tid');
		$shopMemo=I('shopMemo');
		if(empty($shopMemo)){
			$this->error('备注不能为空!');
		}
		$data['shop_memo']=trim($shopMemo);
		//加入管理员订单日志表
		$dealType="订单备注";
		$remarks=$data['shop_memo'];
		$logData['tid']=$tid;
		$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
		$oldMemo=$this->dOrder->getThisOrderInfo($tid,'shop_memo');//备注记录叠加
		if(!empty($oldMemo['shop_memo'])){
			$data['shop_memo']=$oldMemo['shop_memo'].'<br/>'.$data['shop_memo'].'<br/>'.date('Y-m-d H:i:s');
		}
		$res=$this->dOrder->editTradeInfo($tid,$data);//添加备注
		if($res){
			$this->success('备注成功');
		}else{
			$this->success('备注失败，请重试....');
		}
	}
/*
 * 标记订单状态为备货中页面
 * */	
	public function toStock(){
		$tid=I('tid');
		if($tid){
			$items=$this->dOrder->getOrderItems($tid);
			foreach($items as $key=>$value){
				$supplierIds[]=$value['supplier_id'];
			}
			if(!empty($supplierIds)){
				$supplierInfo=$this->dGoods->getConditionsupplierUser($supplierIds);
				foreach($items as $key => $value){
					foreach($supplierInfo as $keys=>$values){
						if($value['supplier_id'] == $values['supplier_id']){
							$items[$key]['supperName']=$values['company_name'];
						}
					}
				}				
			}	
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
			$data['trade_status']='IN_STOCK';
			$res=$this->dOrder->editTradeInfo($tid,$data);//添加备注，订单状态改为备货中
		}
		$condition['oid']=array('in',$oids);
		$SendData['status']='IN_STOCK';
		$SendData['to_stork_mark']=$this->realName."&nbsp;：&nbsp;".$mark."&nbsp;&nbsp".date('Y-m-d H:i:s',time());
		$SendData['modified_time']=time();
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
		$tid=I('tid');
		if($tid){
			//取出该订单的地址信息
			$res=$this->dOrder->getThisOrderInfo($tid,'shop_id,tid,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address,ziti_memo,pay_time');
			$itemInfo=$this->dOrder->getThisTradeInfo($tid,'oid,title,sendnum,num,spec_nature_info,to_stork_mark,supplier_id,send_type,status,disabled,aftersales_status');
			foreach($itemInfo as $key=>$value){
				$supplierIds[]=$value['supplier_id'];
			}
			if($res['shop_id']==10){
				$res['JdOrderNumber']=$this->dOrder->getThisJdOrderNumber($res['tid']);
			}else{
				if(!empty($supplierIds)){
					$supplierInfo=$this->dGoods->getConditionsupplierUser($supplierIds);
					foreach($itemInfo as $key => $value){
						foreach($supplierInfo as $keys=>$values){
							if($value['supplier_id'] == $values['supplier_id']){
								$itemInfo[$key]['supperName']=$values['company_name'];
							}
						}
					}				
				}				
			}
			$this->assign('info',$res);
			$this->assign('item',$itemInfo);
			$this->display();
		}
	}
	/*
	 * 京东发货
	 * $istask为计划任务
	 * */	
	public function sendGoodsByJd($istask=0,$orderId,$tid){
		header("Content-type:text/html;charset=utf-8");
		if(empty($orderId)){
			$orderId=I('orderId');
		}
		if(empty($tid)){
			$tid=I('tid');
		}
		if($orderId){
			$url=C('COMMON_API')."Api/getJdInfoForExpress/";
			$data=array(
				'orderId'=>$orderId,
			);
			$return=$this->requestPost($url,$data);
			$resu=json_decode(trim($return,chr(239).chr(187).chr(191)),true);	
			if($resu['result']==100 && $resu['errcode']==0){
				$logiNo=$resu['data'];
			}
		}
		if(empty($logiNo)){
			$urlExpress=C('COMMON_API')."Api/getExpress/";
			$data=array(
				'orderId'=>$orderId
			);
			$res=$this->requestPost($urlExpress,$data);
			$resu=json_decode(trim($res,chr(239).chr(187).chr(191)),true);	
			if($resu['data']!="该订单没有配送信息"){
				$logiNo=$orderId;
			}else{
				$isno = 1; //将退出此次
				echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>该订单还未发货！</div>";
			} 			
		}
		if(!empty($tid) && !empty($logiNo) && $isno!=1){
			$this->sendGoodsDeal($istask,'Jd',$tid,$logiNo);
		}
		
	}	
/*
 * 发货处理数据传输管理员发货
 * 
 * */
 	public function sendGoodsGetData(){
		$logId=I('logId');
		$tid=I('tid');
		$logiNo=trim(I('logiNo'));
		$oids=I('oid'); 
		if(empty($oids)){
			$this->error('请选择发货的商品!');
		}	
		$this->sendGoodsDeal(0,'self',$tid,$logiNo,$logId,$oids);
 	}
/*
 * 发货处理
 * */	
	public function sendGoodsDeal($istask,$type,$tid,$logiNo,$logId,$oids){
		if($type=='Jd'){
			$logId=3;
		}	
		if(!empty($logId) && !empty($logiNo) && !empty($tid)){
			//快递公司信息
			$expressCom=$this->dOrder->getThisExpressInfo($logId);
			$data=$this->dOrder->getThisOrderInfo($tid);
			$data['logi_no']=$logiNo;
			$data['logi_id']=$expressCom['corp_id'];
			$data['logi_name']=$expressCom['corp_name'];
			$data['logi_code']=$expressCom['logi_no'];
			$data['logi_code']=$expressCom['corp_code'];
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
				if($type=='Jd'){
					$orderInfo=$this->dOrder->getThisTradeInfo($tid,'oid,num,item_id,title,sku_id,bn');
				}else if($type=='self'){
					$orderInfo=$this->dOrder->getOidsOrderInfo($oids,'oid,num,item_id,title,sku_id,bn');
				}
				$SendData['status']='WAIT_BUYER_CONFIRM_GOODS';
				$SendData['consign_time']=time();
				$SendData['modified_time']=time();				
				foreach($orderInfo as $key=>$value){
				//更改order表发货商品数量
					//京东全部发货
					$SendData['sendnum']=$value['num'];
					$this->dOrder->editThisOrderInfo($value['oid'],$SendData,'sendnum,status,consign_time,modified_time');
					$sendOids[]=$value['oid'];
				} 				
				foreach($orderInfo as $key=>$value){
					$orderInfo[$key]['oid']=$value['oid'];
					$orderInfo[$key]['sku_id']=$value['sku_id'];
					$orderInfo[$key]['sku_bn']=$value['bn'];					
					$orderInfo[$key]['number']=$value['num'];
					$orderInfo[$key]['sku_title']=$value['title'];
					$orderInfo[$key]['delivery_id']=$data['delivery_id'];
				}
				foreach($orderInfo as $key=>$value){
					$this->dOrder->addExpressDeatils($orderInfo[$key]);
				} 
				$nowstatus=$this->dOrder->getThisOrderInfo($tid,'status');
				if(in_array($nowstatus['status'], array('IN_STOCK','WAIT_SELLER_SEND_GOODS'))){
					//改变订单状态为待收货
					$dataStatus['status']='WAIT_BUYER_CONFIRM_GOODS';
					$dataStatus['trade_status']='WAIT_BUYER_CONFIRM_GOODS';
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
				$remarks="配送快递：".$expressCom['corp_name'].",快递单号：".$logiNo.",配送流水号".$data['delivery_id'];
				$logData['tid']=$tid;
				$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
				if($res){
					echo "订单:".$tid."标记发货！<br/>配送流水号:".$data['delivery_id'];
				}
			}
		}else{
			if(!$istask){
				$this->error('提交参数不完整！请重新检测输入');
			}
		}
		
	}
/*
 * 取出所有快递
 * 
 * */
	public function getAllexpree(){
		$res=$this->dOrder->getAllexpressInfo();
		echo json_encode($res);
	}
/*
 * 退款审核
 * */	
	public function refundProcess(){
		$tid=I('tid');
		if($tid){
			$this->getProcessData($tid,"WAIT_PROCESS");
			$this->display();
		}else{
			echo '请用正确方式进入';
		}
	}
/*
 *退款审核处理 
 * */
 	public function refundProcessDeal(){
 		$content=I('content','','trim');
		$refundPoint=I('refundPoint','','trim,intval');
		$refundCash=I('refundCash');
		$tid=I('tid');
		$oids=I('oids');
		$process=I('process','0','intval');
		$recoverOids=I('recoverOids');//灰复库存的商品子单号
		if(empty($oids)){
			$this->error('操作方式有误！');
		}		
		if($process == 0){
			$this->error('请选择审核结果！');
		}
		if(empty($content)){
			$this->error('请输入处理审核意见！');
		}
		M()->startTrans();
		try{
			if($process == 1){
				//审核通过
				if($refundCash>0 && !is_numeric($refundCash)){
					$this->error('请输入正确的金额！');
				}
				if($refundPoint<=0 && $refundCash<=0){
					$this->error('请输入正确的金额！');
				}
				//该订单的所有商品
				$allOrderInfo=$this->dOrder->getThisTradeInfo($tid,'oid,price,num,cash,point');
				//申请售后的商品
				$checkInfo=$this->dOrder->getOidsOrderInfo($oids,'oid,price,aftersales_num,cash,point');
				$compareOrderInfo=$checkInfo;
				foreach($compareOrderInfo as $key=>$value){
					$compareOrderInfo[$key]['num']=$value['aftersales_num'];
					unset($compareOrderInfo[$key]['aftersales_num']);
				}
				$compareRes=array_diff_assoc($allOrderInfo,$compareOrderInfo);  //比较两数据
				if(!empty($compareRes)){
					//未全申请售后--需比较商品价值和退款价值
					$checkFee = 0;
					$itemPoints = 0;
					$itemCashs = 0;
					foreach($checkInfo as $key=>$value){
						$checkFee += $value['price']*$value['aftersales_num']; //该有商品总值
						$itemPoints += $value['point']*$value['aftersales_num'];  //总积分
						$itemCashs += $value['cash']*$value['aftersales_num'];   //总现金
					}
					$checkFee= sprintf("%.2f", $checkFee);
					//现金+积分总值不要大于商品总值(先不现金积分各自对比)
					$itemCashs = sprintf("%.2f", $itemCashs);
					$itemTotal = $itemCashs + sprintf("%.2f", ($itemPoints/100));
					$refundCash = sprintf("%.2f", $refundCash);
					$refundTotal = $refundCash + sprintf("%.2f", ($refundPoint/100));
					if(bccomp($refundTotal,$itemTotal,2) == 1){
						$this->error('金额不能大于售后商品的总值！');
					}		
					//退回第三方支付平台金额不能大于商品的第三方金额
					if(bccomp($refundCash,$itemCashs,2) == 1){
						$this->error('退回第三方支付金额不能大于售后商品的第三方支付金额！');
					}		
						
				}
				//修改后面需退款的金额
				$field = 'user_id,shop_id,tid,total_fee,refund_fee,refund_point,refund_cash,refund_time,payed_fee,payed_point,payed_cash';
				$refundData=$this->dOrder->getThisOrderInfo($tid,$field);
				if(I('refundsType')){
					$refundData['refunds_type']=1;
				}
				//退款积分
				if(!empty($refundData['refund_point']) && empty($refundData['refund_time'])){
					$refundPoint = $refundPoint + $refundData['refund_point'];
				}
				//第三方支付平台退款金额
				if(!empty($refundData['refund_cash']) && empty($refundData['refund_time'])){
					$refundCash = $refundCash + $refundData['refund_cash'];
				}
				//总额比较
				$refundTotal = sprintf("%.2f", ($refundPoint/100)) + $refundCash;	
				if(bccomp($refundTotal,sprintf("%.2f", $refundData['payed_fee']),2) == 1){
					$this->error('退款金额大于支付金额，请重新输入退款金额！');
				}
				//退回第三方支付平台金额不能大于商品的第三方金额
				if(bccomp($refundCash,sprintf("%.2f", $refundData['payed_cash']),2) == 1){
					$this->error('退回第三方支付金额不能大于售后商品的第三方支付金额！');
				}				
				unset($refundData['refund_fee']);
				unset($refundData['refund_time']);
				$feeData['refund_point'] = $refundPoint;
				$feeData['refund_cash'] = $refundCash;
				$feeData['refund_time'] = 0;
				$feeRes=$this->dOrder->editTradeInfo($tid,$feeData);
				$status=1;
				$dealRes="REFUND_PROCESS";
				$remarks="审核结果：通过,<br/>退款金额：￥".$refundFee."<br/>,处理说明：".$content;
				//写进去退款申请表
					//读取该订单的信息
				$refundData['total_price']=$refundData['total_fee'];
				$aftersaleCondition['oid']=array('in',$oids);
				$refundInfo=$this->dAftersales->getAllThisAftersales($aftersaleCondition,'aftersales_bn,oid,shop_explanation');
				foreach($refundInfo as $key=>$value){
					$refundData['refund_bn']=date('ymdHis').rand(1000000,9999999);
					$refundData['aftersales_bn']=$value['aftersales_bn'];
					$refundData['oid']=$value['oid'];
					$refundData['refunds_reason']=$value['shop_explanation'];
					$refundData['created_time']=time();
					$refundData['trade_source']="mall";
					$this->dAftersales->addreFundData($refundData);
				}				
			}else if($process == 3){
				//审核不通过
				$status=3;
				$dealRes="SELLER_REFUSE";
				$remarks="审核结果：不通过,处理说明：".$content;
			}
			//写进管理员操作订单日志表
			$dealType="退款订单审核";
			$logData['tid']=$tid;
			$oidsString=implode(',',$oids);			
			$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
			//改变售后申请表状态
			$condition['oid']=array('in',$oids);
			$statusData['status']=$status;
			$statusData['shop_explanation']=$content;
			$statusData['modified_time']=time();
			$aftersaleRes=$this->dAftersales->editAftersales($condition,$statusData,'status,shop_explanation,modified_time');		
			//该订单分表的状态
			$data['aftersales_status']=$dealRes;
			$data['modified_time']=time();
			$statusRes=$this->dOrder->editThisConditionOrderInfo($condition,$data,'aftersales_status,modified_time');
			if($recoverOids){
				//恢复商品库存
				$orderItemInfo=$this->dOrder->getOidsOrderInfo($recoverOids,'oid,num,sku_id');
				foreach($orderItemInfo as $key=>$value){
					$this->dOrder->recoverGoodsStore($value['sku_id'],$value['num']);
				}					
			}			
		}catch (\Exception $e){
			M()->rollback();
			$msg = '操作失败'.$e->getMessage();
			$this->error($msg);
		}
		M()->commit();
		if(!empty($aftersaleRes) && !empty($statusRes)){
			echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>审核处理成功！</div>";
		}			

		
		
		
 	}
/*
 * 退货审核
 * */	
	public function returnProcess(){
		$tid=I('tid');
		$afterType=I('afterType');
		if($tid){
			$this->getProcessData($tid,"WAIT_PROCESS");
			$this->assign('afterType',$afterType);
			$this->display();
		}else{
			echo '请用正确方式进入';
		}
	} 
/**
 * 
 *审核（退款、退货）的数据取出
 *  
 * */
 	public function getProcessData($tid,$aftersalesStatus){
		$condition['tid']=$tid;
		if($aftersalesStatus){
			$condition['aftersales_status']=$aftersalesStatus;
			$condition['aftersales_num']=array('gt',0);
		}
		$field='oid,aftersales_num,title,price,num,aftersales_status,disabled,spec_nature_info,cash,point';
		$items=$this->dOrder->getThisConditionOrderInfo($condition,$field);
		$field='tid,cancel_reason,order_status,status,trade_status,payed_fee,payed_cash,payed_point';
		$res=$this->dOrder->getThisOrderInfo($tid,$field);
		$res['serviceStatus']=$this->orderStatusReturn($res['order_status']);
		$res['orderStatus']=$this->orderStatus($res['status']);
		//取出退货的理由等信息
		$aftersaleCondition['tid']=$tid;
		$aftersaleCondition['trade_source']="mall";
		$aftersaleInfo=$this->dAftersales->getAllThisAftersales($aftersaleCondition,'oid,reason,description,evidence_pic,order_status');
		foreach($items as $key=>$value){
			foreach($aftersaleInfo as $keys=>$values){
				if($value['oid']==$values['oid']){
					$items[$key]['reason']=$values['reason'];
					$items[$key]['description']=$values['description'];
					if(!empty($values['evidence_pic'])){
						$items[$key]['evidence_pic']=array_filter(explode(',', $values['evidence_pic']));
					}
					$items[$key]['orderStatus']=$values['order_status'];
				}
			}
		}	
		$this->assign('orderInfo',$res);
		$this->assign('items',$items); 		
 	}
/*
 *退货审核处理 
 * */
 	public function returnProcessDeal(){
 		$content=trim(I('content'));
		$afterType=I('afterType');//存在时为审核换货、维修，不存在时为审核退货
 		$addr=trim(I('addr'));
		$refuseAway=I('refuseAway');//是否拒收1,3：拒收   2：为拒收    收货情况1-未收到  2-已收货 3-已拒收
		$addrPhone=I('addrPhone');
		$addrName=I('addrName');
		$tid=I('tid');
		$oids=I('oids');
		if(empty($oids)){
			$this->error('操作方式有误！');
		}		
		$process=I('process');
		if(!empty($afterType)){
			$dealType="换货/维修审核";
		}else{
			$dealType="退货审核";
		}		
		if($process!=0){
			if(empty($content)){
				$this->error('请输入处理审核意见！');
			}
			if($process==1){
				//审核通过
				$dealRes="WAIT_BUYER_SEND_GOODS";
				if(!empty($afterType) || (empty($afterType) && $refuseAway==2)){
					if(empty($addr)){
						$this->error('请输入用户退货邮寄的地址！');
					}
					if(empty($addrPhone)){
						$this->error('请输入用户退货邮寄的需填的手机号！');
					}	
					if(empty($addrName)){
						$this->error('请输入用户退货邮寄的收货人姓名！');
					}	
					$referArr['phone']=$addrPhone;
					$referArr['name']=$addrName;
					$referArr['addr']=$addr;
					$referArr['content']=$content;
					$referJson=json_encode($referArr);														
				}
				$status=1;
				if(!empty($afterType)){
					$dealRes="WAIT_BUYER_SEND_GOODS";
					$progress=1;	
				}else{
					if($refuseAway==1 || $refuseAway==3){
						//已拒收   ---状态直接等待商家收货
						$dealRes="WAIT_SELLER_CONFIRM_GOODS";
						$progress=2;
					}else if($refuseAway==2){
						//未拒收  ----待用户回寄
						$dealRes="WAIT_BUYER_SEND_GOODS";
						$progress=1;
					}
				}
				$pro=$this->orderStatusLastReturn($dealRes);
				$remarks="审核结果：通过,状态:".$pro.",邮寄地址：".$addr.",收货人姓名：".$addrName."收货手机号：".$addrPhone.",管理员留言：".$content;
			}else if($process==3){
				//审核不通过
				$status=3;
				$dealRes="SELLER_REFUSE";
				$remarks="审核结果：不通过,管理员留言：".$content;
			}
			$condition['oid']=array('in',$oids);
			$statusData['status']=$status;   //审核进度
			if(!empty($afterType)){
				$statusData['progress']=$progress;   //进度
			}			
			$statusData['shop_explanation']=$referJson;
			$statusData['modified_time']=time();
			$aftersaleRes=$this->dAftersales->editAftersales($condition,$statusData,'status,progress,shop_explanation,modified_time');		
			//该订单分表的状态
			$data['aftersales_status']=$dealRes;
			$data['modified_time']=time();
			$statusRes=$this->dOrder->editThisConditionOrderInfo($condition,$data,'aftersales_status,modified_time');
			//写进管理员操作订单日志表
			$logData['tid']=$tid;
			$oidsString=implode(',',$oids);			
			$this->markTradeLog($tid,$dealType,$remarks,$oidsString);			
			if(!empty($aftersaleRes) && !empty($statusRes)){
				echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>审核处理成功！</div>";
			}
		}else{
			$this->error('请选择审核结果！');
		}
		
		
 	}
/*
 * 回寄审核
 * */	
	public function returnSendProcess(){
		$tid=I('tid');
		$afterType=I('afterType');//如存在为换修 需商家回寄
		if(!empty($afterType)){
			//收货人信息
			$res=$this->dOrder->getThisOrderInfo($tid,'shop_id,tid,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address,ziti_memo');
			$this->assign('info',$res);			
		}
		if($tid){
			$this->getProcessData($tid,"WAIT_SELLER_CONFIRM_GOODS");
			$oids=$this->dOrder->getFieldThisConditionOrderInfo(array('aftersales_status'=>'WAIT_SELLER_CONFIRM_GOODS','tid'=>$tid),'oid');
			$afterCondition['tid']=$tid;
			if(!empty($oids)){
				$afterCondition['oid']=array('in',$oids);
				$afterInfo=$this->dAftersales->getAllThisAftersales($afterCondition,'shop_explanation,sendback_data');
				foreach($afterInfo as $key=>$value){
					$addrJson=$value['shop_explanation'];
					$sendJson=$value['sendback_data'];
					break;
				}
				$addrInfo=json_decode($addrJson,TRUE);
				$sendInfo=json_decode($sendJson,TRUE);
			}
			$this->assign('addrInfo',$addrInfo); 		
			$this->assign('sendInfo',$sendInfo); 
			$this->assign('afterType',$afterType);
			$this->display();
		}else{
			echo '请用正确方式进入';
		}
	} 
/*
 *回寄审核处理 
 * */
 	public function returnSendProcessDeal(){
		$afterType=I('afterType');//存在时为审核换货、维修，不存在时为审核退货
		$content=I('content','','trim');
		$tid=I('tid');
		$oids=I('oids');
		$refundPoint=I('refundPoint','','trim,intval');
		$refundCash=I('refundCash');		
		$logi=trim(I('logi'));
		$logiNo=trim(I('logi_no'));
		if(empty($oids)){
			$this->error('操作方式有误！');
		}		
		if(!empty($afterType)){
			if(empty($logi) || empty($logiNo)){
				$this->error('请输入快递名称或快递单号！');
			}
		}
		if(empty($content)){
			if(empty($afterType)){
				$this->error('请输入处理审核意见！');
			}else{
				$this->error('请输入发货说明！');
			}
		}
		if(empty($afterType) && $refundPoint<=0 && $refundCash<=0){
			$this->error('请输入正确的金额！');
		}
		M()->startTrans();
		try{
			if(empty($afterType)){
				//退货
				//修改后面需退款的金额
				if($refundCash>0 && !is_numeric($refundCash)){
					$this->error('请输入正确的金额！');
				}			
				$checkInfo=$this->dOrder->getOidsOrderInfo($oids,'oid,price,aftersales_num,cash,point');
				foreach($checkInfo as $key=>$value){
					$checkFee += $value['price']*$value['aftersales_num']; //该有商品总值
					$itemPoints += $value['point']*$value['aftersales_num'];  //总积分
					$itemCashs += $value['cash']*$value['aftersales_num'];   //总现金
				}
				$field = 'user_id,shop_id,tid,total_fee,refund_fee,refund_point,refund_cash,refund_time,payed_fee,payed_point,payed_cash,post_fee';
				$refundData=$this->dOrder->getThisOrderInfo($tid,$field);			
				$checkFee= sprintf("%.2f", $checkFee);
				//现金+积分总值不要大于商品总值(先不现金积分各自对比)
				$itemCashs = sprintf("%.2f", $itemCashs);
				$itemTotal = $itemCashs + sprintf("%.2f", ($itemPoints/100)) + sprintf("%.2f", $refundData['post_fee']);
				$refundCash = sprintf("%.2f", $refundCash);
				$refundTotal = $refundCash + sprintf("%.2f", ($refundPoint/100));
				if(bccomp($refundTotal,$itemTotal,2) == 1){
					$this->error('金额(可包含运费)不能大于售后商品的总值！');
				}		
				//退回第三方支付平台金额不能大于商品的第三方金额
				if(bccomp($refundCash,$itemCashs,2) == 1){
					$this->error('退回第三方支付金额不能大于售后商品的第三方支付金额！');
				}				
				if(I('refundsType')){
					$refundData['refunds_type']=1;
				}
				//退款积分
				if(!empty($refundData['refund_point']) && empty($refundData['refund_time'])){
					$refundPoint = $refundPoint + $refundData['refund_point'];
				}
				//第三方支付平台退款金额
				if(!empty($refundData['refund_cash']) && empty($refundData['refund_time'])){
					$refundCash = $refundCash + $refundData['refund_cash'];
				}			
				//总额比较
				$refundTotal = sprintf("%.2f", ($refundPoint/100)) + $refundCash;	
				if(bccomp($refundTotal,sprintf("%.2f", $refundData['payed_fee']),2) == 1){
					$this->error('退款金额大于支付金额，请重新输入退款金额！');
				}
				//退回第三方支付平台金额不能大于商品的第三方金额
				if(bccomp($refundCash,sprintf("%.2f", $refundData['payed_cash']),2) == 1){
					$this->error('退回第三方支付金额不能大于售后商品的第三方支付金额！');
				}	
				unset($refundData['refund_fee']);
				unset($refundData['refund_time']);
				$feeData['refund_point'] = $refundPoint;
				$feeData['refund_cash'] = $refundCash;			
				$feeData['refund_time']=0;
				$feeRes=$this->dOrder->editTradeInfo($tid,$feeData);	
				//写进去退款申请表
					//读取该订单的信息
				$refundData['total_price']=$refundData['total_fee'];
				$aftersaleCondition['oid']=array('in',$oids);
				$refundInfo=$this->dAftersales->getAllThisAftersales($aftersaleCondition,'aftersales_bn,oid,shop_explanation');
				foreach($refundInfo as $key=>$value){
					$refundData['refund_bn']=date('ymdHis').rand(1000000,9999999);
					$refundData['aftersales_bn']=$value['aftersales_bn'];
					$refundData['oid']=$value['oid'];
					$refundData['refunds_reason']=$value['shop_explanation'];
					$refundData['created_time']=time();
					$refundData['trade_source']="mall";
					$this->dAftersales->addreFundData($refundData);
				}	
				$remarks="确认收到回寄商品：管理员备注".$content;
				$statusData['shop_explanation']=$content;
				$data['aftersales_status']="REFUND_PROCESS";
				$resultSay="处理成功,等待财务退款！";
			}else{
				//换货、维修
				$remarks="回寄成功:,快递名称：".$logi.",快递单号：".$logiNo."发货说明：".$content;
				$data['aftersales_status']="SELLER_SEND_GOODS";
				$ExpressArr['logi']=$logi;
				$ExpressArr['logi_no']=$logiNo;
				$ExpressArr['content']=$content;
				$ExpressJson=json_encode($ExpressArr);				
				$statusData['sendconfirm_data']=$ExpressJson;
				//收货状态已处理
				$statusData['status']=2;
				$resultSay="回寄成功，等待用户收货！";
			}
			//写进管理员操作订单日志表
			$oidsString=implode(',',$oids);		
			$dealType="回寄审核";
			$logData['tid']=$tid;
			$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
			//改变售后申请表状态/内容
			$condition['oid']=array('in',$oids);
			$statusData['modified_time']=time();
			$aftersaleRes=$this->dAftersales->editAftersales($condition,$statusData,'shop_explanation,sendconfirm_data,status');		
			//该订单分表的状态
			$data['modified_time']=time();
			$statusRes=$this->dOrder->editThisConditionOrderInfo($condition,$data,'aftersales_status,modified_time');
		}catch (\Exception $e){
			M()->rollback();
			$msg = '操作失败'.$e->getMessage();
			$this->error($msg);			
		}	
		M()->commit();	
		if(!empty($aftersaleRes) && !empty($statusRes)){
			echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>".$resultSay."</div>";
		}
		
		
	}
/*
 * 申请换货/维修
 * 
 * */
	public function changeItem(){
		$tid=I('tid');
		if($tid){
			$items=$this->dOrder->getOrderItems($tid);
			$res=$this->dOrder->getThisOrderInfo($tid,'cancel_reason');
			$this->assign('mark',$res['cancel_reason']);
			$this->assign('tid',$tid);
			$this->assign('items',$items);
			$this->display();
		}else{
			echo 'Can not find pay bill!';
		}
	}
/*
 * 申请换货/维修处理
 * */
	public function savechangeItem(){
		$tid=I('tid');
		$mark=I('mark');
		$order=I('order');
		$applyType=I('applyType'); //申请类型----1换货   2维修
		$reason=I('reason');
		$logi=trim(I('logi'));
		$logiNo=trim(I('logiNo'));		
		$orderArr=explode("#", $order);
		array_pop($orderArr);
		foreach($orderArr as $key=>$value){
			$valueArr=explode("-", $value);
			$item[$key]['oid']=$valueArr[0];
			$item[$key]['num']=$valueArr[1];
			$item[$key]['title']=$valueArr[2];
			$oids[]=$valueArr[0];
			$details[]=$valueArr[1].'x'.$valueArr[2];
		}
		if($tid){
			$tradeInfo=$this->dOrder->findThisTradeInfo($tid,'user_id,shop_id,tid');
			$tradeInfo['description']=$mark;
			$tradeInfo['modified_time']=time();
			$tradeInfo['created_time']=time();
			if($applyType==1){
				//换货
				$data['order_status']="EXCHANGE";
				$tradeInfo['aftersales_type']='EXCHANGING_GOODS';
				$logType="申请换货";
			}else if($applyType==2){
				//维修
				$data['order_status']="REPAIR";
				$tradeInfo['aftersales_type']='REPAIRING_GOODS';
				$logType="申请维修";
			}
			$data['cancel_reason']="管理员:".$this->realName."代用户申请<br/>描述：".$mark;
			$res=$this->dOrder->editTradeInfo($tid,$data);
		}
		//写进售后申请表、改变订单子表
		$logiArr['logi']=$logi;
		$logiArr['logi_no']=$logiNo;
		$logiArr['content']="管理员:".$this->realName."代用户申请";
		$logiJson=json_encode($logiArr);
		$tradeInfo['sendback_data']=$logiJson;
		$tradeInfo['reason']=$reason;		
		foreach($item as $key=>$value){
			if($value['oid']){
				$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
				$tradeInfo['oid']=$value['oid'];
				$tradeInfo['title']=$value['title'];
				$tradeInfo['num']=$value['num'];
				$this->dOrder->editOrderForConfirmGoods($value['oid'],$value['num']);
				$this->dAftersales->addAftersales($tradeInfo);
			}
		}
		//写进管理员操作日志表
		$oidsString=implode(',', $oids);		
		$dealType=$logType;
		$logData['tid']=$tid;
		$remarks='申请理由:'.$mark.'  详情:'.implode(',', $details);
		$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
		//全单申请时改变trade表status为SUCCESS
		$this->checkOrderChange($tid);
				
		echo json_encode($res);
	}			
/*
 * 退款初核
 * */	
	public function refundEarlyProcess(){
		$tid=I('tid');
		if($tid){
			$this->getProcessData($tid,"WAIT_EARLY_PROCESS");
			$this->display();
		}else{
			echo '请用正确方式进入';
		}
	}
	
/*
 *初审核处理 
 * */
 	public function EarlyProcessDeal(){
 		$content=trim(I('content'));
		$tid=I('tid');
		$oids=I('oids');
		$process=I('process');
		$afterTppe=I('afterTppe');//灰复库存的商品子单号
		if(empty($oids)){
			$this->error('操作方式有误！');
		}		
		if(empty($process)){
			$this->error("请选择处理结果");
		}
		if(empty($content)){
			$this->error("请先填写备注处理说明");
		}
		switch($afterTppe){
			case 'REFUND':
				$aftertype="退款申请";
				break;
			case 'RETURN':
				$aftertype="退货申请";
				break;
			case 'EXCHANGE':
				$aftertype="换货申请";
				break;
			case 'REPAIR':
				$aftertype="维修申请";
				break;												
		}
		if($process==1){
			//初审核通过，继续第二步审核
			$status=1;
			$dealRes="WAIT_PROCESS";
			$remarks="初审结果：通过,处理说明：".$content;
			$orderData['cancel_reason']=$content;
		}else if($process==2){
			//取消退款（征得用户同意）
			$status=2;
			$dealRes="CANCEL_APPLY";
			$remarks="用户同意取消".$aftertype.",处理说明：".$content;
			$orderData['order_status']="CANCEL_".$afterTppe;
		}
		//订单主表改变
		$orderData['modified_time']=time();
		$res=$this->dOrder->editOrderStatusData($tid,$orderData);
		//写进管理员操作订单日志表
		$dealType=$aftertype."初核";
		$logData['tid']=$tid;
		$oidsString=implode(',',$oids);			
		$this->markTradeLog($tid,$dealType,$remarks,$oidsString);
		//改变售后申请表状态
		$condition['oid']=array('in',$oids);
		$statusData['status']=$status;
		$statusData['shop_explanation']=$content;
		$statusData['modified_time']=time();
		$aftersaleRes=$this->dAftersales->editAftersales($condition,$statusData,'status,shop_explanation,modified_time');		
		//该订单分表的状态
		$data['aftersales_status']=$dealRes;
		$data['modified_time']=time();
		$statusRes=$this->dOrder->editThisConditionOrderInfo($condition,$data,'aftersales_status,modified_time');
		if(!empty($aftersaleRes) && !empty($statusRes) && !empty($res)){
			echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>审核处理成功！</div>";
		}
		
 	}	
/*
 * 售后详情
 * */	
 	public function aftersaleDeatil(){
 		$tid=I('tid');
		$afterType=I('afterType');
		if($tid){
			$this->getProcessData($tid);
			$this->assign('afterType',$afterType);
			$this->display();
		}else{
			echo '请用正确方式进入';
		}		
		
 	}	
/*
 * 
 * 取消售后申请
 * */
	public function cancelAftersale(){
		$tid=I('tid');
		if($tid){
			$condition['tid']=$tid;
			$condition['aftersales_status']=array('in',array('WAIT_EARLY_PROCESS','WAIT_PROCESS'));
			$items=$this->dOrder->getThisConditionOrderInfo($condition,'oid,title');
			$res=$this->dOrder->getThisOrderInfo($tid,'tid,order_status,status,payed_fee');
			$res['serviceStatus']=$this->orderStatusReturn($res['order_status']);
			$res['orderStatus']=$this->orderStatus($res['status']);
			$this->assign('orderInfo',$res);
			$this->assign('items',$items); 			
			$this->display();
		}else{
			echo '请用正确方式进入';
		}		
	}
/*
 * 
 * 取消售后申请处理
 * */
	public function cancelAftersaleDeal(){
		$tid=I('tid');
		$oids=I('oids');
		$content=I('content'); //部分取消售后
		$allOid=I('allOid');   //全单取消售后
		$serviceType=I('serviceType');  //售后类型
		if($tid){
			if(empty($allOid) && empty($oids)){
				$this->error("请选择要取消售后的商品！");
			}
			if(empty($content)){
				$this->error("请输入取消原因！");
			}	
			$condition['tid']=$tid;
			$condition['aftersales_status']=array('in',array('WAIT_EARLY_PROCESS','WAIT_PROCESS'));
			$oldOids=$this->dOrder->getFieldThisConditionOrderInfo($condition,'oid');
			if(count($oldOids) == count($oids)){
				$allOid=1;
			}
			if($allOid==1){
				//全单取消
				$data = array(
					'order_status' => 'CANCEL_'.$serviceType,
					'status' => array('exp', 'trade_status'),
				);
				$res=$this->dOrder->editTradeInfo($tid,$data);	
				$nocanelCondition['tid']=$tid;
				$nocanelCondition['aftersales_status']=array('in',array('WAIT_EARLY_PROCESS','WAIT_PROCESS'));
				$oids=$this->dOrder->getFieldThisConditionOrderInfo($nocanelCondition,'oid');
			}
			//部分取消				
			$OrderCondition['oid']=array('in',$oids);
			$oidsString=implode(',',$oids);			
			$orderData['aftersales_status']='CANCEL_APPLY';
			$orderData['disabled']=0;
			$afterData['modified_time']=time();
			$afterData['is_delete']=1;
			$orderRes=$this->dOrder->editThisConditionOrderInfo($OrderCondition,$orderData,'aftersales_status,disabled');
			$aftersaleRes=$this->dAftersales->editAftersales($OrderCondition,$afterData,'is_delete');	
			//写进管理员操作订单日志表
			$dealType="取消售后申请";
			$logData['tid']=$tid;
			$remarks="取消申请售后，备注：".$content;
			$this->markTradeLog($tid,$dealType,$remarks,$oidsString);					
			if($orderRes && $aftersaleRes){
				echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>取消售后申请成功！</div>";
			}
		}
	}
/*
 * 之前的售后数据写进售后表及退款表
 * */
	public function sysnOldAftersaleOrder(){
		$startTime=time();
		echo "程序执行时间：".date("Y-m-y H:i:s",time())."<br/>";
		$allAfterTrade=M('systrade_trade')->where(array('order_status'=>array('not in','NO_APPLY,NO_APPLY_CANCEL')))->field('tid,order_status')->select();
		foreach($allAfterTrade as $key=>$value){
			$afterTids[]=$value['tid'];
		}
		$allAfterOrderWhere=array(
			'aftersales_status'=>array('not in','NO_APPLY,NO_APPLY_CANCEL'),
			'tid'              =>array('in',$afterTids)
		);
		$allAfterOrder=M('systrade_order')->where($allAfterOrderWhere)->field('user_id,shop_id,oid,tid,title,aftersales_status,aftersales_num')->select();
		$refundSuccess=M('systrade_refund')->field('tid,mark')->select();
		foreach($allAfterOrder as $key=>$value){
			foreach($allAfterTrade as $keys=>$values){
				if($value['tid']==$values['tid']){
					$allAfterOrder[$key]['order_status']=$values['order_status'];
				}
			}	
			foreach($refundSuccess as $keyr=>$valuer){
				if($value['tid']==$valuer['tid']){
					$allAfterOrder[$key]['description']=$valuer['mark'];
					$allAfterOrder[$key]['refunds_reason ']=$valuer['mark'];
				}
			}						
		}
		foreach($allAfterOrder as $key=>$value){
			if(in_array($value['order_status'], array('REFUND','RETURN'))){
				if(in_array($value['aftersales_status'], array("REFUND_PROCESS","SUCCESS"))){
					$allRefundOrder[$value['oid']]=$value;//需写入退款申请表
				}
			}
		}	
		//售后表已存在的oid
		$nowOids=M('systrade_aftersales')->getField('oid',TRUE);
		foreach($nowOids as $key=>$value){
			$screenOids[$value]=$value;
		}
		echo "开始写入systrade_aftersales,记入日志时间：".date("Y-m-y H:i:s",time())."<br/>";
		foreach($allAfterOrder as $key => $value){
			if(!array_key_exists($value['oid'], $screenOids)){
				if($value['order_status']=="REFUND"){
					$value['aftersales_type']='ONLY_REFUND';
				}else if($value['order_status']=="RETURN"){
					$value['aftersales_type']='REFUND_GOODS';
				}else if($value['order_status']=="EXCHANGE"){
					$value['aftersales_type']='EXCHANGING_GOODS';
				}else if($value['order_status']=="REPAIR"){
					$value['aftersales_type']='REPAIRING_GOODS';
				}
				if($value['aftersales_status']=="SUCCESS"){
					$value['status']=2;
				}else if(in_array($value['aftersales_status'], array("WAIT_EARLY_PROCESS","WAIT_PROCESS"))){
					$value['status']=0;
				}else if($value['aftersales_status']=="SELLER_REFUSE"){
					$value['status']=3;
				}else{
					$value['status']=1;
				}
				$value['aftersales_bn']=date('ymdHis').rand(100000,999999);
				$value['modified_time']=time();
				$res[]=M('systrade_aftersales')->data($value)->add();
			}
			//写进管理员操作订单日志表
			$this->markTradeLog($value['tid'],"系统录入v1.0退款订单","v1.0版本的售后订单，录入新系统");
			sleep(1);	
		}
		//退款表表已存在的oid
		$nowRefundOids=M('systrade_refunds')->getField('oid',TRUE);
		foreach($nowRefundOids as $key=>$value){
			$screenRefundOids[$value]=$value;
		}
		$afterRes=M('systrade_aftersales')->where(array('tid'=>array('in',$afterTids)))->field('aftersales_bn,oid')->select();
		foreach($allRefundOrder as $key=>$value){
			foreach($afterRes as $keys=>$values){
				if($key==$values['oid']){
					$allRefundOrder[$key]['aftersales_bn']=$values['aftersales_bn'];
				}
			}
			unset($allRefundOrder[$key]['description']);
			unset($allRefundOrder[$key]['order_status']);
		}
		echo "开始写入systrade_refunds时间：".date("Y-m-y H:i:s",time())."<br/>";
		foreach($allRefundOrder as $key => $value){
			if(!array_key_exists($value['oid'], $screenRefundOids)){
				$value['refund_bn']=date('ymdHis').rand(1000000,9999999);
				$value['modified_time']=time();
				if($value['aftersales_status']=="SUCCESS"){
					$value['status']=1;
				}else if($value['aftersales_status']=="SELLER_REFUSE"){
					$value['status']=2;
				}else{
					$value['status']=0;
				}				
				$resu[]=M('systrade_refunds')->data($value)->add();
			}
			sleep(1);
		}
		if(!empty($res) && !empty($resu)){
			$endTime=time();
			echo '-_-录入成功-_- 时间：！'.date("Y-m-y H:i:s",time())."<br/>";
		}else{
			$endTime=time();
			echo '^-^好像有点问题^-^ 时间：'.date("Y-m-y H:i:s",time())."<br/>";
		}
		echo '总耗时：'.date('s',($startTime-$endTime)).'秒';
		
	}
/*
 * 改变发货类型
 * */
	public function changeSendType(){
		$sendType=I('sendType');
		$tid=I('tid');
		$oid=I('oid');
		$ret = array('code'=>0,'msg'=>'Unkonw');
		if(empty($oid) || empty($sendType) || empty($tid)){
			$ret['msg'] = '需要oid或发货类型';
			$this->ajaxReturn($ret);
		}
		M()->startTrans();
		if($sendType == 1){
			$data['send_type'] =2;
			$sendName="代发";
			//设置成代发加入供应订单表表
			//取出供应商的id
			$map = array(
				'oid' => $oid
			);
			$supplierId = $this->dOrder->getFieldOrder($map,'supplier_id');
			if(!$supplierId){
				$ret['msg'] = '不存在供应商';
				$this->ajaxReturn($ret);
			}
			//取出支付单号
			$paymentId = $this->dOrder->tradePaymentId($tid);
			if(!$paymentId){
				$ret['msg'] = '无法获取订单的支付单号';
				$this->ajaxReturn($ret);				
			}
			//查看供应订单表
			$map = array(
				'payment_id' => $paymentId,
				'supplier_id' => $supplierId
			);
			$stid = $this->dOrder->getSupplierTrade($map,'stid');
			if(!$stid){
				//需要新加进供应商订单表
				$field = 'user_id,com_id,payed_fee,receiver_name,created_time,receiver_state,receiver_city,
				receiver_district,receiver_address,receiver_zip,receiver_mobile,pay_time';
				$tradeInfo = $this->dOrder->getThisOrderInfo($tid,$field);
				$tradeInfo['status'] = 'WAIT_SELLER_SEND_GOODS';
				$tradeInfo['payment_id'] = $paymentId;
				$tradeInfo['supplier_id'] = $supplierId;
				$tradeInfo['payed_time'] = $tradeInfo['pay_time'];
				$num = 1;
				$stid = date(ymdHis).$num.$tradeInfo['user_id'];
				$tradeInfo['stid'] = $stid;
				$res = $this->dOrder->addSupplierTrade($tradeInfo);
				if(!$res){
					M()->rollback;
					$ret['msg'] = '需要新加进供应商订单失败';
					$this->ajaxReturn($ret);					
				}
			}
			$map = array(
				'stid' => $stid,
				'oid' => $oid
			);			
			$soid = $this->dOrder->getSupplierOrder($map,'soid');
			if(!$soid){
				$num = 2;
				$supplierOrder = array(
					'soid' => date(ymdHis).$num.$tradeInfo['user_id'],
					'stid' => $stid,
					'tid'  => $tid,
					'oid'  => $oid,
					'created_time' => time()
				);
				$res = $this->dOrder->addSupplerOrder($supplierOrder);
				if(!$res){
					M()->rollback;
					$ret['msg'] = '需要新加进供应商子订单失败';
					$this->ajaxReturn($ret);				
				}			
			}
		}else if($sendType == 2){
			$data['send_type'] = 1;
			$sendName="自发";
		}
		$map = array(
			'oid' => $oid
		);
		$res = $this->dOrder->updateOrderInfo($map,$data);
		if(!$res){
			M()->rollback;
			$ret['msg'] = '更改发货类型失败';
			$this->ajaxReturn($ret);				
		}	
		//写进管理员操作订单日志表
		$dealType="更改商品发货类型";
		$remarks="更改该商品的发货类型为".$sendName;
		$this->markTradeLog($tid,$dealType,$remarks,$oid);	
		M()->commit();
		$ret['code'] = 1;
		$ret['msg'] = '更改发货类型成功';
		$this->ajaxReturn($ret);				
				
	}
/*
 * 重新同步京东订单
 * */
	public function syncJdOrder(){
		$tid=I('tid');
		if($tid){
			$paymentId=$this->dOrder->getTradePaymentId($tid);
			$JdOrderNumber=$this->dOrder->getThisJdOrderNumber($tid);
			if(!empty($JdOrderNumber)){
				//同步订单订单支付
				$this->syncJdOrderPay($tid,$paymentId);		
			}else{
				//先下单到京东
				$url=C('COMMON_API')."Order/apiSyncOrder/";
		        $syncData = array(
		            	'paymentId'=>$paymentId,
		            	'opType'=>'creat',
		            	'tid'=>$tid
					);
				$return=$this->requestPost($url,$syncData);
				$resu=json_decode(trim($return,chr(239).chr(187).chr(191)),true);					
				if($resu['result']==100 && $resu['errcode']==0){
					//下单成功
					$this->syncJdOrderPay($tid,$paymentId);		
				}else{
					//退款失败
					echo json_encode(array(0,$resu['msg']));
					exit;
				}				
			}

		}
		
		
	}
/*
 * 同步京东订单支付
 * */ 
 	public function syncJdOrderPay($tid,$paymentId){
		$url=C('COMMON_API')."Order/apiSyncOrder/";
        $syncData = array(
        	'paymentId'=>$paymentId,
        	'opType'=>'pay',
        	'tid'=>$tid
		);
		$return=$this->requestPost($url,$syncData);
		$resu=json_decode(trim($return,chr(239).chr(187).chr(191)),true);	
		if($resu['result']==100 && $resu['errcode']==0){
			//支付成功
			echo json_encode(array(1,$resu['msg']));
			exit;					
		}else{
			//退款失败
			echo json_encode(array(0,$resu['msg']));
			exit;
		}			
 	}
	
	//顺丰取消订单
	public function cancelSf(){
		$this->display();
	}
	
	public function cancelPostSf(){
		$paymentId=I('paymentId');
		$url=C('COMMON_API')."sf/cancelPostSf/";
        $param = array(
        	'paymentId'=>$paymentId
		);
		$return=$this->requestPost($url,$param);
		$result=json_decode(trim($return,chr(239).chr(187).chr(191)),true);	
		if($result['result']==100 && $result['errcode']==0){
			//成功
			echo json_encode(array(1,$result['msg']));
			exit;					
		}else{
			//失败
			echo json_encode(array(0,$result['msg']));
			exit;
		}
	} 
	
	public function sendPostSf(){
		$paymentId=I('paymentId');
		$url=C('COMMON_API')."sf/orderPostSf/";
        $param = array(
        	'paymentId'=>$paymentId
		);
		$return=$this->requestPost($url,$param);
		$result=json_decode(trim($return,chr(239).chr(187).chr(191)),true);	
		if($result['result']==100 && $result['errcode']==0){
			//成功
			echo json_encode(array(1,$result['msg']));
			exit;					
		}else{
			//失败
			echo json_encode(array(0,$result['msg']));
			exit;
		}
	}
		
}