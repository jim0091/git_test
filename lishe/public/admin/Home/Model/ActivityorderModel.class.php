<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐
 * @version  	$Id: OrderModel.class.php v001 2016-10-26
 * @description 订单操作
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class ActivityorderModel extends CommonModel{
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
		$this->modelActivityItem=M('company_activity_item');
		
	}	
	//活动订单商品恢复库存
	public function recoverActivityItenStore($id,$num){
		return $this->modelActivityItem->where(array('aitem_id'=>$id))->setInc('item_remain',$num);
	}
	//取出活动商品信息
	public function getThisAItemInfo($id,$field){
		return $this->modelActivityItem->where(array('aitem_id'=>$id))->field($field)->find();
	}
	//售后订单	
	public function getAftersaleOrder($condition,$OrderTids){
		foreach($OrderTids as $key=>$value){
				$tids[]="'".$value."'";
		}
		if($tids){
			$order="field(atid,".implode(",", $tids).")";
		}
		return $res=$this->modelAtrade->field('atid,status,payed_fee,pay_type,order_status')->where($condition)->order($order)->select();
	}	
	//活动订单中的所有商品
	public function getOrderItems($tids){
		$condition['atid']= array('in',$tids);
		return $this->modelAorder->where($condition)->select();
	}	
//申请退款
	public function cancelThisGoods($oid,$num){
		$data['aftersales_status']="WAIT_PROCESS";
		$data['aftersales_num']=$num;
		$condition['order_id']=$oid;
		return $this->modelAorder->where($condition)->data($data)->save();
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
	public function getAllItem($itemIds,$field='item_id,cat_id,price,cost_price,barcode'){
		$condition['item_id']=array('in',$itemIds);
		return $this->modelItem->where($condition)->field($field)->select();
	}
//取出所所有商品sku下
	public function getAllSkuItem($skuIds,$field='sku_id,item_id,bn,price,cost_price,barcode'){
		$condition['sku_id']=array('in',$skuIds);
		return $this->modelItemSku->where($condition)->field($field)->select();
	}	
//修改company_activity_trade表
	public function editTradeInfo($tid,$data){
		$condition['atid']=$tid;
		return $this->modelAtrade->where($condition)->data($data)->save();		
	}
//修改systrade_order表至待审核
	public function editOrderInfo($oid,$num){
		$data['aftersales_status']="WAIT_PROCESS";
		$data['aftersales_num']=$num;
		$condition['oid']=$oid;
		return $resu=$this->modelOrder->where($condition)->field('aftersales_status,aftersales_num')->data($data)->save();
	}
//修改systrade_order表至待商家收货
	public function editOrderForConfirmGoods($oid,$num){
		$data['aftersales_status']="WAIT_SELLER_CONFIRM_GOODS";
		$data['aftersales_num']=$num;
		$condition['order_id']=$oid;
		return $this->modelAorder->where($condition)->field('aftersales_status,aftersales_num')->data($data)->save();
	}	
//取出单条company_activity_trade表数据
	public function getThisOrderInfo($tid,$field){
		$condition['atid']=$tid;
		return $this->modelAtrade->where($condition)->field($field)->find();		
		
	}
//取出单条activity_trade表数据
	public function getThisActivityOrderInfo($tid,$field){
		$condition['atid']=$tid;
		return $this->modelAtrade->where($condition)->field($field)->find();		
	}	
//取出指定company_activity_order表数据
	public function getThisTradeInfo($tid,$field){
		$condition['atid']=$tid;
		return $resu=$this->modelAorder->where($condition)->field($field)->select();		
	}
//取出指定范围的Oid  getOidsOrderInfo表数据
	public function getOidsOrderInfo($oids,$field){
		$condition['order_id']=array('in',$oids);
		return $resu=$this->modelAorder->where($condition)->field($field)->select();		
	}	
//取出指定systrade_order表单条数据
	public function findThisTradeInfo($tid,$field){
		$condition['atid']=$tid;
		return $this->modelAorder->where($condition)->field($field)->find();		
	}	
//取出指定systrade_order表数据
	public function getFieldThisTradeInfo($oid,$field){
		$condition['order_id']=$oid;
		return $this->modelAorder->where($condition)->field($field)->find();		
	}	
//取出指定systrade_order表数据
	public function getConThisTradeInfo($condition,$field){
		return $this->modelAorder->where($condition)->field($field)->find();		
	}		
//order表添加数据
	public function addsplitOrderInfo($data){
		return $this->modelAorder->add($data);		
	}
//修改editThisOrderInfo表单条数据
	public function editThisOrderInfo($oid,$data,$field){
		return $this->modelAorder->where('order_id='.$oid)->data($data)->field($field)->save();
	}
//修改systrade_order表多条数据
	public function editThisConditionOrderInfo($condition,$data,$field){
		return $this->modelAorder->where($condition)->data($data)->field($field)->save();
	}
//查找订单条件company_activity_order表多条数据
	public function getThisConditionOrderInfo($condition,$field){
		return $this->modelAorder->where($condition)->field($field)->select();
	}
	//查找订单条件systrade_order表多条数据field
	public function getFieldThisConditionOrderInfo($condition,$field){
		return $this->modelAorder->where($condition)->getField($field,TRUE);
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
				$order="field(atid,".implode(",", $tids).")";
			}
			return $this->modelAtrade->where($condition)->order($order)->getField('atid',TRUE);		
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




}  
?>  	