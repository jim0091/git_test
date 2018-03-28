<?php

namespace Home\Model;
use Think\Model;

/**
 * 定后计划模型
 * @author Gaolong
 */
class SupplierPlanModel extends Model{
	
	public function __construct(){
		parent::__construct();
		$this->supplier_goods = D('SupplierPlanGoods');
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
		
		$orderList = $this->supplier_goods->field('cost_price,number')->where("plan_id=$planId")->select();
		foreach ($orderList as $order){
			$totalPrice += ($order['cost_price'] * $order['number']);
		}
		return $totalPrice;
	} 
	
}
