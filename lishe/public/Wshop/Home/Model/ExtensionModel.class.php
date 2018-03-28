<?php   
namespace Home\Model;
use Think\Model;
class ExtensionModel extends Model{
	public function __construct(){
		$this->dbItemStatus = M('sysitem_item_status');//商品上下架表
		$this->dbSku=M('sysitem_sku');//货品的库存
		$this->dbSkuStore=M('sysitem_sku_store');//货品的库存
		$this->dbItem=M('sysitem_item');//商品信息
		$this->dbCompanyActivityCate = M('company_activity_category');//活动商品配置表
        $this->dbTrade=M("systrade_trade");//订单表
        $this->dbOrder=M("systrade_order");//订单子表
		$this->dbItemCount=M('sysitem_item_count');//商品购买数量
    	$this->dbItemSkuStore=M('sysitem_sku_store');
    	$this->dbItemItemStore=M('sysitem_item_store');
		$this->dbAccount=M('sysuser_account');
		$this->dbUser = M('sysuser_user');//用户积分
		$this->dbArea=M('site_area');
		$this->dbEctoolsPayments = M('ectools_payments');//支付表
		$this->dbEctoolsTradePaybill = M('ectools_trade_paybill');//支付子表
	}
	//查询活动配置商品
	public function getCompanyActivityCage($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbCompanyActivityCate->where($condition)->field($field)->find();
		}
	}

	//查询商品sku列表
	public function getSkuList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbSku->table('sysitem_sku sku,sysitem_sku_store store')
					->where('sku.sku_id=store.sku_id '.$condition)
				  	->field($field)
				  	->order('sku.cash ASC')
				  	->select();
		}
	}
	//查询商品列表
	public function getItemList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbItem->where($condition)->field($field)->select();
		}
	}
	//查询用户订单
	public function getTrade($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbTrade->where($condition)->find();
		}
	}
	//查询商品详细信息
	public function getSkuInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbSku
			->table('sysitem_sku_store sto,sysitem_sku sku,sysitem_item i')
			->where('sku.item_id = i.item_id and sku.sku_id = sto.sku_id '.$condition)
			->field('sto.store,sto.freez,sku.sku_id,sku.price,sku.cost_price,sku.weight,sku.spec_info,sku.spec_desc,sku.cash,sku.point,i.item_id,i.shop_id,i.cat_id,i.title,i.supplier_id,i.send_type,i.bn,i.image_default_id')
			->find();
		}
	}

	//添加订单主表
	public function addTrade($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbTrade->data($data)->add();
		}		
	}
	//添加订单子表
	public function addOrder($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbOrder->data($data)->add();
		}
	}
	//下单增加购买数量
	public function setIncItemCount($condition,$quantity){
		if (!$condition || !$quantity) {
			return false;
		}else{
			return $this->dbItemCount->where($condition)->setInc('buy_count',$quantity);
		}
	}
	// //下单预占sku库存
	public function PreholdSkuStore($condition,$quantity){
		if (!$condition || !$quantity) {
			return false;
		}else{
			return $this->dbItemSkuStore->where($condition)->setInc('freez',$quantity);
		}
	}
	//下单预占item库存
	public function PreholdItemStore($condition,$quantity){
		if (!$condition || !$quantity) {
			return false;
		}else{
			return $this->dbItemItemStore->where($condition)->setInc('freez',$quantity);	
		}		
	}

	//根据用户id查询用户登录表
	public function getUserAccount($condition){
		if (!$condition) {
			return false;
		}
		$userAccountInfo = $this->dbAccount->where($condition)->find();
		if ($userAccountInfo) {
			return $userAccountInfo;
		}else{
			return false;
		}
	}
	//根据用户id查询用户信息表
	public function getUser($condition){
		if (!$condition) {
			return false;
		}
		$userInfo = $this->dbUser->where($condition)->find();
		if ($userInfo) {
			return $userInfo;
		}else{
			return false;
		}
	}
	//获取订单信息
	public function getTradeInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbTrade->where($condition)->find();
		}
	}
	//添加支付单表
	public function addPayment($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbEctoolsPayments->data($data)->add();
		}
	}
	//添加支付子表
	public function addPayBill($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbEctoolsTradePaybill->data($data)->add();
		}		
	}  
	//支付页面
	public function getPaymentInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbEctoolsPayments->where($condition)->field($field)->find();
		}
	} 
	//查询支付子表数据
	public function getPayBillInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbEctoolsTradePaybill->where($condition)->field($field)->find();
		}
	}
	//根据tid查询订单主表
	public function getTradeList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbTrade->where($condition)->field('created_time')->select();
		}		
	}
	//根据tid查询订单子表
	public function getOrderInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbOrder->table('systrade_order o,sysitem_sku_store s')->where('o.sku_id = s.sku_id '.$condition)->field('o.item_id,o.status,o.sku_id,o.tid,o.title,o.pic_path,s.store,s.freez')->find();
			
		}
	}
	//修改订单状态
	public function updateTrade($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbTrade->data($data)->where($condition)->save();
		}		
	}
	//修改订单状态
	public function updateOrder($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbOrder->data($data)->where($condition)->save();
		}		
	}
}
