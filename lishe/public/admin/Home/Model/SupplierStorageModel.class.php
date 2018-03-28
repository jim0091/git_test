<?php

namespace Home\Model;
use Think\Model;

/**
 * 入库model
 * @author Gaolong
 */
class SupplierStorageModel extends Model{
	
	public function __construct(){
		parent::__construct();
		$this->supplier_storage_order = D('supplier_storage_order');
		$this->supplier_storage_items = D('supplier_storage_items');
	}
	
	/**
	 * 获取采购计划的总价
	 * @param unknown $planId 采购计划id
	 * @return number 采购总价
	 */
	public function getStorageOrder($where,$page,$pageSize){
		$list=$this->supplier_storage_order->where($where)->order("storage_id desc")->page($page.','.$pageSize)->select();
		return $list;
	}

	//获取总数量
	public function getStorageCount($where){
		return $this->supplier_storage_order->where($where)->count();
	}
	//通过订单列表获取sku详情
	public function getSkusByOrder($orderId){
		return $this->supplier_storage_items->where("storage_id=$orderId")->select();
	}
	
}
