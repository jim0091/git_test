<?php   
namespace Home\Model;
use Think\Model;
/*
 * 2016/11/15
 * 活动
 * */
class ActivityModel extends Model{
	public function __construct(){
		$this->modelAItem=M('company_activity_item');
		$this->modelActivity=M('company_activity');
		$this->modelACart=M('company_activity_cart');
		$this->modelACategory=M('company_activity_category');
		$this->modelAOrder=M('company_activity_order');
		$this->modelATrade=M('company_activity_trade');
		$this->modelSkuStore=M('sysitem_sku_store');
		$this->modelItem=M('sysitem_item');
		$this->modelIStatus=M('sysitem_item_status');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelEctoolsTradePaybill=M('ectools_trade_paybill');
		
	}
	/*
	 * 取出活动详细的数据
	 * */
	public function getACategoryInfo($condition,$field){
		return $this->modelACategory->where($condition)->field($field)->find();		
	}
	/*
	 * 取出商品的信息
	 * */
	public function getAItemInfo($condition,$field){
		return $this->modelAItem->where($condition)->field($field)->find();		
	}
	/*
	 * 查询是否有库存
	 * */
	public function getItemStore($condition){
		return $this->modelSkuStore->where($condition)->select();
	} 
	//根据tid查询订单主表
	public function getTradeList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelATrade->where($condition)->field('creat_time')->select();
		}		
	}	
	//根据tid查询订单子表
	public function getOrderList($condition){
		if (!$condition) {
			return false;
		}else{
			$aitemIds=$this->modelAOrder->where($condition)->getField('aitem_id',TRUE);
		 	$itemInfo=$this->modelAItem->where(array('aitem_id'=>array('in',$aitemIds)))->getField('item_info',TRUE);
			foreach($itemInfo as $key=>$value){
				$itemInfos[]=json_decode($value,TRUE);
			}
			foreach($itemInfos as $key=>$value){
				foreach($value as $keys=>$values){
					$itemIds[]=$values['item_id'];
				}
			}
			if(!empty($itemIds)){
				$orderList = $this->modelIStatus->where(array('item_id'=>array('in',$itemIds)))->field('item_id,approve_status')->select();
			}else{
				//无需验证上下架
				return TRUE;
			}
			if (!$orderList) {
				return false;
			}else{
				return $orderList;
			}
		}
	}	
//取出指定systrade_order表数据
	public function getConThisTradeInfo($condition,$field){
		return $this->modelAOrder->where($condition)->field($field)->find();		
	}		
//取出指定systrade_order表数据
	public function getFieldThisTradeInfo($oid,$field){
		$condition['order_id']=$oid;
		return $this->modelAOrder->where($condition)->field($field)->find();		
	}		
	//取出活动商品信息
	public function getThisAItemInfo($id,$field){
		return $this->modelAItem->where(array('aitem_id'=>$id))->field($field)->find();
	}	
//取出所所有商品
	public function getAllItem($itemIds,$field='item_id,cat_id,price,cost_price,barcode'){
		$condition['item_id']=array('in',$itemIds);
		return $this->modelItem->where($condition)->field($field)->select();
	}
/*
 *取出sku商品的多种规格 
 * */			
	public function getSomeItemSkuInfo($skuIds){
		$condition['sku_id']=array('in',$skuIds);
		return $this->modelItemSku->where($condition)->field('sku_id,item_id,bn,barcode,price,cost_price,spec_info,weight')->select();
	}	
//order表添加数据
	public function addsplitOrderInfo($data){
		return $this->modelAOrder->add($data);		
	}
//修改editThisOrderInfo表单条数据
	public function editThisOrderInfo($oid,$data,$field){
		return $this->modelAOrder->where('order_id='.$oid)->data($data)->field($field)->save();
	}	
	/*
	 * 取出商品指定信息
	 * */
	public function getThisItemInfo($itemId,$field){
		return $this->modelItem->where('item_id='.$itemId)->field($field)->find();
	}		
	/*
	 *取出活动标识 
	 * */
	 public function getActivityIdendity($paymentId){
	 	if(empty($paymentId)){
	 		return null;
	 	}
	 	$tid=$this->modelEctoolsTradePaybill->where(array('payment_id'=>$paymentId))->getField('tid');
		if(!$tid){
	 		return null;
		}
		$aid=$this->modelATrade->where(array('atid'=>$tid))->getField('aid');
		if(!$aid){
			return null;
		}
		return $this->modelActivity->where(array('aid'=>$aid))->getField('Identification');
		
	 }
	 		
}
