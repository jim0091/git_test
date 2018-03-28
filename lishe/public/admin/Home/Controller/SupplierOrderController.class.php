<?php  
namespace Home\Controller;
use Org\Util\Excel;
class SupplierOrderController extends CommonController{
/**
 * 供应商订单管理
 * 2017/2/28
 * @author zhangrui
 **/
	public function __construct(){
		parent::__construct();
		
	} 
    /**
     * 全部订单
	 *@author Zhangrui
     */
    public function all(){
		$map = array(
			'status' => array('not in', 'TRADE_CLOSED_BY_SYSTEM,TRADE_CLOSED_BY_USER,TRADE_CLOSED_BY_ADMIN,WAIT_BUYER_PAY,TRADE_CLOSED_BEFORE_PAY')
		);
		$this->showDeal($map);
		$this->display();
    }
    
    /**
     * 待发货订单
	 *@author Zhangrui
     */
    public function waitSend(){
//		$map = array(
//			'status' => array('in', 'WAIT_SELLER_SEND_GOODS,IN_STOCK'),
//			'aftersales_status' => 'NO_APPLY',
//		);		
		$map = array(
			'sendnum' => 0,
			'aftersales_status' => 'NO_APPLY',
		);			
		$this->showDeal($map, 'nosend');
		$this->display('SupplierOrder/all');	
    }
    
    /**
     * 已发货订单
	 *@author Zhangrui
     */
    public function sended(){
//		$map = array(
//			'status' => array('in', 'WAIT_BUYER_CONFIRM_GOODS,WAIT_COMMENT,TRADE_FINISHED'),
//		);
		$map = array(
			'sendnum' => array('gt', 0),
		);	    	
		$this->showDeal($map, 'sended');
		$this->display('SupplierOrder/all');	
    }
    
