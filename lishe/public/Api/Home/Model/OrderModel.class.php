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
		$this->modelItem=M('sysitem_item');//商品表
		$this->dbEctoolsPayments = M('ectools_payments');//支付表
		$this->dbEctoolsTradePaybill = M('ectools_trade_paybill');//支付子表
		$this->dbTrade = M('systrade_trade');//订单表
		$this->dbOrder = M('systrade_order');//订单子表
		$this->dbCourierTrade = M('courier_trade');//供应商订单表
		$this->dbCourierTradeOrder = M('courier_trade_order');//供应商订单子表
		$this->dbSitemSku = M('supplier_item_sku');//供应商商品sku
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

	//公司商品价格 20160824 开始
	public function getCompanyItemPrice($array){
		/**
  		* $array=array(
    	*	'sku_id'=>1,
    	*	'com_id'=>1
    	*	);
		*/
		if(!is_array($array)){
			$res=array('msg'=>'参数格式不正确');	 
		}elseif(empty($array)){
			$res=array('msg'=>'参数不能为空');	 
		}else{
			$res=$this->modelCompanyItemPrice->where($array)->field('item_id,price,condition')->select();
		}
		return $res;
	}

	//查询商品sku
	public function getSkuList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelSku->where($condition)->field('sku_id,price,cost_price,item_id,type')->select();
		}
	}
	//查询商品
	public function getItemList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelItem->where($condition)->field('item_id,supplier_id,send_type,cat_id,image_default_id')->select();
		}
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
	//添加快递表数据
	public function addCourierTrade($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbCourierTrade->data($data)->add();;
		}
	}
	//添加快递子表数据
	public function addCourierOrder($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbCourierTradeOrder->data($data)->add();
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
	//根据pid修改快递订单支付状态
	public function updateTrade($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbCourierTrade->where($condition)->save($data);
		}
	}
	//修改供应商库存
	public function updateSupplierSkuStock($condition,$num){
		if (!$condition || !$num) {
			return false;
		}else{
			return $this->dbSitemSku->where($condition)->setDec('stock',$num);
		}
	}
}
