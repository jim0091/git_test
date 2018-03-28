<?php   
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
	public function __construct(){		
		$this->modelUser=M('sysuser_user');
        $this->modelAddress=M('sysuser_user_addrs'); //收货地址表
		$this->modelUserDeposit = M('sysuser_user_deposit');//积分表
		$this->modelUserAccount = M('sysuser_account');//用户登录表		
		$this->modelArea=M('site_area');//地址级联表
		$this->modelTrade=M("systrade_trade");//订单表
		$this->modelOrder=M("systrade_order");//订单表子表
		$this->modelShop=M("sysshop_shop");//店铺表
	}

	//获取用户信息
	public function getUserInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelUser->where($condition)->field('name,username')->find();			
		}
	}
	//修改用户资料
	public function updateUserName($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelUser->where($condition)->data($data)->save();
		}		
	}
	//获取用户收货地址
	public function getUserAddressList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelAddress->where($condition)->select();
		}
	}
	//设置支付密码
	public function updatePayPassword($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelUserDeposit->where($condition)->data($data)->save();
		}
	}
	//获取用户信息
	public function getAccountInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return  $this->modelUserAccount->where($condition)->find();
		}
	}
	//删除用户地址
	public function delAddress($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelAddress->where($condition)->delete();
		}		
	}
	//判断是否有四级地址
	public function getAddressTown($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelArea->where($condition)->find();
		}
	}
	//获取地址级联
	public function getSiteList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelArea->where($condition)->select();
		}
	}
	//修改默认地址(取消所有地址的默认设置)
	public function updateDefaultAddress($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelAddress->where($condition)->data($data)->save();
		}
	}	

	//修改地址信息
	public function updateAddress($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelAddress->where($condition)->data($data)->save();        	
		}		
	}
	//添加地址信息
	public function addAddress($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelAddress->data($data)->add();    	
		}		
	}
	//获取用户地址信息
	public function getAddressInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelAddress->where($condition)->find();
		}
	}
	//获取用户订单
	public function getOrderList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelTrade->where($condition)->field('tid,shop_id,status,order_status,buyer_area,post_fee,payment,refund_fee')->order('created_time desc')->select();
		}
	}
	//根据店铺ids查询店铺信息
	public function getShopList($condition){
		if (empty($condition)) {
			return false;
		}else{
			return $this->modelShop->where($condition)->field('shop_id,shop_name,shop_logo,wangwang,shopuser_name,qq')->select();
		}		
	}
	//根据tids获取订单中的商品信息
	public function getOrderItemList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelOrder->where($condition)->select();
		}
	}
	//修改订单状态
	public function updateTrade($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelTrade->data($data)->where($condition)->save();
		}		
	}
	//修改订单状态
	public function updateOrder($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelOrder->data($data)->where($condition)->save();
		}		
	}
		

	
}
