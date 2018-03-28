<?php
namespace Home\Controller;
class PayController extends CommonController {
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			redirect(__APP__."/Login/login/index");
		}
		$this->areaModel=M('site_area');
		$this->userModel=M('sysuser_user');//用户表
		$this->addrModel=M('sysuser_user_addrs'); //收货地址表
		$this->cartModel=M('systrade_cart'); //购物车表
		$this->itemModel=M('sysitem_item');//产品表
		$this->skuStoreModel=M('sysitem_sku_store');//货品的库存
		$this->skuModel=M('sysitem_sku');//货品的库存
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
		$this->itemStatusModel = M('sysitem_item_status');//商品状态表
		$this->modelItemCount=M('sysitem_item_count');
		$this->modelItemStore=M('sysitem_item_store');//商品库存
	}
    //提交订单日志记录
    public function orderLog($data){
        return M("systrade_log")->data($data)->add();
    }


    //检查邮费是否被更改
    public function checkPostFees(){
        $shopIdInfo=$this->cartModel->distinct(true)->field('shop_id')->where('user_id='.$this->uid)->select();
        
        foreach($shopIdInfo as $k2=>$v2){
            $shopIdArr[$k2] = $v2['shop_id'];
        }
        $shopIdStr=implode(',',$shopIdArr);
        if($shopIdStr){
            $where['shop_id']=array('in',$shopIdStr);
            $shopInfo=$this->shopModel->where($where)->field('shop_id,shop_name')->select();
            if(empty($shopInfo)){
                exit;
            }   
        }
        $cartInfo=$this->cartModel->table('systrade_cart c,sysitem_sku s')->where("c.sku_id = s.sku_id and c.user_id=".$this->uid)->field('c.cart_id,c.shop_id,c.item_id,s.sku_id,c.quantity,s.price,s.weight')->select();
        if(empty($cartInfo)){
            $url = __APP__.'/Order/cartEmpty';
            header("location:$url");
            exit;
        }else{
            foreach($cartInfo as $k=>$v){
                $cartInfo[$k]['price']=number_format($cartInfo[$k]['price'],2,'.','');
                // ++++++++++++++++++++++++++++++++++
                $itemId[]=$v['item_id'];
                $itemNum[$v['item_id']]=$v['quantity'];
                $stock[$v['item_id']]=33;//默认有货
                 // ++++++++++++++++++++++++++++++++++
                $cartInfo[$k]['goodsTotalPrice'] = floatval($cartInfo[$k]['price']) * intval($cartInfo[$k]['quantity']);
                $shopTotalPrice += $cartInfo[$k]['goodsTotalPrice'];

                foreach($shopInfo as $k1=>$v1){
                    if($v1['shop_id']==$v['shop_id']){
                      $shopInfo[$k1]['totalPrice'] += floatval($cartInfo[$k]['price']) * intval($cartInfo[$k]['quantity']); 
                       $shopInfo[$k1]['totalWeight'] += floatval($cartInfo[$k]['weight']) * intval($cartInfo[$k]['quantity']);                    
                    }
                }
            }            
        }         
        $addrInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();
        if ($addrInfo) {
            $addrArr=explode(':',$addrInfo['area']);
            $addrInfo['area']=$addrArr;
            //判断库存 start+++++++++++++++++++++++++++++++++++++
            $addrDefaultIdArr=explode('/',$addrArr[1]);
        }
        // 算出配送方式的money start
        $addrFeeProvince=$addrDefaultIdArr[0];//省
        $addrFeeCity=$addrDefaultIdArr[1];//市
        $addrFeeArea=$addrDefaultIdArr[2];//区
        foreach($shopInfo as $k10=>$v10){
            $shopFeeAreaTotal = 0; //初始化
            $shopExpressInfo=$this->logisticsModel->where('shop_id='.$v10['shop_id'])->field('fee_conf,template_id')->find();
            $shopFeeConf=unserialize($shopExpressInfo['fee_conf']);
         
            foreach($shopFeeConf as $k9=>$v9){
                //如果省市区都不在,及自营
                if(count($shopFeeConf) == 1){
                   $shopFeeAreaTotal = floatval($v9['start_fee']) + (intval($v10['totalWeight']) * floatval($v9['add_fee']));
                }

                $shopPressAreaArr=array();
                $shopPressAreaArr=explode(',',$v9['area']);
                 
               
                if(in_array($addrFeeProvince,$shopPressAreaArr)){  //省
                   $shopFeeAreaTotal += floatval($v9['start_fee']) + (intval($v10['totalWeight']) * floatval($v9['add_fee']));
                }

                 if(in_array($addrFeeCity,$shopPressAreaArr)){ //市  $addrFeeCity
                   $shopFeeAreaTotal += floatval($v9['start_fee']) + (intval($v10['totalWeight']) * floatval($v9['add_fee']));
                }

                 if(in_array($addrFeeArea,$shopPressAreaArr)){ //区  $addrFeeArea
                   $shopFeeAreaTotal += floatval($v9['start_fee']) + (intval($v10['totalWeight']) * floatval($v9['add_fee']));
                }
                            
            }

            $shopInfo[$k10]['delivery'] = $shopFeeAreaTotal;
            $shopInfo[$k10]['template_id']=$shopExpressInfo['template_id'];
        }      
        //算出配送方式的money end

        $postInfo=$this->postageModel->select();
        foreach($shopInfo as $k11=>$v11){
            foreach($postInfo as $k12=>$v12){
                if($v11['shop_id']==$v12['shop_id']){
                    $shopInfo[$k11]['postName']=intval($v12['limit_money']);
                    //判断是否增加运费
                    if($shopInfo[$k11]['postName'] > $shopInfo[$k11]['totalPrice']){
                        $shopInfo[$k11]['totalEndPrice'] = $shopInfo[$k11]['totalPrice'] + $shopInfo[$k11]['delivery'];
                        $shopTotalPrice += $shopInfo[$k11]['delivery']; //若不包邮，总价加邮费
                    }else{
                        $shopInfo[$k11]['totalEndPrice'] = $shopInfo[$k11]['totalPrice'];
                        $shopInfo[$k11]['delivery']="0.00";
                    }
                }
            }
        }
            
        return $shopInfo;
        

    }

    //提交订单
    public function addUserOrder(){
        $cartList = $this->cartModel->table('systrade_cart cart ,sysitem_sku sku')->where(' cart.sku_id = sku.sku_id and cart.user_id ='.$this->uid)->select();
       
        $addressInfo = $this->addrModel->where('user_id ='.$this->uid." and def_addr = 1")->find();

        if ($addressInfo['area']) {
            $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
        }
        //判断商品状态
        foreach ($cartList as $keyCart => $valCart) {
            $itemIds[] =$valCart['item_id']; 
        }
        $strItemIds = implode(',',$itemIds);
        $statusWhere['item_id'] = array('in',$strItemIds);
        $itemStatus = $this->itemStatusModel->where($statusWhere)->select();
        foreach ($itemStatus as $keyIS => $valIS) {
            if ($valIS['approve_status'] == 'instock' ) {                
                echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                echo "<script language=\"JavaScript\">\r\n"; 
                echo " alert(\"存在已下架的商品，请重新选择！\");\r\n"; 
                echo "window.location.href=\"http://www.lishe.cn/business/wshop.php/Order/cart\"\r\n"; 
                echo "</script>";
                exit(); 
            }
        }
        
        $postData['remark'] = I('post.remark');//买家留言
        $postData['shopIds'] = I('post.shopIds');//店铺id
        $postData['dlytmplIds'] = I('post.dlyTmplIds');//配送模板id
        $postData['postFees'] = I('post.postFees');//邮费


        //检查运费数据是否一致
        $checkPostFree = $this->checkPostFees();
        if ($checkPostFree) {
            foreach ($checkPostFree as $kPf => $vPf) {
                if ($vPf['delivery'] != $postData['postFees'][$kPf]) {
                    $thisRes['res']= 0;
                    $thisRes['log'] = array(
                        'rel_id' =>$data['tid'],
                        'op_name' =>"系统",
                        'op_role' =>"system",
                        'behavior' =>"cancel",
                        'log_text' => '运费数据有问题',
                        'log_time' =>time()
                    );
                    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                    echo "<script language=\"JavaScript\">\r\n"; 
                    echo " alert(\"下单失败，请重新尝试！\");\r\n"; 
                    echo "window.location.href=\"http://www.lishe.cn/business/wshop.php/Order/cart\"\r\n"; 
                    echo "</script>";
                    exit(); 

                }
            }
        }        

        $log = array();
        $thisRes = array();
        $tid = array();
        //根据店铺id生成订单
        if ($postData['shopIds']) {
            foreach ($postData['shopIds'] as $key => $value) {
                $data['tid'] = substr(date(ymdHis),2).$key.$this->uid;//订单编号
                $tid[$key] = $data['tid'];
                $data['shop_id'] = $value;//订单所属的店铺id
                $data['user_id'] = $this->uid;//会员id
                $data['com_id'] = $this->comId;//员工企业id
                $data['dlytmpl_id'] = $postData['dlytmplIds'][$key];//配送模板id
                $data['status'] = 'WAIT_BUYER_PAY';//订单状态

                //实付金额,订单最终总额
                $toallPrice=0;
                foreach ($cartList as $ke => $val) {
                    if ($val['shop_id'] == $value) {
                        $toallPrice +=  $val['price']*$val['quantity'];
                    }
                }
                $data['payment'] = round($toallPrice+$postData['postFees'][$key],2);
                $data['total_fee'] = $toallPrice;//各子订单中商品price * num的和，不包括任何优惠信息
                $data['post_fee'] = round($postData['postFees'][$key],2);//邮费
                $data['receiver_name'] = $addressInfo['name'];//收货人姓名
                $data['created_time'] = time();
                $data['receiver_state'] = $newTakeAddress[0];//收货人所在省份
                $data['receiver_city'] = $newTakeAddress[1];//收货人所在城市
                $data['receiver_district'] = $newTakeAddress[2];//收货人所在地区
                $data['receiver_address'] = $addressInfo['addr'];//收货人详细地址
                $data['receiver_zip'] = $addressInfo['zip'];//收货人邮编
                $data['receiver_mobile'] = $addressInfo['mobile'];//收货人手机号
                $data['receiver_phone'] = $addressInfo['tel'];//收货人电话
                $data['title'] = "订单明细介绍";//交易标题
                $data['buyer_message'] = $postData['remark'][$key];//买家留言
                $data['trade_from'] = "ws";//订单来源
                //子订单商品购买数量总数
                $toallNum=0;
                foreach ($cartList as $ke => $val) {
                    if ($val['shop_id'] == $value) {
                        $toallNum +=  $val['quantity'];
                    }
                }
                $data['itemnum'] = $toallNum;
                if ($addressInfo['area']) {
                    $areaIds = trim(strstr($addressInfo['area'],':'),":");
                }
                $data['buyer_area'] = $areaIds;//买家下单的地区
                $toallWeight=0;
                foreach ($cartList as $ke => $val) {
                    if ($val['shop_id'] == $value) {
                        $toallWeight +=  $val['weight']*$val['quantity'];
                    }   
                }

                $data['total_weight'] = floatval($toallWeight);//商品重量

                $res = $this->tradeModel->data($data)->add();

                if ($res) {
                    foreach ($cartList as $ke => $val) {
                        if ($val['shop_id'] == $value) {
                            $itemInfo = $this->itemModel->where('item_id ='.$val['item_id'])->field('item_id,cat_id,cost_price,image_default_id,supplier_id,send_type')->find();
                            $da['oid'] = substr(date(ymdHis),2).$ke.$this->uid;
                            $da['tid'] = $data['tid'];
                            $da['cat_id'] = $itemInfo['cat_id'];//类目id
                            $da['shop_id'] = $value;
                            $da['user_id'] = $this->uid;
                            $da['item_id'] = $val['item_id'];
                            $da['sku_id'] = $val['sku_id'];
                            $da['bn'] = $val['bn'];//明细商品的编码
                            $da['title'] = $val['title'];
                            $da['spec_nature_info'] = $val['spec_info'];
                            $da['price'] =  $val['price'];
                            $da['cost_price'] = $val['cost_price'];
                            $da['supplier_id'] = $itemInfo['supplier_id'];
                            $da['send_type'] = $itemInfo['send_type'];
                            if (empty($val['cost_price'])) {
                                $this->log_result('cost_price',"商品进货价为：".$val['cost_price']."\r\n");
                            }
                            $da['num'] = $val['quantity'];
                            //$da['shipping_type'] = ; //运送方式                           
                            $da['total_fee'] = floatval($val['price']*$val['quantity']);//应付金额
                            $da['total_weight'] = floatval($val['weight']*$val['quantity']);
                            $da['modified_time'] = time();
                            $da['order_from'] = "ws";
                            $da['pic_path'] = $itemInfo['image_default_id'];

                            $r = $this->tradeOrderModel->data($da)->add();
                            if ($r) {
                                //下单增加购买数量
                                $itemCount = $this->modelItemCount->where('item_id ='.$val['item_id'])->setInc('buy_count',$val['quantity']);
                                //下单预占sku库存
                                $resStore = $this->skuStoreModel->where('sku_id ='.$val['sku_id'])->setInc('freez',$val['quantity']);
                                //下单预占item库存
                                $resItemStore = $this->modelItemStore->where('item_id='.$val['item_id'])->setInc('freez',$val['quantity']);
                                $resCartDel = $this->cartModel->where('cart_id = '.$val['cart_id'])->delete();
                                if ($resStore && $resCartDel) {
                                    //订单提交成功
                                    $thisRes['res']= 1;
                                    $thisRes['log'] = array(
                                        'rel_id' =>$data['tid'],
                                        'op_name' =>"系统",
                                        'op_role' =>"system",
                                        'behavior' =>"cancel",
                                        'log_text' => '订单提交成功',
                                        'log_time' =>time()
                                        );
                                }else{
                                    //减库存失败
                                    $thisRes['res']= 0;
                                    $thisRes['log'] = array(
                                        'rel_id' =>$data['tid'],
                                        'op_name' =>"系统",
                                        'op_role' =>"system",
                                        'behavior' =>"cancel",
                                        'log_text' => '减库存失败或删除购物车数据失败',
                                        'log_time' =>time()
                                        );
                                    //$this->orderLog($log);
                                }
                            }else{
                              //订单子表插入失败
                                    $thisRes['res']= 0;
                                    $thisRes['log'] = array(
                                        'rel_id' =>$data['tid'],
                                        'op_name' =>"系统",
                                        'op_role' =>"system",
                                        'behavior' =>"cancel",
                                        'log_text' => '订单子表插入失败',
                                        'log_time' =>time()
                                        );
                            }

                        }
   
                    }

                }else{
                    //订单表插入失败
                        $thisRes['res']= 0;
                        $thisRes['log'] = array(
                            'rel_id' =>$data['tid'],
                            'op_name' =>"系统",
                            'op_role' =>"system",
                            'behavior' =>"cancel",
                            'log_text' => '订单表插入失败',
                            'log_time' =>time()
                            );
                }
               

            }
        }else{
            //购物车没有数据
            $thisRes['res']= 0;
            $thisRes['log'] = array(
                'rel_id' =>$data['tid'],
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '购物车没有数据',
                'log_time' =>time()
                );
        }
        //日志记录
        $this->orderLog($thisRes['log']);
        //生成支付数据
        $paymentId = $this->creatPayments($tid);
        if ($paymentId) {
            redirect('pay/paymentid/'.$paymentId);
        }else{
            echo "<script type='text/javascript'>alert('支付单生成失败，请在个人中心重新支付！');window.location.href='__APP__/User/userCenter';</script>";
        }      
         
    }

    public function log_result($type,$content){
        $file  = 'logs/'.$type.'/log'.date('Ymd').'.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个  
        file_put_contents($file, $content,FILE_APPEND);
    }
    //生成支付数据（用于用户个人中心的二次支付使用）
    public function creatPaymentsTwo($tid){
        if (trim($tid)) {
            //判断商品状态
            $orderList = $this->tradeOrderModel->where('tid='.$tid)->field('item_id')->select();
            foreach ($orderList as $key => $value) {
                $itemIds[] =$value['item_id']; 
            }
            $strItemIds = implode(',',$itemIds);
            $statusWhere['item_id'] = array('in',$strItemIds);
            $itemStatus = $this->itemStatusModel->where($statusWhere)->select();
            foreach ($itemStatus as $keyIS => $valIS) {
                if ($valIS['approve_status'] == 'instock' ) {  
                    echo json_encode(array(0,'存在已下架的商品，请重新选择！'));
                    exit;                    
                }
            }
            $paymentId=$this->creatPayments(array($tid));
            if ($paymentId) {
                echo json_encode(array(1,$paymentId)); 
            }else {
                echo json_encode(array(2,0)); 
            }             
        }else{
            echo json_encode(array(0,'支付参数错误！'));
        }

    }

    //生成支付数据
    public function creatPayments($tid){
        $thisRes = array();
        if ($tid) {

            //获取订单表信息
            $where['tid']  = array('in',implode(',', $tid));
            $tradeList = $this->tradeModel->where($where)->select();

            $toallPrice = 0 ;
            if ($tradeList) {
                foreach ($tradeList as $key => $value) {
                    $toallPrice += $value['payment'];
                }
            }

            //插入支付表
            $data['payment_id'] = date(ymdHis).$this->uid.'1';//支付单号
            $data['money'] = round($toallPrice,2);//需要支付的金额
            $data['cur_money'] = 0;//支付货币金额
            $data['user_id'] = $this->uid;
            $data['user_name'] = $this->userName;
            $data['op_name'] = $this->userName; //操作员
            $data['bank'] = '预存款';//收款银行
            $data['pay_account'] ='用户';//支付账号
            $data['created_time'] = time();
            $result = $this->paymentsModel->data($data)->add();
            if ($result) {
                foreach ($tid as $key => $value) {
                    $da['payment_id'] = $data['payment_id'];//主支付单编号
                    $da['tid'] = $value;
                    if ($tradeList) {
                        $payPrice = 0 ;
                        foreach ($tradeList as $ke => $val) {
                            if ($val['tid'] == $value) {
                                $payPrice = $val['payment'];
                            }
                        }
                    }
                    $da['payment'] = $payPrice;
                    $da['user_id'] = $this->uid;
                    $da['created_time'] = time();  

                    $result = $this->tradePaybillModel->data($da)->add();                  
                    if ($result) {
                        //插入数据成功
                        $thisRes = $data['payment_id'];
                        
                    }else{
                        //插入数据失败
                        $thisRes = 0;
                    }
                }
            }else{
                //支付主表插入错误
                        $thisRes = 0;
            }            
        }else{
            //tid为空
                        $thisRes = 0;
        }
        return $thisRes;
    }
    //支付页面
    public function pay(){
        $paymentid = I("get.paymentid");
        //积分表
        $userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id ='.$paymentid)->find();
        $paymentInfo['balance'] = $paymentInfo['money']*100;
        $this->assign('paymentInfo',$paymentInfo);
        $this->assign('paymentid',$paymentid);
        $this->assign('userDepositInfo',$userDepositInfo);
        $this->display();
    }


    //支付操作
    public function operPay(){
        $paymentid = I('post.paymentid');
        $pwd = I('post.pwd');
        $payType = I('post.payType');
        $userDepositInfo = $this->userDepositModel->where('user_id ='.$this->uid)->find();

        //用户登录信息表
        $userAccountInfo = $this->userAccountModel->where('user_id ='.$this->uid)->find();
        //支付表
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentid)->find();
        
        //支付子表
        $paymentBillInfo = $this->tradePaybillModel->where('payment_id = '.$paymentid)->select();
        $tidarry = array();
        foreach ($paymentBillInfo as $key => $value) {
            $tidarry[$key] = $value['tid'];
        }

        //检查是否已经支付
        if ($paymentInfo['status'] =='succ') {
            //已经支付不可再次支付
            echo json_encode(array(2,"该订单已经支付！"));
            exit();
        }else{
            //积分支付
            if ( $payType == 'deposit') {

                if (md5($pwd) == $userDepositInfo['md5_password']) {

                    $payRes = $this->dedect($this->uid,$userAccountInfo['mobile'],$paymentid,round($paymentInfo['money'],2),$paymentInfo['memo']);
                    
                    if($payRes['result']==100){

                        if($payRes['errcode']>0){
                            //支付失败，日志表
                            $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>'支付失败：'.$payRes['msg'],'logtime'=>time());
                            $this->userDataDepositLogModel->data($logdata)->add();
                            echo json_encode(array(0,$payRes['msg']));
                            exit();
                        }else{
                            //支付成功，更新本地积分
                            $this->userDepositModel->where('user_id ='.$this->uid)->setDec('deposit',$paymentInfo['money']);
                            $this->userDepositModel->where('user_id ='.$this->uid)->setDec('balance',$paymentInfo['money']*100);
                            $this->userDepositModel->where('user_id ='.$this->uid)->setDec('commonAmount',$paymentInfo['money']*100);                            

                            //更新支付主表
                            $zdata['cur_money'] = $paymentInfo['money'];
                            $zdata['pay_type'] = $payType;
                            $zdata['pay_app_id'] = 'deposit';
                            $zdata['payed_time'] = time();
                            $zdata['status'] = 'succ';
                            $zdata['trade_no'] = $payRes['data']['info']['transno'];
                            $zres = $this->paymentsModel->where('payment_id ='.$paymentid)->data($zdata)->save();

                            //更新支付副表
                            $fda['status'] = 'succ';
                            $fda['payed_time'] = time();
                            $fres = $this->tradePaybillModel->where('payment_id ='.$paymentid)->data($fda)->save();
                            
                            //更新订单主表
                            foreach ($paymentBillInfo as $kpb => $valpb){
                                $trdeDate = array(
                                    'transno'=>$payRes['data']['info']['transno'],
                                    'status'=>"WAIT_SELLER_SEND_GOODS",
                                    'payed_fee'=>$valpb['payment'],
                                    'pay_time'=>time(),
                                    'modified_time'=>time()
                                );
                                $this->tradeModel->where('tid ='.$valpb['tid'])->data($trdeDate)->save();
                                $this->tradeOrderModel->where('tid ='.$valpb['tid'])->save(array('status'=>'WAIT_SELLER_SEND_GOODS'));
                                $payBillOrderList = $this->tradeOrderModel->where('tid ='.$valpb['tid'])->select();
                                foreach ($payBillOrderList as $kbo => $valbo) {
                                    //购买减sku_store库存和预占库存
                                    $this->skuStoreModel->where('sku_id='.$valbo['sku_id'])->setDec('freez',$valbo['num']);
                                    $this->skuStoreModel->where('sku_id='.$valbo['sku_id'])->setDec('store',$valbo['num']);
                                    //购买增加item销量
                                    $this->modelItemCount->where('item_id ='.$valbo['item_id'])->setInc('sold_quantity',$valbo['num']);
                                    //购买增加sku销量
                                    $this->skuModel->where('sku_id ='.$valbo['sku_id'])->setInc('sold_quantity',$valbo['num']);
                                    //购买减item_store库存和预占库存
                                    $this->modelItemStore->where('item_id='.$valbo['item_id'])->setDec('freez',$valbo['num']);
                                    $this->modelItemStore->where('item_id='.$valbo['item_id'])->setDec('store',$valbo['num']);
                                }
                            }                        

                            //同步京东订单 
                            $this->jdOrderPost($paymentid); 
        			    	
                            //日志表
                            $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>$paymentInfo['memo'],'logtime'=>time());
                            $this->userDataDepositLogModel->data($logdata)->add();
                            echo json_encode(array(1,'订单支付成功！'));
                        
                        }

                    }else{
                        //接口通讯失败，日志表                        
                        $logdata =array('type'=>'expense','user_id'=>$this->uid,'operator'=>$userAccountInfo['mobile'],'fee'=>$paymentInfo['money'],'message'=>$payRes['msg'],'logtime'=>time());
                        $this->userDataDepositLogModel->data($logdata)->add();                        
                        echo json_encode(array(0,$payRes['msg']));
                        exit();
                    }
                }else{
                    //支付密码不正确
                    echo json_encode(array(0,"支付密码不正确！")); 
                    exit();
                }    

            }else if($payType == 'wxpay'){
                //微信支付
                echo json_encode(array(1,$paymentid));
                exit();
            }
        }
    }


    //微信支付
    public function wxpay(){
        //导入微信支付
        vendor('WxpayAPI.Util.WxPayPubHelper','','.class.php');
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
        $unifiedOrder->setParameter("body","礼舍商城在线支付");//商品描述
        $unifiedOrder->setParameter("out_trade_no",$paymentId);//商户订单号 
        $unifiedOrder->setParameter("total_fee",$paymentInfo['money'] * 100);//总金额
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
        $this->display('inteRech');

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
        vendor('Alipay.lib.alipay_submit','','.class.php');
        vendor('Alipay.alipay','','.config.php');

        $paymentId=trim(I('get.paymentId'));
        $paymentInfo = $this->paymentsModel->where('payment_id = '.$paymentId)->find(); 
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
            "subject"   => '积分充值',
            "total_fee" => sprintf("%.2f", $paymentInfo['money']),
            "show_url"  => __APP__."/Pay/inteRechCenter",
            "body"  => "积分充值",
        );
        $alipaySubmit = new \AlipaySubmit($alipayConfig);  
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");

        echo $html_text;
        
    }

    //支付宝同步支付返回结果
    public function aliPayReturnUrl(){
        vendor('Alipay.lib.alipay_notify','','.class.php');
        vendor('Alipay.alipay','','.config.php');        
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
            }else{
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
            $this->userDataDepositLogModel->data($logdata)->add(); 
            $this->assign('res','fail');
        }
        //日志表        
        $this->display('payResult');
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
        $this->assign('rules',$res['data']['info']);
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