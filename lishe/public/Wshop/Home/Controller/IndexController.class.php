<?php
namespace Home\Controller;
class IndexController extends CommonController {
		public function __construct(){
            parent::__construct();
            $this->paymentsModel = M("ectools_payments");//支付表
            $this->userAccountModel = M('sysuser_account');//用户登录表
            $this->tradePaybillModel = M('ectools_trade_paybill');//支付子表
            $this->tradeModel=M("systrade_trade");//订单表
            $this->userDepositModel = M('sysuser_user_deposit');
            $this->userDataDepositLogModel = M('sysuser_user_deposit_log');//消费/充值日志
            $this->modelShufflingDetail = M('mall_shuffling_figure_detail');
            $this->modelShuFigure = M("mall_shuffling_figure");

            $this->modelOrder=M('systrade_order');
            $this->modelItemStore=M('sysitem_item_store');//商品库存
            $this->modelItemSkuStore=M('sysitem_sku_store');
            $this->modelItemCount=M('sysitem_item_count');
            $this->modelItemSku=M('sysitem_sku');
            $this->modelSitemSku = M('supplier_item_sku');//供应商商品sku
	}
    public function index(){
        //头部轮播图
        $shuFigureId = $this->modelShuFigure->where('identify = "wxindex"')->getField('shuffling_id');
        if (empty($shuFigureId)) {
            $shuFigureId = 1;
        }
        $shuDetailList = $this->modelShufflingDetail->where('shuffling_id='.$shuFigureId.' and status = 1 and is_delete = 0')->order("order_sort desc")->select();
        //楼层配置
    	$wshopIndex = M('wshop_index')->where('status = 1 and is_delete = 0')->order('order_sort desc')->select();
    	//首页商品表
        $wshopItemModel = M('wshop_index_item');
    	//$indexItem = $wshopItemModel->where('status = 1 and is_delete = 0')->order('order_sort desc')->select();
        //echo $wshopItemModel->getLastSql();
        $indexItem = $wshopItemModel
            ->alias('wi')
            ->join("sysitem_item si on si.item_id = wi.forkey_item_id")
            ->where('wi.status = 1 and wi.is_delete = 0')
            ->order('wi.order_sort desc')
            ->field('wi.item_id,wi.title,wi.price,wi.pc_title,wi.pc_content,wi.abstract,wi.img_default_id,wi.forkey_item_id,wi.order_sort,wi.forkey_index_id,si.cash,si.point')
            ->select();
    	//合并两个数组
    	$newindexItem = array();
    	foreach ($wshopIndex as $key => $value) {
    		$newindexItem[$key] = $value;
    		foreach ($indexItem as $k => $val) {
    		 	if ($val['forkey_index_id'] == $value['index_id']) {                    
                    $newindexItem[$key]['item'][$k] = $val;                 
                    //$newindexItem[$key]['item'][$k]['price'] = $val['price']*100;
    		 	}
    		}    		
    	}
        $this->assign('shuDetailList',$shuDetailList);
		$this->assign('newindexItem',$newindexItem);        
    	$this->display();        
    }

