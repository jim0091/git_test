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
class TestController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->url='http://www.lishe.cn';
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelReturn=M('systrade_return');
		
		$this->modelActivityTrade=M('company_activity_trade');
		$this->modelActivityOrder=M('company_activity_order');
		
		$this->modelItem=M('sysitem_item');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelPayments=M('ectools_payments');
		$this->modelPaybill = M('ectools_trade_paybill');//支付子表
		
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelDepositLog=M('sysuser_user_deposit_log');
		$this->modelAccount=M('sysuser_account');
	}
	
	public function index(){
		header("Content-type:text/html;charset=utf-8");
		echo '请输入要执行的方法！';
	}
	//拆分订单到供应商表
	public function suppTrade(){
		header("Content-type:text/html;charset=utf-8");
		$paymentId=trim($_GET['id']);
		if(empty($paymentId)){
			echo '请输入支付单号！';
			exit;
		}
		$ret=A("Supplier")->splitTrade($paymentId);
		echo "<pre>";
		print_r($ret);
		echo "</pre>";
		exit;
		
	}
	public function split(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$url = $this->url."/api.php/Test/split/?page=".($page+1);
		$pageSize=20;
		$start=($page-1)*$pageSize;
		$limit=$start.','.$pageSize;
		$condition = array('status'=>'succ','pay_type'=>'online');
		$count = $this->modelPayments->where($condition)->order('payment_id ASC')->count('payment_id');
		$intCount = ceil($count/20);
		$paymentList =  $this->modelPayments->where($condition)->order('payment_id DESC')->limit($limit)->field('payment_id')->select();
		foreach ($paymentList as $key => $value) {
			$res = A("Supplier")->splitTrade($value['payment_id']);
			if($res[0]>0){
				$res = A("Supplier")->payConfirm($value['payment_id']);
			}			
		}
		if ($page < $intCount ) {
			echo '第'.$page.'页执行完成！';
			echo '<script type="text/javascript">window.location.href="'.$url.'"</script>';
		}else{
			echo "操作成功！";
			exit();
		}
	}
	
	//返积分接口 赵尊杰 2016-10-20
	public function apiDoReturn(){
		header("Content-type:text/html;charset=utf-8");
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$url = $this->url."/api.php/Test/apiDoReturn/?page=".($page+1);
		$pageSize=1;
		$start=($page-1)*$pageSize;
		$limit=$start.','.$pageSize;
		$condition=array(
			'return_fee'=>array('gt',0),
			'sys_check'=>array('gt',0),
			'return_status'=>array('eq','WAIT_PROCESS')
		);
		$getReturn=$this->modelReturn->where($condition)->order('return_id ASC')->limit($limit)->find();
		$paymentId=$getReturn['payment_id'];
		if(!empty($paymentId)){
			$condition=array(
				'payment_id'=>$paymentId,
				'sys_check'=>array('gt',0)
			);
			$tradeReturn=$this->modelReturn->where($condition)->order('return_id ASC')->find();
			if(empty($tradeReturn['return_id'])){
				$this->retError(1001,'返积分失败，错误信息：找不到订单！');
			}
			if($tradeReturn['return_status']=='TRADE_FINISHED'){
				$this->retError(1002,'返积分失败，错误信息：此订单已返还积分！');
			}
			if(empty($tradeReturn['user_id'])){
				$this->retError(1003,'返积分失败，错误信息：找不到用户信息！');
			}
			if(empty($tradeReturn['tids'])){
				$this->retError(1004,'返积分失败，错误信息：购物订单单号为空！');
			}
			if(empty($tradeReturn['return_fee'])){
				$this->retError(1005,'返积分失败，错误信息：返现金额为0！');
			}
			$checkCondition=array(
				'return_id'=>array('neq',$tradeReturn['return_id']),
				'tids'=>$tradeReturn['tids']
			);
			$checkData=array(
				'sys_check'=>'-1',
				'return_memo'=>'已返'.$tradeReturn['return_id'],'作废'
			);
			$this->modelReturn->where($checkCondition)->save($checkData);
			
			$trade=$this->modelTrade->field('tid,return_status')->where('tid IN ('.$tradeReturn['tids'].')')->select();
			if(empty($trade)){
				$this->retError(1006,'返积分失败，错误信息：找不到购物订单！');
			}
			foreach($trade as $key=>$value){
				if($value['return_status']=='TRADE_FINISHED'){
					$this->retError(1007,'返积分失败，错误信息：'.$paymentId.'存在已返现的订单！');
				}
			}
			
			$user=$this->modelAccount->field('mobile')->where('user_id='.$tradeReturn['user_id'])->find();
			$addPoint=$tradeReturn['return_fee']*100;
			if($addPoint==0){
				$this->retError(1008,'返积分失败，错误信息：返现金额为0！');
			}		
			$return=A('Point')->pointRecharge($paymentId,$user['mobile'],$addPoint,'new');
			$ret=json_decode($return,true);
			if($ret['result']==100){
				if($ret['errcode']==0){
					$this->modelReturn->where($condition)->setInc('returned_fee',$tradeReturn['return_fee']);
					$info=$ret['data']['info'];
					$returnData=array(
						'ls_trade_no'=>$info['transno'],
						'return_status'=>'TRADE_FINISHED',
						'modifyine_time'=>time(),
						'return_time'=>time(),
						'return_memo'=>$return
					);
					$this->modelReturn->where($condition)->save($returnData);
					$tradeData=array(
						'return_status'=>'TRADE_FINISHED'
					);
					$this->modelTrade->where('tid IN ('.$tradeReturn['tids'].')')->save($tradeData);
					$smsContent='尊敬的用户：您的'.$addPoint.'福利积分已返还成功，请留意积分到账情况，如有疑问请联系客服！';
					$smsReturn=A('Sms')->send($user['mobile'],$smsContent);	
					$this->makeLog('return','smsContent:'.$smsContent.' smsReturn:'.json_encode($smsReturn));
					echo "<pre>";
					print_r(array('paymentId'=>$paymentId,'rechargeNo'=>$info['transno'],'addPoint'=>$info['amount']));
					echo "</pre>";
					sleep(1);
					echo '<script type="text/javascript">window.location.href="'.$url.'"</script>';
				}else{
					$this->makeLog('return','error:1009,data:'.json_encode($returnData));
					$this->retError(1009,'返积分失败，错误信息：'.$ret['msg']);
				}
			}else{
				$this->makeLog('return','error:1009,return:'.$return);
				$this->retError(1010,'返积分失败，错误信息：接口通讯失败');
			}			
		}else{
			$this->retError(1000,'返积分失败，错误信息：没有订单号');
		}		
	}
	
	public function handEcardPay(){
		$paymentId = I('get.paymentId');//订单号
		$orderPay=$this->modelPayments->field('user_id,money,cur_money,user_name')->where('payment_id='.$paymentId)->find();
		$payBill=M('ectools_trade_paybill')->field('tid')->where('payment_id='.$paymentId)->select();
		foreach($payBill as $key=>$value){
			$tids[]=$value['tid'];
		}
		//检查是否有权限支付
		$userName=$orderPay['user_name'];
		$url=C('API_AOSERVER').'card/getUserLoginInfo';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');    	
		$sign=md5('appKey='.$appKey.'&mobileNo='.$userName.C('API_AOSERVER_KEY'));
		$data=array(
    		'appKey'=>$appKey,
    		'mobileNo'=>$userName,
    		'sign'=>$sign
    	);
    	$return=$this->accreditPost($url,json_encode($data),$user,$password);
    	$this->makeLog('gd10086_user','url:'.$url.',data:'.json_encode($data).',return:'.$return."\n");
    	$ret=json_decode($return,true);
    	if($ret['code']==100){
    		if($ret['errCode']==0){			
		    	//推送订单
		    	$empCode=$ret['data']['empCode'];//员工编号
		    	$createTime=date('Y-m-d H:i:s');
		    	$totalFee=$orderPay['money'];
		    	$url=C('API_AOSERVER').'card/insertOrderData';
		    	$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderMoney='.$totalFee.'&orderNum='.$paymentId.'&orderTime='.$createTime.'&orderTotalMoney='.$totalFee.'&orderType=1'.C('API_AOSERVER_KEY'));
				$orderPost=array(
		    		'appKey'=>$appKey,
		    		'empCode'=>$empCode,
		    		'orderMoney'=>$totalFee,
		    		'orderNum'=>$paymentId,
		    		'orderTime'=>$createTime,
		    		'orderTotalMoney'=>$totalFee,
		    		'orderType'=>1,
		    		'sign'=>$sign
		    	);
		    	$returns=$this->accreditPost($url,json_encode($orderPost),$user,$password);
		    	$this->makeLog('gd10086_ecard','url:'.$url.',data:'.json_encode($orderPost).',return:'.$returns."\n");
		    	$rets=json_decode($returns,true);
		    	if($rets['code']==100){
		    		if($rets['errCode']==0){
			    		//更新订单状态	    		
			    		
			    		$data=array(
			    			'pay_time'=>time(),
			    			'pay_memo'=>$returns
			    		);
			    		$order=$this->modelTrade->where('tid IN ('.$tids.')')->save($data);
			    		
						$orderData=array(
			    			'pay_time'=>time()
			    		);
						$this->modelOrder->where('tid IN ('.$tids.')')->save($orderData);
						
			    		$payData=array(
			    			'pay_account'=>$empCode,
			    			'payed_time'=>time(),
			    			'memo'=>$returns
			    		);
			    		$this->modelEctools->where('payment_id='.$paymentId)->save($payData);
			    		
						//记录日志
						$log['detail']='支付成功,post:'.$postData.'data:'.json_encode($payData).',return:'.$returns;
						$this->makeSqlLog($log);
						$this->retSuccess(array('orderId'=>$paymentId),'支付成功');
					}else{
						$this->retError(1004,'支付失败，错误信息：'.$rets['msg']);
					}
				}else{
					//记录日志
					$log['code']=1004;
					$log['detail']='支付失败,post:'.$postData.',return:'.$returns;
					$this->makeSqlLog($log);
					$this->retError(1004,'支付失败，错误信息：'.$ret['msg']);
				}
			}else{
				$this->retError(1005,'支付失败，错误信息：'.$ret['msg']);
			}			
	    }else{
	    	$log['code']=1003;
	    	$log['detail']='用户验证失败,post:'.$postData.',return:'.$return;
			$this->makeSqlLog($log);
			$this->retError(1003,'支付失败，错误信息：'.$ret['msg']);
		}
	}
	
	public function handConfirm(){
		$tid='16111410092501150';
		echo A('Jd')->syncJdConfirm($tid);
	}
	
	public function ecardRefund(){
		$paymentId='16111411170111501';
		$refundFee='544.8';
		$refundSn='161114100925011501';
		$mobile='13922996830';
		
		$return=A('Ecard')->getEcardUser($mobile);
		$ret=json_decode($return,true);
		print_r($ret);exit;
		echo A('Ecard')->ecardRefund($paymentId,$refundFee,$refundSn,$empCode,$mobile);
	}
	
	public function updateJdOrderId(){
		$tid = I('get.tid');//订单号
		$oid = I('get.oid');//订单号
		M('systrade_sync_trade')->where('tid='.$tid)->delete();
		$payBill=M('ectools_trade_paybill')->field('payment_id')->where("tid=".$tid)->select();
		if(!empty($payBill)){
			foreach($payBill as $key=>$value){
				$pid[]=$value['payment_id'];
			}
		}
		$pay=M('ectools_payments')->field("status='succ' AND payment_id IN (".implode(',',$pid).")")->where("tid=".$tid)->find();
		if(empty($pay)){
			echo 'no payment_id';
			exit;
		}
		$data=array(
			'payment_id'=>$pay['payment_id'],
			'tid'=>$tid,
			'sync_order_id'=>$oid,
			'modified_time'=>time()
		);
		echo M('systrade_sync_trade')->add($data);
	}
	
	public function ecardPay(){
		$paymentId = I('get.paymentId');//订单号
		$orderPay=$this->modelPayments->field('user_id,money,cur_money,user_name')->where('payment_id='.$paymentId)->find();
		$payBill=M('ectools_trade_paybill')->field('tid')->where('payment_id='.$paymentId)->select();
		foreach($payBill as $key=>$value){
			$tids[]=$value['tid'];
		}
		//检查是否有权限支付
		$userName=$orderPay['user_name'];
		$url=C('API_AOSERVER').'card/getUserLoginInfo';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');    	
		$sign=md5('appKey='.$appKey.'&mobileNo='.$userName.C('API_AOSERVER_KEY'));
		$data=array(
    		'appKey'=>$appKey,
    		'mobileNo'=>$userName,
    		'sign'=>$sign
    	);
    	$return=$this->accreditPost($url,json_encode($data),$user,$password);
    	$this->makeLog('gd10086_user','url:'.$url.',data:'.json_encode($data).',return:'.$return."\n");
    	$ret=json_decode($return,true);
    	if($ret['code']==100){
    		if($ret['errCode']==0){			
		    	//推送订单
		    	$payment=$paymentId+1;
		    	$empCode=$ret['data']['empCode'];//员工编号
		    	$createTime=date('Y-m-d H:i:s');
		    	$totalFee=$orderPay['money'];
		    	$url=C('API_AOSERVER').'card/insertOrderData';
		    	$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderMoney='.$totalFee.'&orderNum='.$payment.'&orderTime='.$createTime.'&orderTotalMoney='.$totalFee.'&orderType=1'.C('API_AOSERVER_KEY'));
				$orderPost=array(
		    		'appKey'=>$appKey,
		    		'empCode'=>$empCode,
		    		'orderMoney'=>$totalFee,
		    		'orderNum'=>$payment,
		    		'orderTime'=>$createTime,
		    		'orderTotalMoney'=>$totalFee,
		    		'orderType'=>1,
		    		'sign'=>$sign
		    	);
		    	$returns=$this->accreditPost($url,json_encode($orderPost),$user,$password);
		    	$this->makeLog('gd10086_ecard','url:'.$url.',data:'.json_encode($orderPost).',return:'.$returns."\n");
		    	$rets=json_decode($returns,true);
		    	if($rets['code']==100){
		    		if($rets['errCode']==0){
			    		//更新订单状态	    		
			    		
			    		$data=array(
			    			'pay_time'=>time(),
			    			'pay_memo'=>$returns
			    		);
			    		$order=$this->modelTrade->where('tid IN ('.$tids.')')->save($data);
			    		
						$orderData=array(
			    			'pay_time'=>time()
			    		);
						$this->modelOrder->where('tid IN ('.$tids.')')->save($orderData);
						
			    		$payData=array(
			    			'pay_account'=>$empCode,
			    			'payed_time'=>time(),
			    			'memo'=>$returns
			    		);
			    		$this->modelEctools->where('payment_id='.$paymentId)->save($payData);
			    		
						//记录日志
						$log['detail']='支付成功,post:'.$postData.'data:'.json_encode($payData).',return:'.$returns;
						$this->makeSqlLog($log);
						$this->retSuccess(array('orderId'=>$paymentId),'支付成功');
					}else{
						$this->retError(1004,'支付失败，错误信息：'.$rets['msg']);
					}
				}else{
					//记录日志
					$log['code']=1004;
					$log['detail']='支付失败,post:'.$postData.',return:'.$returns;
					$this->makeSqlLog($log);
					$this->retError(1004,'支付失败，错误信息：'.$ret['msg']);
				}
			}else{
				$this->retError(1005,'支付失败，错误信息：'.$ret['msg']);
			}			
	    }else{
	    	$log['code']=1003;
	    	$log['detail']='用户验证失败,post:'.$postData.',return:'.$return;
			$this->makeSqlLog($log);
			$this->retError(1003,'支付失败，错误信息：'.$ret['msg']);
		}
	}
	
	public function confirm(){
		$tid=$_GET['tid'];
		if(empty($tid)){
			echo 'orderId is null';
			exit;
		}
		$param=array('jdOrderId'=>$tid);
		$url=C('API_AOSERVER').'jd/order/occupyStockConfirm';
		$result=$this->requestJdPost($url,json_encode($param));
		$ret=json_decode($result,true);
		print_r($ret);
	}
	
	public function pointPay(){
		$paymentId=17051422100160521;
		$mobile=13590254843;
		$payFee=329800;
		$result=A('Point')->pointPay($paymentId,$mobile,$payFee);
		$ret=json_decode($result,true);
		print_r($ret);
	}

	//获取顺丰出库单状态
	public function getSfSendOrderStatus(){
		//$paymentId=17041215054717881;
		$paymentId=I('paymentId',0,'intval');
		$result=A('Sf')->orderSendStatus($paymentId);
		$ret=json_decode($result,true);
		print_r($ret);
	}
}