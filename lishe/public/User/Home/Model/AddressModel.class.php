<?php   
namespace Home\Model;
use Think\Model;
class AddressModel extends Model{
	public function __construct(){		
        $this->modelAddress=M('sysuser_user_addrs'); //收货地址表	
		$this->modelArea=M('site_area');//地址级联表
	}

	
	//获取用户收货地址
	public function getUserAddressList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelAddress->where($condition)->select();
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

	
}
