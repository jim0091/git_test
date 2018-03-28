<?php

namespace Home\Model;
use Think\Model;

/**
 * 定后计划商品模型
 * @author Gaolong
 */
class SupplierPlanGoodsModel extends Model{
	
	/**
	 * 批量添加订购计划商品
	 * @param unknown $planId 订购计划id
	 * @param unknown $dataArr 商品数组
	 * @return boolean true.成功 false.失败
	 */
	public function batchAddForPlan($planId, $dataArr, $itemArr){
		$dataList = array();
		$data = array();
		foreach ($dataArr as $item){
			$sskuId = $item['ssku_id'];
			$data['plan_id'] = $planId;
			$data['sitem_id'] = $item['sitem_id'];
			$data['item_id'] = $item['item_id'];
			$data['sku_id'] = $item['sku_id'];
			$data['goods_id'] = $item['goods_id'];
			$data['ssku_id'] = $sskuId;
			$data['supplier_id'] = empty($item['supplier_id']) ? 0 : $item['supplier_id']; //注意
			$data['bn'] = empty($item['bn']) ? '' : $item['bn'];
			$data['barcode'] = $item['barcode'];
			$data['title'] = $item['title'];
			$data['number'] = $itemArr[$sskuId]['number'];
			$data['price'] = $item['price'];
			$data['cost_price'] = $item['cost_price'];
			$data['spec_info'] = empty($item['spec_info']) ? '' : $item['spec_info']; //注意
			$data['mkt_price'] = $item['mkt_price'];
			$dataList[] = $data;
		}
		
		//添加
		$result = $this->addAll($dataList);
		
		unset($data);
		unset($dataList);
		
		return $result;
	}
	
	/**
	 * 获取采购计划的总价
	 * @param unknown $planId 采购计划id
	 * @return number 采购总价
	 */
	public function totalPrice($planId){
		
		$totalPrice = 0;
		
		if(!is_numeric($planId)){
			return $totalPrice;
		}
		
		$orderList = $this->field('order_price')->where("plan_id=$planId")->select();
		foreach ($orderList as $order){
			$totalPrice += $order['order_price'];
		}
		return $totalPrice;
	} 
	
}
