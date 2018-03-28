<?php  
namespace Home\Model;
use Think\Model;
class OrderModel extends Model{
	public function __construct(){
		$this->modelCart=M('systrade_cart'); //购物车表	
		$this->modelShop=M('sysshop_shop');//店铺信息		
		$this->modelCompanyItemPrice=M('company_item_price');//公司商品价格	
		$this->modelItemStatus = M('sysitem_item_status');//商品上下架表
		$this->modelAddr=M('sysuser_user_addrs'); //收货地址表	
		$this->modelSku=M('sysitem_sku');//货品的库存
		$this->modelLogisticsDlytmpl=M('syslogistics_dlytmpl');//快递信息表     
		$this->modelFreepostage=M('syspromotion_freepostage');//包邮表
		$this->modeladdress=M('sysuser_user_addrs'); //收货地址表
		$this->modelAdminTradeLog = M('system_admin_trade_log');
		$this->modelTrade=M("systrade_trade");//订单表
		$this->modelOrder=M("systrade_order");//订单表子表
		$this->modelPayment = M('ectools_payments');//支付主表
		$this->modelTradePaybill = M('ectools_trade_paybill');//支付子表
		$this->modelSkuStore = M('sysitem_sku_store');
	}

	//根据店铺ids查询店铺信息
	public function getShopList($condition){
		if (empty($condition)) {
			return false;
		}else{
			return $this->modelShop->where($condition)->field('shop_id,shop_name,shop_logo,wangwang,shopuser_name,qq')->select();
		}		
	}
	//根据用户id查询购物车数据
	public function getCartList($condition){
		if (empty($condition)) {
			return false;
		}else{
			return $this->modelCart->table('systrade_cart c,sysitem_sku s')->where('c.sku_id = s.sku_id '.$condition)->field('c.cart_id,c.shop_id,c.item_id,c.sku_id,c.title,c.image_default_id,c.quantity,s.price,s.spec_info,s.weight')->select();
		}
	}
	//根据itemIds查询商品上下架状态
	public function getItemStatus($condition){
		return $this->modelItemStatus->where($condition)->select();
	}
	//根据用户id查询用户收货地址
	public function getUserAddress($uid){
		if (empty($uid)) {
			return false;
		}
		$addressInfo = $this->modelAddr->where('def_addr = 1 and user_id='.$uid)->find();
		if (!addressInfo) {
			$addInfo = $this->modelAddr->where('user_id='.$uid)->find();
			if ($addInfo['addr_id']) {
				if($this->modelAddr->where('addr_id='.$addInfo['addr_id'])->setField('def_addr',1)){
					return $this->modelAddr->where('def_addr = 1 and user_id='.$uid)->find();
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return $addressInfo;	
		}

	}
	//根据店铺查询运费信息表
	public function getDlytmpl($condition){
		if (!condition) {
			return false;
		}else{
			return $this->modelLogisticsDlytmpl->where($condition)->field('shop_id,fee_conf,template_id')->select();
		}
	}
	//根据店铺查询包邮信息
	public function getFreePost($condition){
		if (!condition) {
			return false;
		}else{
			return $this->modelFreepostage->where($condition)->select();
		}
	}
	//查询地址表
	public function getAddressList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modeladdress->where($condition)->select();
		}
	}
	//查询默认地址表
	public function getDefaultAddressInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modeladdress->where($condition)->find();
		}
	}
	
	//修改订单状态
	public function updateTrade($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelTrade->where($condition)->data($data)->save();
		}
	}
	//修改子订单状态
	public function updateOrder($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelOrder->where($condition)->data($data)->save();
		}
	}
	//批量添加用户操作订单记录
	public function addTradeLog($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelAdminTradeLog->addAll($data);
		}
	}
	
	//获取订单的支付子列表
	public function getPaymentBillList($condition,$limit){
		if (!$condition) {
			return false;
		}else{
			return $this->modelTradePaybill->where($condition)->field('tid,payment_id,status,created_time')->limit($limit)->order('created_time DESC')->select();
		}
	}
	//获取活动订单的支付子列表
	public function getActivityPaymentBillList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelTradePaybill->where($condition)->select();
		}
	}
	//修改支付主表状态
	public function updatePaymentStatus($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelPayment->where($condition)->data($data)->save();
		}
	}
	//修改支付副表状态
	public function updatePaymentBillStatus($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelTradePaybill->where($condition)->data($data)->save();
		}
	}
	//查询购买数量
	public function getOrderItemNums($condition,$field=""){
		if (!$condition) {
			return false;
		}else{
			return $this->modelOrder->where($condition)->field($field)->select();
		}
	}
	
	//取消单，还原库存
	public function updateSku($condition,$num){
		if (!$condition || !$num) {
			return false;
		}else{
			return $this->modelSkuStore->where($condition)->setDec('freez',$num);
		}
	}
	//判断如果已经存在支付的订单则无法取消订单
	public function checkTrade($condition){
		if (!$condition) {
			return false;
		}else{
			$resTrade = $this->modelTrade->where($condition)->field('payed_fee,pay_time')->select();
			$resurt = true;
			foreach ($resTrade as $key => $value) {
				if ($value['payed_fee'] > 0 || !empty($value['pay_time'])) {
					$resurt = false;
					break;
				}
			}
			return $resurt;
		}
	}

}
