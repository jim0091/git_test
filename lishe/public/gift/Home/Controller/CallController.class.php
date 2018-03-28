<?php
/**
 +------------------------------------------------------------------------------
 * CallController
 +------------------------------------------------------------------------------
 * @author   	高龙 <goolog@126.com>
 * @version  	$Id: CallController.class.php v001 2016-11-01
 * @description 回掉控制器封装
 +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
class CallController extends Controller {
	
	public function __construct(){
		parent::__construct();
		$this->paymentsModel = M("ectools_payments");//支付表
		$this->userAccountModel = M('sysuser_account');//用户登录表
		//$this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
		//$this->tradeModel = M("systrade_trade");//订单表
		$this->userDepositModel = M('sysuser_user_deposit');
		$this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
		//$this->modelShufflingDetail = M('mall_shuffling_figure_detail');
		//$this->modelShuFigure = M("mall_shuffling_figure");
	}
	
	//微信支付回调
	public function weixinPayNotify(){
		vendor('WxpayAPI.Util.WxPayPubHelper','','.class.php');
		//使用通用通知接口
		$notify = new \Notify_pub();
		//存储微信的回调
		$xml = file_get_contents('php://input');
		$notify->saveData($xml);
		//验证签名，并回应微信。
		$checkSign = $notify->checkSign();
		if($checkSign == FALSE){
			$this->log_result('wxpay',"\r\n【签名失败】".date('Y-m-d H:i:s')."\r\n");
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
			$this->log_result('wxpay',"\r\n【签名成功】".date('Y-m-d H:i:s')."\r\n");
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
			$notify->setReturnParameter("return_msg","OK");//设置返回码
		}
		$returnXml = $notify->returnXml();
		//以log文件形式记录回调信息
		$this->log_result('wxpay',"\r\n【接收到的notify通知】".date('Y-m-d H:i:s')."  checkSign:".$checkSign."\r\n".$xml."\r\n");
		//签名未通过
		if($checkSign == false){
			echo $returnXml;
			exit();
		}
		$returnCode = $notify->data["return_code"];
		$resultCode = $notify->data["result_code"];
		$this->log_result('wxpay',"\r\n【更新订单开始】".date('Y-m-d H:i:s')." returnCode:".$returnCode." resultCode:".$resultCode."\r\n");
		if ($returnCode == "FAIL") {
			//此处应该更新一下订单状态，商户自行增删操作
			$this->log_result('wxpay',"【通信出错】".date('Y-m-d H:i:s')."\r\n".$xml."\r\n");
			exit;
		}
		if($resultCode == "FAIL"){
			//此处应该更新一下订单状态，商户自行增删操作
			$this->log_result('wxpay',"【业务出错】".date('Y-m-d H:i:s')."\r\n".$xml."\r\n");
			exit;
		}
	
		//$arr = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$paymentId 		= $notify->data["out_trade_no"];
		$totalFee 		= $notify->data["total_fee"];
		$transactionId 	= $notify->data["transaction_id"];
	
		$paymentInfo = $this->paymentsModel->field('user_id, user_name, status')->where("payment_id=$paymentId")->find();
		if(empty($paymentInfo)){
			$this->log_result('wxpay',"【数据库未检索到支付信息】".date('Y-m-d H:i:s')."！\r\n");
			exit();
		}
		if($paymentInfo['status'] == 'succ'){
			$this->log_result('wxpay',"【支付信息已更新】".date('Y-m-d H:i:s')."！\r\n");
			echo $returnXml;
			exit();
		}
		
		$this->log_result('wxpay',"\r\n【更新积分开始】".date('Y-m-d H:i:s')." returnCode:".$returnCode." resultCode:".$resultCode."\r\n");
		//用户信息表
		$uid 		= $paymentInfo['user_id'];
		$userName 	= $paymentInfo['user_name'];
		$balance 	= $totalFee;
		$totalFee 	= $totalFee / 100;
		//获取用户手机号
		$userAccountInfo = $this->userAccountModel->field('user_id, mobile')->where("user_id=$uid")->find();
		if(empty($userAccountInfo)){
			$this->log_result('wxpay',"\r\n【获取用户信息失败】".date('Y-m-d H:i:s')."\r\n");
			exit;
		}
		$userMobile = $userAccountInfo['mobile'];
		
		//同步一企一舍积分
		$API_KEY = C('API_KEY');
		$sign = md5('orderno='.$paymentId.'&phoneNum='.$userName.'&pointsAmount='.$balance.'&pointsType=1&terminalType=WAP'.$API_KEY);
		$this->log_result('wxpay',"\r\n【更新一企一舍积分】".date('Y-m-d H:i:s')." addPoint:".$balance." sign:".$sign."\r\n");
		$url = C('API').'mallPoints/rechargeNew';
		$data=array(
			'phoneNum'		=>$userName,
			'pointsAmount'	=>$balance,
			'orderno'		=>$paymentId,
			'pointsType'	=>1,
			'terminalType'	=>'WAP',
			'sign'			=>$sign
		);
		$return = $this->requestPost($url,$data);
		$this->log_result('wxpay','【一企一舍充值】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");
		$retArr = json_decode($return,true);
		if($retArr['result'] != 100){
			$this->log_result('wxpay','【一企一舍充值失败】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");
			exit();
		}
		unset($data);
		
		$amount = $retArr['data']['info']['amount'];
		$transno = $retArr['data']['info']['transno'];
		$this->log_result('wxpay',"\r\n【更新本地积分】".date('Y-m-d H:i:s')." totalFee:".$totalFee." balance:".$amount."\r\n");
		//支付成功，更新本地积分
		$data = array();
		$data['deposit'] = array('exp',"deposit+{$totalFee}");
		$data['balance'] = array('exp',"balance+{$amount}");
		$data['commonAmount'] = array('exp',"commonAmount+{$amount}");
		$this->userDepositModel->where("user_id=$uid")->save($data);
		unset($data);
		//更新支付主表
		$data = array();
		$data['cur_money'] 	= $totalFee;
		$data['pay_type'] 	= 'recharge';
		$data['pay_app_id'] = 'wxpay';
		$data['pay_name'] 	= '微信支付';
		$data['memo'] 		= '微送礼充值[微信支付]';
		$data['payed_time'] = time();
		$data['status'] 	= 'succ';
		$data['trade_no'] 	= $transactionId;
		$data['recharge_no']= $transno;
		$this->log_result('wxpay',"\r\n【更新支付主表】".date('Y-m-d H:i:s')." curMoney:".$totalFee." payType:recharge\r\n");
		$result = $this->paymentsModel->where('payment_id ='.$paymentId)->data($data)->save();
		$this->log_result('wxpay',"\r\n【更新主表结果】".date('Y-m-d H:i:s')." paymentId:".$paymentId." return:".$result."\r\n");
		$this->addDepositLog(array('type'=>'add','user_id'=>$uid,'operator'=>$userMobile,'fee'=>$totalFee,'message'=>$uid."微信充值，充值单号".$transno,'logtime'=>time()));
		echo $returnXml;
	}
	
	//支付宝异步通知
	public function alipayNotify(){
		//导入支付宝SDK
		vendor('Alipay.alipaydirect.lib.alipay_notify','','.class.php');
		vendor('Alipay.alipaydirect.alipay','','.config.php');
		//计算得出通知验证结果
		$alipayConfig = alipayConfig();
		$alipayNotify = new \AlipayNotify($alipayConfig);
		$verify_result = $alipayNotify->verifyNotify();
		
		if(!$verify_result){
			//验证失败
			$this->log_result('alipay',"【验证失败】".date('Y-m-d H:i:s')."verify_result:".$verify_result."\r\n");
			echo "fail";
		}
		
		//验证成功
		//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
		$out_trade_no = I('post.out_trade_no','','trim');//$_POST['out_trade_no'];//商户订单号
		$trade_no = I('post.trade_no');//$_POST['trade_no'];//支付宝交易号
		$total_fee = I('post.total_fee');//$_POST['trade_status'];//交易状态
		$trade_status = I('post.trade_status');//$_POST['trade_status'];//交易状态
		
		
		if($trade_status == 'TRADE_SUCCESS') {
			//普通即时到账的交易成功状态
			$paymentId = $out_trade_no;
			$paymentInfo = $this->paymentsModel->field('user_id,user_name,status')->where("payment_id=$paymentId")->find();
			$uid = $paymentInfo['user_id'];
			$userName = $paymentInfo['user_name'];
			$status = $paymentInfo['status'];
			
			$this->log_result('alipay',"\r\n【更新积分开始】".date('Y-m-d H:i:s')." status:".$status."\r\n");
			
			if(empty($paymentInfo)){
				$this->log_result('alipay',"【数据库未检索到支付信息】".date('Y-m-d H:i:s')."！\r\n");
			}
			
			if($status == 'succ'){
				$this->log_result('alipay',"【支付信息已更新】".date('Y-m-d H:i:s')."！\r\n");
				echo "success";
			}
			$this->log_result('alipay',"\r\n【更新积分开始】".date('Y-m-d H:i:s')." returnCode:".$_POST['total_fee']."\r\n");
			//用户登录信息表
			$totalFee = $total_fee;//单位元
			$balance = $total_fee * 100;//单位积分
			$userAccountInfo = $this->userAccountModel->field('user_id,mobile')->where('user_id ='.$uid)->find();
			if(empty($userAccountInfo)){
				$this->log_result('alipay',"\r\n【获取用户信息失败】".date('Y-m-d H:i:s')."\r\n");
				exit;
			}
			$userMobile = $userAccountInfo['mobile'];
			//同步一企一舍积分
			$API_KEY = C('API_KEY');
			$sign=md5('orderno='.$paymentId.'&phoneNum='.$userName.'&pointsAmount='.$balance.'&pointsType=1&terminalType=WAP'.$API_KEY);
			$this->log_result('alipay',"\r\n【更新一企一舍积分】".date('Y-m-d H:i:s')." addPoint:".$balance." sign:".$sign."\r\n");
			$url = C('API').'mallPoints/rechargeNew';
			$data=array(
				'phoneNum'	  =>$paymentInfo['user_name'],
				'pointsAmount'=>$balance,
				'orderno'	  =>$paymentId,
				'pointsType'  =>1,
				'terminalType'=>'WAP',
				'sign' 		  =>$sign
			);
			$return=$this->requestPost($url,$data);
			$this->log_result('alipay','【一企一舍充值】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");
			$retArr=json_decode($return,true);
			unset($data);
			
			if($retArr['result'] != 100){
				$this->log_result('alipay','【一企一舍充值失败】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");
				exit();
			}
			
			$transno = $retArr['data']['info']['transno'];
			
			$this->log_result('alipay',"\r\n【更新本地积分】".date('Y-m-d H:i:s')." totalFee:".$totalFee." balance:".$balance."\r\n");
			//支付成功，更新本地积分
			$data = array();
			$data['deposit'] = array('exp',"deposit+{$totalFee}");
			$data['balance'] = array('exp',"balance+{$balance}");
			$data['commonAmount'] = array('exp',"commonAmount+{$balance}");
			$this->userDepositModel->where("user_id=$uid")->save($data);
			unset($data);

			//更新支付主表
			$data = array();
			$data['cur_money'] = $totalFee;
			$data['pay_type'] = 'recharge';
			$data['pay_app_id'] = 'alipay';
			$data['pay_name'] = '支付宝支付';
			$data['memo'] = '微送礼充值[支付宝支付]';
			$data['payed_time'] = time();
			$data['status'] = 'succ';
			$data['trade_no'] = $trade_no;
			$data['recharge_no'] = $transno;
			
			$this->log_result('alipay',"\r\n【更新支付主表】".date('Y-m-d H:i:s')." curMoney:".$totalFee." payType:recharge\r\n");
			$result = $this->paymentsModel->where("payment_id=$paymentId")->save($data);
			$this->log_result('alipay',"\r\n【更新主表结果】".date('Y-m-d H:i:s')." paymentId:".$paymentId." return:".$result."\r\n");
			$this->addDepositLog(array('type'=>'add','user_id'=>$uid,'operator'=>$userMobile,'fee'=>$totalFee,'message'=>$uid."支付宝充值，充值单号".$transno,'logtime'=>time()));
			echo "success";
		}else if ($trade_status == 'TRADE_FINISHED') {
			$this->log_result('alipay',"【普通即时到账】".date('Y-m-d H:i:s')."！\r\n");
		}else{
			$this->log_result('alipay',"【交易失败】".date('Y-m-d H:i:s')."！\r\n");
		}
	}
	
	//记录日志
	private function log_result($type, $content){
		$file  = APP_PATH.'Logs/PayCharge/'.$type.'_'.date('Ymd').'.log';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个
		@file_put_contents($file, $content, FILE_APPEND);
	}
	
	//模拟提交
	private function requestPost($url='', $data=array()) {
		if(empty($url) || empty($data)){
			return false;
		}
		$o="";
		foreach($data as $k=>$v){
			$o.="$k=".$v."&";
		}
		$param=substr($o,0,-1);
		$ch=curl_init();//初始化curl
		curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
		curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
		curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
		$return=curl_exec($ch);//运行curl
		curl_close($ch);
		return $return;
	}
	
	//消费/充值日志
	public function addDepositLog($data){
		return $this->userDataDepositLogModel->data($data)->add();
	}
}