    /**
     * 售后订单
	 *@author Zhangrui
     */
    public function afterSale(){
		$map = array(
			'aftersales_status' => array('not in', array('NO_APPLY','CANCEL_APPLY')),
		);			
		$this->showDeal($map, 'aftersale');
		$this->display('SupplierOrder/all');	
    }
	/**
	 *订单显示处理 
	 *@author Zhangrui
	 **/
	private function showDeal($map, $type){
		$p = I('get.p', 1, 'intval');
		$search = array();
		$search['title'] = I('title', '', 'trim,strip_tags,stripslashes'); //标题
		$search['keyword'] = I('keyword', '', 'trim,strip_tags,stripslashes');  //关键字
		//下单时间
		$search['cStart'] = I('cStart', '', 'trim,strip_tags,stripslashes'); 
		$search['cEnd'] = I('cEnd', '', 'trim,strip_tags,stripslashes');
		//支付时间
		$search['pStart'] = I('pStart', '', 'trim,strip_tags,stripslashes'); 
		$search['pEnd'] = I('pEnd', '', 'trim,strip_tags,stripslashes');	
		//支付单号
		$search['paymentId'] = I('paymentId', '', 'trim,strip_tags,stripslashes');	
		$search['supplierId'] = I('supplierId', 0, 'intval');	
		//订单状态
		$search['orderStatus'] = I('orderStatus','all','strip_tags,stripslashes');
		//商城单号
		$search['tid'] = I('tid','','trim,strip_tags,stripslashes');
		if(empty($map)){
			return false;
		}
		//条件下取出符合子订单号
		//供应商
		if($search['supplierId']){
			$map['supplier_id'] = $search['supplierId'];
		}
		//订单状态
		if($search['orderStatus'] != 'all'){
			$map['status'] = $search['orderStatus'];
		}
		$map['send_type'] = 2;
		$map['pay_time'] = array('gt' ,0);
		//搜索条件
			//商品名称
		if(!empty($search['title'])){
			$map['title'] = array('like','%'.$search['title'].'%');
		}	
			//商城单号
		if(!empty($search['tid'])){
			$map['tid'] = $search['tid'];
		}
		//发货时间
		if(!empty($search['pStart']) && !empty($search['pEnd'])){
			$map['consign_time'] = array('between',array(strtotime($search['pStart']),strtotime($search['pEnd']." +24 hours")));
		}	
		$retOids = M('systrade_order')->where($map)->order('oid desc')->getField('oid',TRUE);
		if(!empty($retOids)){
			$map = array(
				'oid' => array('in',$retOids),
			);
		}else{
			$map = array(
				'oid' => 0,
			);
		}
		$stids = M('supplier_trade_order')->where($map)->order('created_time desc')->getField('stid',TRUE);
		$stids = array_unique($stids);
		//取出子订单对应的供应商订单号
		//搜索条件
		//关键字
		$map = array();
		if(!empty($search['keyword'])){
			$map['stid|receiver_name|receiver_mobile|payment_id'] = array('like','%'.$search['keyword'].'%');
		}
		//下单时间
		if(!empty($search['cStart']) && !empty($search['cEnd'])){
			$map['created_time'] = array('between',array(strtotime($search['cStart']),strtotime($search['cEnd']." +24 hours")));
		}
		if(!empty($map) && !empty($stids)){
			$map['stid'] = array('in', $stids);
			$stids = M('supplier_trade')->where($map)->order('created_time desc')->getField('stid',TRUE);		
		}
		$stids = array_unique($stids);
		if(I('execlType') && I('execlType') == "exportExcel"){
			//订单导出execl
			if(empty($stids)){
				exit('暂无订单信息!');
			}
			$execlData = $this->orderDeal($stids, $type , ture);
			$this->orderExportExcel($execlData,$type);         
		}			
		$num = count($stids);
		$size = 20;
		$start = $size*($p-1);
		//按订单创建时间排下序
		if($num >= $size){
			$orderTids = array_slice($stids,$start,$size);	
		}else{
			$orderTids = $stids;
		}
		$this->orderDeal($orderTids, $type);
		//所有供应商
		$map = array(
			'status' => 1
		);
		$supplierAll = M('supplier_user')->where($map)->field('supplier_id,company_name')->select();
		$this->assign('supplierAll',$supplierAll);		
		$this->assign('pageStr',showPage($num, $size));
		$this->assign('search',$search);
		$this->assign('count',$num);
	} 
	/**
	 *订单信息整合
	 *@author Zhangrui
	 **/
	private function orderDeal($tids, $status, $isExport=false){
		if(empty($tids)){
			return false;
		}
		//订单基本信息
		$map=array(
			'stid' => array('in' , $tids),
		);
		$trade = M('supplier_trade')->where($map)->order('created_time desc')->select();
		if(empty($trade)){
			return false;
		}
		//供应商Id (平台后台独有) 所属供应商
		$supplierIds = arrGetField($trade,'supplier_id'); 
		if(!empty($supplierIds)){
			$maps = array(
				'supplier_id' => array('in', $supplierIds)
			);
			$supplierInfo = M('supplier_user')->where($maps)->field('supplier_id,company_name')->select();
			$revSupplierInfo = keyValRev($supplierInfo,'supplier_id','company_name');
		}
		//订单内子订单信息
		$sOrderInfo = M('supplier_trade_order')->where($map)->order('created_time desc')->select();
		if(empty($sOrderInfo)){
			return false;
		}
		$oids = arrGetField($sOrderInfo,'oid'); 
		//订单商品信息
		$map=array(
			'oid' =>  array('in',$oids),
			'send_type' => 2
		);
		$map['pay_time'] = array('gt' ,0);
		if($status == 'nosend'){
			//待发货页面
			$map['aftersales_status'] = 'NO_APPLY';
//			$map['status'] = array('in',array('WAIT_SELLER_SEND_GOODS','IN_STOCK'));
			$map['sendnum'] = 0;
		}else if($status == 'sended'){
			//已发货页面
			$map['sendnum'] = array('gt' ,0);
//			$map['status'] = array('in',array('WAIT_BUYER_CONFIRM_GOODS','WAIT_COMMENT','TRADE_FINISHED'));
		}else if($status == 'aftersale'){
			//售后页面
			$map['aftersales_status'] = array('not in', array('NO_APPLY','CANCEL_APPLY'));
		}	
		$desc = "field(oid,".implode(",", $oids).")";
		$field = '*';//预定	
		$order = M('systrade_order')->where($map)->field($field)->order($desc)->select();
		$accordOids = arrGetField($order,'oid');  //所有符合条件的oid
		$oidInTid = array();
		foreach($sOrderInfo as $key=>$val){
			if(in_array($val['oid'], $accordOids)){
				$oidInTid[$val['stid']][] = $val['oid'];
			}
		}		
		//已oid为订单商品数组键
		$lastOrder =  keyValRev($order, 'oid');
		unset($order);	
		if($isExport){
			//导出execl 返回
			$returnArr = array(
				'trade' => $trade,
				'oidInTid' => $oidInTid,
				'order' => $lastOrder
			);
			return $returnArr;
		}else{
			//订单列表	
			$this->assign('trade',$trade);	
			$this->assign('oidInTid',$oidInTid);	
			$this->assign('order',$lastOrder);	
			$this->assign('supplierInfo',$revSupplierInfo);
		}
		
	} 
	/**
	 * 订单导出execl
	 *@author Zhangrui
	 **/
	 private  function orderExportExcel($execlData, $type){
		header("Content-type:text/html;charset=utf-8");
	 	$trade = $execlData['trade'];
	 	$oidInTid = $execlData['oidInTid'];
	 	$order = $execlData['order'];
		if(empty($trade) || empty($oidInTid) || empty($order)){
			exit('unkown error');
		}
		//商品sku指定信息
		$skuIds = arrGetField($order,'sku_id');
		$map = array(
			'sku_id' => array('in',$skuIds),
		);
		$field = 'sku_id,bn,barcode';
		$skuInfo = M('sysitem_sku')->where($map)->field($field)->select();
		$revSkuInfo =  keyValRev($skuInfo, 'sku_id');
		//所属供应商
		$supplierIds = arrGetField($order,'supplier_id');
		$map = array(
			'supplier_id' => array('in',$supplierIds),
		);
		$supplierInfo = M('supplier_user')->where($map)->getField('supplier_id,company_name');
		//退款时间
		$tids = array_unique(arrGetField($order,'tid'));
		$map = array(
			'tid' => array('in', $tids)
		);
		$refundTime = M('systrade_refund')->where($map)->getField('tid,modified_time');
		foreach($trade as $key => $val){
			foreach($oidInTid[$val['stid']] as $key1 => $val2){
				if($key1 == 0){
					//订单第一条
					$LastExecl[$val2]['tid'] = " ".$val['stid'];
				}else{
					$LastExecl[$val2]['tid'] = " ";
				}
					$LastExecl[$val2]['payment_id'] = " ".$val['payment_id'];
					$LastExecl[$val2]['supName'] = $supplierInfo[$val['supplier_id']];
					$LastExecl[$val2]['title'] = $order[$val2]['title'];
					$LastExecl[$val2]['barcode'] = " ".$revSkuInfo[$order[$val2]['sku_id']]['barcode'];
					$LastExecl[$val2]['bn'] = " ".$revSkuInfo[$order[$val2]['sku_id']]['bn'];
					$LastExecl[$val2]['spec_nature_info'] = $order[$val2]['spec_nature_info'];
					$LastExecl[$val2]['price'] = " ".$order[$val2]['price'];
					$LastExecl[$val2]['cost_price'] = " ".$order[$val2]['cost_price'];
					$LastExecl[$val2]['num'] = $order[$val2]['num'];
					$LastExecl[$val2]['buyer_message'] =  $order[$val2]['buyer_message'];	
				if($key1 == 0){
					$LastExecl[$val2]['receiver_name'] = $val['receiver_name'];
					$LastExecl[$val2]['receiver_mobile'] = $val['receiver_mobile'];
					$LastExecl[$val2]['address'] = $val['receiver_state'].$val['receiver_city'].$val['receiver_district'].$val['receiver_address'];
					$LastExecl[$val2]['created_time'] = date('Y-m-d H:i:s',$val['created_time']);
					$LastExecl[$val2]['pay_time'] = empty($val['payed_time']) ? '--' :date('Y-m-d H:i:s',$val['payed_time']);
					$LastExecl[$val2]['consign_time'] = empty($order[$val2]['consign_time']) ? '--' :date('Y-m-d H:i:s',$order[$val2]['consign_time']);
				}else{
					$LastExecl[$val2]['receiver_name'] = "";
					$LastExecl[$val2]['receiver_mobile'] = "";
					$LastExecl[$val2]['address']="";
					$LastExecl[$val2]['created_time'] = "";
					$LastExecl[$val2]['pay_time'] = "";	
					$LastExecl[$val2]['consign_time'] = "";
				}
				$LastExecl[$val2]['refund_time'] = empty($refundTime[$order[$val2]['tid']]) ? '--' :date('Y-m-d H:i:s',$refundTime[$order[$val2]['tid']]);//退款时间
			}
			
		}
		$ex=new Excel;
		if($type == 'nosend'){
			$title = "未发货清单";
		}else if($type == 'sended'){
			$title = "已发货清单";
		}else{
			$title = "订单列表";
		}
		$columnName=array('订单号','支付单号','供应商','商品名称','商品条形码','商品编号','商品属性','商品价格/元','进货价/元','商品数量','买家留言','收货人','手机','地址','下单时间','支付时间','发货时间','退款时间');
		$ex->getExcel($LastExecl,$columnName,$title.date('YmdHis'));
				
	 }
	/**
	 *订单详情 
	 *@author Zhangrui
	 * */
	public function detail(){
		$tid = I('tid');
		if(!is_numeric($tid)){
			exit('unkown error');
		}
		$map = array(
			'stid' => $tid
		);
		//基本信息
		$trade = M('supplier_trade')->where($map)->find();
		//商品信息
		$map=array(
			'stid' => $tid,
		);
		$order = M('supplier_trade_order')->where($map)->field('tid,oid')->select();
		if(empty($order)){
			exit('unkown error');
		}
		$oids = array();
		$tids = array();
		foreach($order as $key=>$val){
			$oids[] = $val['oid'];
			$tids[] = $val['tid'];
		}
		$map=array(
			'oid' => array('in',$oids),
		);
		$field = '*';//预定	
		$orderInfo = M('systrade_order')->where($map)->field($field)->select();
		$afterTids = array();  //存在售后的订单号
		foreach($orderInfo as $key=>$val){
			if($val['aftersales_status'] != 'NO_APPLY'){
				$afterTids[] = $val['tid'];
			}
		}
		if(!empty($afterTids)){
			//取出售后类型
			$map = array(
				'tid' => array('in', $afterTids),
			);
			$afterSType = M('systrade_trade')->where($map)->getField('order_status', TRUE);
			$this->assign('afterSType', $afterSType);
		}
		//物流信息
		$map = array(
			'supplier_id' => $trade['supplier_id'],
			'tid' => array('in',$tids),
 		);
		$field = 'delivery_id,post_fee,logi_name,logi_no,status,t_begin';
		$expressInfo = M('syslogistics_delivery')->where($map)->field($field)->select();
		$this->assign('trade',$trade);
		$this->assign('orderInfo',$orderInfo);
		$this->assign('expressInfo',$expressInfo);
		$this->display();
	} 
	/**
	 * 供应商订单监控
	 * 
	 * */
	public function orderMonitorin(){
		$p = I('get.p', 1, 'intval');
		$supplierId =I('get.supplierId');
		$this->waitSendMonitorins();  //待发货订单()
		$this->sendedMonitorin();	 //已发货订单
		//供应商列表
		$map = array(
			'status' => 1
		);		
		if($supplierId){
			$map['supplier_id'] = $supplierId;
		}
		$num = M('supplier_user')->where($map)->field('supplier_id')->count();
		$size = 20;
		$start = $size*($p-1);
		$limit = "{$start},$size";
		$supplierList = M('supplier_user')->where($map)->field('supplier_id,company_name')->limit($limit)->select();
		//所有供应商
		$map = array(
			'status' => 1
		);
		$supplierAll = M('supplier_user')->where($map)->field('supplier_id,company_name')->select();
		$this->assign('supplierAll',$supplierAll);				
		$this->assign('pageStr',showPage($num, $size));
		$this->assign('supplierList',$supplierList);
		$this->assign('supplierNum',$num);
		$this->display();
	}
	/**
	 * 监控的所有条件
	 * 供应商订单监控(待发货订单)
	 * */	
	private function waitSendMonitorins(){
		//代发货订单
		$map = array(
			'status' => array('in', 'WAIT_SELLER_SEND_GOODS,IN_STOCK'),
			'aftersales_status' => 'NO_APPLY',
			'send_type' => 2
		);	
		$orderInfo = M('systrade_order')->where($map)->field('oid,pay_time')->select();
		//预警订单
		$moreWaringOids = $this->getMonitorinOids($orderInfo);
		$alloids = arrGetField($orderInfo,'oid'); //所有未发货的子订单号
		//未发货订单
		$res = $this->monitorinDeal($alloids,true);
		$moreWaringTids = $this->getMonitorinTids($res['orderInfo'],$moreWaringOids['normalOids'],$moreWaringOids['redOids'],$moreWaringOids['yellowOids']);
		$revStrade = keyValRev($res['strade'], 'supplier_id', 'stid', 2);
		$normalTrade = array();
		$redWaringTrade = array();
		$yellowWarningTrade = array();
		foreach($revStrade as $key=>$val){
			foreach($val as $keys=>$vals){
				//有供应商ID的订单组
				if(in_array($vals, $moreWaringTids['normalTids'])){
					//正常
					$normalTrade[$key][] = $vals;
				}
				if(in_array($vals, $moreWaringTids['redTids'])){
					//红色预警
					$redWaringTrade[$key][] = $vals;
				}
				if(in_array($vals, $moreWaringTids['yellowTids'])){
					//黄色预警
					$yellowWarningTrade[$key][] = $vals;
				}								
			}		
		}
		$this->assign('waitSendCount',count(arrGetField($res['strade'], 'stid')));
		$this->assign('normalCount',count($moreWaringTids['normalTids']));
		$this->assign('redWaringCount',count($moreWaringTids['redTids']));
		$this->assign('yellowWarningCount',count($moreWaringTids['yellowTids']));
		$this->assign('waitSendTrade',$revStrade);			
		$this->assign('normalTrade',$normalTrade);			
		$this->assign('redWaringTrade',$redWaringTrade);			
		$this->assign('yellowWarningTrade',$yellowWarningTrade);			
	}
	/**
	 * 待发货订单取出正常、红色预警、黄色预警的子订单号
	 */
	private function getMonitorinOids($orderInfo){
		if(empty($orderInfo) || !is_array($orderInfo)){
			return array();
		}	
		$yellowStart = C('YELLOWWWARNINGLIMITTIME.START')*24*60*60;
		$yellowEnd = C('YELLOWWWARNINGLIMITTIME.END')*24*60*60;	
		$normalEnd = C('NORMALLIMITTIME')*24*60*60;		
		$redStart = C('REDWARNINGLIMITTIME')*24*60*60;
		$newTime = time();
		$normalOids = array();
		$redOids = array();
		$yellowOids = array();
		foreach($orderInfo as $key => $val){
			if(empty($val['pay_time'])){
				continue;
			}
			if(($newTime - $val['pay_time']) < $normalEnd){
				//正常的子订单号
				$normalOids[]=$val['oid'];
			}else if(($newTime - $val['pay_time']) >= $yellowStart && ($newTime - $val['pay_time']) < $yellowEnd){
				//黄色预警子订单
				$yellowOids[]=$val['oid'];
			}else if(($newTime - $val['pay_time']) >= $redStart){
				//红色预警子订单号
				$redOids[]=$val['oid'];
			}
		}	
		return array(
			'normalOids' => array_unique($normalOids),
			'yellowOids' => array_unique($yellowOids),
			'redOids'    => array_unique($redOids),
		);	
	}
	/**
	 * 待发货订单取出正常、红色预警、黄色预警的订单号
	 */
	private function getMonitorinTids($orderInfo,$normalOids=array(),$redOids=array(),$yellowOids=array()){
		if(empty($orderInfo) || !is_array($orderInfo)){
			return array();
		}
		$normalTids = array();
		$redTids = array();
		$yellowTids = array();		
		foreach($orderInfo as $key=>$val){
			if(in_array($val['oid'], $normalOids)){
				//正常的订单号
				$normalTids[] = $val['stid'];
			}
			if(in_array($val['oid'], $redOids)){
				//红色预警的订单号
				$redTids[] = $val['stid'];
			}			
			if(in_array($val['oid'], $yellowOids)){
				//黄色预警的订单号
				$yellowTids[] = $val['stid'];
			}			
		}		
		return array(
			'normalTids' => array_unique($normalTids),
			'redTids'    => array_unique($redTids),
			'yellowTids' => array_unique($yellowTids)
		);					
	}	
	/**
	 * 监控的所有条件
	 * 供应商订单监控(已发货订单)
	 * */	
	private function sendedMonitorin(){
		//已发货订单
		$map = array(
			'status' => array('in', 'WAIT_BUYER_CONFIRM_GOODS,WAIT_COMMENT,TRADE_FINISHED'),
			'send_type' => 2
		);	
		$retOids = M('systrade_order')->where($map)->getField('oid',TRUE);
		$strade = $this->monitorinDeal($retOids);
		$revStrade = keyValRev($strade, 'supplier_id', 'stid', 2);
		$this->assign('sendedCount',count(arrGetField($strade, 'stid')));
		$this->assign('sendedTrade',$revStrade);			
	}	
	/**
	 *订单查询的个数中间处理 
	 *@param $isNeedOid是否需要取出oid数据
	 */
	private function monitorinDeal($retOids,$isNeedOid=false){
		if(empty($retOids) || !is_array($retOids)){
			return array();
		}	
		$supplierTradeOrder = M('supplier_trade_order');		
		$map = array(
			'oid' => array('in',$retOids),
		);
		if($isNeedOid){
			$orderInfo = $supplierTradeOrder->where($map)->field('stid,oid')->order('created_time desc')->select();
			$stids = arrGetField($orderInfo,'stid'); //所有未发货的子订单号
			
		}else{
			$stids = $supplierTradeOrder->where($map)->order('created_time desc')->getField('stid',TRUE);
			$stids = array_unique($stids);	
		}
		//供应商订单号		
		if(empty($stids)){
			return array();
		}
		//订单基本信息
		$map=array(
			'stid' => array('in' , $stids),
		);
		$strade = M('supplier_trade')->where($map)->field('stid,supplier_id')->order('created_time desc')->select();
		if($isNeedOid){
			return array(
				'orderInfo' => $orderInfo,
				'strade' => $strade
			);
		}else{
			return $strade;
		}	
	}
	/**
	 * 有监控样式的供应商订单列表
	 * */
 	public function monitorinOrderList(){
		$p = I('get.p', 1, 'intval');	
		$search = array();
		$warningType = I('warningType','all','strip_tags,stripslashes');
		$search['title'] = I('title', '', 'trim,strip_tags,stripslashes'); //标题		
		$search['keyword'] = I('keyword', '', 'trim,strip_tags,stripslashes');  //关键字		
		//发货时间
		$search['sendStart'] = I('sendStart', '', 'trim,strip_tags,stripslashes'); 
		$search['sendEnd'] = I('sendEnd', '', 'trim,strip_tags,stripslashes');
		//订单推送时间
		$search['pStart'] = I('pStart', '', 'trim,strip_tags,stripslashes'); 
		$search['pEnd'] = I('pEnd', '', 'trim,strip_tags,stripslashes');
		//供应商	
		$search['supplierId'] = I('supplierId', 0, 'intval');			
		$search['warningType'] = $warningType;
		if($warningType == 'all'){
			//所有的订单
			$map = array(
				'status' => array('not in', 'TRADE_CLOSED_BY_SYSTEM,TRADE_CLOSED_BY_USER,TRADE_CLOSED_BY_ADMIN,WAIT_BUYER_PAY')
			);
		}else if($warningType == 'sended'){
			//已发货的
			$map = array(
				'status' => array('in', 'WAIT_BUYER_CONFIRM_GOODS,WAIT_COMMENT,TRADE_FINISHED'),
			);	  			
		}
		//条件下取出符合子订单号
		//供应商(搜索条件)
		if($search['supplierId']){
			$map['supplier_id'] = $search['supplierId'];
		}			
		$map['send_type'] = 2;
		//商品名称
		if(!empty($search['title'])){
			$map['title'] = array('like','%'.$search['title'].'%');
		}	
		//发货时间
		if(!empty($search['sendStart']) && !empty($search['sendEnd'])){
			$map['consign_time'] = array('between',array(strtotime($search['sendStart']),strtotime($search['sendEnd']." +24 hours")));
		}	
		if($warningType == 'all' || $warningType == 'sended'){
			$retOids = M('systrade_order')->where($map)->order('oid desc')->getField('oid',TRUE);
		}
		//用于取出有预定的订单的子单号	
		//预警订单(红色预警订单,黄色预警订单,正常订单,待发货订单)
		$map['status'] = array('in', 'WAIT_SELLER_SEND_GOODS,IN_STOCK');
		$map['aftersales_status'] = 'NO_APPLY';
		$orderInfo = M('systrade_order')->where($map)->field('oid,pay_time')->select();
		$moreWaringOids = $this->getMonitorinOids($orderInfo);	
		$waitsendOids = arrGetField($orderInfo,'oid');
		unset($orderInfo);
		$supplierTradeOrder = M('supplier_trade_order');
		if(!empty($waitsendOids)){
			$map = array(
				'oid' => array('in',$waitsendOids),
			);
			$sTradeInfo = $supplierTradeOrder->where($map)->field('stid,oid')->order('created_time desc')->select();
		}
		if($warningType == 'waitsend'){
			//待发货
			$retOids = $waitsendOids;
		}else if($warningType == 'redWaring'){
			$retOids = $moreWaringOids['redOids'];
		}else if($warningType == 'normal'){
			$retOids = $moreWaringOids['normalOids'];
		}else if($warningType == 'yellowWaring'){
			$retOids = $moreWaringOids['yellowOids'];
		}
		//所有几种未发货的类型的预警订单号
		$moreWaringTids = $this->getMonitorinTids($sTradeInfo,$moreWaringOids['normalOids'],$moreWaringOids['redOids'],$moreWaringOids['yellowOids']);
		unset($moreWaringOids);
		unset($sTradeInfo);
		if(!empty($retOids)){
			$map = array(
				'oid' => array('in',$retOids),
			);
		}else{
			$map = array(
				'oid' => 0,
			);
		}
		$stids = $supplierTradeOrder->where($map)->order('created_time desc')->getField('stid',TRUE);
		$stids = array_unique($stids);	
		//取出子订单对应的供应商订单号
		//搜索条件
		//关键字
		$map = array();
		if(!empty($search['keyword'])){
			$map['stid|receiver_name|receiver_mobile|payment_id'] = array('like','%'.$search['keyword'].'%');
		}
		//订单推送时间
		if(!empty($search['pStart']) && !empty($search['pEnd'])){
			$map['payed_time'] = array('between',array(strtotime($search['pStart']),strtotime($search['pEnd']." +24 hours")));
		}
		if(!empty($map)){
			$map['stid'] = array('in', $stids);
			$stids = M('supplier_trade')->where($map)->order('created_time desc')->getField('stid',TRUE);		
		}	
		$stids = array_unique($stids);	
		$num = count($stids);
		$size = 20;
		$start = $size*($p-1);
		//按订单创建时间排下序
		if($num >= $size){
			$orderTids = array_slice($stids,$start,$size);	
		}else{
			$orderTids = $stids;
		}
		$this->orderDeal($orderTids, $type);
		if(!empty($orderTids)){
			//催单备注
			$reminderOrder = M('supplier_reminder_order_remarks');
			$map = array(
				'stid' => array('in', $orderTids)
			);
			$reminders = $reminderOrder->where($map)->select();
			$revReminders = keyValRev($reminders, 'stid', '', 2);
			//催单记录
			$reminderRecord = M('supplier_trade_news')->where($map)->field('stid,creat_time')->order('id asc')->select();
			$revReminderRecord = keyValRev($reminderRecord, 'stid', 'creat_time', 1); 
		}
		//所有供应商
		$map = array(
			'status' => 1
		);
		$supplierAll = M('supplier_user')->where($map)->field('supplier_id,company_name')->select();
		$this->assign('supplierAll',$supplierAll);		
		$this->assign('pageStr',showPage($num, $size));
		$this->assign('count',$num);
		$this->assign('search',$search); 
		$this->assign('moreWaringTids',$moreWaringTids);  //可预订的订单号
		$this->assign('canReminder',array_merge($moreWaringTids['redTids'],$moreWaringTids['yellowTids'])); //可以催的订单号
		$this->assign('revReminders',$revReminders);
		$this->assign('reminderRecord',$revReminderRecord);
		$this->display();
 	}
	/**
	 * 催单备注
	 */
	public function reminderRemark(){
		$tid = I('tid');
		$type = I('type', 0, 'intval');
		if(!is_numeric($tid)){
			exit('Error 订单号有误!');
		}
		if($type == 1){
			$monitorin = '红色色预警催单';
		}else if($type == 2){
			$monitorin = '黄色预警催单';
		}else{
			exit('Error 预警类型有误!');
		}
		$this->assign('tid',$tid);
		$this->assign('monitorin',$monitorin);
		$this->display();
	} 
	/**
	 * 催单备注处理
	 */ 
 	public function reminderRemarkDeal(){
 		$data = I('post.data');
		$data['reminder_admin_id'] = $this->adminId;
		$data['reminder_admin_name'] = $this->realName;
		$data['reminder_ip'] = get_client_ip();
		$ret = array('code'=>0, 'msg'=>'unkonw');
		$reminderOrder = M('supplier_reminder_order_remarks');
		$res = $reminderOrder->data($data)->add();
		if(!$res){
			$ret['msg'] = '催单备注添加失败!';
		}else{
			$ret['code'] = 1;
			$ret['msg'] = '催单备注添加成功!';
		}
		$this->ajaxReturn($ret);		
 	}
	/**
	 * 催单备注
	 */ 
 	public function reminderShow(){
		$tid = I('tid');
		if(!is_numeric($tid)){
			exit('Error 订单号有误!');
		} 		
		$reminderOrder = M('supplier_reminder_order_remarks');
		$map = array(
			'stid' => $tid
		);
		$reminders = $reminderOrder->where($map)->select(); 	
		$this->assign('reminders',$reminders);	
 		$this->display();
 	}
	/**
	 * 催单页
	 */ 
 	public function reminderOrder(){
		$tid = I('tid');
		$supplierId = I('supplierId', 0, 'intval');
		$type = I('type', 0, 'intval');
		if(!is_numeric($tid)){
			exit('Error 订单号有误!');
		}  	
		$map = array(
			'supplier_id' => $supplierId,
			'sender_status' => 1
		);	
		$senderInfo = M('supplier_sender')->where($map)->field('sender,sender_phone,supplier_id')->find();
		if(empty($senderInfo)){
			exit('供应商暂未设置发货人或发货人被禁用！');
		}
		$content = "管理员，你好，您的礼舍平台订单：{$tid}截止目前还没有发货，如您已经发货，请及时登陆礼舍供应商平台维护发货信息；如您还未发货，请尽快发货后，登陆礼舍供应商平台维护发货信息，谢谢您的配合【礼舍科技】";
		$this->assign('tid',$tid);
		$this->assign('senderInfo',$senderInfo);
		$this->assign('content',$content);
		$this->assign('type',$type);
		$this->display();
 	}
 	/**
	 * 催单处理
	 */
	public function reminderOrderDeal(){
		$data = I('data');
		$ret = array('code'=>0, 'msg'=>'unkonw');
		//发送短信
		$sendRes = A('Recharge')->sendSms($data['sender_phone'],$data['content']);		
		if($sendRes){
			$ret['msg'] = '短信发送成功!';
		}else{
			$ret['msg'] = '短信发送失败!';
		}
		//预警类型
		if($data['type'] == 2){
			$type = '黄色';
		}else{
			$type = '红色';
		}
		//订单基本信息	
		$map = array(
			'stid' => $data['stid'],
			'supplier_id' => $data['supplier_id']
		);
		$tradeInfo = M('supplier_trade')->where($map)->find();
		//取出订单商品信息
		$map = array(
			'stid' => $data['stid'],
		);	
		$oids = M('supplier_trade_order')->where($map)->getField('oid',TRUE);
		if(empty($oids)){
			$ret['msg'] .= '没有符合的商品!';
			$this->ajaxReturn($ret);		
		}
		$map = array(
			'oid' => array('in', $oids),
			'status' => array('in',array('WAIT_SELLER_SEND_GOODS','IN_STOCK')),
			'aftersales_status' => 'NO_APPLY',
			'supplier_id' => $data['supplier_id'],
			'send_type' => 2,
		);
		$orderInfo = M('systrade_order')->where($map)->field('title,spec_nature_info,num')->select();	
		//消息内容
		$news = "尊敬的供应商，您有发货预警订单，订单信息如下：<br/>订单编号 {$tradeInfo['stid']}<br/>";
		foreach($orderInfo as $key=>$val){
			$news .= " 商品：{$val['title']} &nbsp;&nbsp;";
			if(!empty($val['spec_nature_info'])){
				$news .= "规格:{$val['spec_nature_info']} &nbsp;&nbsp;";
			}
			$news .= " 数量:{$val['num']} <br/>";
		}
 		$news .= "收货人：{$tradeInfo['receiver_name']}  &nbsp;&nbsp; {$tradeInfo['receiver_mobile']} &nbsp;&nbsp;";
 		$news .= "地址：{$tradeInfo['receiver_state']}{$tradeInfo['receiver_city']}{$tradeInfo['receiver_district']}{$tradeInfo['receiver_address']}<br/>";
		$news .= "订单由于长时间未发货，已经达礼舍的发货的{$type}预警，严重的影响了用户体验。为避免用户的投诉以及礼舍对供应商的发货效率评级，请及时发货，并填写物流信息。谢谢您的配合！（系统消息）";
		$note = array(
			'stid' => $data['stid'],
			'news_type' => 2,
			'supplier_id' => $data['supplier_id'],
			'news_theme' => '礼舍商品发货通知',
			'news_content' => $news,
			'reminder_admin_id' => $this->admin_id,
			'reminder_admin_name' => $this->realName,
			'ip' => get_client_ip()
		);
		$res = M('supplier_trade_news')->data($note)->add();
		if($res){
			$ret['code'] = 1;
			$ret['msg'] .= '消息推送成功!';
		}else{
			$ret['msg'] .= '消息推送失败!';
			
		}
		$this->ajaxReturn($ret);		
	}
 	/**
	 * 模糊搜索供应商显示
	 */
	public function getSupplierOption(){
		$keyword = I('keyword','','trim,strip_tags,stripslashes');
		$map = array(
			'status' => 1
		);
		if(!empty($keyword)){
			$map['company_name|username'] = array('like','%'.$keyword.'%');
		}
		$supplierList = M('supplier_user')->where($map)->field('supplier_id,company_name')->select();
		$option = '';
		foreach($supplierList as $key=>$val){
			$option .= "<option value=\"{$val['supplier_id']}\">{$val['company_name']}</option>";
		}
		$this->ajaxReturn($option);
	}
	
	
	
}