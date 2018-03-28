<?php
/**
  +------------------------------------------------------------------------------
 * EcardController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: EcardController.class.php v001 2016-09-02
 * @description 东莞移动E卡通支付接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class EcardController extends CommonController{
	public function __construct(){
		parent::__construct();
		
	}
	
	//获取东莞移动E卡通用户信息 赵尊杰 2016-096-07
	public function getEcardUser($mobile){
		$url=C('API_AOSERVER').'card/getUserLoginInfo';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');    	
		$sign=md5('appKey='.$appKey.'&mobileNo='.$mobile.C('API_AOSERVER_KEY'));
		$data=array(
    		'appKey'=>$appKey,
    		'mobileNo'=>$mobile,
    		'sign'=>$sign
    	);
    	$return=$this->accreditPost($url,json_encode($data),$user,$password);
    	$this->makeLog('gd10086_user','url:'.$url.',data:'.json_encode($data).',return:'.$return."\n");
    	return json_decode($return,true);
	}	
	
	//东莞移动E卡通订单支付 赵尊杰 2016-09-24
	public function ecardPay($paymentId,$payFee,$empCode,$mobile){
		$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');
    	$createTime=date('Y-m-d H:i:s');
    	$url=C('API_AOSERVER').'card/insertOrderData';
    	$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&empPhone='.$mobile.'&orderMoney='.$payFee.'&orderNum='.$paymentId.'&orderTime='.$createTime.'&orderTotalMoney='.$payFee.'&orderType=1'.C('API_AOSERVER_KEY'));
		$param=array(
    		'appKey'=>$appKey,
    		'empCode'=>$empCode,
    		'empPhone'=>$mobile,
    		'orderMoney'=>$payFee,
    		'orderNum'=>$paymentId,
    		'orderTime'=>$createTime,
    		'orderTotalMoney'=>$payFee,
    		'orderType'=>1,
    		'sign'=>$sign
    	);
    	$return=$this->accreditPost($url,json_encode($param),$user,$password);
    	$this->makeLog('gd10086_ecard','url:'.$url.',param:'.json_encode($param).',return:'.$return."\n");
    	return $return;
	}	
		
	//东莞移动E卡通订单退单接口 赵尊杰 2016-09-07
	public function ecardRefund($paymentId,$refundFee,$refundSn,$empCode,$mobile){
		$url=C('API_AOSERVER').'card/updateOrderCannel';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');
    	$createTime=date('Y-m-d H:i:s');
    	$signStr='appKey='.$appKey.'&empCode='.$empCode.'&empPhone='.$mobile.'&orderNum='.$paymentId.'&rtnMoney='.$refundFee.'&rtnNum='.$refundSn.'&rtnTime='.$createTime.C('API_AOSERVER_KEY');
		$sign=md5($signStr);
		$param=array(
    		'appKey'=>$appKey,
    		'empCode'=>$empCode,
    		'empPhone'=>$mobile,
    		'orderNum'=>$paymentId,
    		'rtnMoney'=>$refundFee,
    		'rtnNum'=>$refundSn,
    		'rtnTime'=>$createTime,
    		'sign'=>$sign
    	);
    	$return=$this->accreditPost($url,json_encode($param),$user,$password);    	
    	$this->makeLog('gd10086_ecard','param:'.json_encode($param).' return:'.$return);
    	return $return;
	}
}