    //消费/充值日志
    public function addDepositLog($data){
        return $this->userDataDepositLogModel->data($data)->add();
    } 
    //微信支付异步通知
    public function asyNotify(){

        vendor('WxpayAPI.wshop.Util.WxPayPubHelper','','.class.php');
        //使用通用通知接口
        $notify = new \Notify_pub();

        //存储微信的回调
        $xml = file_get_contents('php://input');
        $notify->saveData($xml);
        
        $arr = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        $checkSign=$notify->checkSign();
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

        if($checkSign == TRUE){
        	$returnCode=$notify->data["return_code"];
        	$resultCode=$notify->data["result_code"];
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
            
            $paymentId=trim($arr['out_trade_no']);
            $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find();  
            if ($paymentInfo && $paymentInfo['status'] != 'succ') {
                //积分充值
                if ($paymentInfo['pay_type'] == 'recharge') {
                    $this->log_result('wxpay', "\r\n【更新积分开始】" . date('Y-m-d H:i:s') . " returnCode:" . $returnCode . " resultCode:" . $resultCode . "\r\n");

                    //用户登录信息表
                    $uid = $paymentInfo['user_id'];
                    $totalFee = $arr['total_fee'] / 100;
                    $balance = $arr['total_fee'];
                    $userAccountInfo = $this->userAccountModel->where('user_id =' . $uid)->find();
                    if (empty($userAccountInfo)) {
                        $this->log_result('wxpay', "\r\n【获取用户信息失败】" . date('Y-m-d H:i:s') . "\r\n");
                        exit;
                    }

                    //同步一企一舍积分
                    $sign = md5('orderno=' . $paymentId . '&phoneNum=' . $paymentInfo['user_name'] . '&pointsAmount=' . $balance . '&pointsType=1&terminalType=WAP' . C('API_KEY'));
                    $this->log_result('wxpay', "\r\n【更新一企一舍积分】" . date('Y-m-d H:i:s') . " addPoint:" . $balance . " sign:" . $sign . "\r\n");
                    $url = C('API') . 'mallPoints/rechargeNew';
                    $data = array(
                        'phoneNum' => $paymentInfo['user_name'],
                        'pointsAmount' => $balance,
                        'orderno' => $paymentId,
                        'pointsType' => 1,
                        'terminalType' => 'WAP',
                        'sign' => $sign
                    );
                    $return = $this->requestPost($url, $data);
                    $this->log_result('wxpay', '【一企一舍充值】url:' . $url . 'data:' . json_encode($data) . 'return:' . $return . "\r\n");
                    $retArr = json_decode($return, true);

                    if ($retArr['result'] == 100) {
                        $this->log_result('wxpay', "\r\n【更新本地积分】" . date('Y-m-d H:i:s') . " totalFee:" . $totalFee . " balance:" . $retArr['data']['info']['amount'] . "\r\n");
                        //支付成功，更新本地积分
                        $this->userDepositModel->where('user_id =' . $uid)->setInc('deposit', $totalFee);
                        $this->userDepositModel->where('user_id =' . $uid)->setInc('balance', $retArr['data']['info']['amount']);
                        $this->userDepositModel->where('user_id =' . $uid)->setInc('commonAmount', $retArr['data']['info']['amount']);
                        //更新支付主表
                        $zdata['cur_money'] = $totalFee;
                        $zdata['pay_type'] = 'recharge';
                        $zdata['pay_app_id'] = 'wxpay';
                        $zdata['pay_name'] = '微信支付';
                        $zdata['memo'] = '微商城充值[微信支付]';
                        $zdata['payed_time'] = time();
                        $zdata['status'] = 'succ';
                        $zdata['trade_no'] = $arr['transaction_id'];
                        $zdata['ls_trade_no'] = $retArr['data']['info']['transno'];
                        $this->log_result('wxpay', "\r\n【更新支付主表】" . date('Y-m-d H:i:s') . " curMoney:" . $totalFee . " payType:" . $zdata['pay_type'] . "\r\n");
                        $zres = $this->paymentsModel->where('payment_id =' . $paymentInfo['payment_id'])->data($zdata)->save();
                        $this->log_result('wxpay', "\r\n【更新主表结果】" . date('Y-m-d H:i:s') . " paymentId:" . $paymentInfo['payment_id'] . " return:" . $zres . "\r\n");
                        $this->addDepositLog(array('type' => 'add', 'user_id' => $uid, 'operator' => $userAccountInfo['mobile'], 'fee' => $totalFee, 'message' => $uid . "微信充值，充值单号" . $retArr['data']['info']['transno'], 'logtime' => time()));
                        echo 'succ';
                    } else {
                        $this->log_result('wxpay', '【一企一舍充值失败】url:' . $url . 'data:' . json_encode($data) . 'return:' . $return . "\r\n");
                    }
                }

                //现金支付
                if ($paymentInfo['pay_type'] == 'online') {
                    //现金支付
                    $resCashPay = $this->cashPay($paymentInfo, $arr['transaction_id'], $arr['total_fee'] / 100, 'wxpay');
                    if ($resCashPay != 'success') {
                        $this->log_result('wxpay',"【现金支付失败】".date('Y-m-d H:i:s')."paymentId:".$paymentId."\r\n");
                        echo "fail";
                    }
                }
            } else {
                $this->log_result('wxpay', "【支付信息已更新】" . date('Y-m-d H:i:s') . "！\r\n");
                echo 'succ';
            }
        }
    }

