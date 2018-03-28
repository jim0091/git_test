<?php

namespace Home\Model;
use Think\Model;

/**
 * 供应商商品sku
 * @author Gaolong
 */
class SupplierItemSkuModel extends Model{
	
	/**
	 * 获取订购计划的ssku商品信息
	 * @param unknown $sskuId
	 */
	public function getItem4Plan($sskuIdArr){

		$field = 'ssku_id, sku_id, sitem_id, item_id, title, bn, cost_price, price, mkt_price, barcode, spec_info';
		$where['ssku_id'] = array('in',$sskuIdArr);
		$skuItemList = $this->field($field)->where($where)->select();
		unset($where['ssku_id']);
		
		$sitemIdArr = $this->keyValRev($skuItemList, 'sitem_id', true);
		
		if(!empty($sitemIdArr)){
			$where['sitem_id'] = array('in', $sitemIdArr);
			$itemList = $this->table('supplier_item')
							 ->field('sitem_id, goods_id, supplier_id')
							 ->where($where)->select();
			
			$itemList = $this->keyValRev($itemList, 'sitem_id', false);
			
		}
		//合并数据
		foreach ($skuItemList as &$item){
			$itemInfo = $itemList[$item['sitem_id']];
			$item['goods_id'] = $itemInfo['goods_id'];
			$item['supplier_id'] = $itemInfo['supplier_id'];
		}
		
		return $skuItemList;			
	}
	
	/**
	 * 为订购计划列表获取skuid信息
	 * @param unknown $sitemIdArr sitem_id数组
	 */
	function getSku4Plan($sitemIdArr){

		if(empty($sitemIdArr)){
			return array();
		}
		
		$where['sitem_id'] = array('in',$sitemIdArr);
		$where['quote_status'] = 4; //只查询审核通过
		$where['status'] = array('neq',-1); //没有删除的
		$field = 'ssku_id, sitem_id, item_id, sku_id, bn, barcode, price, cost_price, mkt_price, spec_info,delivery_period,status';
		$skuList = $this->field($field)->where($where)->select();
		//print_r($this->getLastSql());exit();
		$data = array();
		$skuArr = array();
		$skuIdArr = array();
		foreach ($skuList as $sku){
			$skuArr[$sku['sitem_id']][$sku['ssku_id']] = $sku;
			$skuIdArr[] = $sku['sku_id'];
		}
		
		unset($skuList);
		$data['skuList'] = &$skuArr;
		$data['skuIdArr'] = array_unique($skuIdArr);
		
		return $data;
	}
	
	/**
	 * 模糊查询，根据商品条码查询item_id
	 * @param unknown $barcode
	 */
	public function getItemIdByCode($barcode){
		if(empty($barcode)){
			return array();
		}
		$where = array();
		$where['bn|barcode'] = array('like',"%{$barcode}%");
		$skuList = $this->where($where)->field('sitem_id')->select();
		if(empty($skuList)){
			return array();
		}
		return $this->keyValRev($skuList, 'sitem_id', true);
	}
	
	/**
	 * 二位数组val值转为数组的key
	 * @param unknown $arr
	 * @param unknown $keyValName
	 * @return unknown[]
	 */
	private function keyValRev($arr, $keyValName, $onlyKeyVal=false){
		$tmpArr = array();
		if($onlyKeyVal){
			foreach ($arr as $val){
				$kv = $val[$keyValName];
				$tmpArr[$kv] = $kv;
			}
		}else{
			foreach ($arr as $val){
				$kv = $val[$keyValName];
				$tmpArr[$kv] = $val;
			}
		}
		return $tmpArr;
	}
}
