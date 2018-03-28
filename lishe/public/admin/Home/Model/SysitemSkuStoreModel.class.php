<?php

namespace Home\Model;
use Think\Model;

/**
 * 商品库存模型
 * @author Gaolong
 */
class SysitemSkuStoreModel extends Model{
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 根据item_id数组批量获取库存数据，推荐使用
	 * @param unknown $itemIdArr item_id 数组 例如:array(1,2,3)
	 * @param string $field 字段
	 * @return mixed
	 * @author Gaolong
	 */
	public function skuStoreByItemIdArr($itemIdArr, $field='*'){
		if(empty($itemIdArr)){
			return array();
		}
		$where['item_id'] = array('in',$itemIdArr);
		return $this->field($field)->where($where)->select();
	}
	
	/**
	 * 根据sku_id获取库存数据，不推荐使用，无法使用索引
	 * @param unknown $skuId
	 * @param string $field
	 * @return boolean|mixed|boolean|NULL|string|unknown|object
	 */
	public function getStoreBySkuId($skuId){
		if(!is_numeric($skuId)){
			return false;
		}
		return $this->field($field)->where("sku_id=$skuId")->getField('store');
	}
	
	/**
	 * 获取库存数据，推荐使用此方法
	 * @param unknown $itemId 商品 ID
	 * @param unknown $skuId sku ID
	 * @return mixed
	 * @author Gaolong 
	 */
	public function getStore($itemId, $skuId=null){
		if(!is_numeric($itemId)){
			return false;
		}
		$where['item_id'] = $itemId;
		if(!empty($skuId)){
			$where['sku_id'] = $skuId;
		}
		return $this->field($field)->where($where)->getField('store');
	}
	
	/**
	 * 采购计划库存查询分装
	 * @param unknown $itemIdArr
	 */
	public function getStoreByItemIdArr($itemIdArr){
		
		$storeList = $this->skuStoreByItemIdArr($itemIdArr, 'item_id, sku_id, store');
		if(empty($storeList)){
			return array();
		}
		$storeArr = array();
		foreach ($storeList as $val){
			$storeArr[$val['item_id']][$val['sku_id']] = $val['store'];
		}
		unset($storeList);
		return $storeArr;
	}
}
