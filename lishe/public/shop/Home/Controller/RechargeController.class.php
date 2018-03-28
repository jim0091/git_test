<?php
namespace Home\Controller;
class RechargeController extends CommonController {
	public function __construct(){
        if(strstr($_SERVER["REQUEST_URI"],'createqrcode')){
            ob_start();
        } 
		parent::__construct();
        $this->userDepositModel = M('sysuser_user_deposit');//积分表
        $this->paymentsModel = M("ectools_payments");//支付表
        $this->userAccountModel = M('sysuser_account');//用户登录表
        $this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
        $this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
        $this->tradeModel=M("systrade_trade");//订单表
        $this->modelOrder=M('systrade_order');
        $this->modelItemStore=M('sysitem_item_store');//商品库存
        $this->modelItemSkuStore=M('sysitem_sku_store');
        $this->modelItemCount=M('sysitem_item_count');
        $this->modelItemSku=M('sysitem_sku');
        $this->modelSitemSku = M('supplier_item_sku');//供应商商品sku
	}
	
	public function index(){
        $this->error("未找到页面！");		
		//$this->display();
	}


	//支付宝充值(生成二维码页面)
	public function alipay(){
		header("Content-type:text/html;charset=utf-8");

		//导入支付宝支付
        vendor('Alipay.alipaydirect.lib.alipay_submit','','.class.php');
        vendor('Alipay.alipaydirect.alipay','','.config.php');

        $paymentId=trim(I('get.paymentId'));
	if(empty($paymentId)){
			echo '没有支付单号';
			exit;
       }
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->field('payment_id,money,cash_fee')->find(); 
        $type = I('type');
	if ($type) {
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
            "total_fee" => $total_fee,
            "show_url"  => C('LISHE_URL')."/user.php/Info/index.html",
            "body"  => $body,
        );
        $alipaySubmit = new \AlipaySubmit($alipayConfig);  
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");

        echo $html_text;
	}


