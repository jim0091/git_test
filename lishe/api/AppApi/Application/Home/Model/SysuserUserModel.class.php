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
class SysuserUserModel extends CommonModel{
	public function __construct(){
		$this->sysuserUser=M('sysuser_user');
		$this->modelUser=M('sysuser_user');
		$this->modelDeposit=M('sysuser_user_deposit'); //用户积分表
		$this->modelAccount=M('sysuser_account'); //用户登录信息表
		$this->modelUserCoupon=M("sysuser_user_coupon");
		$this->modelTrade=M('systrade_trade');//订单主表
		$this->shopModel=M('sysshop_shop');//店铺信息
		$this->tradeModel=M('systrade_trade');//订单主表
		$this->orderModel=M('systrade_order');//订单附表
		
		
	}
	//登录
	public function adminLogin($userName){
		$condition=array(
			'username'=>$userName
		);
		return $this->sysuserUser->where($condition)->find();
	}
	//生成token
	public function makeToken($uid,$passwd){
		$str=$uid.$passwd.time();
		$token=md5($str);
		return $token;
	}
	//获取详情
	public function getDeposit($uid){
		if(!$uid){
			return false;
		}
		$res=$this->modelDeposit->where('user_id='.$uid)->field('balance,commonAmount,limitAmount,comId,comName')->find();
		//echo $this->modelDeposit->getLastSql();
		return $res;
	}
		//获取订单列表数量
	public function getOrderNumber($uid){
		$orderList = $this->modelTrade->where(array('user_id'=>$uid,'status'=>array('neq','TRADE_FINISHED')))->field('status')->select();
		$count['WAIT_BUYER_PAY'] = 0;//待付款 WAIT_BUYER_PAY
		$count['WAIT_SELLER_SEND_GOODS'] = 0;//待发货 WAIT_SELLER_SEND_GOODS
		$count['WAIT_BUYER_CONFIRM_GOODS'] = 0;//待收货 WAIT_BUYER_CONFIRM_GOODS
		$count['WAIT_COMMENT'] = 0;//待评价 WAIT_COMMENT
		foreach($orderList as $item){
			if($item['status'] == "WAIT_BUYER_PAY"){
				$count['WAIT_BUYER_PAY']++;
			}
			if ($item['status'] == "WAIT_SELLER_SEND_GOODS") {
				$count['WAIT_SELLER_SEND_GOODS']++;
			}
			if ($item['status'] == "WAIT_BUYER_CONFIRM_GOODS") {
				$count['WAIT_BUYER_CONFIRM_GOODS']++;
			}
			if ($item['status'] == "WAIT_COMMENT") {
				$count['WAIT_COMMENT']++;
			}
		}
		return $count;

	}
	//公共方法得到用户账号信息
	public function commonUserInfo($uid){
		if(!$uid){
			return false;
		}
		$where=array(
			'user_id'=>$uid
		);
		$userInfo=$this->modelUser->where($where)->field('name,username')->find();

		if($userInfo){
			return $userInfo;
		}else{
			return '';
		}
	}
	//获取订单列表
	public function getOrderList($whereSys){
		if(empty($whereSys)){
			return false;
		}
		$sysTradeInfo=$this->modelTrade->where($whereSys)->field('tid,shop_id,status,order_status,buyer_area,post_fee,payment,refund_fee')->order('created_time desc')->select();
		
		$trade_PaybillObj=M('ectools_trade_paybill');//订单支付关联表
		foreach ($sysTradeInfo as &$v){
			$where['tid'] = $v['tid'];
			$trade_Paybill_Info = $trade_PaybillObj->where($where)->field('tid,payment_id')->find();
			$v['payment_id'] = $trade_Paybill_Info['payment_id'];
		}
		return $sysTradeInfo;
	}
	//获取商铺列表
	public function getShopList($shopId){
		if(empty($shopId)){
			return false;
		}
		$shop=array();
		$shopArr=$this->shopModel->where('shop_id IN ('.implode(',',$shopId).')')->field('shop_id,shop_name')->select();
		if(!empty($shopArr)){
			foreach($shopArr as $key=>$value){
				$shop[$value['shop_id']]=$value['shop_name'];
			}
		}
		return $shop;
	}
	//通过订单号好去订单列表
	public function getOrders($tid){
		if(empty($tid)){
			return false;
		}
		$order=array();
		$orderInfo=$this->orderModel->where('tid IN('.implode(',',$tid).')')->field('tid,oid,item_id,title,price,num,total_weight,pic_path')->select();
		if(!empty($orderInfo)){
			foreach($orderInfo as $key=>$value){
				$value['price']=number_format($value['price'],2,'.','');
				$order[$value['tid']][]=$value;
			}
		}
		return $order;
	}

	//获取退款订单
	public function getRefundOrder($uid){
		if($uid<=0){
			return false;
		}
		$where=array(
			'order_status'=>array('in','REFUND,RETURN'),
			'user_id'=>$uid
		);
		$res=$this->modelTrade->where($where)->field('tid,shop_id,status,order_status,buyer_area,post_fee,payment,refund_fee')->order('created_time desc')->select();
		//$res=$this->orderModel->where($where)->select();
		return $res;
	}



			
}