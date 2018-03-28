<?php  
namespace Home\Model;
use Think\Model;
class SupplierModel extends Model{
	public function __construct(){		
		$this->dbEctoolsPayments = M('ectools_payments');//支付表
		$this->dbEctoolsTradePaybill = M('ectools_trade_paybill');//支付子表
		$this->dbTrade = M('systrade_trade');//订单表
		$this->dbOrder = M('systrade_order');//订单子表
		$this->dbSuppliserTrade = M('supplier_trade');//供应商订单表
		$this->dbSuppliserTradeOrder = M('supplier_trade_order');//供应商订单子表
	}

	//根据订单id查询支付子表数据
	public function getPaybillList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbEctoolsTradePaybill->where($condition)->field($field)->select();
		}		
	}
	//根据tid查询订单表数据
	public function getTradeInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbTrade->where($condition)->field($field)->find();
		}
	}
	//根据tid查询订单子表数据
	public function getTradeOrderList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbOrder->where($condition)->field($field)->select();
		}
	}
	//添加供应商表数据
	public function addsupplierTrade($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbSuppliserTrade->data($data)->add();;
		}
	}
	//添加供应商子表数据
	public function addsupplierOrder($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbSuppliserTradeOrder->data($data)->add();
		}
	}
	//根据pid查询支付表信息
	public function getPaymentInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbEctoolsPayments->where($condition)->field($field)->find();
		}
	}
	//根据pid修改供应商订单支付状态
	public function updateTrade($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbSuppliserTrade->where($condition)->save($data);
		}
	}
	//根据paymenId查询供应商订单表是否存在该订单
	public function getSupplierInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbSuppliserTrade->where($condition)->field($field)->find();
		}
	}
	
}
