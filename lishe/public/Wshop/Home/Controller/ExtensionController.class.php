<?php
namespace Home\Controller;
class ExtensionController extends CommonController {
    private $activityConfigId = 71;//线上
    //private $activityConfigId = 62;//测试
	public function __construct(){
        parent::__construct();
		$this->userModel=M('sysuser_user');
		$this->userAccountModel=M('sysuser_account');//用户登录表
		$this->modelDeposit=M('sysuser_user_deposit');
        $this->modelExtension = D('Extension');
	}
    //模拟登陆，临时使用
    public function testZzw(){
        //http://h.ls.com/wshop.php/Extension/itemList/comName/礼舍/userName/18664377851/mobile/18664377851/comId/-1/sign/d5d38e44a94c572df5b56698b35206e7
        //http://test.lishe.cn/wshop.php/Extension/itemList/comName/%E7%A4%BC%E8%88%8D/userName/18664377851/mobile/18664377851/comId/-1/sign/cb2badeaf97085a6442d95daeb947c90
        $comName = I('comName');
        $userName = I('userName');
        $mobile = I('mobile');
        $comId = I('comId');
        var_dump($comName);
        var_dump($userName);
        var_dump($mobile);
        var_dump($comId);
        $sign = md5('comId='.$comId.'&comName='.$comName.'&mobile='.$mobile.'&userName='.$userName.C('API_KEY'));
        echo $sign;
        $sign2 = md5('mobile='.$mobile.C('API_KEY'));
        echo "<br/>";
        echo $sign2;
    }
    //文件日志
    public function makeLog($type='',$data=''){
        if(!empty($type)){
            @file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
        }
    }
	/**
	 * 园区推广
	 * @author zzw
	 * 
	*/
	public function itemList(){	
		$comName = I('comName');
		$userName = I('userName');
		$mobile = I('mobile');
		$comId = I('comId');
		$sign = I('sign');
		if (empty($comName) || empty($userName) || empty($mobile) || empty($comId)) {            
			$this->makeLog('extensionOrder','error:1001,错误信息：公司名称、用户名称、公司ID或手机号为空 mobile:'.$mobile);
			//$this->retError(1001,':生成订单失败，错误信息：店铺ID、商品ID、配送模板ID或库存ID为空');
            $this->error('资料信息不完整，无法购买！');
        }	
        $newSign = md5('comId='.$comId.'&comName='.$comName.'&mobile='.$mobile.'&userName='.$userName.C('API_KEY'));
        $userName = urldecode($userName);
        $comName = urldecode($comName);
        if ($sign != $newSign) {
        	$this->error('非法登录！');
        }
        $infoArr = array('comName'=>$comName,'userName'=>$userName,'mobile'=>$mobile,'comId'=>$comId);
        
        $this->login($infoArr);
        $account = json_decode(cookie('account'),true);        
        $this->uid = $account['id'];
          
        //检查是否登录
		if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        }
		if (empty($this->activityConfigId)) {
			$this->error('活动已结束！');
		}
		$condition = array('activity_config_id'=>$this->activityConfigId);
		$field = array('activity_config_id','item_ids');
		$companyActivityCageInfo = $this->modelExtension->getCompanyActivityCage($condition,$field);
		if (empty($companyActivityCageInfo['item_ids'])) {
			$this->error('活动已结束！！');
		}
		unset($condition);
		unset($field);
		$condition = ' and sku.sku_id in ('.$companyActivityCageInfo['item_ids'].')';
		$field = array('sku.sku_id','sku.item_id','sku.title','sku.price','sku.cash','sku.point');
		$skuList = $this->modelExtension->getSkuList($condition,$field);
		if (!$skuList) {
			$this->error('活动已结束！');
		}
		foreach ($skuList as $key => $value) {
			$itemIds[$key] = $value['item_id'];
		}
		unset($condition);
		unset($field);
		$condition['item_id'] = array('in',$itemIds); 
		$field = array('item_id','image_default_id','jd_price','mkt_price','jd_sku');
		$itemList = $this->modelExtension->getItemList($condition,$field);
        if (!itemList) {
            $this->error('活动已结束！');
        }
        $newItemList =array();
        foreach ($itemList as $key => $value) {
            $newItemList[$value['item_id']] = $value; 
        }
		$itemSkuList = array();
		foreach ($skuList as $key => $value) {
            $itemSkuList[$key] = $value;
            $itemSkuList[$key]['image_default_id'] = $newItemList[$value['item_id']]['image_default_id'];   
            $itemSkuList[$key]['jd_price'] = $newItemList[$value['item_id']]['jd_price'];     
            $itemSkuList[$key]['mkt_price'] = $newItemList[$value['item_id']]['mkt_price'];   
            $itemSkuList[$key]['jdId'] = $newItemList[$value['item_id']]['jd_sku'];   		
		}
		$this->assign('itemSkuList',$itemSkuList);
		$this->display();
	}

	//本地注册
	public function register($account,$info,$balance){
		$account['createtime']=time();
		$account['modified_time']=time();
		$userId=$this->userAccountModel->add($account);
		if($userId>0){
			$info['user_id']=$userId;
			$this->userModel->add($info);
			$balance['user_id']=$userId;
			$this->modelDeposit->add($balance);
		}
		return $userId;
	}

	//同步登录
	public function login($arrData){	
        $condition['mobile']=$arrData['mobile'];
        $checkUser=$this->userAccountModel->field('user_id')->where($condition)->find();
        //如果没有发现本地信息，注册用户
        if(empty($checkUser['user_id'])){
    		$balance=array(
        		'deposit'=>0,
        		'balance'=>0,
        		'commonAmount'=>0,
        		'limitAmount'=>0,
        		'comId'=>$arrData['comId'],
        		'comName'=>$arrData['comName']
        	);
        	$user=array(
        		'login_account'=>$arrData['userName'],
        		'mobile'=>$arrData['mobile'],
        		'login_password'=>'sync'
        	);
        	$info=array(
        		'ls_user_id'=>0,
        		'name'=>$arrData['mobile'],	
        		'username'=>$arrData['userName']
        	);    	
    		$userId = $this->register($user,$info,$balance);
        }else{
            $userId = $checkUser['user_id'];  
            $arrData['comId'] = $this->modelDeposit->where('user_id='.$checkUser['user_id'])->getField('comId');                 
        }
		$account=array(
    		'id'=>$userId,
    		'comId'=>$arrData['comId'],
    		'account'=>$arrData['mobile'],
    		'userName'=>$arrData['userName']
		); 		 		     
        session('account',json_encode($account));                            
        cookie('account',json_encode($account));
	}
	//点击购买
	public function extensionOrder(){	        
        $skuId = I('skuId');	
		//检查是否登录
		if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        }
        //检查是否已经购买
        $condition = "pay_time !='' and user_id = ".$this->uid." and activity_id = ".$this->activityConfigId;
        $tradeInfo = $this->modelExtension->getTrade($condition);
        if ($tradeInfo) {
            echo json_encode(array(0,"该活动限制每人只能参加一次！"));
            exit();
        }
        if (empty($skuId)) {            
            echo json_encode(array(0,'系统繁忙，请稍后！'));
            exit();
        } 
        //查询商品信息
        unset($condition);
        $condition = ' and sku.sku_id='.$skuId;
        $skuInfo = $this->modelExtension->getSkuInfo($condition);
        if (empty($skuInfo)) {
            $this->makeLog('extensionOrder','error:1002,错误信息：未查询到商品SKU信息 UID:'.$this->uid);       
            echo json_encode(array(0,'下单失败！'));
            exit();          
        }	
		
		//检查购买数量是否超过库存
        $noFreez = $skuInfo['store']-$skuInfo['freez'];
        if ($noFreez < 1 ) {       
            $this->makeLog('extensionOrder','error:1003,错误信息：购买数量超过商品库存数量 UID:'.$this->uid);  
            echo json_encode(array(0,'库存不足，请选择购买其他商品！'));
            exit();  
        }

        //根据店铺id生成订单
        $data['tid'] = date(ymdHis).$this->uid;//订单编号
        $data['shop_id'] = $skuInfo['shop_id'];//订单所属的店铺id
        $data['user_id'] = $this->uid;//会员id
        $data['com_id'] = $this->comId;//员工企业id
        $data['dlytmpl_id'] = 0;//配送模板id
        $data['status'] = 'WAIT_BUYER_PAY';//订单状态
        $data['trade_status'] = 'WAIT_BUYER_PAY';//订单状态

        //实付金额,订单最终总额
        $toallPrice=0;
        $totalCash = 0;
        $totalPoint = 0;
        $toallPrice =  round($skuInfo['price'],2);
        $totalCash = round($skuInfo['cash'],2);
        $totalPoint = $skuInfo['point'];
        $data['postFees'] = 0;
        $data['payment'] = round($toallPrice,2);
        $data['cash_fee'] = $totalCash;//需要支付的现金
        $data['point_fee'] = $totalPoint;//需要支付的积分+上运费
        $data['total_fee'] = $toallPrice;//各子订单中商品price * num的和，不包括任何优惠信息
        $data['post_fee'] = 0;//邮费
        $data['receiver_name'] = $this->userName;//收货人姓名
        $data['created_time'] = time();
        $data['receiver_state'] = '广东';//收货人所在省份
        $data['receiver_city'] = '深圳市';//收货人所在城市
        $data['receiver_district'] = '南山区';//收货人所在地区
        $data['receiver_address'] = '现场取货';//收货人详细地址
        $data['receiver_zip'] = '518000';//收货人邮编
        $data['receiver_mobile'] = $this->account;//收货人手机号
        $data['receiver_phone'] = $this->userName;//收货人电话
        $data['title'] = "用户订单";//交易标题
        $data['buyer_message'] = '';//买家留言
        $data['trade_from'] = 'ws';//订单来源
        $data['trade_type'] = '1';
        //子订单商品购买数量总数
        $data['itemnum'] = 1;
        $data['buyer_area'] = '19/1607/3155';//买家下单的地区
        $data['total_weight'] = $skuInfo['weight'];
        $data['activity_id'] = $this->activityConfigId;
        //开启事物
        $this->model = new \Think\Model(); 
        $this->model->startTrans();

        try {
            $res = $this->modelExtension->addTrade($data);
            if (!$res) {
                $this->model->rollback();
                $this->makeLog('extensionOrder','error:1008,错误信息：主订单表插入失败 UID:'.$this->uid);                    
                echo json_encode(array(0,'下单失败！'));
                exit();                       
            }
        } catch (\Exception $e) { 
            $this->model->rollback();               
            $this->makeLog('extensionOrder','error:1009,错误信息：主订单表插入失败 UID:'.$this->uid.' errorMsg:'.$e->getMessage());    
            echo json_encode(array(0,'下单失败！'));
            exit();  
        }        
        //订单子表添加记录
        $da['oid'] = date(ymdHis).$this->uid;
        $da['tid'] = $data['tid'];
        $da['supplier_id'] = empty($skuInfo['supplier_id']) ? 0 : $skuInfo['supplier_id'];
        $da['send_type'] =$skuInfo['send_type'];
        $da['cat_id'] = $skuInfo['cat_id'];
        $da['shop_id'] = $skuInfo['shop_id'];
        $da['user_id'] = $this->uid;
        $da['item_id'] = $skuInfo['item_id'];
        $da['sku_id'] = $skuInfo['sku_id'];
        $da['bn'] = $skuInfo['bn'];
        $da['title'] = $skuInfo['title'];
        $da['spec_nature_info'] = $skuInfo['spec_info'];
        $da['price'] =  round($skuInfo['price'],2);
        $da['cost_price'] = $skuInfo['cost_price'];
        $da['num'] = 1;                                           
        $da['total_fee'] = round($skuInfo['price'],2);
        $da['cash'] = round($skuInfo['cash'],2);//单品需要支付的现金
        $da['point'] = $skuInfo['point'];//单品需要支付的积分
        $da['total_cash'] = round($skuInfo['cash'],2);//该商品需要支付的现金
        $da['total_point'] = $skuInfo['point'];//该商品需要支付的积分

        $da['total_weight'] = $skuInfo['weight'];
        $da['modified_time'] = time();
        $da['order_from'] = 'ws';
        $da['pic_path'] = $skuInfo['image_default_id'];
        try {
            $r = $this->modelExtension->addOrder($da);
            if (!$r) {
                $this->model->rollback();
                $this->makeLog('extensionOrder','error:1010,错误信息：子订单表插入失败 UID:'.$this->uid);     
                echo json_encode(array(0,'下单失败！'));
                exit();  
            }
        } catch (\Exception $e) {      
            $this->model->rollback();      
            $this->makeLog('extensionOrder','error:1011,错误信息：子订单表插入失败 UID:'.$this->uid.' errorMsg:'.$e->getMessage());  
            echo json_encode(array(0,'下单失败！'));
            exit();  
        }

        //下单增加购买数量
        try {            
            $itemCount = $this->modelExtension->setIncItemCount('item_id ='.$skuInfo['item_id'],1);
            if ($itemCount === false) {                
                $this->model->rollback();
                $this->makeLog('extensionOrder','error:1012,错误信息：下单增加购买数量修改失败 UID:'.$this->uid.' itemId：'.$skuInfo['item_id']);    
                echo json_encode(array(0,'下单失败！'));
                exit();  
            }
        } catch (\Exception $e) {            
            $this->model->rollback();
            $this->makeLog('extensionOrder','error:1013,错误信息：下单增加购买数量修改失败 UID:'.$this->uid.' itemId：'.$skuInfo['item_id'].' errorMsg:'.$e->getMessage()); 
            echo json_encode(array(0,'下单失败！'));
            exit();  
        }
        //下单预占sku库存
        try {
            $resStore = $this->modelExtension->PreholdSkuStore('sku_id ='.$skuInfo['sku_id'],1);
            if ($resStore === false) {
                $this->model->rollback();
                $this->makeLog('extensionOrder','error:1014,错误信息：下单预占sku库存失败 UID:'.$this->uid.' itemId：'.$skuInfo['item_id']);
                echo json_encode(array(0,'下单失败！'));
                exit();              
            }
        } catch (\Exception $e) {
            $this->model->rollback();
            $this->makeLog('extensionOrder','error:1015,错误信息：下单预占sku库存失败 UID:'.$this->uid.' itemId：'.$skuInfo['item_id'].' errorMsg:'.$e->getMessage());
            echo json_encode(array(0,'下单失败！')); 
            exit();            
        }        
        //下单预占item库存
        try {
            $resItemStore = $this->modelExtension->PreholdItemStore('item_id='.$this->uid,1);
            if ($resItemStore === false) {
                $this->model->rollback();
                $this->makeLog('extensionOrder','error:1016,错误信息：下单预占item库存失败 UID:'.$this->uid.' itemId：'.$skuInfo['item_id']);
                echo json_encode(array(0,'下单失败！')); 
            exit();                   
            }
        } catch (\Exception $e) {
            $this->model->rollback();
            $this->makeLog('extensionOrder','error:1017,错误信息：下单预占item库存失败 UID:'.$this->uid.' itemId：'.$skuInfo['item_id'].' errorMsg:'.$e->getMessage());
            echo json_encode(array(0,'下单失败！')); 
            exit(); 
            
        }
        $this->model->commit();
                
        //生成支付数据
        $paymentId = $this->extensionCreatPayments($data['tid'],$this->uid);  

        if (!$paymentId) {
        	$this->makeLog('extensionOrder','error:1018,错误信息：支付单号不能为空');     
            echo json_encode(array(0,'下单失败！'));
            exit();  
        }
        echo json_encode(array(1,$paymentId,'订单提交成功'));
        exit(); 
	}

	//生成支付数据
    public function extensionCreatPayments($tid='',$uid=''){        
    	if (empty($tid) || empty($uid)) {
        	$this->makeLog('extensionCreatPayments','error:1001,错误信息：订单编号有误');
            return false;   		
    	}
    	//查询用户登录信息
        $userAccountInfo = $this->modelExtension->getUserAccount('user_id='.$uid); 
        if (!$userAccountInfo) {
            $this->makeLog('extensionCreatPayments','error:1002,错误信息：无法获取用户登录信息');
			return false;   
        } 
        //查询用户资料信息
        $userInfo = $this->modelExtension->getUser('user_id='.$uid);
        if (!$userInfo) {
            $this->makeLog('extensionCreatPayments','error:1003,错误信息：无法获取用户资料信息');
			return false;   
        }
        $thisRes = array();
        //获取订单表信息
        $tradeInfo = $this->modelExtension->getTradeInfo('tid='.$tid);
        if (!$tradeInfo) {
            $this->makeLog('extensionCreatPayments','error:1004,错误信息：无法获取订单信息');
            return false;              
        }
        //插入支付表
        $data['payment_id'] = date(ymdHis).$uid;//支付单号
        $data['money'] = round($tradeInfo['payment'],2);//需要支付的金额
        $data['cash_fee'] = $tradeInfo['cash_fee'];//需要支付的现金
        $data['point_fee'] = $tradeInfo['point_fee'];//需要支付的积分
        $data['cur_money'] = 0;//支付货币金额
        $data['user_id'] = $uid;
        $data['user_name'] = $userAccountInfo['mobile'];
        $data['op_name'] = $userInfo['username']; //操作员
        $data['bank'] = '预存款';//收款银行
        $data['pay_account'] ='用户';//支付账号
        $data['created_time'] = time();

        //开启事物
        $this->model = new \Think\Model(); 
        $this->model->startTrans();
        try {
            $result = $this->modelExtension->addPayment($data);
            if (!$result) {   
                $this->model->rollback();      
                $this->makeLog('extensionCreatPayments','error:1005,错误信息：支付主表插入数据失败tid:'.$tid);
                return false;     
            }
        } catch (\Exception $e) {            
            $this->model->rollback();      
            $this->makeLog('extensionCreatPayments','error:1006,错误信息：支付主表插入数据失败tid:'.$tid.'errorMsg:'.$e->getMessage());
            return false;  
        }
        //添加支付子表
        $da['payment_id'] = $data['payment_id'];//主支付单编号
        $da['tid'] = $tid;
        $da['payment'] = $tradeInfo['payment'];
        $da['cash'] = $tradeInfo['cash_fee'];
        $da['point'] = $tradeInfo['point_fee'];
        $da['user_id'] = $uid;
        $da['created_time'] = time(); 
        try {            
            $result = $this->modelExtension->addPayBill($da);                  
            if (!$result) {   
                $this->model->rollback();    
                $this->makeLog('extensionCreatPayments','error:1007,错误信息：子订单表数据生成失败tid:'.$tid);
                return false;   
            }
        } catch (\Exception $e) {            
            $this->model->rollback();    
            $this->makeLog('extensionCreatPayments','error:1008,错误信息：子订单表数据生成失败tid:'.$tid.'errorMsg:'.$e->getMessage());
            return false;  
        }
        $this->model->commit();
        return $data['payment_id'];
    }

    //支付页面
    public function pay(){
        $paymentid = I("get.paymentId");
        //支付表
        $condition = array('payment_id'=>$paymentid);
        $field = array('payment_id','money','cash_fee','point_fee','payed_cash','payed_point');
        $paymentInfo = $this->modelExtension->getPaymentInfo($condition,$field);
        $this->assign('paymentInfo',$paymentInfo);
        $this->assign('paymentid',$paymentid);
        $this->assign('rules',$res['data']['info']);
        $this->display();
    }

    //支付操作
    public function operPay(){
        $paymentid = I('post.paymentid');
        //检查是否登录
        if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        }
        if (empty($paymentid)) {
            echo json_encode(array(0,"支付错误！"));
            exit();
        }
        //支付表
        $condition = array('payment_id'=>$paymentid);
        $field = array('user_id','status','money','cash_fee','payed_cash','point_fee','payed_point');
        $paymentInfo = $this->modelExtension->getPaymentInfo($condition,$field);
      
        //检查该订单是否是自己的
        if ($paymentInfo['user_id'] != $this->uid) {
            echo json_encode(array(0,"无法为他人买单！"));
            exit();
        }
        //支付子表
        unset($condition);
        unset($field);
        $condition = 'payment_id = '.$paymentid;
        $field = array('payment_id','paybill_id','tid');
        $paymentBillInfo = $this->modelExtension->getPayBillInfo($condition,$field);
     
        //检查是否已经支付
        if ($paymentInfo['status'] == 'succ') {
            //已经支付不可再次支付
            echo json_encode(array(2,"该订单已经支付！"));
            exit();
        }
        
        //检查支付情况
        if (!$paymentBillInfo['tid']) {
            echo json_encode(array(0,"支付失败！"));
            exit();
        }
        //校验订单数据
        unset($condition);
        unset($field);
        $condition = 'tid ='.$paymentBillInfo['tid'];
        $field = array('tid','created_time');
        $tradeList = $this->modelExtension->getTradeList($condition,$field);
        if (!$tradeList) {
            echo json_encode(array(0,"订单已超过支付期限，请重新下单！"));
            exit();
        }
        foreach ($tradeList as $kTrade => $vTrade) {
            if ($vTrade['created_time']+60*60*24 < time()) {
                echo json_encode(array(0,"订单已超过支付期限，请重新下单！"));
                exit();
            }
        }
        unset($condition);
        $condition = ' and tid ='.$paymentBillInfo['tid'];
        $orderInfo = $this->modelExtension->getOrderInfo($condition);
        if (!$orderInfo) {
            echo json_encode(array(0,"订单已超过支付期限，请重新下单！"));
            exit();
        }
        if ($orderInfo['store'] < 1 || $orderInfo['store'] == $orderInfo['freez'] || $orderInfo['store'] < $orderInfo['freez']) {
            echo json_encode(array(0,"库存不足，请选择购买其他商品！"));
            exit();
        }
        //现金支付
        if ($paymentInfo['cash_fee'] != '0' && $paymentInfo['payed_cash'] == '0') {
            echo json_encode(array(3,$paymentid));
            exit();
        }        
        echo json_encode(array(1,$res['msg']));
    }
    //微信在线支付页面
    public function onlinePay(){
        $paymentId = I('get.paymentId');        
        $condition = array('payment_id'=>$paymentId);
        $field = array('payment_id','money','cash_fee','point_fee','payed_cash','payed_point');
        $paymentInfo = $this->modelExtension->getPaymentInfo($condition,$field);
        $this->assign('paymentInfo',$paymentInfo);
        $this->display();
    }
    //支付的时候检查库存
    public function checkStroe(){
        $paymentId = I('paymentId');
        unset($condition);
        unset($field);
        $condition = 'payment_id = '.$paymentId;
        $field = array('payment_id','paybill_id','tid');
        $paymentBillInfo = $this->modelExtension->getPayBillInfo($condition,$field);
        if (!$paymentBillInfo['tid']) {
            echo json_encode(array(0,'库存不足，无法支付！'));
        }
        unset($condition);
        $condition = ' and tid ='.$paymentBillInfo['tid'];
        $orderInfo = $this->modelExtension->getOrderInfo($condition);
        if (!$orderInfo) {
            echo json_encode(array(0,"订单已超过支付期限，请重新下单！"));
            exit();
        }
        if ($orderInfo['store'] < 1 || $orderInfo['store'] == $orderInfo['freez'] || $orderInfo['store'] < $orderInfo['freez']) {
            echo json_encode(array(0,"库存不足，请选择购买其他商品！"));
            exit();
        }
        echo json_encode(array(1,"库存充足！"));
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
            $jsApiCallUrl = \WxPayConf_pub::JS_API_CALL_URL_EXTENSION."&paymentId=".$paymentId;
            $url = $JsApi->createOauthUrlForCode(urlencode($jsApiCallUrl));
            Header("Location: $url"); 
        }else{
            //获取code码，以获取openid
            $code = $_GET['code'];
            $JsApi->setCode($code);
            $openid = $JsApi->getOpenId();
        }
        $condition = array('payment_id'=>$paymentId);
        $paymentInfo = $this->modelExtension->getPaymentInfo($condition);    
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

        //支付表
        $paymentInfo = $this->modelExtension->getPaymentInfo($condition);  

        $this->assign('paymentInfo',$paymentInfo);
        $this->assign('paymentid',$paymentInfo['payment_id']);

        $this->assign("jsApiParameters",$jsApiParameters);

        if($paymentInfo['pay_type'] == 'online'){
            $this->display('onlinePay');
        }else{
            $this->display('inteRech');
        }
    }

    //检查是否已经下单
    public function checkOrder(){
        $mobile = I('mobile');
        $sign = I('sign');
        if (empty($mobile)) {            
            $this->makeLog('checkOrder','error:1001,错误信息：手机号码错误！');
            $this->retError(1001,'验证失败，错误信息：手机号码错误！');
        }
        $newSign = md5('mobile='.$mobile.C('API_KEY'));
        if ($sign != $newSign) {            
            $this->makeLog('checkOrder','error:1002,错误信息：验证失败，非法操作');
            $this->retError(1002,'验证失败，错误信息：验证失败，非法操作！');
        }
        unset($condition);
        $condition['mobile']=$mobile;
        $checkUser=$this->userAccountModel->field('user_id')->where($condition)->find();
        if (empty($checkUser['user_id'])) {
            $this->makeLog('checkOrder','error:1003,日志信息：该手机号未注册！');
            $this->retSuccess(array('status'=>1));          
        }
        //检查是否已经购买并支付
        unset($condition);
        $condition = "pay_time !='' and user_id = ".$checkUser['user_id']." and activity_id = ".$this->activityConfigId;
        $tradeInfo = $this->modelExtension->getTrade($condition);
        
        if ($tradeInfo) {
            unset($condition);
            unset($field);
            $condition = array('tid'=>$tradeInfo['tid']);
            $field = array('payment_id','paybill_id','tid');
            $paymentBillInfo = $this->modelExtension->getPayBillInfo($condition,$field);
            if (!$paymentBillInfo) {
            //未下单并支付
                $this->makeLog('checkOrder','error:1004,日志信息：未下单并支付！');
                $this->retSuccess(array('status'=>2));                
            }
            //已经下单并支付
            if ($tradeInfo['status'] == 'WAIT_COMMENT') {
                //已经领取
                $this->makeLog('checkOrder','error:1005,日志信息：已领取！');
                $this->retSuccess(array('status'=>4,'paymentId'=>$paymentBillInfo['payment_id']));                
            }else{
                //未领取
                $this->makeLog('checkOrder','error:1006,日志信息：未领取！');
                $this->retSuccess(array('status'=>3,'paymentId'=>$paymentBillInfo['payment_id']));
            }
        }else{
            //未下单并支付
            $this->makeLog('checkOrder','error:1007,日志信息：未下单并支付！');
            $this->retSuccess(array('status'=>2));
        }
    }
    //支付结果页面
    public function onlinePayResult(){        
        $recode = I('get.recode');
        $paymentId = I('get.paymentId');
        $mobile = I('mobile');
        $sign = I('sign');
        $newSign = md5('mobile='.$mobile.C('API_KEY'));
        if (empty($recode)) {
            //一起一舍直接过来的页面需要验证登录
            if ($sign != $newSign) {
                $this->retError(1001,'验证失败，错误信息：验证失败，非法操作！');           
            }
            $infoArr = array('mobile'=>$mobile);
            $this->login($infoArr);
        }        
        $account = json_decode(cookie('account'),true);
        $this->uid = $account['id'];
        //检查是否登录
        if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        }
        $condition = array('payment_id'=>$paymentId);
        $field = array('payment_id','status');
        $paymentInfo = $this->modelExtension->getPaymentInfo($condition,$field);
        if (!$paymentInfo) {
            $paymentInfo['status'] ='ready';
        }
        unset($condition);
        unset($field);
        $condition = 'payment_id = '.$paymentId;
        $field = array('payment_id','paybill_id','tid');
        $paymentBillInfo = $this->modelExtension->getPayBillInfo($condition,$field);
        if (!$paymentBillInfo['tid']) {
            $paymentInfo['status'] ='ready';
        }
        unset($condition);
        $condition = ' and tid ='.$paymentBillInfo['tid'];
        $orderInfo = $this->modelExtension->getOrderInfo($condition);
        if (!$orderInfo) {
            $paymentInfo['status'] ='ready';
        }
        $this->assign('paymentInfo',$paymentInfo);
        $this->assign('orderInfo',$orderInfo);
        $this->assign('mobile',$mobile);
        $this->assign('sign',$newSign);
        $this->assign('res',$recode);
        $this->display();
    }
    //确认领取操作
    public function confirmReceive(){
        $tid = I('tid');
        $paymentId = I('paymentId');
        if (!$tid || !$paymentId) {            
            echo json_encode(array(0,"领取失败！"));
            exit();
        }
        $condition = array('payment_id'=>$paymentId);
        $field = array('payment_id','status');
        $paymentInfo = $this->modelExtension->getPaymentInfo($condition,$field);
        if ($paymentInfo['status'] != 'succ') {          
            echo json_encode(array(0,"未支付商品无法领取！"));
            exit();            
        }
        try {   
            unset($condition);         
            $data['status'] = 'WAIT_COMMENT';
            $data['modified_time']=time();
            $condition['tid'] = $tid;
            $res = $this->modelExtension->updateTrade($condition,$data);
            $result = $this->modelExtension->updateOrder($condition,$data);
            if ($res === false || $result === false) { 
                $this->makeLog('confirmReceive','error:1001,错误信息：订单表或子订单表状态修改失败tid:'.$tid);
                echo json_encode(array(0,"领取失败！"));
                exit();                
            }
        } catch (\Exception $e) {            
            $this->makeLog('confirmReceive','error:1002,错误信息：订单表或子订单表状态修改失败tid:'.$tid.'errorMsg:'.$e->getMessage());
            echo json_encode(array(0,"领取失败！"));
            exit(); 
        }
        echo json_encode(array(1,"领取成功！"));
    }
    //已领取页面
    public function receiveed(){
        $mobile = I('mobile');
        $sign = I('sign');
        $newSign = md5('mobile='.$mobile.C('API_KEY'));
        if (empty($this->uid)) {
            //一起一舍直接过来的页面需要验证登录
            if (empty($mobile) || empty($sign)) {
                redirect(__APP__."/Login/login/index");
            }
            if ($sign != $newSign) {
                $this->retError(1001,'验证失败，错误信息：验证失败，非法操作！');           
            }
            $infoArr = array('mobile'=>$mobile);
            $this->login($infoArr);
            $account = json_decode(cookie('account'),true);
            $this->uid = $account['id'];

        }
        $this->display();
    }

    //接口返回错误信息
    public function retError($errCode=1,$msg='操作失败'){
        $ret=array(
            'result'=>100,
            'errcode'=>$errCode,
            'msg'=>$msg
        );
        echo json_encode($ret);
        exit;
    }
    
    //接口返回结果
    public function retSuccess($data=array(),$msg='操作成功'){
        $ret=array(
            'result'=>100,
            'errcode'=>0,
            'msg'=>$msg,
            'data'=>$data
        );
        echo json_encode($ret);
        exit;
    }
    

}