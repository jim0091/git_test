<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐
 * @version  	$Id: OrderModel.class.php v001 2016-08-29
 * @description 订单操作
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class OrderModel extends CommonModel{
	public function __construct(){
		$this->modelItem=M('sysitem_item');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelItemStore=M('sysitem_sku_store');
		$this->modelItemSku=M('sysitem_sku');
		
		$this->modelTrade=M('systrade_trade');
		$this->modelJd=M('systrade_sync_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelAccount=M('sysuser_account');
		$this->modelCompany=M('company_config');
		$this->modelShop=M('sysshop_shop');
		$this->modelShopSeller=M('sysshop_seller');
		$this->modelUser=M('sysuser_user');
		$this->modelCategory=M('syscategory_cat');
		
		$this->modelExpreeCom=M('syslogistics_dlycorp');
		$this->modelExpress=M('syslogistics_delivery');
		$this->modelExpressDetail=M('syslogistics_delivery_detail');
		
		$this->modelSingle=M('ectools_trade_paybill');
		$this->modelPayment=M('ectools_payments');
		
		$this->modelAtrade=M('company_activity_trade');
		$this->modelAorder=M('company_activity_order');
		$this->modelAInfo=M('company_activity_item');
		
		$this->modelTradeLog=M('system_admin_trade_log');	
		$this->modelAddr=M('site_area'); //收货地址表
		$this->modelActivity=M('company_activity');
		
		$this->modelRefundCash = M('systrade_refund_cash_record'); 
		
	}	
	//订单数量	
	public function getOrderCount($condition){
		return $this->modelTrade->where($condition)->count();
	}
	//订单	
	public function getOrder($condition,$limit){
		$field = 'tid,com_id,shop_id,user_id,receiver_name,receiver_state,receiver_city,receiver_district,
		receiver_address,receiver_mobile,created_time,payment,payed_fee,pay_time,status,trade_status,
		pay_type,order_status,refund_fee,post_fee,cash_fee,point_fee,payed_cash,payed_point,is_vip,trade_type';
		return $this->modelTrade->where($condition)->field($field)->order('created_time desc')->limit($limit)->select();
	}
	//售后订单	
	public function getAftersaleOrder($condition,$OrderTids){
		foreach($OrderTids as $key=>$value){
				$tids[]="'".$value."'";
		}
		if($tids){
			$order="field(tid,".implode(",", $tids).")";
		}
		return $this->modelTrade->field('tid,status,payed_fee,pay_type,order_status,post_fee')->where($condition)->order($order)->select();
	}	
	//订单-》导出execl
	public function getOrderExecl($condition){
		$condition['_string']='t.tid=o.tid';
		return M('')->table('systrade_order o,systrade_trade t')->where($condition)->field('t.tid,t.user_id,o.item_id,o.title,o.sku_id,o.spec_nature_info,o.price,o.num,o.supplier_id,o.send_type,t.com_id,t.shop_id,t.receiver_name,t.receiver_state,t.receiver_city,t.receiver_district,t.receiver_address,t.receiver_mobile,t.created_time,o.consign_time,t.payment,t.payed_fee,t.pay_time,t.status')->select();
	}
	//订单-》导出execl数量
	public function getOrderExeclCount($condition){
		$condition['_string']='o.tid=t.tid';
		return M('')->table('systrade_order o,systrade_trade t')->where($condition)->count();
	}
	//订单-》京东订单号订单
	public function getJdOrderNumber($tids){
		$condition['tid']= array('in',$tids);
		return $this->modelJd->where($condition)->field('tid,sync_order_id')->select();
	}		
	//订单中的所有商品
	public function getOrderItems($tids){
		$condition['tid']= array('in',$tids);
//		$condition['disabled']= 0;
		return $this->modelOrder->where($condition)->select();
	}	
//查找订单中指定字段
	public function getFieldOrder($map,$field){
		return $this->modelOrder->where($map)->getField($field);
	}
	//订单中的所有售后商品
	public function getOrderAfterItems($tids){
		$condition['tid']= array('in',$tids);
		$condition['aftersales_status']=array('not in','NO_APPLY,NO_APPLY_CANCEL,CANCEL_APPLY');
		return $this->modelOrder->where($condition)->select();
	}		
	//活动指定订单的所有商品
	public function getAllActivityItems($tids,$field){
		$condition['atid']=array('in',$tids);
		return $this->modelAorder->where($condition)->field($field)->select();
	}	
	//活动订单的所有商品
	public function getActivityItems($tid,$field){
		$condition['atid']=$tid;
		return $this->modelAorder->where($condition)->field($field)->select();
	}
	//活动订单指定条件取单个字段
	public function getOrderSomeField($condition,$field){
		return $this->modelAorder->where($condition)->getField($field,TRUE);
	}
	//下单者所属公司
	public function getThisCompany($comIds){
		$condition['tid']= array('in',$comIds);
		return $this->modelCompany->where($condition)->field('com_id,com_name')->select();		
	}
	//所有公司
	public function getAllCompany(){
		$condition['is_delete']=0;
		return $this->modelCompany->where($condition)->field('com_id,com_name')->select();
	}
	//所有店铺
	public function getAllShop(){
		return $this->modelShop->where($condition)->field('shop_id,shop_name,shop_descript,shop_type,open_time,shop_area,shop_addr,shopuser_name')->find();
	}
	//通过员工手机号取得员工id
	public function getUserId($phone){
		$condition['mobile']=$phone;
		return $this->modelAccount->where($condition)->getField('user_id');
	}
	//通过模糊查询取出符合的订单号
	public function getOrderItemsToNum($condition){
		return $this->modelOrder->where($condition)->order('modified_time desc')->getField('tid',true);
	}	
	//条件吸的符合的主订单号
	public function getConditionOrderTid($condition){
		return $this->modelTrade->where($condition)->order('created_time desc')->getField('tid',TRUE);
	}
/*计算改状态下的数量*/
	public function getOrderItemsNum($condition){
		return $this->modelOrder->where($condition)->field('tid,aftersales_status')->select();
	}		
	//取出一定分类下的所有商品id
	public function getAllCartItenIds($cartIds){
		$condition['cat_id']=array('in',$cartIds);
		return $this->modelItem->where($condition)->getField('item_id',true);
		
	}	
	//订单详情
	public function getOrderDetail($tid){
		$res=$this->modelTrade->where('tid='.$tid)->find();
		$info=$this->modelOrder->where('tid='.$tid)->select();
		$res['more']=$info;
		return $res;
	}
	//店铺信息
	public function getShopInfo($shopIds){
		$condition['shop_id']=$shopIds;
		return $this->modelShop->where($condition)->field('shop_id,shop_name,shop_descript,shop_type,open_time,shop_area,shop_addr,shopuser_name')->find();
	}
	//订单-》京东订单号订单
	public function getThisJdOrderNumber($tid){
		$condition['tid']= $tid;
		return $this->modelJd->where($condition)->getField('sync_order_id');
	}	
	//用户所属公司
	public function getUserCompany($comId){
		$condition['com_id']=$comId;
		return $this->modelCompany->where($condition)->getField('com_name');
	}	
	//用户信息
	public function getUserInfo($userId){
		return M('')->table('sysuser_user a,sysuser_account b')->where('a.user_id = b.user_id and a.user_id='.$userId)->field('a.name,a.birthday,a.userName,a.sex,a.reg_ip,a.regtime,a.source,b.mobile')->find();
	}
	//指定用户信息
	public function getThisUserName($userId){
		return $this->modelUser->where('user_id='.$userId)->getField('name');
	}
	//取出相应分类的id和名字
	public function getThisCategoryInfo($catId,$level){
		return $this->modelCategory->where('cat_id='.$catId.' and level='.$level)->field('cat_id,cat_name')->find();
	}
	//取出一定范围内分类id的名字
	public function getInCategoryInfo($catIds,$field="cat_id,cat_name"){
		$condition['cat_id']=array('in',$catIds);
		return $this->modelCategory->where($condition)->field($field)->select();
	}	
	//取出该信息的物流信息
	public function getExpress($tid){
		return $this->modelExpress->where('tid='.$tid)->field('delivery_id,post_fee,logi_name,logi_no,status,t_begin,supplier_id,logi_id')->select();
	}
	//取出指定部分订单号的物流信息
	public function getInExpress($tids){
		$condition['tid']=array('in',$tids);
		return $this->modelExpress->where($condition)->field('tid,delivery_id,post_fee,logi_name,logi_no,status,t_begin')->select();
	}	
	
	//取出相应条件的用户名
	public function getThiUserInfo($userIds){
		$condition['user_id']=array('in',$userIds);
		return $this->modelAccount->where($condition)->field('user_id,mobile')->select();
	}
	//查合单中的所有订单号
	public function getSingleOrderNums($tid){
		$paymentId=$this->modelSingle->where('tid='.$tid)->getField('payment_id');
		if($paymentId){
			$tids=$this->modelSingle->where('payment_id='.$paymentId)->field('status,payment_id,tid')->select();
		}
		return $tids;
	}
	//取出订单的支付单号
	public function getSomeSingleNums($tids,$order){
		$condition['tid']=array('in',$tids);
		return $this->modelSingle->where($condition)->field('payment_id,tid')->order($order)->select();
	}	
//取出订单的支付单号
	public function tradePaymentId($tid){
		$map = array(
			'tid' => $tid
		);
		return $this->modelSingle->where($map)->getField('payment_id');
	}
	//取出指定支付单号的订单号
	public function getPaymentToTids($paymentId){
		$condition=array(
			'payment_id'=>$paymentId,
		);
		return $this->modelSingle->where($condition)->getField('tid',TRUE);
	}
	//取出支付单的所有子订单
	public function getSingleForOrder($paymentIds){
			$condition['payment_id']=array('in',$paymentIds);
			return $this->modelSingle->where($condition)->field('payment_id,tid')->select();
	}
	//订单详情
	public function getOrderDetails($tids){
		$condition['tid']=array('in',$tids);
		$res=$this->modelTrade->where($condition)->select();
		$info=$this->modelOrder->where($condition)->field('tid,item_id,title,spec_nature_info,price,num,sendnum,total_fee,aftersales_status,aftersales_num')->select();
		foreach($res as $key=>$value){
			foreach($info as $keys=>$values){
					if($value['tid']==$values['tid']){
						$res[$key]['more'][]=$values;
					}
			}
		}
		return $res;
	}	
	//查合单中的所有订单号
	public function getTradePaymentId($tid){
		$bill=$this->modelSingle->where('tid='.$tid)->getField('payment_id',TRUE);
		if(!empty($bill)){
			foreach($bill as $key=>$value){
				$tids[]=$value;
			}
			$condition=array(
				'status'=>'succ',
				'payment_id'=>array('in',implode(',',$tids))
			);
			return $this->modelPayment->where($condition)->getField('payment_id');
		}else{
			return NULL;
		}
	}
	//取出订单支付方式
	public function getOrderPayName($paymentIds){
		$condition['payment_id']=array('in',$paymentIds);
		return $this->modelPayment->where($condition)->field('payment_id,pay_name,account,bank')->select();
	}
	//取出支付宝支付流水号一些信息
	public function getPayTradeNo($paymentId,$field){
		if(empty($paymentId)){
			return FALSE;
		}
		$condition['payment_id']=$paymentId;
		return $this->modelPayment->where($condition)->field($field)->find();		
	}	
	//查询活动订单 赵尊杰 2016-09-03
	public function getActivityTradeList($condition,$page=1,$pageSize=0){
    	$start=$this->pageStart($page,$pageSize);
    	$count=$this->modelAtrade->field('orderId')->where($condition)->count();
    	$order=$this->modelAtrade->where($condition)->order('creat_time DESC')->limit($start.','.$pageSize)->select();
    	return array('count'=>$count,'list'=>$order);
    }
	public function getActivityOrder($condition,$limit){
		return $this->modelAtrade->where($condition)->limit($limit)->order('creat_time DESC')->select();
	}
	public function getAcitityOrderCount($condition){
		return $this->modelAtrade->where($condition)->count();
	}
	//所有套餐信息
	public function getActivityInfo(){
		return $this->modelAInfo->field('aitem_id,market_price')->select();
	}
	//取出活动订单详情
	public function getThisAOrderDetail($tid){
		$condition['atid']=$tid;
		$res=$this->modelAtrade->where($condition)->find();
		$info=$this->modelAorder->where($condition)->select();
		$res['more']=$info;
		return $res;		
	}
	//取出订单的套餐信息
	public function getThisActivityInfo($aid){
		return $this->modelAInfo->where('aitem_id='.$aid)->find();
	}
//同步京东订单前取消同步指定商品 设置disabled=1	
	public function cancelThisGoods($oid,$num){
		$data['disabled']=1;
		$data['aftersales_status']="WAIT_PROCESS";
		$data['aftersales_num']=$num;
		$condition['oid']=$oid;
		return $this->modelOrder->where($condition)->data($data)->save();
	}
//查询京东sku
	public function getItemJdsku($itemIds){
		$condition['item_id']=array('in',$itemIds);
		return $this->modelItem->where($condition)->field('item_id,jd_sku')->select();
	}	
//申请退款等退款/退货/换货/维修
	public function editOrderStatus($tid,$datas,$type='REFUND'){
		 $datas['order_status']=$type;
		 $condition['tid']=$tid;
		 return $this->modelTrade->where($condition)->data($datas)->save();
	}
//order主单该数据
	public function editOrderStatusData($tid,$datas){
		 $condition['tid']=$tid;
		 return $this->modelTrade->where($condition)->data($datas)->save();
	}
//取出所所有商品
	public function getAllItem($itemIds,$field='item_id,cat_id,barcode'){
		$condition['item_id']=array('in',$itemIds);
		return $this->modelItem->where($condition)->field($field)->select();
	}
//取出所所有商品sku下
	public function getAllSkuItem($skuIds,$field='sku_id,item_id,bn,barcode'){
		$condition['sku_id']=array('in',$skuIds);
		return $this->modelItemSku->where($condition)->field($field)->select();
	}	
//修改systrade_trade表
	public function editTradeInfo($tid,$data,$field){
		$condition['tid']=$tid;
		return $this->modelTrade->where($condition)->data($data)->field($field)->save();		
	}
//修改systrade_order表至待审核
	public function editOrderInfo($oid,$num){
		$data['aftersales_status']="WAIT_PROCESS";
		$data['aftersales_num']=$num;
		$condition['oid']=$oid;
		return $this->modelOrder->where($condition)->field('aftersales_status,aftersales_num')->data($data)->save();
	}
//修改systrade_order表至待商家收货
	public function editOrderForConfirmGoods($oid,$num){
		$data['aftersales_status']="WAIT_SELLER_CONFIRM_GOODS";
		$data['aftersales_num']=$num;
		$condition['oid']=$oid;
		return $this->modelOrder->where($condition)->field('aftersales_status,aftersales_num')->data($data)->save();
	}	
//取出单条systrade_trade表数据
	public function getThisOrderInfo($tid,$field){
		$condition['tid']=$tid;
		return $this->modelTrade->where($condition)->field($field)->find();		
		
	}
//取出单条activity_trade表数据
	public function getThisActivityOrderInfo($tid,$field){
		$condition['atid']=$tid;
		return $this->modelAtrade->where($condition)->field($field)->find();		
	}	
//取出指定systrade_order表数据
	public function getThisTradeInfo($tid,$field){
		$condition['tid']=$tid;
		return $this->modelOrder->where($condition)->field($field)->select();		
	}
//取出指定范围的Oid  systrade_order表数据
	public function getOidsOrderInfo($oids,$field){
		$condition['oid']=array('in',$oids);
		return $this->modelOrder->where($condition)->field($field)->select();		
	}	
//取出指定systrade_order表单条数据
	public function findThisTradeInfo($tid,$field){
		$condition['tid']=$tid;
		return $this->modelTrade->where($condition)->field($field)->find();		
	}	
//取出指定systrade_order表数据
	public function getFieldThisTradeInfo($oid,$field){
		$condition['oid']=$oid;
		return $this->modelOrder->where($condition)->field($field)->find();		
	}	
//取出所有的快递
	public function getAllexpressInfo(){
		return $this->modelExpreeCom->field('corp_id,corp_name')->order('order_sort asc')->select();
	}
//取出指定快递信息
	public function getThisExpressInfo($logId){
			return $this->modelExpreeCom->where('corp_id='.$logId)->field('corp_id,corp_name,corp_code')->find();
	}
//取出店铺的一些信息
	public function getThisShopInfo($shopId){
		return $this->modelShopSeller->where('shop_id='.$shopId)->getField('seller_id');
	}
//发货添加至发货表
	public function sendGoodsaddExpree($data){
		return $this->modelExpress->data($data)->add();
	} 
//发货添加至发货详情表
	public function addExpressDeatils($data){
		return $this->modelExpressDetail->data($data)->add();
		
	}
//修改systrade_order表单条数据
	public function editThisOrderInfo($oid,$data,$field){
		return $this->modelOrder->where('oid='.$oid)->data($data)->field($field)->save();
	}
	public function updateOrderInfo($map,$data){
		return $this->modelOrder->where($map)->save($data);
	}	
//修改systrade_order表多条数据
	public function editThisConditionOrderInfo($condition,$data,$field){
		return $this->modelOrder->where($condition)->data($data)->field($field)->save();
	}
//查找订单条件systrade_order表多条数据
	public function getThisConditionOrderInfo($condition,$field){
		return $this->modelOrder->where($condition)->field($field)->select();
	}
	//查找订单条件systrade_order表多条数据field
	public function getFieldThisConditionOrderInfo($condition,$field){
		return $this->modelOrder->where($condition)->getField($field,TRUE);
	}
	/**
	 * 查找order、表数据
	 */
	public function getOrderInfo($condition){
		return $this->modelOrder->where($condition)->getField($field,TRUE);
	}
	//查找订单条件售后类型systrade_trade表多条数据field
	public function getFieldThisConditionTradeInfo($tids,$orderStatus){
		if(!empty($orderStatus)){
			$condition['order_status']=array('in',$orderStatus);
		}
		if($tids){
			$condition['tid']=array('in',$tids);
			$order="field(tid,".implode(",", $tids).")";
		}else{
			return null;
		}		
		return $this->modelTrade->where($condition)->order($order)->getField('tid',TRUE);
	}	
//查找订单条件systrade_order表单个字段多条数据多条数据
	public function getFieldConditionOrderInfo($aftersaleTids,$aftersalesStatus,$field){
		if($aftersaleTids){
			$condition['tid']=array('in',$aftersaleTids);
			$condition['aftersales_status']=$aftersalesStatus;
			foreach($aftersaleTids as $key=>$value){
					$tids[]="'".$value."'";
			}
			if($tids){
				$order="field(tid,".implode(",", $tids).")";
			}
			return $this->modelOrder->where($condition)->order($order)->getField($field,TRUE);		
		}else{
			return null;
		}
	}	
//查找订单条件systrade_order表单个字段多条数据多条数据
	public function getFieldConditionOrderTids($condition,$tids){
			foreach($tids as $key=>$value){
					$tids[]="'".$value."'";
			}
			if($tids){
				$order="field(tid,".implode(",", $tids).")";
			}
			return $this->modelOrder->where($condition)->order($order)->getField('tid',TRUE);		
	}		
//查找订单条件systrade_trade表单个字段多条数据多条数据
	public function getFieldConditionTradeTids($condition,$tids){
			foreach($tids as $key=>$value){
					$tids[]="'".$value."'";
			}
			if($tids){
				$order="field(tid,".implode(",", $tids).")";
			}
			return $this->modelTrade->where($condition)->order($order)->getField('tid',TRUE);		
	}		
//恢复商品库存
	public function recoverGoodsStore($skuId,$num){
		return $this->modelItemStore->where('sku_id='.$skuId)->setInc('store',$num);
	}	
//查看订单操作日志
	public function getThisOrderLogInfo($tid){
			return $this->modelTradeLog->where('tid='.$tid)->order('created_time asc')->select();
	}
//查找订单条件systrade_order表单个字段多条数据多条数据
	public function getFieldOrderInfoOrder($aftersaleTids,$aftersalesStatus,$field){
		if($aftersaleTids){
			$condition['tid']=array('in',$aftersaleTids);
			$condition['aftersales_status']=$aftersalesStatus;
			foreach($aftersaleTids as $key=>$value){
					$tids[]="'".$value."'";
			}
			if($tids){
				$order="field(tid,".implode(",", $tids).")";
			}		
			return $this->modelOrder->where($condition)->order($order)->getField($field,TRUE);
		}else{
			return null;
		}
	}	
//收货地址省市县
	public function addrDetail($level,$jdPid){
    $where=array(
        'level'=>$level,
        'jd_pid'=>$jdPid
        );		
    return $this->modelAddr->field('jd_id,name,level')->where($where)->select();
	}
/*
 * 所有订单活动类型
 * */
	public function getAllActivityType($field="aid,activity_name,Identification",$where){
		if(empty($where)){
			$where=array(
				'type'=>1,
			);
		}
		return $this->modelActivity->where($where)->field($field)->select();
	}
/*
 * 活动订单类型
 * */
 		public function getActivityType($aid,$field="aid,activity_name,Identification"){
 			if(!$aid){
 				return null;
 			}	
			$where=array(
				'aid'=>$aid,
			);			
			return $this->modelActivity->where($where)->field($field)->find();
 		}
	/**
	 * 现金退款表添加退款记录
	 */	
		public function addRefundCash($data){
			return $this->modelRefundCash->data($data)->add();
		}
	/**
	 * 取出退款表的数据
	 */	
		public function getRefundCash($tids){
			if(empty($tids)){
				return array();
			}
			$condition = array(
				'tid' => array('in', $tids),
			);
			return $this->modelRefundCash->where($condition)->field('tid,refund_fee')->select();
		}
		//取出供应商订单表数据
		public function getSupplierTrade($map,$field){
			return M('supplier_trade')->where($map)->getField($field);
		}
		//添加供应商订单表数据
		public function addSupplierTrade($data){
			return M('supplier_trade')->data($data)->add();
		}		
		//取出供应商zi订单表数据
		public function getSupplierOrder($map,$field){
			return M('supplier_trade_order')->where($map)->getField($field);
		}		
		//添加供应商子表
		public function addSupplerOrder($data){
			return M('supplier_trade_order')->data($data)->add();
		}
	
}  
?>  	