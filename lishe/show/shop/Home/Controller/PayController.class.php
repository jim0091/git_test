<?php
namespace Home\Controller;
class PayController extends CommonController {
    private $jdShopId;
  public function __construct(){
    parent::__construct();
        if(empty($this->uid)){
            header("Location:".__APP__."/Sign/index");
            exit;
        }   
        $this->areaModel=M('site_area');
        $this->userModel=M('sysuser_user');//用户表
        $this->addrModel=M('sysuser_user_addrs'); //收货地址表
        $this->cartModel=M('systrade_cart'); //购物车表
        $this->itemModel=M('sysitem_item');//产品表
        $this->skuModel=M('sysitem_sku');//货品的库存
        $this->skuStoreModel=M('sysitem_sku_store');//货品的库存
        $this->shopModel=M('sysshop_shop');//店铺信息
        $this->logisticsModel=M('syslogistics_dlytmpl');//快递信息表
        $this->tradeModel=M("systrade_trade");//订单表
        $this->tradeOrderModel=M("systrade_order");//订单子表
        $this->userDepositModel = M('sysuser_user_deposit');//积分表
        $this->postageModel=M('syspromotion_freepostage');//包邮表
        $this->paymentsModel = M("ectools_payments");//支付表
        $this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
        $this->userAccountModel = M('sysuser_account');//用户登录表
        $this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
        $this->modelPay = D('Pay');
	   $this->dActivity=D('Activity');
        $this->jdShopId = C('JD_SHOP_ID');  

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
        $postData['uid'] = $this->uid;
        $postData['orderFrom'] = $this->getComDomain();//pc：心意商城，show：企业秀，app：手机app，ws微信商城
        //调用订单生成接口
        $strData = json_encode($postData);
        $data = array(
            'strData' => $strData,
            'source'  => 'company'
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
                $this->error("京东接口通讯失败！", U('Order/Cart'));
            }
            if($syncRes['errcode'] > 0){
            	$errMsg = empty($jdRes['msg']) ? '抱歉，京东订单同步失败！' : $jdRes['msg'];
                $logoData = array(
                    'rel_id' =>'1',
                    'op_name' =>"系统",
                    'op_role' =>"system",
                    'behavior' =>"cancel",
                    'log_text' => $errMsg,
                    'log_time' =>time()
                ); 
                $this->orderLog($logoData);
                $this->error($errMsg, U('Order/Cart'));
            }
        }
        redirect('pay/paymentid/'.$res['data']['paymentId']);        
    }

    //生成支付数据（用于用户个人中心的二次支付使用）
    public function creatPayments(){
        $tid = I('get.tid');
        if (trim($tid)) {
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
            'terminalType'=>'PC'
        );
        $url=C('API').'pointActive/getAllPointActive';
        $res = json_decode($this->requestPost($url,$data),true);      

        $this->assign('paymentInfo',$paymentInfo);
        $this->assign('paymentid',$paymentid);
        $this->assign('userDepositInfo',$userDepositInfo);
        $this->assign('rules',$res['data']['info']);
        $this->assign('payType',$this->getPayType());
        $this->display();
    }


    //支付操作
    public function operPay(){
        $paymentid = I('post.paymentid');
        $pwd = I('post.pwd');
        $payType = I('post.payType');
        $payName = I('post.payName');
        if (empty($paymentid)) {
        	echo json_encode(array(0,"支付错误！"));
        	exit();
        }
        $userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();
        //用户登录信息表
        $userAccountInfo = $this->userAccountModel->where('user_id ='.$this->uid)->find();
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentid)->field('user_id,status,money,cash_fee,payed_cash,point_fee,payed_point')->find();
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
        if(in_array($this->jdShopId , $shopidArry)){
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
            if (empty($value['type'])) {   
                if ($value['price'] != $value['item_price'] || $value['item_cost_price'] != $value['cost_price']) {
                    echo json_encode(array(0,"订单已超过支付期限，请重新下单！")); 
                    exit();     
                }
            }
        }
        //积分支付
        if ($paymentInfo['point_fee'] != 0 && $paymentInfo['payed_point'] == 0) {
            //调用支付接口
            $data=array(
            	'paymentId'=>$paymentid,
            	'payType'=>$payName
            );
            $url=C('COMMON_API').'Order/apiPayOrder';
            $resPay = $this->requestPost($url,$data);
            $resPay = trim($resPay, "\xEF\xBB\xBF");//去除BOM头
            $res = json_decode($resPay,true);
            if ($res['result'] != 100) {
            	$this->makeLog('payOp','user_id:'.$this->uid.' operator:'.$userAccountInfo['mobile'].' fee:'.$paymentInfo['money'].' message:error:1000,错误信息：接口通讯失败！ time:'.time()."\r\n");
            	echo json_encode(array(0,'接口通讯失败！'));
            	exit();
            }
            //支付失败，日志表
            if($res['errcode'] > 0){
            	$this->makeLog('payOp','user_id:'.$this->uid.' operator:'.$userAccountInfo['mobile'].' fee:'.$paymentInfo['money'].' message:支付失败:'.$res['msg'].' time:'.time()."\r\n");
            	echo json_encode(array(0,$res['msg']));
            	exit();
            }
        }
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentid)->field('user_id,status,money,cash_fee,payed_cash,point_fee,payed_point')->find();
        //现金支付
        if ($paymentInfo['cash_fee'] != '0' && $paymentInfo['payed_cash'] == '0') {
            $paymentType = 'alipay';
            echo json_encode(array(3,$paymentid,$paymentType));
            exit();
        }
        //只有单一积分支付成功才执行
        if ($paymentInfo['cash_fee'] == '0' && $paymentInfo['payed_cash'] == '0' && $paymentInfo['point_fee'] != '0' && $paymentInfo['payed_point'] != '0') {             
            $condition=array(
            		'tid'=>array('in',implode(',',$tidarry))
            );
            $trade=$this->tradeModel->field('tid,shop_id')->where($condition)->select();
            $jd = 0;
            $sf = 0;
            $jdShopId = C('JD_SHOP_ID');
            foreach ($trade as $key => $value) {
            	if ($value['shop_id'] == $jdShopId) {
            		$jd = 1;
            	}else{
            		$sf = 1;
            	}
            }
            if($jd==1){
            	//调用京东确认预占库存订单
            	$syncData = array('paymentId'=>$paymentid,'opType'=>'pay');
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
            				'log_text' => '京东确认预占库存订单接口通讯失败！',
            				'log_time' =>time()
            		);
            		$this->orderLog($logoData);
            		//$this->error("京东接口通讯失败！");
            		//echo json_encode(array(0, '京东接口通讯失败！'));
            		//exit();
            	}
            	if($syncRes['errcode'] > 0){
            		$logoData = array(
            				'rel_id' =>'1',
            				'op_name' =>"系统",
            				'op_role' =>"system",
            				'behavior' =>"cancel",
            				'log_text' => $syncRes['msg'],
            				'log_time' =>time()
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
            	$syncData = array('paymentId'=>$paymentid);
            	$syncUrl=C('COMMON_API').'Sf/orderPostSf';
            	$syncReturn=$this->requestPost($syncUrl,$syncData);
            	$syncReturn = trim($syncReturn, "\xEF\xBB\xBF");//去除BOM头
            	$syncRes = json_decode($syncReturn,true);
            
            	if ($syncRes['result'] != 100) {
            		$logoData = array(
            				'rel_id' =>$paymentid,
            				'op_name' =>"系统",
            				'op_role' =>"system",
            				'behavior' =>"cancel",
            				'log_text' => '订单推送顺丰仓库接口通讯失败！',
            				'log_time' =>time()
            		);
            		$this->orderLog($logoData);
            		//$this->error("订单推送顺丰仓库接口通讯失败！");
            		//echo json_encode(array(0,'订单推送顺丰仓库接口通讯失败！'));
            		//exit();
            	}
            	if($syncRes['errcode'] > 0){
            		$logoData = array(
            				'rel_id' =>$paymentid,
            				'op_name' =>"系统",
            				'op_role' =>"system",
            				'behavior' =>"cancel",
            				'log_text' => $syncRes['msg'],
            				'log_time' =>time()
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

    //支付页面
    public function activityPay(){
        $paymentid = I("get.paymentid");
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
    public function operPayActivity(){
        $paymentid = I('post.paymentid');
        $pwd = I('post.pwd');
        $payType = I('post.payType');
        if (empty($paymentid)) {
            echo json_encode(array(0,"支付错误！"));
            exit();
        }
        $userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();
        //用户登录信息表
        $userAccountInfo = $this->userAccountModel->where('user_id ='.$this->uid)->find();
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentid)->field('user_id,status,money')->find();
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
            'atid'=>array('in',implode(',',$tidarry))
        );
        $tradeList = $this->dActivity->getTradeList($conditionTrade);
        if (!$tradeList) {
            echo json_encode(array(0,"订单已超过支付期限，请重新下单！")); 
            exit();
        }
        foreach ($tradeList as $kTrade => $vTrade) {
            if ($vTrade['creat_time']+60*60*24 < time()) {
                echo json_encode(array(0,"订单已超过支付期限，请重新下单！")); 
                exit();
            }
        }
				$conditionOrder['atid']=array('in',$tidarry);
        $orderList = $this->dActivity->getOrderList($conditionOrder);
        if (!$orderList) {
            echo json_encode(array(0,"订单已超过支付期限，请重新下单！")); 
            exit();
        }
        foreach ($orderList as $key => $value) {
            if ($value['approve_status'] == "instock") {                
                echo json_encode(array(0,"订单中存在已下架的商品，请重新下单！")); 
                exit();
            }
//          if ($value['price'] != $value['item_price']) {
//              echo json_encode(array(0,"订单已超过支付期限，请重新下单!！")); 
//              exit();     
//          }
        }

        //调用支付接口
        $data=array(
            'paymentId'=>$paymentid,
            'orderType'=>'activity'
        );
        $url=C('COMMON_API').'Order/apiPayOrder';
        $resPay = $this->requestPost($url,$data);
        $resPay = trim($resPay, "\xEF\xBB\xBF");//去除BOM头 
        $res = json_decode($resPay,true); 
        if ($res['result'] != 100) {
            $this->makeLog('payOp','user_id:'.$this->uid.' operator:'.$userAccountInfo['mobile'].' fee:'.$paymentInfo['money'].' message:error:1000,错误信息：接口通讯失败！ time:'.time()."\r\n");
            echo json_encode(array(0,'接口通讯失败！')); 
            exit();
        }
        //支付失败，日志表
        if($res['errcode'] > 0){            
            $this->makeLog('payOp','user_id:'.$this->uid.' operator:'.$userAccountInfo['mobile'].' fee:'.$paymentInfo['money'].' message:支付失败:'.$res['msg'].' time:'.time()."\r\n");
            echo json_encode(array(0,$res['msg']));
            exit();
        }
        echo json_encode(array(1,$res['msg']));
    }
    //支付结束跳转活动
    public function payPromptActivity(){
        $payStatus = I("get.payStatus");
        $paymentId = I("get.paymentId");
        if ($paymentId) {
            $paymentInfo = $this->paymentsModel->where('payment_id ='.$paymentId)->field('money')->find();
            $paymentInfo['integral'] = floatval($paymentInfo['money'])*100;
        }

        $this->assign('payStatus',$payStatus);
        $this->assign('paymentInfo',$paymentInfo);
        $this->display();
    }
    //支付结束跳转
    public function payPrompt(){
        $payStatus = I("get.payStatus");
        $paymentId = I("get.paymentId");
        if ($paymentId) {
            $paymentInfo = $this->paymentsModel->where('payment_id ='.$paymentId)->find();
            $paymentInfo['integral'] = floatval($paymentInfo['money'])*100;
        }

        $this->assign('payStatus',$payStatus);
        $this->assign('paymentInfo',$paymentInfo);
        $this->display();
    }

    //积分充值操作
    public function inteRechDo(){
        if ($this->userName) {
            $money = I("post.money");
            if (floatval($money)) {    
                //插入支付表
                $data['payment_id'] = date(YmdHis).$this->uid.'1';//支付单号
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

    
    public function setPayPwd(){
        $paymentid = I('get.paymentid');
        $this->assign('paymentid',$paymentid);
        $this->display();
    }
    //设置支付密码
    public function doSetPayPwd(){
        $data['md5_password'] = md5(I('post.payPwd'));
        $res = $this->userDepositModel->where('user_id ='.$this->uid)->data($data)->save(); 
        if ($res !== false) {
            echo 1;
        }else{
            echo 0;
        }
    }

    //找回支付密码
    public function retrievePwd(){
        $paymentid = I('get.paymentid');
        $accountInfo = $this->userAccountModel->where('user_id ='.$this->uid)->find();
        $this->assign('accountInfo',$accountInfo);
        $this->assign('paymentid',$paymentid);
        $this->display();
    }
    //发送手机验证码
    public function sendPhoneCode(){        
        vendor('SendPhoneCode.SendCode','','.php');
        $phone = I("post.phone"); 
        $randomNumber=rand(10000,99999);
        session('phoneCode',md5($randomNumber));
        cookie('phoneCode',md5($randomNumber),3600);  
        $content = "您的礼舍验证码：".$randomNumber."。";
        $sendCode = new \SendCode();
        $codeResult = $sendCode->sendPhoneCode($phone,$content);
        if ($codeResult['message'] == "成功") {
            echo 1;
        }else{
            echo 0;
        }
    }
    //找回密码操作
    public function retrievePwdDo(){
        $phoneCode = I('post.code');
        if (md5($phoneCode) == cookie('phoneCode') || md5($phoneCode) == session('phoneCode')) {
            echo 1;
        }else{
            echo 0;
        }
    }

}
