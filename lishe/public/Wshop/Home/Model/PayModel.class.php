<?php  
namespace Home\Model;
use Think\Model;
class PayModel extends Model{
	public function __construct(){
        $this->modelTrade=M("systrade_trade");//订单表
        $this->modelTradeOrder=M("systrade_order");//订单子表
        $this->modelSku=M('sysitem_sku');//货品的库存
        $this->modelSkuStore=M('sysitem_sku_store');//货品的库存
		$this->modelItemStatus = M('sysitem_item_status');//商品上下架表
		$this->modelTradeSyncTrade = M('systrade_sync_trade');//京东订单表
	}

	//根据tid查询订单主表
	public function getTradeList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelTrade->where($condition)->field('created_time,shop_id')->select();
		}		
	}
	//根据tid查询订单子表
	public function getOrderList($condition){
		if (!$condition) {
			return false;
		}else{
			$orderList = $this->modelTradeOrder->table('systrade_order o,sysitem_item_status s')->where('o.item_id = s.item_id '.$condition)->field('o.item_id,o.sku_id,o.price,o.cost_price,s.approve_status')->select();
			if (!$orderList) {
				return false;
			}else{
				foreach ($orderList as $key => $value) {
					$skuIds[$key] = $value['sku_id'];
				}				
				$itemSkuList = $this->modelSku->where('sku_id in('.implode(',',$skuIds).')')->field('item_id,price,price,cost_price,type')->select();
				foreach ($orderList as $key => $value) {
					foreach ($itemSkuList as $k => $val) {
						if ($value['sku_id'] == $val['sku_id']) {
							$orderList[$key]['item_price'] = $val['price'];
							$orderList[$key]['item_cost_price'] = $val['cost_price'];
							$orderList[$key]['type'] = $val['type'];
						}
					}
				}
				return $orderList;
			}
		}
	}
	//根据payment查询京东订单号
	public function getJdTrade($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelTradeSyncTrade->where($condition)->find();
		}
		
	}

}