    //现金支付
    public function cashPay($paymentInfo, $trade_no, $total_fee, $pay_name){
        if (!$paymentInfo || !$trade_no) {
            return 'fail';
        }
        $this->log_result($pay_name,"\r\n【更新现金支付开始】".date('Y-m-d H:i:s')." status:".$paymentInfo['status']." out_trade_no:".$paymentInfo['payment_id']." trade_no:".$trade_no."\r\n");
        if ($paymentInfo['payed_cash'] == '0') {
            try {
                $data['payed_cash'] = $total_fee;
                $data['trade_no'] = $trade_no;
                $data['pay_name'] = $pay_name;
                $data['modified_time'] = time();
                $data['status'] = 'succ';
                $res = $this->paymentsModel->where('payment_id ='.$paymentInfo['payment_id'])->data($data)->save();
                $resCurMoney = $this->paymentsModel->where('payment_id ='.$paymentInfo['payment_id'])->setInc('cur_money', $total_fee);
                // 用户的积分加3
                if ($res === false && $resCurMoney ===false) {
                    $this->log_result($pay_name,"\r\n【更新现金支付失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                    return 'fail';
                }
            } catch (\Exception $e) {
                $this->log_result($pay_name,"\r\n【更新现金支付失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."错误信息：".$e->getMessage()."\r\n");
            }

            //更新支付副表
            $paybillData = array(
                'modified_time'=>time(),
                'status' => 'succ'
            );
            try {
                $paybillRes = $this->tradePaybillModel->where('payment_id ='.$paymentInfo['payment_id'])->save($paybillData);
                if ($paybillRes === false) {
                    $this->log_result($pay_name,"\r\n【更新现金支付失败】更新支付副表状态失败 ".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                    return 'fail';
                }
            } catch (\Exception $e) {
                $this->log_result($pay_name,"\r\n【更新现金支付失败】更新支付副表状态失败 ".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."错误信息：".$e->getMessage()."\r\n");
                return 'fail';
            }

            //支付子表
            $paymentBillInfo = $this->tradePaybillModel->where('payment_id = '.$paymentInfo['payment_id'])->field('payment_id,paybill_id,tid,point,cash')->select();
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
                        'payed_fee'=>$valpb['point']/100 + $valpb['cash'],
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
                        $this->makeLog($pay_name,"\r\n【更新订单表失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                    }
                } catch (\Exception $e) {
                    $this->makeLog($pay_name,$e->getMessage());
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
                        $this->makeLog($pay_name,$e->getMessage());
                    }
                }
            }
            //供应商订单支付确认
            $returnSupplier=A('Supplier')->payConfirm($paymentInfo['payment_id']);
            if ($returnSupplier[0] != 1) {
                $this->makeLog($pay_name,'error:1010,错误信息：供应商订单确认支付失败 paymentId='.$paymentInfo['payment_id']);
            }
            //拆分订单至快递订单表中
            /*$resCourier = $this->courierTrade($paymentInfo['payment_id']);
            if ($resCourier[0] != 1) {
                $this->makeLog($pay_name,'error:1011,错误信息：拆分订单至快递订单表失败paymentId='.$paymentInfo['payment_id']);
            }*/
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
                    $this->log_result($pay_name,"\r\n【京东确认预占库存订单接口通讯失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                }
                if($syncRes['errcode'] > 0){
                    $this->log_result($pay_name,"\r\n【京东确认预占库存订单接口通讯失败】".date('Y-m-d H:i:s')."error".$syncRes['msg']."\r\n");
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
                    $this->log_result($pay_name,"\r\n【订单推送顺丰仓库接口通讯失败】".date('Y-m-d H:i:s')."paymentId".$paymentInfo['payment_id']."\r\n");
                }
                if($syncRes['errcode'] > 0){
                    $this->log_result($pay_name,"\r\n【订单推送顺丰仓库接口通讯失败】".date('Y-m-d H:i:s')."error".$syncRes['msg']."\r\n");
                }
            }
        }else{
            $this->log_result($pay_name,"【现金支付信息已更新】".date('Y-m-d H:i:s')."！\r\n");
            return "success";
        }
    }

    //积分充值
    public function inteRechApi(){
        $sign=md5('orderno='.$tradeNo.'&phoneNum='.$mobile.'&pointsAmount='.$addPoint.'&pointsType=1'.C('API_KEY'));
        $url=C('API').'mallPoints/recharge';
        $data=array(
            'phoneNum'=>$mobile,
            'pointsAmount'=>$addPoint,
            'orderno'=>$tradeNo,
            'pointsType'=>1,
            'sign'=>$sign
        );      
        $return=$this->requestPost($url,$data);
    }

    //记录日志
    public function log_result($type,$content){
        $file  = C('DIR_LOG').$type.'/log'.date('Ymd').'.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个 'logs/'.
        file_put_contents($file, $content,FILE_APPEND);
    }

    //支付宝异步通知
    public function alipayNotify(){
        vendor('Alipay.wshop.lib.alipay_notify','','.class.php');
        vendor('Alipay.wshop.alipay','','.config.php');        
        //计算得出通知验证结果
        $alipayConfig = alipayConfig();
        $alipayNotify = new \AlipayNotify($alipayConfig);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {
            //验证成功            
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no = $_POST['out_trade_no'];//商户订单号
            $trade_no = $_POST['trade_no'];//支付宝交易号
            $trade_status = $_POST['trade_status'];//交易状态
            if($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //普通即时到账的交易成功状态
                $paymentId=trim($out_trade_no);
                $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find();
                $this->log_result('alipay',"\r\n【更新积分.现金支付开始】".date('Y-m-d H:i:s')." status:".$paymentInfo['status']."\r\n");
                if ($paymentInfo && $paymentInfo['status'] != 'succ') {
                    //积分充值
                    if ($paymentInfo['pay_type'] == 'recharge') {
                        $this->log_result('alipay', "\r\n【更新积分开始】" . date('Y-m-d H:i:s') . " returnCode:" . $_POST['total_fee'] . "\r\n");
                        //用户登录信息表
                        $uid = $paymentInfo['user_id'];
                        $totalFee = $_POST['total_fee'];//单位元
                        $balance = $_POST['total_fee'] * 100;//单位积分
                        $userAccountInfo = $this->userAccountModel->where('user_id =' . $uid)->find();
                        if (empty($userAccountInfo)) {
                            $this->log_result('alipay', "\r\n【获取用户信息失败】" . date('Y-m-d H:i:s') . "\r\n");
                            exit;
                        }

                        //同步一企一舍积分
                        $sign = md5('orderno=' . $paymentId . '&phoneNum=' . $paymentInfo['user_name'] . '&pointsAmount=' . $balance . '&pointsType=1&terminalType=WAP' . C('API_KEY'));
                        $this->log_result('alipay', "\r\n【更新一企一舍积分】" . date('Y-m-d H:i:s') . " addPoint:" . $balance . " sign:" . $sign . "\r\n");
                        $url = C('API') . 'mallPoints/rechargeNew';
                        $data = array(
                            'phoneNum' => $paymentInfo['user_name'],
                            'pointsAmount' => $balance,
                            'orderno' => $paymentId,
                            'pointsType' => 1,
                            'terminalType' => 'WAP',
                            'sign' => $sign
                        );
                        $return = $this->requestPost($url, $data);
                        $this->log_result('alipay', '【一企一舍充值】url:' . $url . 'data:' . json_encode($data) . 'return:' . $return . "\r\n");
                        $retArr = json_decode($return, true);

                        if ($retArr['result'] == 100) {
                            $this->log_result('alipay', "\r\n【更新本地积分】" . date('Y-m-d H:i:s') . " totalFee:" . $totalFee . " balance:" . $balance . "\r\n");
                            //支付成功，更新本地积分
                            $this->userDepositModel->where('user_id =' . $uid)->setInc('deposit', $totalFee);
                            $this->userDepositModel->where('user_id =' . $uid)->setInc('balance', $balance);
                            $this->userDepositModel->where('user_id =' . $uid)->setInc('commonAmount', $balance);
                            //更新支付主表
                            $zdata['cur_money'] = $totalFee;
                            $zdata['pay_type'] = 'recharge';
                            $zdata['pay_app_id'] = 'alipay';
                            $zdata['pay_name'] = '支付宝支付';
                            $zdata['memo'] = '微商城充值[支付宝支付]';
                            $zdata['payed_time'] = time();
                            $zdata['status'] = 'succ';
                            $zdata['trade_no'] = $trade_no;
                            $zdata['ls_trade_no'] = $retArr['data']['info']['transno'];
                            $this->log_result('alipay', "\r\n【更新支付主表】" . date('Y-m-d H:i:s') . " curMoney:" . $totalFee . " payType:" . $zdata['pay_type'] . "\r\n");
                            $zres = $this->paymentsModel->where('payment_id =' . $paymentInfo['payment_id'])->data($zdata)->save();
                            $this->log_result('alipay', "\r\n【更新主表结果】" . date('Y-m-d H:i:s') . " paymentId:" . $paymentInfo['payment_id'] . " return:" . $zres . "\r\n");
                            $this->addDepositLog(array('type' => 'add', 'user_id' => $uid, 'operator' => $userAccountInfo['mobile'], 'fee' => $totalFee, 'message' => $uid . "支付宝充值，充值单号" . $retArr['data']['info']['transno'], 'logtime' => time()));
                            echo "success";
                        } else {
                            $this->log_result('alipay', '【一企一舍充值失败】url:' . $url . 'data:' . json_encode($data) . 'return:' . $return . "\r\n");
                        }
                    }

                    //现金支付
                    if ($paymentInfo['pay_type'] == 'online') {
                        //现金支付
                        $resCashPay = $this->cashPay($paymentInfo, $trade_no, $_POST['total_fee'], 'alipay');
                        if ($resCashPay != 'success') {
                            $this->log_result('alipay',"【现金支付失败】".date('Y-m-d H:i:s')."paymentId:".$paymentId."\r\n");
                            echo "fail";
                        }
                    }
                }else{
                    $this->log_result('alipay',"【支付信息已更新】".date('Y-m-d H:i:s')."！\r\n");
                    echo "success";
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

}