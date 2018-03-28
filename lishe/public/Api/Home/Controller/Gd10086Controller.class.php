<?php
/**
  +------------------------------------------------------------------------------
 * TestController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: TestController.class.php v001 2016-09-09
 * @description 本地接口封装测试
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class Gd10086Controller extends CommonController{
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		header("Content-type:text/html;charset=utf-8");
		$this->display();
	}
	
	public function recharge(){
		header("Content-type:text/html;charset=utf-8");
		$mobile = I('post.mobile');
		$fee = I('post.fee');
		if(empty($mobile) or empty($fee)){
			echo '手机号和充值金额不能为空！';
			exit;
		}
		$user=A('Ecard')->getEcardUser($mobile);
		$empCode=$user['data']['empCode'];//员工编号
		$posBalance=$user['data']['posBalance'];//员工餐补余额
		if(empty($empCode)){
			echo '错误信息：未查询到该用户！';
			exit;
		}
		if($posBalance < $fee){
			echo '错误信息：账户余额不足！';
			exit;
		}
		$paymentId=date('ymdHis').$empCode;
		$return=A('Ecard')->ecardPay($paymentId,$fee,$empCode,$mobile);
		$ret=json_decode($return,true);
		$retCode=$ret['code'];
		$errCode=$ret['errCode'];
		$retMsg=$ret['msg'];
		if($retCode==100 && $errCode==0){
			$addPoint=$fee*100; //增加的积分
			$returns=A('Point')->pointRecharge($paymentId,$mobile,$addPoint,'PC');
			$rets=json_decode($returns,true);
			if($rets['result']==100 && $rets['errcode']==0){
				echo '充值成功：充值积分'.$addPoint.'！';
				exit;
			}else{
				echo $rets['msg'];
			}
		}else{
			echo $retMsg;
		}
	}
	
	public function confirm(){
		$param=array('jdOrderId'=>'46079934681');
		$url=C('API_AOSERVER').'jd/order/occupyStockConfirm';
		$result=$this->requestJdPost($url,json_encode($param));
		$this->makeLog('syncJdConfirm','url:'.$url.' param:'.json_encode($param).' return:'.$result);
        $ret=json_decode($result,true);
        print_r($ret);
	}
	
	public function getUser(){
		header("Content-type:text/html;charset=utf-8");
		$mobile='13922992355';
		$user=A('Ecard')->getEcardUser($mobile);
		print_r($user);
	}
}