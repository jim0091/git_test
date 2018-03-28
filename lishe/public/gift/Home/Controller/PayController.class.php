<?php
/**
 +------------------------------------------------------------------------------
 * PayController
 +------------------------------------------------------------------------------
 * @author   	高龙 <goolog@126.com>
 * @version  	$Id: PayController.class.php v001 2016-11-01
 * @description 支付控制器
 +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
class PayController extends CommonController {
    
	public function __construct(){
		parent::__construct();
		//判断登录
		if(empty($this->uid)){
			redirect(__APP__.'/Login/login?refer='.__SELF__);
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
		$this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
	}
	
	/**
	 * 支付
	 * @author Gaolong
	 */
	public function purch(){
		header('Content-type:text/html;charset=utf-8');
		$tid = I('post.tid');
		$skuId = I('post.sku_id', -1, 'intval');
		$quantity = I('post.quantity', -1, 'intval');
		$passwd = I('post.passwd','','trim');
		
		$latestTid = session('latest_tid');
		if($tid !== $latestTid){
			echo "<script type='text/javascript'>alert('交易号有误！');history.back();</script>";
			exit();
		}
		
		if(!is_numeric($skuId) && $skuId < 1){
			echo "<script type='text/javascript'>alert('invalid sku_id！');history.back(-);</script>";
			exit();
		}
		if(!is_numeric($quantity) && $quantity < 1){
			echo "<script type='text/javascript'>alert('invalid quantity！');history.back(-1)</script>";
			exit();
		}
		if(empty($passwd)){
			echo "<script type='text/javascript'>alert('支付密码不能为空！');history.back()</script>";
			exit();
		}
		$md5Password = $this->userDepositModel->where("user_id={$this->uid}")->getField('md5_password');
		if(md5($passwd) !== $md5Password){
			echo "<script type='text/javascript'>alert('支付密码错误！');history.back();</script>";
			exit();
		}
		$field = 'sku_id, item_id, price, bn, title, spec_info, cost_price, weight, status';
		$itemSku = $this->skuModel->field($field)->where("sku_id=$skuId")->find();
		if(empty($itemSku)){
			echo "<script type='text/javascript'>alert('商品不存在！');history.back();</script>";
			exit();
		}
		$itemId = $itemSku['item_id'];
		$skuPrice = $itemSku['price'];
		$skuStatus = trim($itemSku['status']) ;
		//sku状态
		if($skuStatus != 'normal'){
			echo "<script type='text/javascript'>alert('该商品已停止销售！');history.back();</script>";
			exit();
		}
		// 1.判断商品状态
		$approveStatus = $this->itemStatusModel->where("item_id=$itemId")->getField('approve_status');
		if($approveStatus != 'onsale'){
			echo "<script type='text/javascript'>alert('商品已下架，请重新选择！');history.back();</script>";
			exit();
		}
		//查询商品信息
		$field = 'item_id, shop_id, cat_id, supplier_id, jd_sku, send_type, image_default_id';
		$item = $this->itemModel->field($field)->where("item_id=$itemId")->find();
		if(empty($item)){
			echo "<script type='text/javascript'>alert('商品不存在！');history.back();</script>";
			exit();
		}
		$skuStore = 0;
		//2.查询库存
		if($item['jd_sku'] > 0){
			echo "<script type='text/javascript'>alert('商品已售罄，请重新选择！(jd)');history.back();</script>";
			exit();
			//京东库存 TODO
			// 			$url=C('API_STORE').'checkCartStock';
			// 			$result=$this->requestPost($url,$data);
		}else{
			$skuStoreArr = $this->skuStoreModel->field('store, freez')->where("sku_id=$skuId")->find();
			if(!empty($skuStoreArr)){
				$skuStore = $skuStoreArr['store'] - $skuStoreArr['freez'];
			}
		}
		
		if($skuStore < 1){
			echo "<script type='text/javascript'>alert('商品已售罄，请重新选择！');history.back();</script>";
			exit();
		}
		
		if($quantity > $skuStore){
			echo "<script type='text/javascript'>alert('该商品库存数为{$skuStore}，请调整您的购买数量！');history.back();</script>";
			exit();
		}
		//商品支付总金额（商品价格*购买数量+邮费-优惠）,暂无邮费和优惠
		$totalPay = $skuPrice * $quantity;
		$totalPayBalance = $totalPay * 100;
		//判断积分是够使用
		$userBalance = $this->userDepositModel->where('user_id ='.$this->uid)->getField('balance');
		if($userBalance < $totalPayBalance){
			echo "<script type='text/javascript'>alert('您的积分不够');history.back();</script>";
			exit();
		}
		//添加交易数据
		$result = $this->addGiftTrade($tid, $item, $itemSku, $quantity);
		if(!$result){
			echo "<script type='text/javascript'>alert('交易失败');history.back();</script>";
			exit();
		}
		session('latest_tid', null);
		//购买减sku_store预占库存
		$this->skuStoreModel->where("sku_id=$skuId")->setInc('freez',$quantity);
		//购买减item_store预占库存
		$this->modelItemStore->where("item_id=$itemId")->setInc('freez',$quantity);
		
		//支付，扣款
 		$result = $this->payment($tid, $totalPay);
 		if(!$result){
 			echo "<script type='text/javascript'>alert('生成支付账单失败');history.back();</script>";
 			exit();
 		}
 		
 		session('post_tid', $tid);
		redirect(U('Post/edit'));
	}
	
	//添加到订单
	private function addGiftTrade($tid, $item, $itemSku, $quantity){
		if(!is_numeric($quantity) || $quantity < 1){
			return false;
		}
		$payAmount = $itemSku['price'] * $quantity;//总额
		
		$data = array();
		$data['tid'] = $tid;
		$data['shop_id'] = $item['shop_id'];
		$data['user_id'] = $this->uid;
		$data['com_id'] = $this->comId;//员工企业id
		//$data['dlytmpl_id'] = $postData['dlytmplIds'][$key];//配送模板id
		$data['supplier_id'] = $item['supplier_id'];
		$data['item_id'] = $item['item_id'];
		$data['sku_id'] = $itemSku['sku_id'];
		$data['cat_id'] = $item['cat_id'];
		$data['bn'] = $itemSku['bn'];
		$data['title'] = $itemSku['title'];
		$data['spec_nature_info'] = $itemSku['spec_info'];
		$data['price'] = $itemSku['price'];
		$data['cost_price'] = $itemSku['cost_price'];
		$data['num'] = $quantity;
		$data['total_fee'] = $payAmount;
		$data['post_fee'] = 0;//无邮费
		$data['payment'] = $payAmount;
		$data['total_weight'] = $itemSku['weight'] * $quantity;
		$data['send_type'] = $item['send_type'];
		$data['pic_path'] = $item['image_default_id'];
		$data['create_time'] = time();
		$data['from'] = 'gift';
		return $this->giftTrade->add($data);
	}
	
	//生成支付数据
	private function payment($tid, $totalPay){
		if(empty($tid)){
			return false;
		}
		//插入支付表
		$paymentId =  date('YmdHis').$this->uid.rand(0,9);//支付单号
		$data = array();
		$data['payment_id'] = $paymentId;
		$data['money'] = round($totalPay,2);//需要支付的金额
		$data['cur_money'] = 0;//支付货币金额
		$data['user_id'] = $this->uid;
		$data['user_name'] = $this->userName;
		$data['op_name'] = $this->userName; //操作员
		$data['bank'] = '预存款';//收款银行
		$data['pay_account'] ='用户';//支付账号
		$data['memo'] = '微送礼支付';
		$data['created_time'] = time();
		$result = $this->paymentsModel->add($data);
		unset($data);
		if(!$result){
			return false;
		}
		$billData = array();
		$billData['payment_id'] = $paymentId;
		$billData['tid'] = $tid;
		$billData['payment'] = $totalPay;
		$billData['user_id'] = $this->uid;
		$billData['created_time'] = time();
		$result = $this->tradePaybillModel->add($billData);
		unset($billData);
		if(!$result){
			return false;
		}
		$url = C('API_URL').'/Order/apiPayOrder';
		$data = array(
			'paymentId'=>$paymentId,
			'payType'  =>'point',
			'orderType'=>'gift',
		);
		$result = curl($url, $data);
		$result = json_decode($result, true);
		if($result['errcode'] === 0){
			return $paymentId;//成功
		}
		return false;
	}
	
	//充值
	public function recharge(){
		if(IS_POST){
			$points = I('post.points', -1, 'intval');
			$payType = I('post.payType', -1, 'intval'); //1.微信 2.支付宝
			$ret = array('code'=>-1, 'msg'=>'unkown error');
			//判断参数
			if(!is_numeric($points)){
				$ret['msg']="参数有误（points）";
				$this->ajaxReturn($ret);
			}
			if($payType !== 1 && $payType !== 2){
				$ret['msg']="参数有误（type）";
				$this->ajaxReturn($ret);
			}
			//计算金额
			$amount = $points / 100;
			
			if(!is_int($amount) || ($amount < 10 || $amount > 300)){
				$ret['msg']="参数有误（amount）";
				$this->ajaxReturn($ret);
			}
			
			$paymentId = date('ymdHis').$this->uid.'1';//支付单号
			//插入支付表
			$data = array();
			$data['payment_id'] = $paymentId;//支付单号
			$data['money'] = round($amount, 2);//需要支付的金额
			$data['cur_money'] = 0;//支付货币金额
			$data['user_id'] = $this->uid;
			$data['user_name'] = $this->account;
			$data['pay_type'] = 'recharge';
			$data['op_name'] = $this->userName; //操作员
			$data['bank'] = '预存款';//收款银行
			$data['pay_account'] =  $this->uid;//支付账号
			$data['created_time'] = time();
			$result = $this->paymentsModel->add($data);
			if(!$result){
				$ret['msg']="充值失败";
				$this->ajaxReturn($ret);
			}
			$ret['code'] = 1;
			$ret['msg']="success";
			//支付方式
			if($payType === 1){
				$ret['url'] = U('Pay/wxpay',array('paymentId'=>$paymentId));
				//$this->payForWeixin($paymentId);
			}else{
				$ret['url'] = U('Pay/aliPay',array('paymentId'=>$paymentId));
			}
			$this->ajaxReturn($ret);
			//跳转页面
		}else{
			$this->display('recharge');
		}
	}
	
	//微信支付
