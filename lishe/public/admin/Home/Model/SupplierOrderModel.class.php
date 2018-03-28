<?php  

/**
 * 供应商订单模型
 * 
 */

 namespace Home\Model;
 use Think\Model;
 
 class SupplierOrderModel extends Model{
 	
 	public function __construct(){
 		parent::__construct();
		$this->supplier_order_goods = M('supplier_order_goods');
	}
	
 	/**
 	 * 更据订购计划和订购计划商品生成订单，并返回订单号order_id
 	 * @param unknown $plan
 	 * @return boolean
 	 */
 	public function addOrder($plan, $planGoodsList){
 		if(empty($plan)){
 			return false;
 		}
 		//按供应商分类
 		$orderGoodsArr = array();
 		$supplierIdArr = array();
 		foreach ($planGoodsList as $item){
 			$orderArr[$item['supplier_id']][] = $item;
 			$supplierIdArr[$item['supplier_id']] = $item['supplier_id'];
 		}
 		//开始添加
 		$order = array();
 		//生成采购单
 		$this->startTrans();//开启事物，可以取消事物？？？？？
 		foreach ($supplierIdArr as $supplierId){
 			$order['plan_id']		 	= $plan['plan_id'];
 			$order['supplier_id'] 		= $supplierId;
 			$order['uid'] 				= $plan['uid'];
 			$order['settlement_method'] = $plan['settlement_method'];
 			$order['delivery_time'] 	= $plan['delivery_time'];
 			$order['delivery_address'] 	= $plan['delivery_address'];
 			$order['warehouse_id'] 		= $plan['warehouse_id'];
 			$order['build_people'] 		= $plan['build_people'];
 			$order['build_time'] 		= $plan['build_time'];
 			$order['period'] 			= $plan['period'];
 			
 			$orderId = $this->add($order);
 			if(!$orderId){
 				$this->rollback();//回滚
 				return false;
 			}
 			$result = $this->addOrderGoods($orderId, $orderArr[$supplierId]);
 			if(!$result){
 				$this->rollback();//回滚
 				return false;
 			}
 		}
 		$this->commit();//提交
 		return true;
 	}
 	
 	/**
 	 * 批量添加订购单商品
 	 * @param unknown $orderId 订单id
 	 * @param unknown $planGoodsList 订购商品集合
 	 * @return boolean|string 失败返回false, 成功返回最后一条自增主键id
 	 */
 	private function addOrderGoods($orderId, &$planGoodsList){
 		$dataList = array();
 		$data = array();
 		foreach ($planGoodsList as $val){
 			$data['order_id'] 	 = $orderId;
 			$data['supplier_id'] = $val['supplier_id'];
 			$data['sitem_id'] 	 = $val['sitem_id'];
 			$data['item_id'] 	 = $val['item_id'];
 			$data['sku_id'] 	 = $val['sku_id'];
 			$data['ssku_id'] 	 = $val['ssku_id'];
 			$data['bn'] 		 = $val['bn'];
 			$data['title'] 		 = $val['title'];
 			$data['number'] 	 = $val['number']; //实际数量
 			$data['plan_number'] = $val['number']; //计划数量
 			$data['barcode'] 	 = empty($val['barcode']) ? '' : $val['barcode'];
 			$data['cost_price']  = $val['cost_price'];
 			$data['order_price'] = $val['cost_price']; //采购价=结算价，采购员初审时可修改
 			$data['price'] 		 = $val['price'];
 			$data['case_count']  = $val['case_count'];
 			$data['unit'] 		 = $val['unit'];
 			$data['goods_count'] = $val['goods_count'];
 			$data['spec_info'] 	 = $val['spec_info'];
 			$data['remarks'] 	 = $val['remarks'];
 			$data['mkt_price'] 	 = $val['mkt_price'];
			if($data['number']>0){
				$dataList[] = $data;
			}
 		}
 		
 		unset($data);
 		
 		if(!empty($dataList)){
 			$result = $this->supplier_order_goods->addAll($dataList);
 			unset($dataList);
 			return $result;
 		}
 		return false;
 	}
 	
 	/**
 	 * 采购订单总价
 	 * @param unknown $orderId 订单ids
 	 * @return number 总价
 	 */
 	public function toatalPrice($orderId){
 		
 		$totalPrice = 0;
 		
 		if(!is_numeric($orderId)){
 			return $totalPrice;
 		}
 		
 		$orderList = $this->supplier_order_goods->field('order_price,number')->where("order_id=$orderId")->select();
 		foreach ($orderList as $order){
 			$totalPrice += ($order['order_price'] * $order['number']);
 		}
 		
 		return $totalPrice;
 	}
 }
