<?php

namespace Home\Model;
use Think\Model;

/**
 * SystiemSku 模型
 * @author Gaolong
 */
class SysitemSkuModel extends Model{
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 根据skuid批量获取sku数据
	 * @param unknown $skuIdArr sku_id数组
	 * @param string $field 需要查询的字段
	 * @return mixed
	 */
	public function getSysitemSkuByIdArr($skuIdArr, $field="*"){
		if(empty($skuIdArr)){
			return array();
		}
		$where['sku_id'] = array('in',$skuIdArr);
		return $this->field($field)->where($where)->select();
	}
	
	/**
	 * 获取销量
	 * @param unknown $skuIdArr sku_id数组
	 * @return mix 正常则返回以sku_id为key，销量（sold_quantity）为value的一维数组
	 */
	public function getSoldBySkuIdArr($skuIdArr){
		
		$skuList = $this->getSysitemSkuByIdArr($skuIdArr,'sku_id, sold_quantity');
		
		if(empty($skuList)){
			return array();
		}
		
		$soldArr = array();
		foreach ($skuList as $sku){
			$soldArr[$sku['sku_id']] = $sku['sold_quantity'];
		}
		unset($skuList);
		return $soldArr;
	}
}