// 	private function payForWeixin($paymentId){
// 		//导入微信支付
// 		vendor('WxpayAPI.Util.WxPayPubHelper','','.class.php');
// 		//=========步骤1：网页授权获取用户openid============
// 		//通过code获得openid
// 		$JsApi = new \JsApi_pub();
// 		//触发微信返回code码
// 		$jsApiCallUrl = \WxPayConf_pub::GIFT_JS_API_CALL_URL."&paymentId=".$paymentId;
// 		//print_r($jsApiCallUrl);
// 		$url = $JsApi->createOauthUrlForCode(urlencode($jsApiCallUrl));
// 		Header("Location: $url");
// 	}
	
	/**
	 * 微信支付
	 */
	public function wxpay(){
		//导入微信支付
		vendor('WxpayAPI.Util.WxPayPubHelper','','.class.php');
		$paymentId= I('get.paymentId',-1,'trim');
		$code = I('get.code','', 'trim');
		
		if(!is_numeric($paymentId) || $paymentId < 1){
			echo 'invalid payment_id';
			exit();
		}
		$JsApi = new \JsApi_pub();
		
		if(empty($code)){//触发微信返回code码
			$jsApiCallUrl = \WxPayConf_pub::GIFT_JS_API_CALL_URL."&paymentId=".$paymentId;
			$url = $JsApi->createOauthUrlForCode(urlencode($jsApiCallUrl));
			Header("Location: $url");
		}else{
			$JsApi->setCode($code);
			$openid = $JsApi->getOpenId();
		}
		$payment = $this->paymentsModel->where("payment_id=$paymentId")->find();
		if(empty($payment)){
			exit();
		}
		$totalFee = $payment['money'] * 100; //total_fee为分
		
		$unifiedOrder = new \UnifiedOrder_pub();
		$unifiedOrder->setParameter("openid", $openid);//openid
		$unifiedOrder->setParameter("body", "礼舍在线充值");//商品描述
		$unifiedOrder->setParameter("out_trade_no", $paymentId);//商户订单号
		$unifiedOrder->setParameter("total_fee", $totalFee);//总金额
		$unifiedOrder->setParameter("notify_url", \WxPayConf_pub::GIFT_NOTIFY_URL);//通知地址
		$unifiedOrder->setParameter("trade_type", "JSAPI");//交易类型
		
		$prepay_id = $unifiedOrder->getPrepayId();
		//=========步骤3：使用JsApi调起支付============
		$JsApi->setPrepayId($prepay_id);
		$jsApiParameters = $JsApi->getParameters();
		
		$redirectURL = cookie('order_page');
		if(empty($redirectURL)){
			$redirectURL = '/gift.php';
		}
		$points = $totalFee;
		$this->assign('redirectURL',$redirectURL);
		$this->assign('points',$points);
		$this->assign('jsApiParameters',$jsApiParameters);
		$this->display('recharge');
	}
	
	//微信支付
	
	/**
	 *
	 * 拼接签名字符串
	 * @param array $urlObj
	 *
	 * @return 返回已经拼接好的字符串
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}
	
		$buff = trim($buff, "&");
		return $buff;
	}
	
	
	
	//支付宝
	public function aliPay($paymentId){
		$paymentId = I('get.paymentId','-1', 'intval');
		if(!is_numeric($paymentId) || $paymentId < 1){
			exit();
		}
		header("Content-type:text/html;charset=utf-8");
		//导入支付宝支付
		vendor('Alipay.alipaydirect.lib.alipay_submit','','.class.php');
		vendor('Alipay.alipaydirect.alipay','','.config.php');
		
		$money = $this->paymentsModel->where("payment_id=$paymentId")->getField('money');
		if(empty($money)){
			exit();
		}
		
		$redirectURL = cookie('order_page');
		if(empty($redirectURL)){
			$redirectURL = '/gift.php';
		}
		
		$alipayConfig = alipayConfig();
		
		$notify_url = 'http://'.$_SERVER['HTTP_HOST'].U('Call/alipayNotify');
		$return_url = 'http://'.$_SERVER['HTTP_HOST'].U('Pay/aliPayReturnUrl');
		
		$parameter = array(
				"service"       => $alipayConfig['service'],
				"partner"       => $alipayConfig['partner'],
				"seller_id"  	=> $alipayConfig['seller_id'],
				"payment_type"  => $alipayConfig['payment_type'],
				"notify_url"    => $notify_url,
				"return_url"    => $return_url,
				"_input_charset"=> trim(strtolower($alipayConfig['input_charset'])),
				"out_trade_no"  => $paymentId,
				"subject"   	=> '积分充值',
				"total_fee" 	=> sprintf("%.2f", $money),
				"show_url"  	=> 'http://'.$_SERVER['HTTP_HOST'].$redirectURL,
				"body"  		=> "积分充值",
		);
		$alipaySubmit = new \AlipaySubmit($alipayConfig);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		
		echo $html_text;
	}
	
	//支付宝同步通知
	public function aliPayReturnUrl(){
		$out_trade_no = I('get.out_trade_no','');//商户订单号
		$trade_no = I('get.trade_no','');//支付宝交易号
		$trade_status = I('get.trade_status','');//交易状态
		//导入支付宝SDK
		vendor('Alipay.alipaydirect.lib.alipay_notify','','.class.php');
		vendor('Alipay.alipaydirect.alipay','','.config.php');
		//计算得出通知验证结果
		$alipayConfig = alipayConfig();
		$alipayNotify = new \AlipayNotify($alipayConfig);
		$verify_result = $alipayNotify->verifyReturn();
		$res = 'fail';
		if($verify_result && ($trade_status == 'TRADE_FINISHED' 
				|| $trade_status == 'TRADE_SUCCESS')) {
			//验证成功
			//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			$res = 'success';
		}else {
			//验证失败
			$paymentInfo = $this->paymentsModel->field('user_id, user_name')
								->where("payment_id=$out_trade_no")
								->find();
			//如要调试，请看alipay_notify.php页面的verifyReturn函数
			$data =array(
				'type'=>'expense',
				'user_id'=>$paymentInfo['user_id'],
				'operator'=>$paymentInfo['user_name'],
				'message'=>'验证失败！',
				'logtime'=>time()
			);
			$this->userDataDepositLogModel->add($data);
		}
		//日志表
		//$this->assign('res',$res);
		//$this->display('payResult');
		if($res == 'success'){
			echo '支付成功';
		}else{
			echo '支付失败';
		}
	}
}