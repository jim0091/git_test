<?php  
/**
  +----------------------------------------------------------------------------------------
 *  CategoryModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐 
 * @description 店铺信息
  +-----------------------------------------------------------------------------------------
 */
 namespace Home\Model;
 use Think\Model;
 class ShopinfoModel extends CommonModel{
	public function __construct(){
		$this->shopinfo=M('sysshop_shop');
	}
	
	public function getShopInfo($condition,$field){
 		$res= $this->shopinfo->where($condition)->field($field)->select();
		return $res;		
	}
	public function getAllShopName(){
		return $this->shopinfo->where('status="active" and close_time is null')->field('shop_id,shop_name')->select();
	}	
	//获取店铺列表
	public function getShopList(){
		return $this->shopinfo->field("shop_id,shop_name")->select();
	}
 }
