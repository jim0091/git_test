<?php
/**
  +------------------------------------------------------------------------------
 * InterfaceController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: InterfaceController.class.php v001 2016-06-02
 * @description 本地接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class InterfaceController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->modelEcardRefund=M('ecard_refund');
		$this->modelEcardRefundLog=M('ecard_refund_log');
		$this->modelTradeRefund=M('systrade_refund');
		$this->modelTradeRefundLog=M('systrade_refund_log');
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelItem=M('sysitem_item');
		$this->modelItemSku=M('sysitem_sku');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelEctools=M('ectools_payments');
		$this->modelDepositLog=M('sysuser_user_deposit_log');
	}
		
	public function makeSqlLog($data){
		return M('systrade_sync_log')->add($data);
	}

	public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents("/data/www/b2b2c/public/business/logs/".$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
		}
	}
	
	public function getLoginInfo(){
		$data=array($this->uid,$this->comId,$this->account,$this->userName,urldecode($this->index),urldecode($this->refer));
		
		echo json_encode($data);
	}
	
	//同步订单到第三方 赵尊杰 2016-06-15
	public function syncOrder(){
		header("Content-type:text/html;charset=utf-8");
		$tid = I('get.tid');		
		if(!empty($tid)){
			echo $this->syncJdOrder($tid);
		}else{
			echo 'Fail:No orderId!';
		}		
	}
	
	//同步订单到京东 赵尊杰 2016-06-15
	public function syncJdOrder($tid){
		$invoiceType=2;//发票类型，1-普票，2-增值税发票
		$msg=array('code'=>-2,'msg'=>'unbeknown');
		$condition=array(
			'shop_id'=>C('JD_SHOP_ID'),
			'tid'=>$tid
		);
		$trade=$this->modelTrade->field('tid,pay_time,payed_fee,buyer_area,receiver_name,receiver_address,receiver_mobile,receiver_phone')->where($condition)->find();
		//如果订单存在并且已支付
		if(!empty($trade['tid']) && !empty($trade['pay_time']) && !empty($trade['payed_fee'])){
			list($province,$city,$county,$town)=explode('/',$trade['buyer_area']);
			$order=M('systrade_order')->field('item_id,num')->where('disabled=0 AND tid='.$trade['tid'])->select();
			foreach($order as $key=>$value){
				$itemId[]=$value['item_id'];
				$num[$value['item_id']]=$value['num'];
			}
			$jdSkuArr=M('sysitem_item')->field('jd_sku,item_id')->where('item_id IN ('.implode(',',$itemId).')')->select();
			foreach($jdSkuArr as $key=>$value){
				$sku[]=array(
					'id'=>$value['jd_sku'],
					'num'=>$num[$value['item_id']]
				);
			}
			$param = array(
				"thirdOrder"=>$trade['tid'],//第三方的订单单号
				"sku"=>$sku,
				"name"=>$trade['receiver_name'],//收货人
				"province"=>intval($province),//一级地址
				"city"=>intval($city),//2级地址
				"county"=>intval($county),//3级地址
				"town"=>intval($town),//4级地址
				"address"=>$trade['receiver_address'],//详细地址
				"zip"=>'100000',//邮编
				"phone"=>$trade['receiver_phone'],//座机号
				"mobile"=>$trade['receiver_mobile'],//手机号
				"email"=>'severs@lishe.cn',//必选 //邮箱
				"unpl"=>'', 
				"remark"=>'',//备注
				"invoiceState"=>2,//开票方式(1为随货开票，0为订单预借，2为集中开票 )
				"invoiceType"=>$invoiceType,//发票类型，1-普票，2-增值税发票
				"invoiceName"=>'蔡慧丽',//增值票收票人姓名
				"invoicePhone"=>'15811818115',//增值票收票人电话
				"invoiceProvice"=>'19',//增值票收票人所在省(京东地址编码)
				"invoiceCity"=>'1607',//增值票收票人所在市(京东地址编码)
				"invoiceCounty"=>'3155',//增值票收票人所在区/县(京东地址编码)
				"invoiceTown"=>'0',//
				"invoiceAddress"=>'高新区中区科研路9号比克科技大厦20楼2001-B',//增值票收票人所在地址
				"regCompanyName"=>'深圳礼舍科技有限公司',
				"regCode"=>'440300071136394',
				"regAddr"=>'深圳市南山区高新区中区科研路9号比克科技大厦20楼2001-B',
				"regPhone"=>'0755-66632121',
				"regBank"=>'中国建设银行深圳市莲花北支行',
				"regBankAccount"=>'44201567100052523926',
				"selectedInvoiceTitle"=>'5',//4个人，5单位
				"companyName"=>'深圳礼舍科技有限公司',//发票抬头  (如果selectedInvoiceTitle=5则此字段Y)
				"invoiceContent"=>'1',//1:明细，3：电脑配件，19:耗材，22：办公用品
				"paymentType"=>4,//1货到付款，2邮局付款，4在线支付（余额支付），5公司转账，6银行转账，7网银钱包， 101金采支付
				"isUseBalance"=>'1',//预存款【即在线支付（余额支付）】下单固定1 使用余额非预存款下单固定0 不使用余额
				"submitState"=>1,//是否预占库存，0是预占库存（需要调用确认订单接口），1是不预占库存
				"doOrderPriceMode"=>'',
				"orderPriceSnap"=>''
			);
			$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/uniteSubmit',json_encode($param));
        	$ret=json_decode($result,true);
        	//记录日志
        	$paybill=M('ectools_trade_paybill')->field('payment_id')->where('tid='.$trade['tid'])->find();
        	$paymentId=$paybill['payment_id'];
        	$log=array(
    			'payment_id'=>$paymentId,
    			'tid'=>$trade['tid'],
    			'log_type'=>'synsJdOrder',
    			'code'=>$ret['code'],
    			'detail'=>$result,
    			'modified_time'=>time()
    		);
			
        	if($ret['code']==100){
        		$data=array(
        			'payment_id'=>$paymentId,
        			'tid'=>$trade['tid'],
        			'sync_order_id'=>$ret['data']['jdOrderId'],
        			'modified_time'=>time()
        		);
        		$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'success','sync_pay_status'=>'success'));
				M('systrade_sync_trade')->add($data);
				$msg=array('code'=>$ret['data']['jdOrderId'],'msg'=>'success');
				$log['sync_order_id']=$ret['data']['jdOrderId'];
			}else{
				$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'failure','sync_pay_status'=>'failure'));
				$msg=array('code'=>-1,'msg'=>$ret['msg'].$ret['data']);
			}
			$this->makeSqlLog($log);			
		}
		echo json_encode($msg);
	}
	
	//订单退单接口 赵尊杰 2016-09-07
	public function doRefund(){
		$refundType = I('post.refundType');//退款方式 
		$paymentId = I('post.paymentId');//支付单号
		$refundFee = I('post.fee');//退款金额
		$tid = I('post.tid');//退款的商城订单
		$items = I('post.items');//退款的商城订单商品及数量
		$items=str_replace('&quot;','"',$items);
		$mark = I('post.mark');//退款备注
		$postData=json_encode($_POST); 
		$this->makeLog('refund','post:'.$postData);
		if(empty($paymentId) || empty($refundFee) || empty($tid) || empty($items)){
			$this->makeLog('refund','error:1001,错误信息：支付单号或退款金额为空');
			$this->retError(1001,'退款失败，错误信息：支付单号或退款金额为空');
		}
		$orderPay=$this->modelEctools->field('payment_id,user_id,money,cur_money,user_name,status,pay_account')->where('payment_id='.$paymentId)->find();
		if(empty($orderPay['payment_id'])){
			$this->makeLog('refund','error:1002,错误信息：未查到该订单');
			$this->retError(1002,'退款失败，错误信息：未查到该订单');
		}
		
		if($orderPay['cur_money']<$refundFee){
			$this->makeLog('refund','error:1003,错误信息：退款金额大于订单金额');
			$this->retError(1003,'退款失败，错误信息：退款金额大于订单金额');
		}
		
		$condition=array(
			'payment_id'=>$paymentId,
    		'tid'=>$tid,
    		'items'=>$items,
    		'refund_fee'=>$refundFee
		);
		$refundCheck=$this->modelEcardRefund->field('refund_sn')->where($condition)->find();
		if(!empty($refundCheck['refund_sn'])){
			$this->makeLog('refund','error:1004,错误信息：重复退款');
			$this->retError(1004,'退款失败，错误信息：重复退款');
		}
		$refundSn=date('ymdHis').rand(100000,999999);
		$empCode=$orderPay['pay_account'];
		if($refundType=='ecard'){		
			if(empty($empCode)){
				$user=$this->getEcardUser($orderPay['user_name']);
				$empCode=$user['data']['empCode'];//员工编号
				if(empty($empCode)){
					$this->makeLog('refund','error:1005,错误信息：没有员工编号');
					$this->retError(1005,'退款失败，错误信息：没有员工编号');
				}
				$this->modelEctools->where('payment_id='.$paymentId)->save(array('pay_account'=>$empCode));
			}
			$return=$this->ecardRefund($paymentId,$refundFee,$refundSn,$empCode);
			$ret=json_decode($return,true);
			$retCode=$ret['code'];
		}elseif($refundType=='point'){
			$trade=$this->modelTrade->field('transno,refund_fee')->where('tid='.$tid)->find();
			if(empty($trade['transno'])){
				$this->makeLog('refund','error:1006,错误信息：找不到积分支付单号');
				$this->retError(1006,'退款失败，错误信息：找不到积分支付单号');
			}else{
				$return=$this->pointRefund($paymentId,$refundFee,$trade['transno']);
				$ret=json_decode($return,true);
				$retCode=$ret['result'];
			}
		}else{
			$this->makeLog('refund','error:1005,错误信息：未选择退款方式');
			$this->retError(1007,'退款失败，错误信息：未选择退款方式');
		}
    	   	
    	$data=array(
    		'refund_sn'=>$refundSn,
    		'payment_id'=>$paymentId,
    		'tid'=>$tid,
    		'items'=>$items,
    		'refund_fee'=>$refundFee,
    		'refund_type'=>$refundType,
    		'emp_code'=>$empCode,
    		'mark'=>$mark,
    		'modified_time'=>strtotime($createTime)
    	);
    	$this->makeLog('refund','data:'.$data.' return:'.$return);
    	if($retCode==100){
			$data['memo']='退款成功,post:'.json_encode($postData).' return:'.$return;
			$refundId=$this->modelTradeRefund->add($data);
			$data['refund_id']=$refundId;
			$this->modelTradeRefundLog->add($data);			
			$order=json_decode($items,TRUE);
			foreach($order as $key=>$value){
				$condition=array(
					'oid'=>$value['oid']
				);
				$orderData=array(
					'refund_id'=>$refundId,
					'aftersales_status'=>'SUCCESS',
					'aftersales_num'=>$value['num']
				);
				$this->modelOrder->where($condition)->save($orderData);
			}
			$tradeData=array(
				'refund_id'=>$refundId,
				'order_status'=>'REFUND',
				'refund_fee'=>$refundFee+$trade['refund_fee'],
				'refund_time'=>time()
			);
			$this->modelTrade->where('tid='.$tid)->save($tradeData);
			$this->makeLog('refund','退款成功,tradeData:'.json_encode($tradeData));	
			$this->retSuccess(array('refundId'=>$refundId),'退款成功');
		}else{
			$data['memo']='退款失败,post:'.$postData.' return:'.$return;
			$data['refund_id']=0;
			$this->modelEcardRefundLog->add($data);
			$this->makeLog('refund','退款成功,data:'.$data);
			$this->retError(1000,'退款失败，错误信息：'.$ret['msg']);
		}
	}
	
	//东莞移动E卡通订单退单接口 赵尊杰 2016-09-07
	public function ecardRefund($paymentId,$refundFee,$refundSn,$empCode){
		$url=C('API_AOSERVER').'card/updateOrderCannel';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');
    	$createTime=date('Y-m-d H:i:s');    	
		$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderNum='.$paymentId.'&rtnMoney='.$refundFee.'&rtnNum='.$refundSn.'&rtnTime='.$createTime.C('API_AOSERVER_KEY'));
		$param=array(
    		'appKey'=>$appKey,
    		'empCode'=>$empCode,
    		'orderNum'=>$paymentId,
    		'rtnMoney'=>$refundFee,
    		'rtnNum'=>$refundSn,
    		'rtnTime'=>$createTime,
    		'sign'=>$sign
    	);
    	$this->makeLog('refund','url:'.$url.' param:'.json_encode($param));
    	$return=$this->accreditPost($url,json_encode($param),$user,$password);    	
    	$this->makeLog('refund','param:'.json_encode($param).' return:'.$return);
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
	
	//东莞移动E卡通订单退单接口 赵尊杰 2016-08-31
	public function notifyEcardRefund(){
		header("Content-type:text/html;charset=utf-8");
		$paymentId = I('post.paymentId');//支付单号
		$refundFee = I('post.fee');//退款金额
		$tid = I('post.tid');//退款的商城订单
		$items = I('post.items');//退款的商城订单商品及数量
		$items=str_replace('&quot;','"',$items);
		$mark = I('post.mark');//退款备注
		$this->makeLog('refund','post:'.json_encode($_POST));
		if(empty($paymentId) || empty($refundFee) || empty($tid) || empty($items)){
			$this->makeLog('refund','error:1001,错误信息：支付单号或退款金额为空');
			$this->retError(1001,'退款失败，错误信息：支付单号或退款金额为空');
		}
		$orderPay=$this->modelEctools->field('payment_id,user_id,money,cur_money,user_name,status,pay_account')->where('payment_id='.$paymentId)->find();
		if(empty($orderPay['payment_id'])){
			$this->makeLog('refund','error:1002,错误信息：未查到该订单');
			$this->retError(1002,'退款失败，错误信息：未查到该订单');
		}
		
		if($orderPay['cur_money']<$refundFee){
			$this->makeLog('refund','error:1003,错误信息：退款金额大于订单金额');
			$this->retError(1003,'退款失败，错误信息：退款金额大于订单金额');
		}
		$condition=array(
			'payment_id'=>$paymentId,
    		'tid'=>$tid,
    		'items'=>$items,
    		'refund_fee'=>$refundFee
		);
		$refundCheck=$this->modelEcardRefund->field('refund_sn')->where($condition)->find();
		if(!empty($refundCheck['refund_sn'])){
			$this->makeLog('refund','error:1006,错误信息：重复退款');
			$this->retError(1006,'退款失败，错误信息：重复退款');
		}
		
		if(empty($orderPay['pay_account'])){
			$user=$this->getEcardUser($orderPay['user_name']);
			$empCode=$user['data']['empCode'];//员工编号
			if(empty($empCode)){
				$this->makeLog('refund','error:1004,错误信息：没有员工编号');
				$this->retError(1004,'退款失败，错误信息：没有员工编号');
			}
			$this->modelEctools->where('payment_id='.$paymentId)->save(array('pay_account'=>$empCode));
		}else{
			$empCode=$orderPay['pay_account'];
		}
		
		$url=C('API_AOSERVER').'card/updateOrderCannel';
    	$user=C('API_AOSERVER_USER');
    	$password=C('API_AOSERVER_PASSWORD');       	
    	$appKey=C('API_AOSERVER_APPKEY');    	
    	$createTime=date('Y-m-d H:i:s');
    	$refundSn=date('ymdHis').rand(100000,999999);
		$sign=md5('appKey='.$appKey.'&empCode='.$empCode.'&orderNum='.$paymentId.'&rtnMoney='.$refundFee.'&rtnNum='.$refundSn.'&rtnTime='.$createTime.C('API_AOSERVER_KEY'));
		$param=array(
    		'appKey'=>$appKey,
    		'empCode'=>$empCode,
    		'orderNum'=>$paymentId,
    		'rtnMoney'=>$refundFee,
    		'rtnNum'=>$refundSn,
    		'rtnTime'=>$createTime,
    		'sign'=>$sign
    	);
    	$this->makeLog('refund','url:'.$url.' param:'.json_encode($param));
    	$return=$this->accreditPost($url,json_encode($param),$user,$password);
    	$this->makeLog('refund','param:'.json_encode($param).' return:'.$return);
    	$ret=json_decode($return,true);
    	
    	$postData=json_encode($_POST);    	
    	$data=array(
    		'refund_sn'=>$refundSn,
    		'payment_id'=>$paymentId,
    		'tid'=>$tid,
    		'items'=>$items,
    		'refund_fee'=>$refundFee,
    		'emp_code'=>$empCode,
    		'mark'=>$mark,
    		'modified_time'=>strtotime($createTime)
    	);
    	if($ret['code']==100){
			$data['memo']='退款成功,post:'.$postData.'data:'.json_encode($param).',return:'.$return;
			$refundId=$this->modelEcardRefund->add($data);
			$data['refund_id']=$refundId;
			$this->modelEcardRefundLog->add($data);
			$order=json_decode($items,TRUE);
			foreach($order as $key=>$value){
				$condition=array(
					'oid'=>$value['oid']
				);
				$orderData=array(
					'refund_id'=>$refundId,
					'aftersales_status'=>'SUCCESS',
					'aftersales_num'=>$value['num']
				);
				$this->modelOrder->where($condition)->save($orderData);
			}
			$tradeData=array(
				'refund_id'=>$refundId,
				'order_status'=>'REFUND',
				'refund_fee'=>$refundFee,
				'refund_time'=>time()
			);
			$this->modelTrade->where('tid='.$tid)->save($tradeData);
			$this->retSuccess(array('refundId'=>$refundId),'退款成功');
		}else{
			$data['memo']='退款失败,post:'.$postData.'data:'.json_encode($param).',return:'.$return;
			$data['refund_id']=0;
			$this->modelEcardRefundLog->add($data);
			$this->retError(1005,'退款失败，错误信息：'.$ret['msg']);
		}
	}
	
	//获取东莞移动E卡通用户信息 赵尊杰 2016-096-07
	public function getEcardUser($userName){
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
    	return json_decode($return,true);
	}
	
	
	//查询京东订单详情 赵尊杰 2016-06-23
	public function getJdOrderInfo(){
		header("Content-type:text/html;charset=utf-8");
		$orderId = I('get.orderId');//第三方订单号
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/queryJdOrderInfo','{"jdOrderId":'.$orderId.'}');
		$ret=json_decode($result,true);
		echo '<pre>';
		print_r($ret);
		echo '</pre>';
	}
	
	//查询京东订单物流 赵尊杰 2016-06-23
	public function getJdExpress(){ 
		header("Content-type:text/html;charset=utf-8");
		$orderId = I('post.orderId');//第三方订单号，京东子订单号
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/queryOrderTrack','{"jdOrderId":'.$orderId.'}');
		echo $result;
	}
	
	public function getJdAfterSale(){
		$orderId=I('get.orderId');//京东子订单号，即物流单号，不是京东订单号
		$sku=I('get.sku');
		$param=array(
			'jdOrderId'=>$orderId,
			'skuId'=>$sku
		);
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/afterSale/getCustomerExpectComp',json_encode($param));
        $ret=json_decode($result,true);
        echo "<pre>";	
    	print_r($ret);
    	echo "</pre>";
	}
	
	public function addJdAfterSale(){
		$param=array(
			'jdOrderId'=>'',//京东子订单号，即物流单号，不是京东订单号
			'questionDesc'=>'',
			'questionDesc'=>'',//问题描述，必填项
		);
	}
}