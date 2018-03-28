<?php
namespace Home\Controller;
class PayController extends CommonController {
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			redirect(__APP__."/Login/login/index");
		}
		$this->tradeModel=M("systrade_trade");//订单表
		$this->userDepositModel = M('sysuser_user_deposit');//积分表
		$this->paymentsModel = M("ectools_payments");//支付表
		$this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
		$this->userAccountModel = M('sysuser_account');//用户登录表
		$this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
        $this->modelPay = D('Pay');
	}
    //提交订单日志记录
    public function orderLog($data){
        return M("systrade_log")->data($data)->add();
    }

    //提交订单
    public function addUserOrder(){
        $postData['remark'] = I('post.remark');//买家留言
        $postData['shopIds'] = I('post.shopIds');//店铺id
        $postData['itemIds'] = I('post.itemIds');//商品ids
        $postData['skuIds'] = I('post.skuIds');//库存规格ids
        $postData['dlytmplIds'] = I('post.dlyTmplIds');//配送模板id
        $postData['postFees'] = I('post.postFees');//邮费
        $postData['tradeType'] = I('post.tradeType');   
        $postData['activityId'] = I('post.activityId');  
        $postData['orderFrom'] = "ws";//pc：心意商城，show：企业秀，app：手机app，ws微信商城
        $postData['uid'] = $this->uid;
        //调用订单生成接口
        $strData = json_encode($postData);
        $data = array(
            'strData' => $strData
        );
        $url=C('COMMON_API').'Order/apiCreateOrder';  
        $return=$this->requestPost($url,$data); 
        $return = trim($return, "\xEF\xBB\xBF");//去除BOM头
        $res = json_decode($return,true);   
        if ($res['result'] != 100) {
            $logoData = array(
                'rel_id' =>'1',
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '生成订单接口通讯失败！',
                'log_time' =>time()
            );            
            $this->orderLog($logoData);
            $this->error("接口通讯失败！");
        }
        //支付失败，日志表
        if($res['errcode'] > 0){            
            $logoData = array(
                'rel_id' =>'1',
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => $res['msg'],
                'log_time' =>time()
            ); 
            $this->orderLog($logoData);
            $this->error($res['msg']);
        }

        if (in_array(C('JD_SHOP_ID'), $postData['shopIds'])) {
            $syncData = array('paymentId'=>$res['data']['paymentId'],'opType'=>'creat');
            $syncUrl=C('COMMON_API').'Order/apiSyncOrder';       
            $syncReturn=$this->requestPost($syncUrl,$syncData);
            $syncReturn = trim($syncReturn, "\xEF\xBB\xBF");//去除BOM头
            $syncRes = json_decode($syncReturn,true);
            if ($syncRes['result'] != 100) {
                $logoData = array(
                    'rel_id' =>'1',
                    'op_name' =>"系统",
                    'op_role' =>"system",
                    'behavior' =>"cancel",
                    'log_text' => '同步京东订单接口通讯失败！',
                    'log_time' =>time()
                );            
                $this->orderLog($logoData);
                $this->error("京东接口通讯失败！");
            }
            if($syncRes['errcode'] > 0){            
                $logoData = array(
                    'rel_id' =>'1',
                    'op_name' =>"系统",
                    'op_role' =>"system",
                    'behavior' =>"cancel",
                    'log_text' => $jdRes['msg'],
                    'log_time' =>time()
                ); 
                $this->orderLog($logoData);
                $this->error($jdRes['msg']);
            }
        }
        redirect('pay/paymentid/'.$res['data']['paymentId']);        
    }

    //生成支付数据（用于用户个人中心的二次支付使用）
    public function creatPayments(){
        $tid = I('get.tid');
        if (trim($tid)) {

            //用于旧版判断用户订单是否来源微信商城
            $tradeInfo = $this->tradeModel->where('tid='.$tid)->find();
            if ($tradeInfo['trade_from'] == "ws" && $this->comId == '1467166836740') {
                echo json_encode(array(0,'无法支付微商城订单，请到电脑端下单！'));
            }

            $jsonTid = json_encode(array($tid));
            $data = array('tid'=>$jsonTid,'uid'=>$this->uid);
            $url=C('COMMON_API').'Order/creatPayments';
            $return=$this->requestPost($url,$data);
            $return = trim($return, "\xEF\xBB\xBF");//去除BOM头
            $res = json_decode($return,true);
            if ($res['result'] != 100) {
                $logoData = array(
                    'rel_id' =>'1',
                    'op_name' =>"系统",
                    'op_role' =>"system",
                    'behavior' =>"cancel",
                    'log_text' => '二次支付接口通讯失败！',
                    'log_time' =>time()
                );            
                $this->orderLog($logoData);
                $this->error("接口通讯失败！");
            }
            if($res['errcode'] > 0){            
                $logoData = array(
                    'rel_id' =>'1',
                    'op_name' =>"系统",
                    'op_role' =>"system",
                    'behavior' =>"cancel",
                    'log_text' => $res['msg'],
                    'log_time' =>time()
                ); 
                $this->orderLog($logoData);
                $this->error($res['msg']);
            }
            
            if ($res['data']['paymentId']) {
                //判断是否是东莞移动用户
                $payType = 0;
                if ($this->comId == '1467166836740') {
                    $payType = 1;
                }
                echo json_encode(array(1,$res['data']['paymentId'],$payType)); 
            }else {
                echo json_encode(array(2,0)); 
            } 
        }else{
            echo json_encode(array(0,'支付参数错误！'));
        } 
    }

    //更新本地积分
    public function syncBalance($userId,$balance){
        $checkBalance=$this->userDepositModel->field('user_id')->where('user_id='.$userId)->find();
        if(empty($checkBalance['user_id'])){
            $balance['user_id']=$userId;
            return $this->userDepositModel->add($balance);
        }
        return $this->userDepositModel->where('user_id='.$userId)->save($balance);
    }

    //支付页面
    public function pay(){
        $paymentid = I("get.paymentid");

        //更新积分
        $sign=md5('phoneNum='.$this->account.C('API_KEY'));
        $data=array(
            'phoneNum'=>$this->account,
            'sign'=>$sign
        );
        $point=$this->requestPost(C('API').'mallPoints/getUserPoints',$data);
        $point = trim($point, "\xEF\xBB\xBF");//去除BOM头
        $point=json_decode($point,TRUE);
        $data=$point['data']['info'];
        if (!empty($data['phoneNum'])) {
            //更新积分
            $balance=array(
                'deposit'=>$data['userPointsList'][0]['remainScore']/100,
                'balance'=>$data['userPointsList'][0]['remainScore']
            );
            $this->syncBalance($this->uid,$balance);
        }

        //积分表
        $userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id ='.$paymentid)->find();
        $paymentInfo['deposit'] = floatval($paymentInfo['money'])*100;
        //同步一企一舍积分
        $data=array(
            'terminalType'=>'WAP'
        );
        $url=C('API').'pointActive/getAllPointActive';
        $res = json_decode($this->requestPost($url,$data),true);
        $this->assign('paymentInfo',$paymentInfo);
        $this->assign('paymentid',$paymentid);
        $this->assign('userDepositInfo',$userDepositInfo);
        $this->assign('rules',$res['data']['info']);
        $this->display();
    }


    //支付操作
    public function operPay(){
    	$paymentid = I('post.paymentid');
    	$pwd = I('post.pwd');
    	$payType = I('post.payType');
        $jdShopId = C('JD_SHOP_ID');
    	if (empty($paymentid)) {
    		echo json_encode(array(0,"支付错误！"));
    		exit();
    	}
    	$userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();
    	//用户登录信息表
    	$userAccountInfo = $this->userAccountModel->where('user_id ='.$this->uid)->find();
    	//支付表
    	$paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentid)->field('user_id,status,money,cash_fee,payed_cash,point_fee,payed_point')->find();
    	//检查是否有足够积分
    	if ($paymentInfo['money'] > $userDepositInfo['deposit']) {
    		echo json_encode(array(0,"积分不足，请充值！"));
    		exit();
    	}
    	//检查该订单是否是自己的
    	if ($paymentInfo['user_id'] != $this->uid) {
    		echo json_encode(array(0,"无法为他人买单！"));
    		exit();
    	}
    	//支付子表
    	$paymentBillInfo = $this->tradePaybillModel->where('payment_id = '.$paymentid)->field('payment_id,paybill_id,tid')->select();
    	$tidarry = array();
    	foreach ($paymentBillInfo as $key => $value) {
    		$tidarry[$key] = $value['tid'];
    	}
    	//检查是否已经支付
    	if ($paymentInfo['status'] == 'succ') {
    		//已经支付不可再次支付
    		echo json_encode(array(2,"该订单已经支付！"));
    		exit();
    	}
    	//支付密码不正确
    	if (md5($pwd) != $userDepositInfo['md5_password']) {
    		echo json_encode(array(0,"支付密码不正确！"));
    		exit();
    	}
    	
    	//检查支付情况
    	if (!is_array($paymentBillInfo)) {
    		echo json_encode(array(0,"支付失败！"));
    		exit();
    	}
    	//校验订单数据
    	$conditionTrade=array(
    			'tid'=>array('in',implode(',',$tidarry))
    	);
    	$tradeList = $this->modelPay->getTradeList($conditionTrade);
    	if (!$tradeList) {
    		echo json_encode(array(0,"订单已超过支付期限，请重新下单！"));
    		exit();
    	}
    	foreach ($tradeList as $kTrade => $vTrade) {
            $shopidArry[] = $vTrade['shop_id'];
    		if ($vTrade['created_time']+60*60*24 < time()) {
    			echo json_encode(array(0,"订单已超过支付期限，请重新下单！"));
    			exit();
    		}
    	}
        if(in_array($jdShopId , $shopidArry)){
            //校验京东商品是否有京东单号
            $jdTradeNo = $this->modelPay->getJdTrade('payment_id='.$paymentid);
            if (!$jdTradeNo['sync_order_id']) {
                echo json_encode(array(0,"订单已超过支付期限，请重新下单！！")); 
                exit();                
            }
        }
    	$conditionOrder = " and tid in (".implode(',',$tidarry).")";
    	$orderList = $this->modelPay->getOrderList($conditionOrder);
    	if (!$orderList) {
    		echo json_encode(array(0,"订单已超过支付期限，请重新下单！"));
    		exit();
    	}
    	foreach ($orderList as $key => $value) {
    		if ($value['approve_status'] == "instock") {
    			echo json_encode(array(0,"订单中存在已下架的商品，请重新下单！"));
    			exit();
    		}
            // if ($value['price'] != $value['item_price']) {
            //     echo json_encode(array(0,"订单已超过支付期限，请重新下单!!!"));
            //     exit();
            // }
    	}
        //积分支付
        if ($paymentInfo['status'] != 'succ' && $paymentInfo['point_fee'] != '0' && $paymentInfo['payed_point'] == '0') {
            //调用支付接口
            $data = array(
                'paymentId' => $paymentid
            );
            $url = C('COMMON_API') . 'Order/apiPayOrder';
            $resPay = $this->requestPost($url, $data);
            $resPay = trim($resPay, "\xEF\xBB\xBF");//去除BOM头
            $res = json_decode($resPay, true);
            if ($res['result'] != 100) {
                $this->makeLog('payOp', 'user_id:' . $this->uid . ' operator:' . $userAccountInfo['mobile'] . ' fee:' . $paymentInfo['money'] . ' message:error:1000,错误信息：接口通讯失败！ time:' . time() . "\r\n");
                echo json_encode(array(0, '接口通讯失败！'));
                exit();
            }
            //支付失败，日志表
            if ($res['errcode'] > 0) {
                $this->makeLog('payOp', 'user_id:' . $this->uid . ' operator:' . $userAccountInfo['mobile'] . ' fee:' . $paymentInfo['money'] . ' message:支付失败:' . $res['msg'] . ' time:' . time() . "\r\n");
                echo json_encode(array(0, $res['msg']));
                exit();
            }
        }
        //支付表，获取更新后的
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentid)->field('user_id,status,money,cash_fee,payed_cash,point_fee,payed_point')->find();
        //现金支付
        if ($paymentInfo['cash_fee'] != '0' && $paymentInfo['payed_cash'] == '0') {
            echo json_encode(array(3,$paymentid));
            exit();
        }

        if ($paymentInfo['cash_fee'] == '0' && $paymentInfo['payed_cash'] == '0' && $paymentInfo['point_fee'] != '0' && $paymentInfo['payed_point'] != '0') {
            $condition = array(
                'tid' => array('in', implode(',', $tidarry))
            );
            $trade = $this->tradeModel->field('tid,shop_id')->where($condition)->select();
            $jd = 0;
            $sf = 0;
            foreach ($trade as $key => $value) {
                if ($value['shop_id'] == $jdShopId) {
                    $jd = 1;
                } else {
                    $sf = 1;
                }
            }
            if ($jd == 1) {
                //调用京东确认预占库存订单
                $syncData = array('paymentId' => $paymentid, 'opType' => 'pay');
                $syncUrl = C('COMMON_API') . 'Order/apiSyncOrder';
                $syncReturn = $this->requestPost($syncUrl, $syncData);
                $syncReturn = trim($syncReturn, "\xEF\xBB\xBF");//去除BOM头
                $syncRes = json_decode($syncReturn, true);
                if ($syncRes['result'] != 100) {
                    $logoData = array(
                        'rel_id' => '1',
                        'op_name' => "系统",
                        'op_role' => "system",
                        'behavior' => "cancel",
                        'log_text' => '京东确认预占库存订单接口通讯失败！',
                        'log_time' => time()
                    );
                    $this->orderLog($logoData);
                    //$this->error("京东接口通讯失败！");
                    //echo json_encode(array(0, '京东接口通讯失败！'));
                    //exit();
                }
                if ($syncRes['errcode'] > 0) {
                    $logoData = array(
                        'rel_id' => '1',
                        'op_name' => "系统",
                        'op_role' => "system",
                        'behavior' => "cancel",
                        'log_text' => $syncRes['msg'],
                        'log_time' => time()
                    );
                    $this->orderLog($logoData);
                    //$this->error($syncRes['msg']);
                    //echo json_encode(array(0,$syncRes['msg']));
                    //exit();
                }
                //暂时关闭顺丰仓库推送
            }

            ///////////////////////////////////////////////////////////////////零时允许用户数据 start，用完删除///////////////////////////////////////
            //$tmpAllow = false;
            //$allowUserList = array(104, 726, 4206);
            //$tmpAllow = in_array($this->uid, $allowUserList);
            //////////////////////////////////////////////////////////////////零时允许用户数据 end////////////////////////////////////////////

            if ($sf == 1) {
                //调用订单推送顺丰仓库接口
                $syncData = array('paymentId' => $paymentid);
                $syncUrl = C('COMMON_API') . 'Sf/orderPostSf';
                $syncReturn = $this->requestPost($syncUrl, $syncData);
                $syncReturn = trim($syncReturn, "\xEF\xBB\xBF");//去除BOM头
                $syncRes = json_decode($syncReturn, true);

                if ($syncRes['result'] != 100) {
                    $logoData = array(
                        'rel_id' => $paymentid,
                        'op_name' => "系统",
                        'op_role' => "system",
                        'behavior' => "cancel",
                        'log_text' => '订单推送顺丰仓库接口通讯失败！',
                        'log_time' => time()
                    );
                    $this->orderLog($logoData);
                    //$this->error("订单推送顺丰仓库接口通讯失败！");
                    //echo json_encode(array(0,'订单推送顺丰仓库接口通讯失败！'));
                    //exit();
                }
                if ($syncRes['errcode'] > 0) {
                    $logoData = array(
                        'rel_id' => $paymentid,
                        'op_name' => "系统",
                        'op_role' => "system",
                        'behavior' => "cancel",
                        'log_text' => $syncRes['msg'],
                        'log_time' => time()
                    );
                    $this->orderLog($logoData);
                    //$this->error($syncRes['msg']);
                    //echo json_encode(array(0, $syncRes['msg']));
                    //exit();
                }
            }
        }
    	echo json_encode(array(1,$res['msg']));
    }


    //微信支付
    public function wxpay(){
        //导入微信支付
        vendor('WxpayAPI.wshop.Util.WxPayPubHelper','','.class.php');
        //=========步骤1：网页授权获取用户openid============
        $paymentId=trim(I('get.paymentId'));
        //通过code获得openid
        $JsApi = new \JsApi_pub();
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $jsApiCallUrl = \WxPayConf_pub::JS_API_CALL_URL."&paymentId=".$paymentId;
            $url = $JsApi->createOauthUrlForCode(urlencode($jsApiCallUrl));
            Header("Location: $url"); 
        }else{
            //获取code码，以获取openid
            $code = $_GET['code'];
            $JsApi->setCode($code);
            $openid = $JsApi->getOpenId();
        }
        
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find();       
        $unifiedOrder = new \UnifiedOrder_pub();
        $unifiedOrder->setParameter("openid",$openid);//商品描述
        if($paymentInfo['pay_type'] == 'online'){
            $unifiedOrder->setParameter("body","礼舍商城在线支付");//商品描述
            $unifiedOrder->setParameter("total_fee",$paymentInfo['cash_fee'] * 100);//总金额
        }else{
            $unifiedOrder->setParameter("body","礼舍商城在线充值");//商品描述
            $unifiedOrder->setParameter("total_fee",$paymentInfo['money'] * 100);//总金额
        }
        //$unifiedOrder->setParameter("body","礼舍商城在线支付");//商品描述
        //$unifiedOrder->setParameter("total_fee",$paymentInfo['money'] * 100);//总金额
        $unifiedOrder->setParameter("out_trade_no",$paymentId);//商户订单号
        //$unifiedOrder->setParameter("total_fee",100);//总金额
        $unifiedOrder->setParameter("notify_url",\WxPayConf_pub::NOTIFY_URL);//通知地址 
        $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型

        $prepay_id = $unifiedOrder->getPrepayId();
        //=========步骤3：使用JsApi调起支付============
        $JsApi->setPrepayId($prepay_id);

        $jsApiParameters = $JsApi->getParameters();

        //积分表
        $userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id ='.$paymentInfo['payment_id'])->find();

        $this->assign('paymentInfo',$paymentInfo);
        $this->assign('paymentid',$paymentInfo['payment_id']);
        $this->assign('userDepositInfo',$userDepositInfo);

        $this->assign("jsApiParameters",$jsApiParameters);

        if($paymentInfo['pay_type'] == 'online'){
            $this->display('onlinePay');
        }else{
            $this->display('inteRech');
        }
    }

    //微信支付返回结果
    public function payResult(){
        $res = $_GET['recode']; 
        //用户登录信息表
        $userAccountInfo = $this->userAccountModel->where('user_id ='.$this->uid)->find();
        $this->assign('res',$res); 
        $this->display();
    }

    //支付宝支付
    public function alipay(){
    	header("Content-type:text/html;charset=utf-8");
        //导入支付宝支付
        vendor('Alipay.wshop.lib.alipay_submit','','.class.php');
        vendor('Alipay.wshop.alipay','','.config.php'); 

        $paymentId=trim(I('get.paymentId'));
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find();
        //$type = I('type');
        if ($paymentInfo['pay_type'] == 'online') {
            $total_fee = round($paymentInfo['cash_fee'],2);
            $subject = "现金支付";
            $body = "现金支付";
        }else{
            $total_fee = round($paymentInfo['money'],2);
            $subject = "积分充值";
            $body = "积分充值";
        }

        $alipayConfig = alipayConfig();
        $parameter = array(
            "service"       => $alipayConfig['service'],
            "partner"       => $alipayConfig['partner'],
            "seller_id"  => $alipayConfig['seller_id'],
            "payment_type"  => $alipayConfig['payment_type'],
            "notify_url"    => $alipayConfig['notify_url'],
            "return_url"    => $alipayConfig['return_url'],
            "_input_charset"    => trim(strtolower($alipayConfig['input_charset'])),
            "out_trade_no"  => $paymentId,
            "subject"   => $subject,
            "total_fee" => $total_fee, //sprintf("%.2f", $paymentInfo['money']), 0.01, //
            "show_url"  => __APP__."/Pay/inteRechCenter",
            "body"  => $body,
        );
        $alipaySubmit = new \AlipaySubmit($alipayConfig);  
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");

        echo $html_text;
        
    }

    //支付宝同步支付返回结果
    public function aliPayReturnUrl(){
        vendor('Alipay.wshop.lib.alipay_notify','','.class.php');
        vendor('Alipay.wshop.alipay','','.config.php');        
        //计算得出通知验证结果
        $alipayConfig = alipayConfig();
        $alipayNotify = new \AlipayNotify($alipayConfig);
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {
            //验证成功                  
            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];
            //支付宝交易号
            $trade_no = $_GET['trade_no'];
            //交易状态
            $trade_status = $_GET['trade_status'];
            $paymentInfo = $this->paymentsModel->where('payment_id = '.$out_trade_no)->find();
            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                $this->assign('res','success');
                $logdata =array(
                    'type'=>'expense',
                    'user_id'=>$paymentInfo['user_id'],
                    'operator'=>$paymentInfo['user_name'],
                    'message'=>'支付成功',
                    'logtime'=>time()
                );
            }else{
                $this->assign('res','fail');
                $logdata =array(
                    'type'=>'expense',
                    'user_id'=>$paymentInfo['user_id'],
                    'operator'=>$paymentInfo['user_name'],
                    'message'=>'支付失败！',
                    'logtime'=>time()
                );
            }
        }else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            $logdata =array(
                'type'=>'expense',
                'user_id'=>$paymentInfo['user_id'],
                'operator'=>$paymentInfo['user_name'],
                'message'=>'验证失败！',
                'logtime'=>time()
            );
            //$this->userDataDepositLogModel->data($logdata)->add();
            $this->assign('res','fail');
        }
        //日志表        
        //$this->display('payResult');
        //日志表
        $this->userDataDepositLogModel->data($logdata)->add();
        $this->assign('paymentInfo',$paymentInfo);
        if ($paymentInfo['pay_type'] == 'recharge') {
            $this->display('Pay/payResult');
        }else{
            $this->display('Pay/onlinePayResult');
        }
    }

    //积分充值列表页面
    public function inteRechCenter(){
        $paymentList = $this->paymentsModel->where('pay_type = "recharge" and user_id ='.$this->uid)->field('payment_id,status,pay_type,money,pay_name')->order('payment_id desc')->select();
        $this->assign('paymentList',$paymentList);
        $this->display();
    }

    //积分充值页面
    public function inteRech(){
        //获取积分充值规则
        $data=array(
            'terminalType'=>'WAP'
        );
        $url=C('API').'pointActive/getAllPointActive';
        $res = json_decode($this->requestPost($url,$data),true);
        $rules=$res['data']['info'];
        $this->assign('rules',$rules);
        $this->assign('count',count($rules));
        $this->display();
    }

    //微信在线支付页面
    public function onlinePay(){
        $paymentId = I('get.paymentId');
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find();
        $this->assign('paymentInfo',$paymentInfo);
        $this->display();
    }

    //支付结果页面
    public function onlinePayResult(){
        $recode = I('get.recode');
        $this->assign('res',$recode);
        $this->display();
    }

    //积分充值操作
    public function inteRechDo(){
        if ($this->account) {
            $money = I("post.money");
            if (floatval($money)) {    
                //插入支付表
                $data['payment_id'] = date(ymdHis).$this->uid.'1';//支付单号
                $data['money'] = floatval($money);//需要支付的金额
                $data['cur_money'] = 0;//支付货币金额
                $data['user_id'] = $this->uid;
                $data['user_name'] = $this->account;
                $data['pay_type'] = 'recharge';
                $data['op_name'] = $this->userName; //操作员
                $data['bank'] = '预存款';//收款银行
                $data['pay_account'] = $this->uid;//支付账号
                $data['created_time'] = time();
                $result = $this->paymentsModel->data($data)->add();
                if ($result) {
                    echo json_encode(array(1,$data['payment_id']));
                }else{
                    echo json_encode(array(0,'订单生成失败，请重试！'));
                }
            }else{
                echo json_encode(array(0,'获取金额失败，请重试！'));
            }
        }else {
            echo json_encode(array(0,'手机号码信息不正确！'));
        }
    }

        /**
     * 会员扣费接口
     *
     * @params userId int 会员id
     * @params operator string 操作用户的账号/手机号码
     * @params fee float 金额
     *
     * @return bool 是否成功
     *
     */
    public function dedect($userId, $operator, $orderNumber, $fee, $memo){
        $url = C('API').'mallPoints/payOrder';
        $payFee=$fee*100;
        $sign=md5('orderno='.$orderNumber.'&phoneNum='.$operator.'&pointsAmount='.$payFee.'&pointsType=1lishe_md5_key_56e057f20f883e');

        $data=array(
            'phoneNum'=>$operator,
            'orderno'=>$orderNumber,
            'pointsAmount'=>$payFee,
            'pointsType'=>1,
            'sign'=>$sign
        );

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt ( $ch, CURLOPT_POST, 1 ); //启用POST提交
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
        $file_contents = curl_exec ( $ch );
        curl_close ( $ch );

        return json_decode($file_contents,TRUE);

    }

}