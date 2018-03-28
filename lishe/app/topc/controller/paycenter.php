<?php
class topc_ctl_paycenter extends topc_controller{

    public function __construct($app){
        parent::__construct();
        $this->setLayoutFlag('paycenter');
        // 检测是否登录
    }

    public function index(){
    	$filter = input::get();
        $pagedata['payment_id_rel'] = $filter;
        $pagedata['user_id'] = array('userId'=>$_SESSION['account']['member']['id']);
        $pagedata['user_account'] = array('account'=>$_SESSION['account']['member']['account']);
        
        if(isset($filter['tid']) && $filter['tid']){
            $pagedata['payment_type'] = "offline";
            $ordersMoney = app::get('topc')->rpcCall('trade.money.get',array('tid'=>$filter['tid']),'buyer');
            if($ordersMoney)
            {
                foreach($ordersMoney as $key=>$val)
                {
                    $newOrders[$val['tid']] = $val['payment'];
                    $newMoney += $val['payment'];
                }
                $paymentBill['money'] = $newMoney;
                $paymentBill['cur_money'] = $newMoney;
            }
            $pagedata['trades'] = $paymentBill;
            $pagedata['payment_type'] = "offline";
            $pagedata['mainfile'] = "topc/payment/payment.html";
            return $this->page('topc/payment/index.html', $pagedata);
        }
        if($filter['newtrade']){
            $newtrade = $filter['newtrade'];
            unset($filter['newtrade']);
        }

        if($filter['merge']){
            $ifmerge = $filter['merge'];
            unset($filter['merge']);
        }

        //获取可用的支付方式列表
        $filter['fields'] = "*";
        $paymentBill = app::get('topc')->rpcCall('payment.bill.get',$filter,'buyer');
        if($paymentBill['status'] == "succ"){
            return $this->finish(['payment_id'=>$paymentBill['payment_id']]);
        }
        //检测订单中的金额是否和支付金额一致 及更新支付金额
        $trade = $paymentBill['trade'];
        $tids['tid'] = implode(',',array_keys($trade));
        $ordersMoney = app::get('topc')->rpcCall('trade.money.get',$tids,'buyer');
        
        if($ordersMoney){
            foreach($ordersMoney as $key=>$val){
            	$tid[]=$val['tid'];
                $newOrders[$val['tid']]=$val['payment'];
                $newMoney+=$val['payment'];
                $postFee+=$val['post_fee'];
            }            
            //检测是否是东莞移动用户，是且减掉邮费的金额满99则减掉邮费
	        $pagedata['ecardPay']=0;
	        $pagedata['reliefFee']=0;
	        $memo='新订单';
            $db = app::get('topc')->database();
			$result= $db->executeQuery('select comId from sysuser_user_deposit where user_id='.$pagedata['user_id']['userId'])->fetch();
			if($result['comId']=='1467166836740'){
				$pagedata['ecardPay']=1;
				$newPay=$paymentBill['cur_money']-$postFee;
				if($newPay>=99){
					$paymentBill['cur_money']=$newPay;
					$newMoney=$newMoney-$postFee;
					$pagedata['reliefFee']=$postFee;//减免金额
					$memo='东莞移动用户订单满'.$newMoney.'减免运费'.$postFee;
					$db->executeUpdate("UPDATE systrade_trade SET relief_fee=post_fee,payment=payment-post_fee,post_fee=0 WHERE tid IN (".implode(',',$tid).")");
				}
			}
			
			$data=array(
                'trade_own_money'=>json_encode($newOrders),
                'money'=>$newMoney,
                'cur_money'=>$newMoney,
                'payment_id'=>$filter['payment_id'],
                'memo'=>$memo
            );

            if($newMoney!=$paymentBill['cur_money'] || $result['comId']=='1467166836740'){
                try{
                    app::get('topc')->rpcCall('payment.money.update',$data);
                }
                catch(Exception $e)
                {
                    $msg = $e->getMessage();
                    $url = url::action('topc_ctl_member_trade@tradeList');
                    return $this->splash('error',$url,$msg,true);
                }
                $paymentBill['money'] = $newMoney;
                $paymentBill['cur_money'] = $newMoney;
            }
        }

        
        $paymentBill['cur_money_deposit'] = $paymentBill['cur_money'] * 100;

        $payType['platform'] = 'ispc';
        $payments = app::get('topc')->rpcCall('payment.get.list',$payType,'buyer');

        $pagedata['tids'] = $tids['tid'];
        $pagedata['trades'] = $paymentBill;
        $pagedata['payments'] = $payments;
        $pagedata['newtrade'] = $newtrade;
        $pagedata['go']=urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $pagedata['mainfile'] = "topc/payment/payment.html";

        $pagedata['hasDepositPassword']=app::get('topc')->rpcCall('user.deposit.password.has', ['user_id'=>userAuth::id()]);
        return $this->page('topc/payment/index.html', $pagedata);
    }

