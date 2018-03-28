<?php
/**
  +----------------------------------------------------------------------------------------
 *  AdminModel
  +----------------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: AdminrModel.class.php v001 2015-10-27
 * zhangrui 2016/9/20
 * @description 管理后台数据库操作管理员功能部分
  +-----------------------------------------------------------------------------------------
 */
	namespace Home\Model;
	use Think\Model;
class CartModel extends CommonModel
{
	public function __construct()
	{
		$this->modelCart=M('systrade_cart'); //购物车表
		$this->modelShop=M('sysshop_shop');//店铺信息
		$this->modelCompanyItemPrice=M('company_item_price');//公司商品价格
		$this->modelItemStatus = M('sysitem_item_status');//商品上下架表
		$this->modelAddr=M('sysuser_user_addrs'); //收货地址表
		$this->modelSku=M('sysitem_sku');//货品的库存
		$this->modelSkuStore=M('sysitem_sku_store');//货品的库存
		$this->modelLogisticsDlytmpl=M('syslogistics_dlytmpl');//快递信息表
		$this->modelFreepostage=M('syspromotion_freepostage');//包邮表
		$this->modeladdress=M('sysuser_user_addrs'); //收货地址表
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
	//查询默认地址
	public function getDefaultAddressInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modeladdress->where($condition)->find();
		}
	}

	//删除购物车商品
	public function delCartItems($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelCart->where($condition)->delete();
		}
	}

	//购物车增加商品
	public function addCartNum($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelCart->where($condition)->setInc('quantity',1);
		}
	}
	//购物车减少商品
	public function decCartNum($condition,$num){
		if (!$condition) {
			return false;
		}else{
			$data=array(
				'quantity'=>$num
			);
			return $this->modelCart->where($condition)->save($data);
		}
	}
	//查询购物车单个商品信息
	public function getCartIteminfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelCart->table('systrade_cart c,sysitem_sku_store s')->where('c.sku_id = s.sku_id '.$condition)->find();
		}
	}
	//查询单个商品库存
	public function getItemSkuStore($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelSkuStore->field('store,freez')->where($condition)->find();
		}
	}

}
			
