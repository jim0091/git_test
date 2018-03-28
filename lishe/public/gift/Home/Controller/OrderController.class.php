<?php
/**
 +------------------------------------------------------------------------------
 * OrderController
 +------------------------------------------------------------------------------
 * @author   	高龙 <goolog@126.com>
 * @version  	$Id: OrderController.class.php v001 2016-11-01
 * @description 订单控制器
 +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
class OrderController extends CommonController {
   	
	public function __construct(){
		parent::__construct();
		//判断登录
		if(empty($this->uid)){
			redirect(__APP__.'/Login/login?refer='.urlencode(__SELF__));
		}
		$this->giftTrade = M('gift_trade');
		$this->itemModel = M('sysitem_item');
		$this->skuModel = M('sysitem_sku');//货品的库存
		$this->skuStoreModel=M('sysitem_sku_store');//货品的库存
		$this->itemStatusModel = M('sysitem_item_status');//商品状态表
		$this->userDepositModel = M('sysuser_user_deposit');//积分表
		$this->modelItemStore = M('sysitem_item_store');//商品库存
		$this->paymentsModel = M("ectools_payments");//支付表
		$this->modelItemCount = M('sysitem_item_count');
		$this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
	}
	
	//购买
	public function buy(){
		
		header("Content-type:text/html;charset=utf-8");
		$skuId = I('get.sku_id', -1, 'intval');
		$quantity = I('get.quantity', -1, 'intval');
		cookie('order_page', __SELF__);
		if(!is_numeric($skuId) && $skuId < 1){
			echo 'invalid sku_id';
			exit();
		}
		if(!is_numeric($quantity) && $quantity < 1){
			echo 'invalid quantity';
			exit();
		}
		
		$field = 'sku_id, item_id, price, status';
		$itemSku = $this->skuModel->field($field)->where("sku_id=$skuId")->find();
		if(empty($itemSku)){
			echo "<script type='text/javascript'>alert('商品不存在！');history.go(-1);</script>";
			exit();
		}
		$itemId = $itemSku['item_id'];
		$skuPrice = $itemSku['price'];
		$skuStatus = trim($itemSku['status']) ;
		
		//sku状态
		$skuStatus = trim($itemSku['status']) ;
		if($skuStatus != 'normal'){
			echo "<script type='text/javascript'>alert('该商品已停止销售！');history.go(-1);</script>";
			exit();
		}
		
		// 1.判断商品状态
		$approveStatus = $this->itemStatusModel->where("item_id=$itemId")->getField('approve_status');
		if($approveStatus != 'onsale'){
			echo "<script type='text/javascript'>alert('商品已下架，请重新选择！');history.go(-1);</script>";
			exit();
		}
		
		//查询商品信息
		$field = 'item_id, shop_id, supplier_id, jd_sku, send_type, image_default_id';
		$item = $this->itemModel->field($field)->where("item_id=$itemId")->find();
		if(empty($item)){
			echo "<script type='text/javascript'>alert('商品不存在！');history.go(-1);</script>";
			exit();
		}
		//2.查询库存
		if($item['jd_sku'] > 0){
			echo "<script type='text/javascript'>alert('商品已售罄，请重新选择（jd）！');history.go(-1);</script>";
			exit();
			//京东库存 TODO
 			//$url=C('API_STORE').'checkCartStock';
 			//$result=$this->requestPost($url,$data);
		}else{
			$skuStore = $this->skuStoreModel->where("sku_id=$skuId")->getField('store');
		}
		if($skuStore < 1){
			echo "<script type='text/javascript'>alert('商品已售罄，请重新选择！');history.go(-1);</script>";
			exit();
		}
		
		if($quantity > $skuStore){
			echo "<script type='text/javascript'>alert('该商品库存数为{$skuStore}，请调整您的购买数量');history.go(-1);</script>";
			exit();
		}
		//订单编号
		$tid = substr(date('YmdHis'),2).$this->uid;//订单编号
		session('latest_tid', $tid);
		//商品支付总金额（商品价格*购买数量+邮费-优惠）
		$totalPay = $skuPrice * $quantity;
		$totalPayBalance = $totalPay * 100;
		$userBalance = $this->userDepositModel->where("user_id=$this->uid")->getField('balance');
		$this->assign('tid', $tid);
		$this->assign('skuId', $skuId);
		$this->assign('quantity', $quantity);
		$this->assign('paymentId',$paymentId);
		$this->assign('userBalance', $userBalance);
		$this->assign('totalPayBalance', $totalPayBalance);
		$this->display('pay');	
	}
}