    public function createPay(){
        $filter = input::get();
        $filter['user_id'] = userAuth::id();
        $filter['user_name'] = userAuth::getLoginName();
        if($filter['merge']){
            $ifmerge = $filter['merge'];
            unset($filter['merge']);
        }

        try
        {
            $paymentId = kernel::single('topc_payment')->getPaymentId($filter);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topc_ctl_member_trade@tradeList');
            echo '<meta charset="utf-8"><script>alert("'.$msg.'");location.href="'.$url.'";</script>';
            exit;
        }
        $url = url::action('topc_ctl_paycenter@index',array('payment_id'=>$paymentId,'merge'=>$ifmerge));
        return $this->splash('success',$url,$msg,true);
    }

    public function dopayment(){
        $postdata = input::get();
        $payment = $postdata['payment'];
        $payment['deposit_password'] = $postdata['deposit_password'];
        $payment['user_id'] = userAuth::id();
        $payment['platform'] = "pc";
        $syncUrl=config::get('link.lishe_shop_url').'Interface/syncOrderItem';
    	$syncData=array(
    		'paymentId'=>$payment['payment_id']
    	);
    	$return=kernel::single('base_httpclient')->post($syncUrl,$syncData);
    	@file_put_contents('/data/www/b2b2c/public/business/logs/syncOrderItem/syncOrderItem_'.date('Ymd').'.txt','url:'.$syncUrl.' data:'.json_encode($syncData).' return:'.$return."\n",FILE_APPEND);
        //增加东莞移动E卡通支付 赵尊杰 2016-07-01
        if($payment['pay_app_id']=='e-card'){
        	$checkPassword = kernel::single('sysuser_data_deposit_password')->checkPassword($payment['user_id'],$payment['deposit_password'],'e-card');
	        if($checkPassword!==true){
	            return $this->errorPay($payment['payment_id'],$checkPassword);
	        }
        	//第三方E卡支付订单接口 赵尊杰 2016-07-01
	    	$url=config::get('link.lishe_shop_url').'Interface/syncEcardOrder';
	    	$data=array(
	    		'paymentId'=>$payment['payment_id'],
	    		'fee'=>$payment['money'],
	    		'tids'=>$payment['tids'],
	    		'payPassword'=>$payment['deposit_password'],
	    		'userId'=>$payment['user_id'],
	    		'ip'=>''
	    	);
	    	
			$return=kernel::single('base_httpclient')->post($url,$data);
			@file_put_contents('/data/www/b2b2c/public/business/logs/gd10086/e-card_'.date('Ymd').'.txt','url:'.$url.' data:'.json_encode($data).' return:'.$return."\n",FILE_APPEND);
			$ret=json_decode($return,true);
			if($ret['result']==100){
				if($ret['errcode']===0){
					return redirect::action('topc_ctl_paycenter@finish',array('payment_id'=>$payment['payment_id']));					
				}else{
					if(!empty($ret['msg'])){
						$msg=$ret['msg'];
					}else{
						$msg='支付失败，请稍后重试！';
					}
					return $this->errorPay($payment['payment_id'],$msg);
				}
			}else{
				return $this->errorPay($payment['payment_id'],'网络原因支付失败，请重试！');
			}
		}else{
	        try{
	            app::get('topc')->rpcCall('payment.trade.pay',$payment);
	        }catch(Exception $e){
	            $msg = $e->getMessage();
	            return $this->errorPay($payment['payment_id'],$msg);
	        }
	    }
        $url = url::action('topc_ctl_paycenter@finish',array('payment_id'=>$payment['payment_id']));
        return $this->splash('success',$url,$msg,true);
    }