    //支付宝同步支付返回结果
    public function aliPayReturnUrl(){     
        vendor('Alipay.alipaydirect.lib.alipay_notify','','.class.php');
        vendor('Alipay.alipaydirect.alipay','','.config.php');       
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
            if(empty($out_trade_no) or empty($trade_no)){
				echo '没有商户订单号或支付宝交易号';
				exit;
			}
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
                $logdata =array(
                    'type'=>'expense',
                    'user_id'=>$paymentInfo['user_id'],
                    'operator'=>$paymentInfo['user_name'],
                    'message'=>'支付失败！',
                    'logtime'=>time()
                );
                $this->assign('res','fail');
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
            $this->assign('res','fail');
        }
        //日志表        
        $this->userDataDepositLogModel->data($logdata)->add(); 
        $this->assign('paymentInfo',$paymentInfo);
        if ($paymentInfo['pay_type'] == 'recharge') {
            $this->display('Pay/payResult');
        }else{
            $this->display('Pay/payPrompt');
        }
        
    }

    //记录日志
    public function log_result($type,$content){
        $file  = 'logs/'.$type.'/log'.date('Ymd').'.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个  
        file_put_contents($file, $content,FILE_APPEND);
    }


    //支付宝异步通知
    public function alipayNotify(){
        vendor('Alipay.alipaydirect.lib.alipay_notify','','.class.php');
        vendor('Alipay.alipaydirect.alipay','','.config.php');         
        //计算得出通知验证结果
        $alipayConfig = alipayConfig();
        $alipayNotify = new \AlipayNotify($alipayConfig);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {
            //验证成功            
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no = $_POST['out_trade_no'];//商户订单号
            $trade_no = $_POST['trade_no'];//支付宝交易号
            if(empty($out_trade_no) or empty($trade_no)){
				echo '没有商户订单号或支付宝交易号';
				exit;
			}
            $trade_status = $_POST['trade_status'];//交易状态
            if($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //普通即时到账的交易成功状态
                $paymentId=trim($out_trade_no);
                $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find();
                if ($paymentInfo['pay_type'] == 'recharge') {
                    //积分充值
                    $resRecharge = $this->updateDeposit($paymentInfo,$trade_no);
                    if ($resRecharge != 'success') {
                        $this->log_result('alipay',"【积分更新失败】".date('Y-m-d H:i:s')."paymentId:".$paymentId."\r\n");
                        echo "fail";
                    }
                }
                if ($paymentInfo['pay_type'] == 'online') {
                    //现金支付
                    $resCashPay = $this->cashPay($paymentInfo,$trade_no);
                    if ($resCashPay != 'success') {
                        $this->log_result('alipay',"【现金支付失败】".date('Y-m-d H:i:s')."paymentId:".$paymentId."\r\n");
                        echo "fail";
                    }
                }
                
            }else if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                $this->log_result('alipay',"【普通即时到账】".date('Y-m-d H:i:s')."！\r\n");
            }else{
                $this->log_result('alipay',"【交易失败】".date('Y-m-d H:i:s')."！\r\n");
            }
        }else{
            //验证失败
            $this->log_result('alipay',"【验证失败】".date('Y-m-d H:i:s')."verify_result:".$verify_result."\r\n");
            echo "fail";
        }
    }

    //更新积分
    public function updateDeposit($paymentInfo,$trade_no){
        if (!$paymentInfo || !$trade_no) {
            return 'fail';
        }
        $paymentId = $paymentInfo['payment_id'];
        $this->log_result('alipay',"\r\n【更新积分开始】".date('Y-m-d H:i:s')." status:".$paymentInfo['status']."out_trade_no".$paymentId."\r\n");
        if ($paymentInfo && $paymentInfo['status'] != 'succ') {
            $this->log_result('alipay',"\r\n【更新积分开始】".date('Y-m-d H:i:s')." returnCode:".$_POST['total_fee']."\r\n");
            //用户登录信息表
            $uid=$paymentInfo['user_id'];
            $totalFee=$_POST['total_fee'];//单位元
            $balance=$_POST['total_fee']*100;//单位积分
            $userAccountInfo = $this->userAccountModel->where('user_id ='.$uid)->find();
            if(empty($userAccountInfo)){
                $this->log_result('alipay',"\r\n【获取用户信息失败】".date('Y-m-d H:i:s')."\r\n");
                return 'fail';
            }
            
            //同步一企一舍积分
            $sign=md5('orderno='.$paymentId.'&phoneNum='.$paymentInfo['user_name'].'&pointsAmount='.$balance.'&pointsType=1&terminalType=WAP'.C('API_KEY'));
            $this->log_result('alipay',"\r\n【更新一企一舍积分】".date('Y-m-d H:i:s')." addPoint:".$balance." sign:".$sign."\r\n");
            $url=C('API').'mallPoints/rechargeNew';
            $data=array(
                'phoneNum'=>$paymentInfo['user_name'],
                'pointsAmount'=>$balance,
                'orderno'=>$paymentId,
                'pointsType'=>1,
                'terminalType'=>'WAP',
                'sign'=>$sign
            );      
            $return=$this->requestPost($url,$data);
            $this->log_result('alipay','【一企一舍充值】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");                
            $retArr=json_decode($return,true);
            
            if($retArr['result']==100){         
                $this->log_result('alipay',"\r\n【更新本地积分】".date('Y-m-d H:i:s')." totalFee:".$totalFee." balance:".$balance."\r\n");
                //支付成功，更新本地积分
                $this->userDepositModel->where('user_id ='.$uid)->setInc('deposit',$totalFee);
                $this->userDepositModel->where('user_id ='.$uid)->setInc('balance',$balance);
                $this->userDepositModel->where('user_id ='.$uid)->setInc('commonAmount',$balance);
                //更新支付主表
                $zdata['cur_money'] = $totalFee;
                $zdata['pay_type'] = 'recharge';
                $zdata['pay_app_id'] = 'alipay';
                $zdata['pay_name'] = '支付宝支付';
                $zdata['memo'] = '商城充值[支付宝支付]';
                $zdata['payed_time'] = time();
                $zdata['status'] = 'succ';
                $zdata['trade_no'] = $trade_no;
                $zdata['ls_trade_no'] = $retArr['data']['info']['transno'];
                $this->log_result('alipay',"\r\n【更新支付主表】".date('Y-m-d H:i:s')." curMoney:".$totalFee." payType:".$zdata['pay_type']."\r\n");
                $zres = $this->paymentsModel->where('payment_id ='.$paymentInfo['payment_id'])->data($zdata)->save();
                $this->log_result('alipay',"\r\n【更新主表结果】".date('Y-m-d H:i:s')." paymentId:".$paymentInfo['payment_id']." return:".$zres."\r\n");
                return "success";
            }else{
                $this->log_result('alipay','【一企一舍充值失败】url:'.$url.'data:'.json_encode($data).'return:'.$return."\r\n");
            }
        }else{
            $this->log_result('alipay',"【支付信息已更新】".date('Y-m-d H:i:s')."！\r\n");
            return "success";
        }
    }
    //现金支付
    public function cashPay($paymentInfo,$trade_no){
        if (!$paymentInfo || !$trade_no) {
            return 'fail';
        }
        $this->log_result('alipay',"\r\n【更新现金支付开始】".date('Y-m-d H:i:s')." status:".$paymentInfo['status']."out_trade_no".$paymentInfo['payment_id']."\r\n");
        if ($paymentInfo['payed_cash'] == '0') {
            try {
                $data['payed_cash'] = $_POST['total_fee'];
                $data['trade_no'] = $trade_no;
                $data['modified_time'] = time();
                $data['status'] = 'succ';
                $data['pay_name'] = 'alipay';
                $res = $this->paymentsModel->where('payment_id ='.$paymentInfo['payment_id'])->data($data)->save();
                $resCurMoney = $this->paymentsModel->where('payment_id ='.$paymentInfo['payment_id'])->setInc('cur_money',$_POST['total_fee']); // 用户的积分加3
                if ($res === false && $resCurMoney ===false) {
                    $this->log_result('alipay',"\r\n【更新现金支付失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                    return 'fail';
                }
            } catch (\Exception $e) {
                $this->log_result('alipay',"\r\n【更新现金支付失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."错误信息：".$e->getMessage()."\r\n");
            } 
            //更新支付副表
            $paybillData = array(
                'modified_time'=>time(),
                'status' => 'succ'
            );
            try {
                $paybillRes = $this->tradePaybillModel->where('payment_id ='.$paymentInfo['payment_id'])->save($paybillData); 
                if ($paybillRes === false) {
                    $this->log_result('alipay',"\r\n【更新现金支付失败】更新支付副表状态失败 ".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                    return 'fail';
                }
            } catch (\Exception $e) {
                $this->log_result('alipay',"\r\n【更新现金支付失败】更新支付副表状态失败 ".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."错误信息：".$e->getMessage()."\r\n");
                return 'fail';
            } 
            //支付子表
            $paymentBillInfo = $this->tradePaybillModel->where('payment_id = '.$paymentInfo['payment_id'])->field('payment_id,paybill_id,tid,cash,point')->select();
            $tidarry = array();
            foreach ($paymentBillInfo as $key => $value) {
                $tidarry[$key] = $value['tid'];
            } 
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

            //更新订单主表
            foreach ($paymentBillInfo as $kpb => $valpb){
                try {
                    $trdeDate = array(
                        'status'=>"WAIT_SELLER_SEND_GOODS",
                        'payed_fee'=>$valpb['point']/100+$valpb['cash'],
                        'payed_cash'=>$valpb['cash'],
                        'pay_time'=>time(),
                        'modified_time'=>time()
                    );
                    $trdeDate['trade_status']='WAIT_SELLER_SEND_GOODS';                    
                    $resTrade = $this->tradeModel->where('tid ='.$valpb['tid'])->data($trdeDate)->save();
                    $orderData['status'] = "WAIT_SELLER_SEND_GOODS";                      
                    $orderData['pay_time'] = time();
                    $orderData['modified_time'] = time();
                    $resOrder = $this->modelOrder->where('tid ='.$valpb['tid'])->save($orderData);
                    if ($resTrade === false || $resOrder === false) {
                        $this->makeLog('alipay',"\r\n【更新订单表失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                    }
                } catch (\Exception $e) {
                    $this->makeLog('alipay',$e->getMessage());
                }                
            } 
            $conditionOrder = array('tid'=>array('in',$tidarry));
            $orderList = $this->modelOrder->where($conditionOrder)->select();
            //更新库存      
            if(!empty($orderList)){
                foreach ($orderList as $key => $value) {
                    try{
                        //支付减sku_store库存和预占库存
                        $this->modelItemSkuStore->where('sku_id='.$value['sku_id'])->setDec('freez',$value['num']);
                        $this->modelItemSkuStore->where('sku_id='.$value['sku_id'])->setDec('store',$value['num']);
                        //购买减item_store库存和预占库存
                        $this->modelItemStore->where('item_id='.$value['item_id'])->setDec('freez',$value['num']); 
                        $this->modelItemStore->where('item_id='.$value['item_id'])->setDec('store',$value['num']);      
                        //购买增加item销量
                        $this->modelItemCount->where('item_id ='.$value['item_id'])->setInc('sold_quantity',$value['num']);
                        //购买增加sku销量
                        $this->modelItemSku->where('sku_id ='.$value['sku_id'])->setInc('sold_quantity',$value['num']);
                        //修改供应商商品sku库存
                        $this->modelSitemSku->where('item_id ='.$value['sku_id'])->setDec('stock',$value['num']);
                    }catch (\Exception $e){
                        $this->makeLog('alipay',$e->getMessage());
                    }                       
                }
            }
            //供应商订单支付确认
            $supplierData = array('paymentId'=>$paymentInfo['payment_id']);
            $supplierUrl=C('COMMON_API').'Order/apiSupplier';       
            $returnSupplier=$this->requestPost($supplierUrl,$supplierData);
            $syncSupplier = trim($returnSupplier, "\xEF\xBB\xBF");//去除BOM头
            $syncSup = json_decode($syncSupplier,true);
            if ($syncSup['result'] != 100) {
                $this->makeLog('alipay','\r\n【供应商订单支付确认失败】 paymentId='.$paymentInfo['payment_id']);            
            }
            if($syncSup['errcode'] > 0){  
                $this->log_result('alipay',"\r\n【供应商订单支付确认失败】".date('Y-m-d H:i:s')."error".$syncSup['msg']."\r\n");          
            }
            //拆分订单至快递订单表中
            $courierData = array('paymentId'=>$paymentInfo['payment_id']);
            $courierUrl=C('COMMON_API').'Order/apiCourier';       
            $resCourier=$this->requestPost($courierUrl,$courierData);
            $syncCourier = trim($resCourier, "\xEF\xBB\xBF");//去除BOM头
            $syncCour = json_decode($syncCourier,true);
            if ($syncCour['result'] != 100) {
                $this->makeLog('alipay','\r\n【拆分订单至快递订单表失败】 paymentId='.$paymentInfo['payment_id']);            
            }
            if($syncCour['errcode'] > 0){  
                $this->log_result('alipay',"\r\n【拆分订单至快递订单表失败】".date('Y-m-d H:i:s')."error".$syncCour['msg']."\r\n");          
            }

            if($jd==1){
                //调用京东确认预占库存订单
                $syncData = array('paymentId'=>$paymentInfo['payment_id'],'opType'=>'pay');
                $syncUrl=C('COMMON_API').'Order/apiSyncOrder';       
                $syncReturn=$this->requestPost($syncUrl,$syncData);
                $syncReturn = trim($syncReturn, "\xEF\xBB\xBF");//去除BOM头
                $syncRes = json_decode($syncReturn,true);
                if ($syncRes['result'] != 100) {
                    $this->log_result('alipay',"\r\n【京东确认预占库存订单接口通讯失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                }
                if($syncRes['errcode'] > 0){  
                    $this->log_result('alipay',"\r\n【京东确认预占库存订单接口通讯失败】".date('Y-m-d H:i:s')."error".$syncRes['msg']."\r\n");          
                }
            }
            if ($sf == 1) {        
                //调用订单推送顺丰仓库接口
                $syncData = array('paymentId'=>$paymentInfo['payment_id']);
                $syncUrl=C('COMMON_API').'Sf/orderPostSf';
                $syncReturn=$this->requestPost($syncUrl,$syncData);
                $syncReturn = trim($syncReturn, "\xEF\xBB\xBF");//去除BOM头
                $syncRes = json_decode($syncReturn,true);
                if ($syncRes['result'] != 100) {
                    $this->log_result('alipay',"\r\n【订单推送顺丰仓库接口通讯失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");                    
                }
                if($syncRes['errcode'] > 0){ 
                    $this->log_result('alipay',"\r\n【订单推送顺丰仓库接口通讯失败】".date('Y-m-d H:i:s')."error".$syncRes['msg']."\r\n");                            
                }
            }         
        }else{
            $this->log_result('alipay',"【现金支付信息已更新】".date('Y-m-d H:i:s')."！\r\n");
            return "success";
        }
    }




	//微信充值（生成二维码页面）
	public function wxpay(){
		//导入微信支付
        vendor('WxpayAPI.WxPayApi','','.php');
        $paymentId=trim(I('get.paymentId'));
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find(); 
        $wxPay = new \WxPayApi();
        $code_url = $wxPay->get_code($paymentInfo);
        // $wxPay->setParameter("body", $order['topdrawal']); // 商品描述
        // $wxPay->setParameter("out_trade_no", $order['topdrawal_num']); // 商户订单号
        // $wxPay->setParameter("total_fee", $order['amount_money'] * 100); // 总金额
        // $wxPay->setParameter("notify_url", \WxPayConfig::NOTIFY_URL); // 通知地址
        // $wxPay->setParameter("trade_type", "NATIVE"); // 交易类型
        // var_dump($wxPay->setParameter);
        // exit();
        // $code_url = $wxPay->getCodeUrl($wxPay->setParameter);
        // var_dump($code_url);
        // exit();
        $this->assign('code_url',$code_url);
        $paymentInfo['newmoney'] = sprintf("%.2f",$paymentInfo['money']);
        $this->assign('paymentInfo',$paymentInfo);
        $this->display('qrcode');
	}

    //生成二维码
    public function createqrcode(){         
        vendor('WxpayAPI.phpqrcode.phpqrcode','','.php'); 

        $url = urldecode(str_replace('urlimg=','',$_SERVER['QUERY_STRING']));
        ob_end_clean();
        QRcode::png("www.baidu.com");
    }
    //支付回调
    public function notify(){
        $msg = array();
        $postStr = file_get_contents('php://input');

        $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);


        require_once (APPPATH."third_party/wxpay/wxipay.php");
        $getcode = new wxipay();
        $result = $getcode->respond($msg);
        switch ($result) {
            case 1:
                $where = array('topdrawal_num' => $msg['out_trade_no'],'status' =>'0');
                $data['status'] = '1';
                $data['status_time'] = time();
                $topdrawalinfo = $this->Database_Model->get_table_row('default','topdrawal_recor', $where);
                if(!empty($topdrawalinfo)){
                    $is_row = $this->Database_Model->update('default','topdrawal_recor',$data,$where);
                    if ($is_row) {
                        //给用户账户添加充值的金额
                        $member_id = $this->session->userdata('member_id');
                        if(empty($member_id)){
                           $member_id = $topdrawalinfo['member_id'];
                        }
                            $memberinfo = $this->Database_Model->get_table_row('default','member',array('member_id' => $member_id));
                            $amount_money = $msg['total_fee']/100+$memberinfo['amount_money'];
                            $this->Database_Model->update('default','member',array('amount_money'=>$amount_money),array('member_id' => $member_id));     
                    }
                }
                 

                break;
            case 2:
                $returndata['return_code'] = 'SUCCESS';
                break;
            case 3:
                //签名失败
                $returndata['return_code'] = 'FAIL';
                $returndata['return_msg'] = '签名失败';
                break;
            
            default:
                //空数据
                $returndata['return_code'] = 'FAIL';
                $returndata['return_msg'] = '无数据返回';    
                break;
        }   
        //数组转化为xml
        $xml = "<xml>";
        foreach ($returndata as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        
        echo $xml;
    }

    //异步通知
    public function asyWxpayNotify(){
        echo 1;
    }
	
}