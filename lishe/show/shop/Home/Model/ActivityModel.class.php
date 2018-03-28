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
		$this->modelACart=M('company_activity_cart');
		$this->modelACategory=M('company_activity_category');
		$this->modelAOrder=M('company_activity_order');
		$this->modelATrade=M('company_activity_trade');
		$this->modelSkuStore=M('sysitem_sku_store');
		$this->modelIStatus=M('sysitem_item_status');
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
		 	$itemIds=$this->modelAItem->where(array('aitem_id'=>array('in',$aitemIds)))->getField('item_ids',TRUE);
			foreach($itemIds as $key=>$value){
				foreach(explode(',', $value) as $k=>$v){
					$LastitemIds[]=$v;
				}
			}
			if(!empty($LastitemIds)){
				$orderList = $this->modelIStatus->where(array('item_id'=>array('in',$LastitemIds)))->field('item_id,approve_status')->select();
			}
			if (!$orderList) {
				return false;
			}else{
				return $orderList;
			}
		}
	}	

}