    //用来确认支付单是否支付成功
    public function checkPayments(){
        $postdata = input::get();
        if(!is_numeric($postdata['payment_id']))
        {
            $this->splash('failed',null,"payment_id格式错误",true);exit;
        }
        $params['payment_id'] = $postdata['payment_id'];
        $result = app::get('topc')->rpcCall('payment.checkpayment.statu',$params);
        return $result;
    }

    public function finish($postdata = array()){
        if(!$postdata){
            $postdata = input::get();
        }

        //查看订单付款状态并做出判断
        $params['payment_id'] = $postdata['payment_id'];
        $result = app::get('topc')->rpcCall('payment.checkpayment.statu',$params);
        if( $result !='succ'){
            $msg = '订单支付失败，请重试';
            return $this->errorPay($params['payment_id'], $msg, $result);
        }
        try{
            $params['payment_id'] = $postdata['payment_id'];
            $params['fields'] = 'payment_id,status,pay_app_id,pay_name,money,cur_money';
            $result = app::get('topc')->rpcCall('payment.bill.get',$params);
            $result['money_deposit'] = $result['money'] * 100;
        }catch(Exception $e){
            $msg = $e->getMessage();
        }
        
    	//第三方订单接口 赵尊杰 2016-06-15
    	$data=array(
    		'paymentId'=>$postdata['payment_id']
    	);
    	$url = config::get('link.lishe_shop_url').'Interface/syncOrder';
		kernel::single('base_httpclient')->post($url,$data);
        $result['num'] = count($result['trade']);
        $pagedata['msg'] = $msg;
        $pagedata['payment'] = $result;
        $pagedata['mainfile'] = "topc/payment/finish.html";

        if('deposit' == $result['pay_app_id']){
            // setcookie('UPINTEGRAL','1');
            $pagedata['payment_id'] = $postdata['payment_id'];
            $pagedata['mainfile'] = "topc/payment/finish_deposit.html";
        }else{
            $pagedata['mainfile'] = "topc/payment/finish.html";
        }
        return $this->page('topc/payment/index.html', $pagedata);
    }


    /**
     *  订单错误页面提示
     *  @param int $paymentId
     *  @param string $msg
     *  @param string $result
     *  @return void
     * */
    public function errorPay($paymentId, $msg = '', $result=''){
        $postdata = input::get();
        if($postdata['payment_id']){
            $paymentId = $postdata['payment_id'];
        }
        if(!$paymentId){
            kernel::abort('404');
        }
        $params['payment_id'] = $paymentId;

        $notice = '订单支付失败，请重试';
        $msg = $msg ? $msg : $notice;
        $pagedata = array();

        //status表示订单是否存在
        $pagedata['status'] = true;
        $pagedata['msg'] = $msg;

        //判断订单状态
        if(!$result){
            $result = app::get('topc')->rpcCall('payment.checkpayment.statu',$params);
            if(!$result){
                $pagedata['msg'] = '订单不存在';
                $pagedata['status'] = false;
                return $this->page('topc/payment/error.html', $pagedata);
            }
        }

        if( $result !='succ'){
            //获取订单详情
            $params['fields'] = 'cur_money';
            $paymentBill = app::get('topc')->rpcCall('payment.bill.get',$params);
            $trade = $paymentBill['trade'];
            $tids = array_keys($trade);
            $iparams['tid'] = $tids;
            $iparams['user_id'] = userAuth::id();
            $iparams['fields'] = "tid,orders.title";
            $itrade = app::get('topc')->rpcCall('trade.get',$iparams);
            $orders = $itrade['orders'];
            $pagedata['cur_money'] = $paymentBill['cur_money'];
            $pagedata['orders'] = $orders;
            $pagedata['payment_id'] = $paymentId;

            return $this->page('topc/payment/error.html', $pagedata);
        }else{
            return redirect::action('topc_ctl_paycenter@finish', array('payment_id' => $postdata['payment_id']));
        }

    }
}


