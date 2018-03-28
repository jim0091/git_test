<?php
/**
  +------------------------------------------------------------------------------
 * InterfaceController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: InterfaceController.class.php v001 2016-06-02
 * @description 积分接口封装：积分支付
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class PointController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelItem=M('sysitem_item');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelPayments=M('ectools_payments');
		$this->modelPaybill = M('ectools_trade_paybill');//支付子表
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelDepositLog=M('sysuser_user_deposit_log');		
      	$this->modelAccount = M('sysuser_account');//用户登录信息表
	}
	
	//积分支付接口 赵尊杰 2016-09-24
	public function pointPay($paymentId,$mobile,$payFee){
        $url = C('API').'mallPoints/payOrder';
        $sign=md5('orderno='.$paymentId.'&phoneNum='.$mobile.'&pointsAmount='.$payFee.'&pointsType=1'.C('API_KEY'));
        $param=array(
            'phoneNum'=>$mobile,
            'orderno'=>$paymentId,
            'pointsAmount'=>$payFee,
            'pointsType'=>1,
            'sign'=>$sign
        );
        $this->makeLog('pointPay','url:'.$url.' param:'.json_encode($param));
		$return=$this->requestPost($url,$param); 
    	$this->makeLog('pointPay','param:'.json_encode($param).' return:'.$return);
    	return $return;
    }    
	
	//积分退单接口 赵尊杰 2016-09-07
	public function pointRefund($paymentId,$refundFee,$transno){
		$url=C('API').'mallPoints/refundOrder';
		$refundPoint=$refundFee*100;
        $sign=md5('orderno='.$paymentId.'&refundAmount='.$refundPoint.'&transno='.$transno.C('API_KEY'));
		$param=array(
        	'orderno'=>$paymentId,
        	'refundAmount'=>$refundPoint,
        	'transno'=>$transno,
        	'sign'=>$sign
        );
        $this->makeLog('refund','url:'.$url.' param:'.json_encode($param));
		$return=$this->requestPost($url,$param);    	
    	$this->makeLog('refund','param:'.json_encode($param).' return:'.$return);
    	return $return;
	}
	
	//查询积分充值赠送配置 赵尊杰 2016-10-15
	//$terminalType 终端类型(PC, WAP, APP )
	public function getRechargeRule($terminalType='PC'){
		$sign=md5('terminalType='.$terminalType.C('API_KEY'));
        $url=C('API').'pointActive/getAllPointActive';
        $data=array(
            'terminalType'=>$terminalType,
            'sign'=>$sign
        );		
        $return=$this->requestPost($url,$data);
        $this->makeLog('getRechargeRule','url:'.$url.',data:'.json_encode($data).',return:'.$return."\n");
        return $return;
	}
	
	//积分充值 赵尊杰 2016-10-15
	public function pointRecharge($paymentId,$mobile,$addPoint,$terminalType='PC',$op='new'){
		$data=array(
            'phoneNum'=>$mobile,
            'pointsAmount'=>$addPoint,
            'orderno'=>$paymentId,
            'pointsType'=>1
        );		
		if($op=='new'){
			$sign=md5('orderno='.$paymentId.'&phoneNum='.$mobile.'&pointsAmount='.$addPoint.'&pointsType=1&terminalType='.$terminalType.C('API_KEY'));
			$url=C('API').'mallPoints/rechargeNew';
			$data['terminalType']=$terminalType;
		}else{
			$sign=md5('orderno='.$paymentId.'&phoneNum='.$mobile.'&pointsAmount='.$addPoint.'&pointsType=1'.C('API_KEY'));
			$url=C('API').'mallPoints/recharge';
		}
		$data['sign']=$sign;        
        
        $return=$this->requestPost($url,$data);
        $this->makeLog('recharge','url:'.$url.',data:'.json_encode($data).',return:'.$return."\n");
        return $return;
	}
}