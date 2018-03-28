<?php
/**
  +------------------------------------------------------------------------------
 * OrderController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: OrderController.class.php v001 2016-10-15
 * @description 订单相关接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class OrderController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->modelTradeRefund=M('systrade_refund');
		$this->modelTradeRefundLog=M('systrade_refund_log');
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->giftTrade=M('gift_trade');
		$this->modelReturn=M('systrade_return');
		
		$this->modelActivityTrade=M('company_activity_trade');
		$this->modelActivityOrder=M('company_activity_order');
		
		$this->modelItem=M('sysitem_item');
    	$this->modelItemSku=M('sysitem_sku');
    	$this->modelItemSkuStore=M('sysitem_sku_store');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelPayments=M('ectools_payments');
		$this->modelPaybill = M('ectools_trade_paybill');//支付子表
		
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelDepositLog=M('sysuser_user_deposit_log');
		$this->modelAccount=M('sysuser_account');

		$this->modelCart=M('systrade_cart');
		$this->modelAddr=M('sysuser_user_addrs');
		$this->modelArea=M('site_area');
		$this->modelShop=M('sysshop_shop');
		$this->modelLogistics=M('syslogistics_dlytmpl');//快递信息表
		$this->modelPostage=M('syspromotion_freepostage');//包邮表
		$this->modelItemCount=M('sysitem_item_count');
		$this->modelItemStore=M('sysitem_item_store');//商品库存
		$this->companyItemPriceModel=M('company_item_price');//公司特价
  
		$this->modelAItem=M('company_activity_item');
        
		$this->modelOrd=D('Order');
		
		//东莞移动用户邮费表
		$this->syslogisticsDgyd = M('syslogistics_dgyd');
	}
	//特价商品--zhangrui
	private function specialPriceItem($itemInfo,$comId){
		if($itemInfo){
			foreach($itemInfo as $key=>$value){
				$itemInfo[$key]['shop_price']=$value['price'];
				$skuIds[]=$value['sku_id'];
			}
			if(!empty($skuIds)){
				$condition['sku_id']=array('in',$skuIds);
				$condition['is_delete']=0;
				$specialInfo=$this->companyItemPriceModel->where($condition)->field('sku_id,price,com_id,start_time,end_time')->select();
				if($specialInfo){
					//存在打特价商品
					$nowTime=time();
					foreach($itemInfo as $key=>$value){
						foreach($specialInfo as $keys=>$values){
							if(($value['sku_id']==$values['sku_id']) && (empty($values['com_id']) || $values['com_id']==$comId) && ($nowTime>=$values['start_time'] && $nowTime<=$values['end_time'])){
								$itemInfo[$key]['price']=$values['price'];
							}
						}
					}
				}
			}
			return $itemInfo;
		}
	} 	
	//商品生成订单和支付单 awen 2016-09-26
	public function apiCreateOrder(){
		$strData = I('post.strData');
		$source  = I('post.source'); //订单来源  公司/心意商城
		$strData=str_replace('&quot;','"',$strData);
		$arrData = json_decode($strData,true);
		if (empty($arrData['shopIds']) || empty($arrData['itemIds']) || empty($arrData['skuIds']) || empty($arrData['dlytmplIds']) || empty($arrData['uid'])) {            
			$this->makeLog('createOrder','error:1001,错误信息：店铺ID、商品ID、配送模板ID或库存ID为空');
			$this->retError(1001,':生成订单失败，错误信息：店铺ID、商品ID、配送模板ID或库存ID为空');
        }
        //查询用户comid 
        $userDepositInfo = getUserDeposit($arrData['uid']); 
        if (!$userDepositInfo) {
             $this->makeLog('createOrder','error:1002,错误信息：无法获取用户信息');
			$this->retError(1002,':生成订单失败，错误信息：无法获取用户信息');
        }      
		$userDepositInfo = $this->modelDeposit->where('user_id ='.$arrData['uid'])->find();
        $skuIdsStr = implode(',', $arrData['skuIds']); 
        $cartList = $this->modelCart->table('systrade_cart cart ,sysitem_sku sku,sysitem_sku_store sto')
        				->where(" cart.sku_id = sku.sku_id and sku.sku_id = sto.sku_id and cart.user_id =".$arrData['uid']." and cart.sku_id in($skuIdsStr)")->select();
		
        if(empty($cartList)){
        	$this->makeLog('createOrder','error:1014,错误信息：未找到购物车商品信息，检查是否已成功提交');
        	$this->retError(1014,'生成订单失败，错误信息：未找到购物车商品信息，检查是否已成功提交');
        }	
		
		//商品打特价 ---begin zhangrui
		if($source == 'company'){
			$cartList=$this->specialPriceItem($cartList,$userDepositInfo['comId']);
		}
		//商品打特价 ---end
        $addressInfo = $this->modelAddr->where('user_id ='.$arrData['uid']." and def_addr = 1")->find();
        //处理留言
        $newRemark = array();
        if ($arrData['remark']) {
            foreach ($arrData['remark'] as $key => $value) {
                $skuKeys = array_keys($value);
                $remarkVal = array_values($value);
                $newRemark[$skuKeys[0]] = $remarkVal[0];
            }
        }
		//检查购买数量是否超过库存
        foreach ($cartList as $kcl => $valcl) {
        	//可购买商品数量
            $itemIds[$kcl] = $valcl['item_id'];
        	$noFreez = $valcl['store']-$valcl['freez'];
            if ($noFreez < 1 || $valcl['quantity'] >  $noFreez) {            
				$this->makeLog('createOrder','error:1003,错误信息：购买数量超过商品库存数量');
				$this->retError(1003,'生成订单失败，错误信息：购买数量超过商品库存数量');
            }
        } 
        //查询sku表
        $conditionSku = "sku_id IN (".implode(',', $arrData['skuIds']).")";
        $itemSkuList = $this->modelOrd->getSkuList($conditionSku);
        if (!$itemSkuList) {            
            $this->makeLog('createOrder','error:1004,错误信息：商品sku信息有误');
            $this->retError(1004,'生成订单失败，错误信息：商品sku信息有误');
        }
        //校验进货价和销售价
        foreach ($itemSkuList as $key => $value) {  
            if (empty($value['type'])) {                               
                if (empty($value['cost_price']) || $value['price'] <= $value['cost_price']) {
                    $this->makeLog('createOrder','error:1005,错误信息：商品价格信息有误');
                    $this->retError(1005,'生成订单失败，错误信息：商品价格信息有误');
                } 
            } 
        }
        //查询item表
        $conditionItem = "item_id IN (".implode(',', $arrData['itemIds']).")";
        $itemItemList = $this->modelOrd->getItemList($conditionItem);
        if (!$itemItemList) {            
            $this->makeLog('createOrder','error:1006,错误信息：商品信息有误');
            $this->retError(1006,'生成订单失败，错误信息：商品信息有误');
        }
        $itemList = array();
        foreach ($itemSkuList as $key => $value) {
            foreach ($itemItemList as $k => $val) {
                if ($value['item_id'] == $val['item_id']) {
                    $itemList[$key] = array_merge($value,$val);
                }
            }
        }        
        //京东商品判断地址是否准确
        if($this->checkJdAddress(trim(strstr($addressInfo['area'],':'),":")) == false){
			$this->makeLog('createOrder','error:1007,错误信息：地址信息有误');
			$this->retError(1007,'生成订单失败，错误信息：地址信息有误');
        }
        $newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
    
        //检查运费数据是否一致
        $checkPostFree = $this->checkPostFees($arrData['shopIds'] ,$arrData['skuIds'],$arrData['uid']);
        if (!$checkPostFree) {            
			$this->makeLog('createOrder','error:1008,错误信息：运费数据不一致');
			$this->retError(1008,'生成订单失败，错误信息：运费数据不一致'); 
        }
        foreach ($checkPostFree as $kPf => $vPf) {
			$result = bccomp($vPf['delivery'],$arrData['postFees'][$kPf]);
            if ($result !== 0) {
            	$this->makeLog('createOrder','error:1009,错误信息：运费数据不一致');
				$this->retError(1009,'生成订单失败，错误信息：运费数据不一致');                     
            }
        }

        	
        $thisRes = array();
        $tid = array();
        //根据店铺id生成订单
        foreach ($arrData['shopIds'] as $key => $value) {
            $data['tid'] = date(ymdHis).$key.$arrData['uid'];//订单编号
            $tid[$key] = $data['tid'];
            $data['shop_id'] = $value;//订单所属的店铺id
            $data['user_id'] = $arrData['uid'];//会员id
            $data['com_id'] = $userDepositInfo['comId'];//员工企业id
            $data['dlytmpl_id'] = $arrData['dlytmplIds'][$key];//配送模板id
            $data['status'] = 'WAIT_BUYER_PAY';//订单状态
            $data['trade_status'] = 'WAIT_BUYER_PAY';//订单状态

            //实付金额,订单最终总额
            $toallPrice=0;
            $totalCash = 0;
            $totalPoint = 0;
            foreach ($cartList as $ke => $val) {
                if ($val['shop_id'] == $value) {
                    $toallPrice +=  sprintf("%.2f",$val['price'])*$val['quantity'];
                    $totalCash += round($val['cash']*$val['quantity'],2);
                    $totalPoint += $val['point']*$val['quantity'];
                }
            }
            $arrData['postFees'][$key] = empty($arrData['postFees'][$key]) ? 0 : $arrData['postFees'][$key];
            $data['payment'] = round($toallPrice+$arrData['postFees'][$key],2);
            $data['cash_fee'] = $totalCash;//需要支付的现金
            $data['point_fee'] = $totalPoint+round($arrData['postFees'][$key]*100);//需要支付的积分+上运费
            $data['total_fee'] = $toallPrice;//各子订单中商品price * num的和，不包括任何优惠信息
            $data['post_fee'] = floatval($arrData['postFees'][$key]);//邮费
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
            //$data['buyer_message'] = $arrData['remark'][$key];//买家留言
            $arrData['orderFrom'] = empty($arrData['orderFrom']) ? 'pc' : $arrData['orderFrom'];
            $data['trade_from'] = $arrData['orderFrom'];//订单来源
            $data['trade_type'] = empty($arrData['tradeType'][$key]) ? 0 : $arrData['tradeType'][$key];
            $data['activity_id'] = empty($arrData['activityId'][$key]) ? 0 : $arrData['activityId'][$key];
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
            $data['total_weight'] = floatval($toallWeight);
            $res = $this->modelTrade->data($data)->add();
            if (!$res) {
				$this->makeLog('createOrder','error:1010,错误信息：主订单表插入失败');
				$this->retError(1010,'生成订单失败，错误信息：主订单表插入失败');             	
            }
            foreach ($cartList as $ke => $val) {
                if ($val['shop_id'] == $value) {
                    foreach ($itemList as $kitem => $vitem) {
                        if ($vitem['sku_id'] == $val['sku_id']) {
                            $da['oid'] = date(ymdHis).$ke.$arrData['uid'];
                            $da['tid'] = $data['tid'];
                            $da['supplier_id'] = empty($vitem['supplier_id']) ? 0 : $vitem['supplier_id'];
                            $da['send_type'] =$vitem['send_type'];
                            $da['cat_id'] = $vitem['cat_id'];
                            $da['shop_id'] = $value;
                            $da['user_id'] = $arrData['uid'];
                            $da['item_id'] = $val['item_id'];
                            $da['sku_id'] = $val['sku_id'];
                            $da['bn'] = $val['bn'];
                            $da['title'] = $val['title'];
                            $da['spec_nature_info'] = $val['spec_info'];
                            $da['price'] =  round($val['price'],2);
                            $da['cost_price'] = $vitem['cost_price'];
                            if (empty($val['quantity']) || $val['quantity'] < 1) {
                                $da['num'] = 1;
                            }else{
                                $da['num'] = $val['quantity'];
                            }                                            
                            $da['total_fee'] = floatval(round($val['price'],2)*$da['num']);
                            $da['cash'] = round($val['cash'],2);//单品需要支付的现金
                            $da['point'] = $val['point'];//单品需要支付的积分
                            $da['total_cash'] = round($val['cash'],2)*$da['num'];//该商品需要支付的现金
                            $da['total_point'] = $val['point']*$da['num'];//该商品需要支付的积分

                            $da['total_weight'] = floatval($val['weight']*$val['quantity']);
                            $da['modified_time'] = time();
                            $da['order_from'] = $arrData['orderFrom'];
                            $da['pic_path'] = $vitem['image_default_id'];
                            $da['buyer_message'] = $newRemark[$val['sku_id']];
                            $r = $this->modelOrder->data($da)->add();
                            if (!$r) {
        						$this->makeLog('createOrder','error:1011,错误信息：子订单表插入失败');
        						$this->retError(1011,'生成订单失败，错误信息：子订单表插入失败');
                            }
                            //下单增加购买数量
                            $itemCount = $this->modelItemCount->where('item_id ='.$val['item_id'])->setInc('buy_count',$val['quantity']);
                            //下单预占sku库存
                            $resStore = $this->modelItemSkuStore->where('sku_id ='.$val['sku_id'])->setInc('freez',$val['quantity']);
                            //下单预占item库存
                            $resItemStore = $this->modelItemStore->where('item_id='.$val['item_id'])->setInc('freez',$val['quantity']);
                            $resCartDel = $this->modelCart->where('cart_id = '.$val['cart_id'])->delete();
                            if (!$resStore && !$resCartDel && !$itemCount && !$resItemStore) {
                            	$this->makeLog('createOrder','error:1012,错误信息：预占库存、增加下单数量或删除购物车数据失败');
        						$this->retError(1012,'生成订单失败，错误信息：预占库存、增加下单数量或删除购物车数据失败');                        
                            }
                        }
                    }
                }
            }
        } 
        //生成支付数据
        $paymentId = $this->creatPayments($tid,$arrData['uid'],1);  

        if (!$paymentId) {
        	$this->makeLog('createOrder','error:1013,错误信息：支付单号不能为空');
			$this->retError(1013,'生成订单失败，错误信息：支付单号不能为空'); 
        }
        //团购和预售订单不做处理
        $isSpliteTrade = 0;
        if ($arrData['tradeType']) {
            foreach ($arrData['tradeType'] as $key => $value) {
                if ($value == 4 || $value == 5) {
                    $isSpliteTrade = 1;
                }
            }
        }
        if (empty($isSpliteTrade)) {
            //拆分订单2017-02-14 
            $returnSupplier=A('Supplier')->splitTrade($paymentId);
            if ($returnSupplier[0] != 1) {
                $this->makeLog('createOrder','error:1014,错误信息：订单拆分失败');
                $this->retError(1014,'拆分订单失败，错误信息：订单拆分失败');             
            }            
        }
        
        $this->retSuccess(array('paymentId'=>$paymentId),'订单提交成功');
	}

	//生成支付数据
    public function creatPayments($tid='',$uid='',$iden=0){
        if (empty($iden) || (empty($tid) && empty($uid))) {            
             $tidData = I('post.tid');            
             $tidData=str_replace('&quot;','"',$tidData);
             $tid = json_decode($tidData,true); 
             $uid = I('post.uid'); 
        }
    	if (empty($tid) || !is_array($tid) || empty($uid)) {
        	$this->makeLog('creatPayments','error:1001,错误信息：订单编号有误');
			$this->retError(1001,'生成订单失败，错误信息：订单编号有误');    		
    	}
    	//查询用户登录信息
        $userAccountInfo = getUserAccount($uid); 
        if (!$userAccountInfo) {
             $this->makeLog('creatPayments','error:1002,错误信息：无法获取用户登录信息');
			$this->retError(1002,':生成订单失败，错误信息：无法获取用户登录信息');
        } 
        //查询用户资料信息
        $userInfo = getUser($uid);
        if (!$userInfo) {
            $this->makeLog('creatPayments','error:1003,错误信息：无法获取用户资料信息');
			$this->retError(1003,':生成订单失败，错误信息：无法获取用户资料信息');
        }
        $thisRes = array();
        //获取订单表信息
        $where['tid']  = array('in',implode(',', $tid));
        $tradeList = $this->modelTrade->where($where)->select();
        $toallPrice = 0 ;
        $totalCash = 0;
        $totalPoint = 0;
        if ($tradeList) {
            foreach ($tradeList as $key => $value) {
                $toallPrice += $value['payment'];
                $totalCash += $value['cash_fee'];
                $totalPoint += $value['point_fee'];
            }
        }
        //插入支付表
        $data['payment_id'] = date(ymdHis).$uid.'1';//支付单号
        $data['money'] = round($toallPrice,2);//需要支付的金额
        $data['cash_fee'] = $totalCash;//需要支付的现金
        $data['point_fee'] = $totalPoint;//需要支付的积分
        $data['cur_money'] = 0;//支付货币金额
        $data['user_id'] = $uid;
        $data['user_name'] = $userAccountInfo['mobile'];
        $data['op_name'] = $userInfo['username']; //操作员
        $data['bank'] = '预存款';//收款银行
        $data['pay_account'] ='用户';//支付账号
        $data['created_time'] = time();
        $result = $this->modelPayments->data($data)->add();
        if (!$result) {        	
        	$this->makeLog('creatPayments','error:1004,错误信息：支付主表插入数据失败');
			$this->retError(1004,'生成订单失败，错误信息：支付主表插入数据失败');  
        }
        foreach ($tid as $key => $value) {
            $da['payment_id'] = $data['payment_id'];//主支付单编号
            $da['tid'] = $value;
            if ($tradeList) {
                $payPrice = 0 ;
                $cash = 0;
                $point = 0;
                foreach ($tradeList as $ke => $val) {
                    if ($val['tid'] == $value) {
                        $payPrice = $val['payment'];
                        $cash = $val['cash_fee'];
                        $point = $val['point_fee'];
                    }
                }
            }
            $da['payment'] = $payPrice;
            $da['cash'] = $cash;
            $da['point'] = $point;
            $da['user_id'] = $uid;
            $da['created_time'] = time(); 
            $result = $this->modelPaybill->data($da)->add();                  
            if (!$result) {
	        	$this->makeLog('creatPayments','error:1005,错误信息：子订单表数据生成失败');
				$this->retError(1005,'生成订单失败，错误信息：子订单表数据生成失败'); 
            }
            $thisRes = $data['payment_id'];
        }
        if (!empty($iden)) {
            return $thisRes;
        }else{
            $this->retSuccess(array('paymentId'=>$thisRes),'支付单生成成功！');
        }
        
    }

	//校验是否有四级地址，防止京东商品无法下单
    function checkJdAddress($jdIds){
        if (empty($jdIds)) {
            return false;
        }
        $addressArr =  explode("/",trim($jdIds,'/'));
        if (!is_array($addressArr)) {
            return false;
        }
        if (count($addressArr) < 4) {
            $count = $this->modelArea->where('jd_pid='.$addressArr[2])->find();
            if ($count) {
                //有四级地址，返回false
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }        
    }

    //检查邮费是否被更改
    public function checkPostFees($shopIds,$skuIds,$uid){
    	$source  = I('post.source'); //订单来源  公司/心意商城
    	//查询用户的公司id
    	$userComId = $this->modelDeposit->where('user_id='.$uid)->getField('comId');
        $shopIdStr=implode(',',$shopIds);
        //查询店铺信息
        $where['shop_id']=array('in',$shopIdStr);
        $shopList=$this->modelOrd->getShopList($where);
        //获取购物车勾选商品的信息
        $conditionCart = " and c.user_id=".$uid." and c.sku_id IN (".implode(',', $skuIds).")";
        $cartList=$this->modelOrd->getCartList($conditionCart);
        if($source == 'company'){
        	$cartList=$this->specialPriceItem($cartList, $userComId);
        }
        foreach ($cartList as $kCart => $vCart) {
            //商品ids
            $itemIds[$kCart] = $vCart['item_id'];
            //商品skus和itemids
            $skuIds[$kCart] = $vCart['sku_id'];
            $skuidItemid[$kCart]['itemId'] = $vCart['item_id'];
            $skuidItemid[$kCart]['skuId'] = $vCart['sku_id'];
            $skuidItemid[$kCart]['num'] = $vCart['quantity'];
        }
        foreach ($cartList as $kCart => $vCart) {
            $cartList[$kCart]['price'] = round($vCart['price'],2);
            $cartList[$kCart]['goodsTotalPrice'] = round($vCart['price'],2) * $vCart['quantity'];
            $shopTotalPrice += $cartList[$kCart]['goodsTotalPrice'];
            $totalCartQuantity += $vCart['quantity'];
            foreach ($shopList as $kShop => $vShop) {
                if ($vShop['shop_id'] == $vCart['shop_id']) {
                    $shopList[$kShop]['totalPrice'] += round($vCart['price'],2) * $vCart['quantity']; 
                    $shopList[$kShop]['totalWeight'] += $vCart['weight'] * $vCart['quantity']; 
                    $shopList[$kShop]['totalNum'] += $vCart['quantity'];
                }
            }
        }
        //查询用户默认地址
        $userAddressInfo = $this->modelOrd->getUserAddress($uid);
        if (!$userAddressInfo) {
            return false;
        }
        $jd_ids = str_replace('/','_',trim(strstr($userAddressInfo['area'],':'),":"));
        //店铺配送方式
        $conditionDlytmpl['shop_id']=array('in',$shopIdStr);
        $dlytmplList = $this->modelOrd->getDlytmpl($conditionDlytmpl);
        $addrDefaultIdArr=explode('_',$jd_ids);
        if (!$dlytmplList) {
            return false;
        }      
        foreach ($dlytmplList as $kdp => $vtp) {
            $dlytmplList[$vtp['shop_id']] = $vtp;
            $shopDlytmpConf[$vtp['shop_id']] = unserialize($vtp['fee_conf']);
        }

        //包邮信息
        $totalCartDelivery="0.00";
        $conditionFreepost['shop_id'] = array('in',$shopIdStr);     
        $freePostList = $this->modelOrd->getFreePost($conditionFreepost);
        foreach ($freePostList as $kfp => $vfp) {
            $shopFreePost[$vfp['shop_id']] = $vfp;
        }
        foreach ($shopList as $kshop => $vshop) {
            $shopFeeAreaTotal = 0; //初始化
            foreach ($shopDlytmpConf[$vshop['shop_id']] as $key => $val) {
                $shopPressAreaArr=array();
                $shopPressAreaArr=explode(',',$val['area']);
                if(!empty($shopPressAreaArr[0])){               
                    if(in_array($addrDefaultIdArr[0],$shopPressAreaArr)){  //省
                        $shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
                    }
                    if(in_array($addrDefaultIdArr[1],$shopPressAreaArr)){ //市  $addrFeeCity
                        $shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
                    }
                    if(in_array($addrDefaultIdArr[2],$shopPressAreaArr)){ //区  $addrFeeArea
                        $shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
                    }   
                }else{
                    if(empty($shopFeeAreaTotal)){
                        $shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
                    }
                }
            }
            $shopList[$kshop]['delivery'] = $shopFeeAreaTotal;
            $shopList[$kshop]['template_id']=$dlytmplList[$vshop['shop_id']]['template_id'];
            $totalCartWeight += $vshop['totalWeight'];
            $shopList[$kshop]['postFree']=$shopFreePost[$vshop['shop_id']]['limit_money'];

            if($shopList[$kshop]['postFree'] > $vshop['totalPrice']){
                $shopList[$kshop]['totalEndPrice'] = $vshop['totalPrice'] + $shopList[$kshop]['delivery'];
                $shopTotalPrice += $shopList[$kshop]['delivery']; //若不包邮，总价加邮费
                $totalCartDelivery += $shopList[$kshop]['delivery'];
            }else{
                $shopList[$kshop]['totalEndPrice'] = $vshop['totalPrice'];
                $shopList[$kshop]['delivery']="0.00";
            }
        }
        //如果是东莞移动计算邮费
        $gdydComId = '1467166836740';
        if($gdydComId == $userComId && $shopTotalPrice > 0){
        	//这里会计算订单总价，邮费，减免费用
        	$this->checkDgydPostFees($shopList, $shopTotalPrice, $totalCartDelivery, $addrDefaultIdArr, $totalCartWeight);
        }
        return $shopList;
    }
	
	//订单支付接口封装 赵尊杰 2016-09-09 注意：后面加事务处理
	public function apiPayOrder(){
		$paymentId = I('post.paymentId');
		$payType = I('post.payType');//支付方式，积分支付：point，ecard：东莞移动一卡通支付
		$orderType = I('post.orderType');//支付订单的类型，商城订单：shop，活动订单：activity，微送礼订单：gift，默认空是商城订单
        if(empty($payType)){
			$payType='point';
		}
		if(empty($orderType)){
			$orderType='shop';
		}
		$postData=json_encode($_POST); 
		$this->makeLog('pay','post:'.$postData);
		if(empty($paymentId)){
			$this->makeLog('pay','error:1001,错误信息：支付单号为空');
			$this->retError(1001,'支付失败，错误信息：支付单号为空');
		}
		$payment=$this->modelPayments->field('payment_id,user_id,money,cur_money,user_name,status,pay_account,point_fee,cash_fee')->where('payment_id='.$paymentId)->find();
		//判断是否是混合支付
        $mixed = 0;
        if ($payment['cash_fee'] != 0 && $payment['point_fee'] != 0) {
            $mixed = 1;
        }
        if(empty($payment['payment_id'])){
			$this->makeLog('pay','error:1002,错误信息：未查到该订单');
			$this->retError(1002,'支付失败，错误信息：未查到该订单');
		}
        if (empty($payment['user_id'])) {
            $this->makeLog('pay','error:1003,错误信息：用户id为空');
            $this->retError(1003,'支付失败，错误信息：用户id为空');            
        }
        $accountInfo = $this->modelAccount->field('user_id,mobile')->where('user_id='.$payment['user_id'])->find();
		if (empty($accountInfo['mobile'])) {
            $this->makeLog('pay','error:1004,错误信息：用户电话为空');
            $this->retError(1004,'支付失败，错误信息：用户电话为空');
        }
		if($payment['status']=='succ'){
			$this->makeLog('pay','error:1005,错误信息：订单已支付，不能重复支付');
			$this->retError(1005,'支付失败，错误信息：订单已支付，不能重复支付');
		}

        $userAccountInfo = getUserAccount($payment['user_id']); 
        if (!$userAccountInfo) {
            $this->makeLog('pay','error:1006,错误信息：无法获取用户登录信息');
            $this->retError(1006,':获取数据失败，错误信息：无法获取用户登录信息');
        }

        //支付子表
        $paymentBillInfo = $this->modelPaybill->where('payment_id = '.$paymentId)->select();
        if (empty($paymentBillInfo)) {
            $this->makeLog('pay','error:1007,错误信息：获取子订单失败');
            $this->retError(1007,':获取数据失败，错误信息：获取子订单失败');            
        }
		foreach ($paymentBillInfo as $key => $value) {
            $tids[$key] = $value['tid'];
        }
		//积分支付
		if($payType=='point'){
			$payPoint=$payment['point_fee'];
			$return=A('Point')->pointPay($paymentId,$accountInfo['mobile'],$payPoint);
			$ret=json_decode($return,true);
			$retCode=$ret['result'];
			$errCode=$ret['errcode'];
			$retMsg=$ret['msg'];
			$transno=$ret['data']['info']['transno'];
		}elseif($payType=='e-card'){//东莞移动一卡通支付
			$user=A('Ecard')->getEcardUser($accountInfo['mobile']);
			$empCode=$user['data']['empCode'];//员工编号
			$posBalance=$user['data']['posBalance'];//员工餐补余额
			if(empty($empCode)){
				$this->makeLog('pay','error:1008,错误信息：未查询到该用户');
				$this->retError(1008,'支付失败，错误信息：未查询到该用户');
			}
			if($posBalance < $payment['point_fee']/100){
				$this->makeLog('pay','error:1009,错误信息：账户余额不足！');
				$this->retError(1009,'支付失败，错误信息：账户余额不足！');
			}
			
			$return=A('Ecard')->ecardPay($paymentId,$payment['point_fee']/100,$empCode,$accountInfo['mobile']);
			$ret=json_decode($return,true);
			$retCode=$ret['code'];
			$errCode=$ret['errCode'];
			$retMsg=$ret['msg'];
			$transno=$paymentId;
		}
		
		if($retCode==100){
			if($errCode==0){			
	        	//支付成功，更新本地积分
	            if($payType=='point'){
			            $this->modelDeposit->where('user_id ='.$payment['user_id'])->setDec('deposit',$payment['point_fee']/100);
			            $this->modelDeposit->where('user_id ='.$payment['user_id'])->setDec('balance',$payment['point_fee']);
			            $this->modelDeposit->where('user_id ='.$payment['user_id'])->setDec('commonAmount',$payment['point_fee']);
							}	
	            //更新支付主表
	            $paymentData = array(
	            	'cur_money'=>round($payment['point_fee']/100,2),	
                    'payed_point'=>$payment['point_fee'],    
	            	'pay_app_id'=>$payType,
	            	'payed_time'=>time(),
	            	'trade_no'=>$transno,
	            	'ls_trade_no'=>$transno,
                    'pay_type'=>'online'
	            );
                if ($mixed == 1) {
                    $paymentData['pay_app_id'] = 'mixed';
                }else{
                    $paymentData['status'] = 'succ';
                }
	            if($payType=='e-card'){
	            	//e卡支付
	            	$paymentData['pay_name']='东莞移动E卡通';
	            	$paymentData['op_id']=$payment['user_id'];
	            	$paymentData['pay_account']=$empCode;//东莞移动员工编号
	            	$paymentData['currency']='CNY';
	            	$paymentData['tids']=$tids;
	            }
	            $paymentRes = $this->modelPayments->where('payment_id ='.$paymentId)->save($paymentData);

	            //更新支付副表
	            $paybillData = array(
	            	'payed_time'=>time(),
	            	'modified_time'=>time()
	            );
                if (empty($mixed)) {
                    $paybillData['status'] = 'succ';
                }
	            $paybillRes = $this->modelPaybill->where('payment_id ='.$paymentId)->save($paybillData);
				if($orderType == 'activity'){
				    $conditionOrder = array('atid'=>array('in',$tids));
				    $aitemIds = $this->modelActivityOrder->where($conditionOrder)->getField('aitem_id',TRUE);	
					$itemInfo=$this->modelAItem->where(array('aitem_id'=>array('in',$aitemIds)))->getField('item_info',TRUE);
					$orderList=array();
					foreach($itemInfo as $key=>$value){
						$orderList=array_merge($orderList,json_decode($value,TRUE));
					}												
				}else if($orderType == 'gift'){
					$conditionOrder = array('tid'=>array('in',$tids));
					$orderList = $this->giftTrade->where($conditionOrder)->select();
				}else{
        			$conditionOrder = array('tid'=>array('in',$tids));
        			$orderList = $this->modelOrder->where($conditionOrder)->select();
				}

	            //更新订单主表
                foreach ($paymentBillInfo as $kpb => $valpb){
                    $trdeDate = array(
                        'transno'=>$transno,
                        'payed_fee'=>round($valpb['point']/100,2),
                        'payed_point'=>$valpb['point'],
                        'pay_time'=>time(),
                        'modified_time'=>time()
                    );
		            if($payType=='e-card'){
		            	//e卡支付
		            	$trdeDate['pay_type']='e-card';
		            }
		            $this->makeLog('pay','payType:'.$payType.',data:'.json_encode($trdeDate));	
                    if($orderType == 'gift'){
                    	//更新微送礼
                    	$this->giftTrade->where('tid ='.$valpb['tid'])->save($trdeDate);
                    }else if($orderType == 'activity'){
                      	$this->modelActivityTrade->where('atid ='.$valpb['tid'])->data($trdeDate)->save();
                		$this->modelActivityOrder->where('atid ='.$valpb['tid'])->save(array('status'=>'WAIT_SELLER_SEND_GOODS','pay_time'=>time(),'modified_time'=>time()));                 	
            		}else{
                        if (empty($mixed)) {
                            $trdeDate['trade_status'] = 'WAIT_SELLER_SEND_GOODS';
                            $trdeDate['status'] = 'WAIT_SELLER_SEND_GOODS';
                        }
            			$this->makeLog('pay','tid:'.$valpb['tid'].',data:'.json_encode($trdeDate));
                		$this->modelTrade->where('tid ='.$valpb['tid'])->data($trdeDate)->save();
                        if (empty($mixed)) {
                            $orderData['status'] = "WAIT_SELLER_SEND_GOODS";
                            $orderData['pay_time'] = time();
                        }
                        $orderData['modified_time'] = time();
                		$this->modelOrder->where('tid ='.$valpb['tid'])->save($orderData);
                    }
                } 

                //更新库存      
                if(!empty($orderList) && empty($mixed)){
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
                            $this->modelOrd->updateSupplierSkuStock('item_id ='.$value['sku_id'],$value['num']);
						}catch (\Exception $e){
							$this->makeLog('pay',$e->getMessage());
						}						
	                }
                }
                if (empty($mixed)) {
                    //供应商订单支付确认
                    $returnSupplier=A('Supplier')->payConfirm($paymentId);
                    if ($returnSupplier[0] != 1) {
                        $this->makeLog('pay','error:1010,错误信息：供应商订单确认支付失败 paymentId={$paymentId}');            
                    }
                    //拆分订单至快递订单表中
                    $resCourier = $this->courierTrade($paymentId);
                    if ($resCourier[0] != 1) {
                        $this->makeLog('pay','error:1011,错误信息：拆分订单至快递订单表失败paymentId={$paymentId}');
                    }
    	        }    
	            //日志表
	            $logData =array('type'=>'expense','user_id'=>$payment['user_id'],'operator'=>$userAccountInfo['mobile'],'fee'=>$payment['point_fee']/100,'message'=>'订单消费，支付单号：'.$paymentId,'logtime'=>time());
	            $this->modelDepositLog->add($logData);
	            $this->makeLog('pay','支付成功，支付信息：'.json_encode($paymentData));
	            $this->retSuccess(array('paymentId'=>$paymentId,'transno'=>$transno),'succuss');
            }else{
				$this->makeLog('pay','error:1012,错误信息：'.$retMsg);
				$this->retError(1010,'支付失败，错误信息：'.$retMsg);
			}
		}else{
			if(empty($retMsg)){
				$retMsg='网络繁忙，请稍后再试';
			}
			$this->makeLog('pay','error:1013,错误信息：'.$retMsg);
			$this->retError(1000,'支付失败，错误信息：'.$retMsg);
		}
	}	
	
	//同步订单到第三方接口 赵尊杰 2016-09-09 目前只有同步到京东
	public function apiSyncOrder(){
		$paymentId = I('post.paymentId');
		$tid = I('post.tid');//单个订单操作时该数据必须
		$opType = I('post.opType');//操作，留空是creat，creat：下单，pay：支付(确认)，cancel：取消
		if(empty($opType)){
			$opType='creat';
		}
		if(!empty($paymentId)){
			$paybill=M('ectools_trade_paybill')->field('tid')->where('payment_id='.$paymentId)->select();
			if(!empty($paybill)){
				if(!empty($tid)){
					$tidArr=json_decode($tid,TRUE);
				}
				if(empty($tidArr)){
					foreach($paybill as $key=>$value){
						$tidArr[]=$value['tid'];
					}
				}
								
				$condition=array(
					'tid'=>array('in',implode(',',$tidArr))
				);
				$trade=$this->modelTrade->field('tid,shop_id')->where($condition)->select();
				$total=0;
				$failed=0;
				$success=0;
				$data=array();
				$failedTid=array();
				foreach($trade as $key=>$value){
					//同步京东订单
					if($value['shop_id']==C('JD_SHOP_ID')){
						if($opType=='creat'){
							$sync=A('Jd')->syncJdReserved($value['tid'],$paymentId);
						}elseif($opType=='pay'){
							$sync=A('Jd')->syncJdConfirm($value['tid']);
						}elseif($opType=='cancel'){
							$sync=A('Jd')->syncJdCancel($value['tid']);							
						}
						if($sync['code']>0){
							$success++;	
							$succTid[]=$value['tid'];
						}else{
							$failed++;
							$failedTid[]=$value['tid'];
						}
						$total++;
						$data[$value['tid']]=array($sync);
					}
					//同步齐采网订单
					if($value['shop_id']==C('QC_SHOP_ID')){
						if($sync['code']>0){
							$success++;
							$succTid[]=$value['tid'];	
						}else{
							$failed++;
							$failedTid[]=$value['tid'];
						}
						//待开发
						$data[$value['tid']]=array($sync);
						$total++;
					}
				}
				$retData=array(
					'total'=>$total,
					'successNum'=>$success,
					'failedNum'=>$failed,
					'detail'=>json_encode($data)
				);
				if($total==0){
					$this->retError(1001,'没有第三方订单');
				}
				if($total>0 and $total==$success){
					$this->retSuccess($retData,'同步订单:'.$total.',成功:'.$success.',失败:'.$failed);
				}
				if($total>0 and $total>$success){
					$this->retError(1002,'同步订单:'.$total.',成功:'.$success.',失败:'.$failed.',失败订单号:'.implode(',',$failedTid));
				}		
			}
		}else{
			$this->retError(1000,'同步订单失败，错误信息：没有支付单号');
		}
	}
	
	//活动订单同步到京东或顺丰
	public function syncActivityOrder(){
		$tid = I('post.tid');//单个订单操作时该数据必须
		$this->makeLog('syncActOrder',"mark ".REQUEST_METHOD." tid={$tid}");
		if(empty($tid) || !is_numeric($tid)){
			//tid错误
			$this->makeLog('syncActOrder', "error(1001) msg:tid错误 tid={$tid}");
			$this->retError(1001,"tid错误(1001) tid={$tid}");
		}
		//检索条件
		$where = array();
		$where['tid'] = $tid;
		//检索支付单
		$payment = $this->modelPaybill->field('payment_id,status')->where($where)->find();
		if(empty($payment)){
			//不存在支付信息
			$this->makeLog('syncActOrder', "error(1002) msg:未找到支付单 tid={$tid}");
			$this->retError(1002,'未找到支付单');
		}
		if('succ' != $payment['status']){
			//未成功支付
			$this->makeLog('syncActOrder', "error(1003) msg:订单未支付 tid={$tid} status={$payment['status']}");
			$this->retError(1003,'订单未支付 ');
		}
		$paymentId = $payment['payment_id'];
		//检索订单表
		
		$where = array();
		$where['atid'] = $tid;
		$field = 'shop_id, send_type';
		$orderCount = $this->modelActivityOrder->where($where)->count('order_id');
		if($orderCount == 1){
			//单品
			$orderList = $this->modelActivityOrder->field($field)->where($where)->select();
		}else if($orderCount > 1){
			//组合商品
			$where['splitOrder_id'] = array('gt', 0);
			$orderList = $this->modelActivityOrder->field($field)->where($where)->select();
		}else{
			//数据为空
			$this->makeLog('syncActOrder', "error(1004) msg:未查找到此订单 tid={$tid}");
			$this->retError(1004,"未查找到此订单 tid={$tid}");
		}
		if(empty($orderList)){
			//数据为空
			$this->makeLog('syncActOrder', "error(1004) msg:未查找到此订单 tid={$tid}");
			$this->retError(1004,"未查找到此订单 tid={$tid}");
		}
		//判断京东，顺丰，齐采
		$jsShopId = C('JD_SHOP_ID');
		$isJdOrder = 0;
		$isSfOrder = 0;
		foreach ($orderList as $order){
			if($jsShopId == $order['shop_id']){
				$isJdOrder = 1;
			}else{
				if($order['send_type'] == 3){ //这个send_type=3应该写入配置文件
					$isSfOrder = 1;
				}
			}
		}

		//是否有需要同步的数据
		if($isJdOrder == 0 && $isSfOrder == 0){
			$this->makeLog('syncActOrder', "error(1005) msg:没有任何同步的数据 tid={$tid}");
			$this->retError(1005, "没有任何同步的数据 tid={$tid}");
		}
		$jdSyncErr = 0;
		$jdSyncErrMsg = '';
		$sfSyncErr = 0;
		$sfSyncErrMsg = '';
		
		if(1 === $isJdOrder){
			//有京东
			$result = A('Jd')->syncActivityJd($paymentId, $tid);
			if($result['code'] != 100){
				$this->makeLog('syncActOrder', "error(1007) msg:同步京东订单失败 tid={$tid} result=".json_encode($result));
				$jdSyncErr = 1;
				$jdSyncErrMsg = $result['msg'];
				//$this->retError(1007, $result['msg']);
			}else{
				$this->makeLog('syncActOrder', "success(jd) msg:同步京东订单成功 tid={$tid} result=".json_encode($result));
			}
		}
		
		if(1 === $isSfOrder){
			//有顺丰
			$result = A('Sf')->activityOrderPostSf($paymentId, $tid);
			if($result['code'] != 100){
				$this->makeLog('syncActOrder', "error(1008) msg:同步顺丰订单失败 tid={$tid} result=".json_encode($result));
				$sfSyncErr = 1;
				$sfSyncErrMsg = $result['msg'];
				//$this->retError(1008, $result['msg']);
			}else{
				$this->makeLog('syncActOrder', "success(sf) msg:同步顺丰订单成功 tid={$tid} result=".json_encode($result));
			}
		}
		
		if($jdSyncErr === 0 && $sfSyncErr === 0){
			$this->retSuccess(array(),'已全部成功同步');
		}else{
			$msg = '';
			if($jdSyncErr == 1){
				if($jdSyncErr == 1){
					$msg .='【京东失败】'.$jdSyncErrMsg;
				}else{
					$msg .='【京东成功】同步成功';
				}
			}
			if($isSfOrder){
				if($sfSyncErr == 1){
					$msg .= '【顺丰失败】'.$sfSyncErrMsg;
				}else{
					$msg .= '【顺丰成功】同步成功';
				}
			}
			
			$this->retError(1007, $msg);
		}
		
	}
	
	//订单确认退款接口，用于财务退款给用户，非申请退款 赵尊杰 2016-09-24
	public function apiDoRefundOrder(){ 
		$orderType = I('post.orderType');//退款订单类别
		$paymentId = I('post.paymentId');//支付单号
		$tid = I('post.tid');//退款的商城订单
		$refundType = I('post.refundType');//退款方式
		$refundFee = I('post.fee');//退款金额
		$items = I('post.items');//退款的商城订单商品及数量
		$items=str_replace('&quot;','"',$items);
		$mark = I('post.mark');//退款备注
		$postData=json_encode($_POST); 
		$this->makeLog('refund','post:'.$postData);
		if(empty($orderType)){
			$orderType='shop';
		}
		if(empty($paymentId) || empty($refundFee) || empty($tid) || empty($items)){
			$this->makeLog('refund','error:1001,错误信息：支付单号或退款金额为空');
			$this->retError(1001,'退款失败，错误信息：支付单号或退款金额为空');
		}
		$orderPay=$this->modelPayments->field('payment_id,user_id,money,cur_money,user_name,status,pay_account,ls_trade_no')->where('payment_id='.$paymentId)->find();
		if(empty($orderPay['payment_id'])){
			$this->makeLog('refund','error:1002,错误信息：未查到该订单');
			$this->retError(1002,'退款失败，错误信息：未查到该订单');
		}
		if($orderType=='shop'){
			$trade=$this->modelTrade->field('payed_fee,refund_fee')->where('tid='.$tid)->find();
		}elseif($orderType=='activity'){
			$trade=$this->modelActivityTrade->field('payed_fee,refund_fee')->where('atid='.$tid)->find();
		}
		
		if($trade['payed_fee']<$refundFee){
			$this->makeLog('refund','error:1003,错误信息：退款金额大于订单金额');
			$this->retError(1003,'退款失败，错误信息：退款金额大于订单金额');
		}
		
		$condition=array(
			'payment_id'=>$paymentId,
    		'tid'=>$tid,
    		'items'=>$items,
    		'refund_fee'=>$refundFee
		);
		
		$refundCheck=$this->modelTradeRefund->field('refund_sn')->where($condition)->find();
		if(!empty($refundCheck['refund_sn'])){
			$this->makeLog('refund','error:1004,错误信息：重复退款');
			$this->retError(1004,'退款失败，错误信息：重复退款');
		}
		$refundSn=date('ymdHis').rand(100000,999999);
		$userAccount=$this->modelAccount->field('mobile')->where('user_id='.$orderPay['user_id'])->find();
		//$empCode=$orderPay['pay_account'];
		if($refundType=='ecard'){		
			//if(empty($empCode)){
				$user=A('Ecard')->getEcardUser($orderPay['user_name']);
				$empCode=$user['data']['empCode'];//员工编号
				if(empty($empCode)){
					$this->makeLog('refund','error:1005,错误信息：没有员工编号');
					$this->retError(1005,'退款失败，错误信息：没有员工编号');
				}
				$this->modelPayments->where('payment_id='.$paymentId)->save(array('pay_account'=>$empCode));
			//}
			$this->modelPayments->where('payment_id='.$paymentId)->save(array('pay_account'=>$empCode));			
			$return=A('Ecard')->ecardRefund($paymentId,$refundFee,$refundSn,$empCode,$userAccount['mobile']);
			$ret=json_decode($return,true);
			$retCode=$ret['code'];
			$errCode=$ret['errCode'];
		}elseif($refundType=='point'){			
			if(empty($orderPay['ls_trade_no'])){
				$this->makeLog('refund','error:1006,错误信息：找不到积分支付单号');
				$this->retError(1006,'退款失败，错误信息：找不到积分支付单号');
			}else{
				$return=A('Point')->pointRefund($paymentId,$refundFee,$orderPay['ls_trade_no']);
				$ret=json_decode($return,true);
				$retCode=$ret['result'];
				$errCode=$ret['errcode'];
				$empCode=0;
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
    		'modified_time'=>time()
    	);
    	if($retCode==100){
    		if($errCode==0){		
				$data['memo']='退款成功,post:'.$postData.' return:'.$return;
				$refundId=$this->modelTradeRefund->add($data);
				$data['refund_id']=$refundId;
				$this->modelTradeRefundLog->add($data);			
				$order=json_decode($items,TRUE);
				foreach($order as $key=>$value){
					$oid[]=$value['oid'];
				}				
				$orderData=array(
					'refund_id'=>$refundId,
					'aftersales_status'=>'SUCCESS',
					'aftersales_num'=>$value['num']
				);
				
				if($orderType=='shop'){
					$condition=array(
						'oid'=>array('in',implode(',',$oid))
					);
					$this->modelOrder->where($condition)->save($orderData);
				}elseif($orderType=='activity'){
					$condition=array(
						'order_id'=>array('in',implode(',',$oid))
					);
					$this->modelActivityOrder->where($condition)->save($orderData);
				}
					
				$tradeData=array(
					'refund_id'=>$refundId,
					'order_status'=>'REFUND',
					'refund_mark'=>$mark,
					'refund_time'=>time()
				);
				
				if($orderType=='shop'){
					$reTid=$this->modelTrade->where('tid='.$tid)->save($tradeData);
				}elseif($orderType=='activity'){
					$this->modelActivityTrade->where('atid='.$tid)->save($tradeData);
				}	
				$this->retSuccess(array('refundId'=>$refundId,'refundSn'=>$refundSn,'refundFee'=>$refundFee),'退款成功');
			}else{
				$data['memo']='退款失败,post:'.$postData.' return:'.$return;
				$data['refund_id']=0;
				$this->makeLog('refund','error:1008,data:'.json_encode($data));
				$this->modelTradeRefundLog->add($data);
				$this->retError(1008,'退款失败，错误信息：'.$ret['msg']);
			}
		}else{
			$data['memo']='退款失败,post:'.$postData.' return:'.$return;
			$data['refund_id']=0;
			$this->makeLog('refund','error:1000,data:'.json_encode($data));
			$this->modelTradeRefundLog->add($data);
			if(empty($ret['msg'])){
				$ret['msg']='接口通讯失败';
			}
			$this->retError(1000,'退款失败，错误信息：'.$ret['msg']);
		}
	}
	
	
	//查询积分充值赠送配置 赵尊杰 2016-10-15
	public function apiGetRechargeRule(){
		$terminalType=$totalFee=I('post.type');//终端类型(PC, WAP, APP )
        $return=A('Point')->getRechargeRule($terminalType);
        $ret=json_decode($return,true);
        if($ret['result']==100){
			if($ret['errcode']==0){
				$this->retSuccess(array('refundId'=>$refundId,'refundSn'=>$refundSn,'refundFee'=>$refundFee),'查询成功');
			}else{
				$this->retError(1001,'查询失败，错误信息：'.$ret['msg']);
			}
		}
        $this->retError(1000,'查询失败，错误信息：接口通讯失败');
	}
	
	//积分充值创建充值单接口 赵尊杰 2016-10-15
	public function apiCreateRecharge(){
		$totalFee=I('post.totalFee');
		$userId=$this->uid;
		$mobile=I('post.mobile');
		$payType=I('post.payType');//第三方支付平台标识支付宝(alipay)、微信(wxpay)等
		$payName=I('post.payName');//第三方支付平台名称支付宝、微信等
		$payFrom=I('post.payFrom');
		
		if(empty($totalFee) || empty($userId) || empty($mobile) || empty($payType) || empty($payName)){
			$this->retError(1001,'创建失败，错误信息：必要参数为空');
		}
		
		$ip=I('post.ip');//客户端IP地址
		if(empty($ip)){
			$ip=getIp();
		}
		if(empty($payFrom)){
			$payFrom='pc';
		}		
		
		$paymentId = date(ymdHis).$userId.'1';//商城支付单号
		$paymentData = array(
        	'payment_id' =>$paymentId,	
            'money'=>$totalFee,
            'cur_money'=>0,
            'user_id'=>$userId,
            'user_name'=>$mobile,
            'pay_type'=>'recharge',
            'pay_app_id'=>$payType,
            'pay_name'=>$payName,
            'pay_from'=>$payFrom,
            'op_id'=>$userId,
            'op_name'=>$mobile,
            'memo'=>'积分充值['.$payName.'支付]',
            'ip'=>$ip,
            'created_time'=>time(),
            'modified_time'=>time()
        );
        $this->modelPayments->add($paymentData);
		$this->retSuccess(array('paymentId'=>$paymentId,'payFee'=>$totalFee),'创建成功');
	}
	
	//积分充值更新充值单接口 赵尊杰 2016-10-15
	public function apiDoRecharge(){
		$paymentId=I('post.paymentId');
		$paidFee=I('post.paidFee');//实际支付金额
		$payFrom=I('post.payFrom');
		$tradeNo=I('post.tradeNo');//第三方支付平台支付宝、微信等流水号
		
		if(empty($paymentId) || empty($paidFee) || empty($tradeNo) || empty($payFrom)){
			$this->retError(1001,'更新失败，错误信息：必要参数为空');
		}
		
		$checkPayment=$this->modelPayments->field('user_name,money,status,cur_money')->where('payment_id='.$paymentId)->find();
		if($checkPayment['status']=='succ' && $checkPayment['cur_money']>0){
			$this->retError(1002,'更新失败，错误信息：重复操作');
		}
		
		if($checkPayment['money']!=$paidFee){
			$this->retError(1003,'更新失败，错误信息：实际支付金额与订单金额不符');
		}
		
		$addPoint=$paidFee*100; //增加的积分
		$mobile=$checkPayment['user_name'];
		$paymentData = array(
            'cur_money'=>$paidFee,
            'status'=>'succ',
            'payed_time'=>time(),
            'pay_from'=>$payFrom,
            'trade_no'=>$tradeNo
        );
        $this->modelPayments->where('payment_id='.$paymentId)->save($paymentData);
        
        //调用一企一舍接口充积分                
        $return=A('Point')->pointRecharge($paymentId,$mobile,$addPoint,'new');
        $ret=json_decode($return,true);
        if($ret['result']==100){
			if($ret['errcode']==0){
				//执行数据积分表表的更新操作 
		    	$this->modelDeposit->where('user_id ='.$userId)->setInc('deposit',$paidFee);//用户的积分加3
		        $this->modelDeposit->where('user_id ='.$userId)->setInc('balance',$addPoint);
		        $this->modelDeposit->where('user_id ='.$userId)->setInc('commonAmount',$addPoint);

				//写入充值记录
				$message = "积分充值,支付单号：".$tradeNo;
				$log = array(
		             'type'=>'add',
		             'user_id'=>$userId,
		             'operator'=>$mobile,
		             'fee'=>$paidFee,
		             'message'=>$message,
		             'logtime'=>time()
				);
		        $this->modelDepositLog->add($log);
				$info=$ret['data']['info'];
				$data=array(
					'ls_trade_no'=>$info['transno']
				);
				$this->modelPayments->where('payment_id='.$paymentId)->save($data);
				$this->retSuccess(array('paymentId'=>$paymentId,'rechargeNo'=>$info['transno'],'addPoint'=>$info['amount']),'充值成功');
			}else{
				$this->makeLog('recharge','error:1004,data:'.json_encode($paymentData));
				$this->retError(1001,'充值失败，错误信息：'.$ret['msg']);
			}
		}
		$this->makeLog('recharge','error:1000,data:'.json_encode($paymentData));
        $this->retError(1000,'查询失败，错误信息：接口通讯失败');
	}
	
		//返积分接口 赵尊杰 2016-10-20
	public function apiDoReturn(){
		$paymentId = I('get.paymentId');
		if(!empty($paymentId)){
			$condition=array(
				'payment_id'=>$paymentId,
				'sys_check'=>array('gt',0)
			);
			$tradeReturn=$this->modelReturn->where($condition)->order('return_id ASC')->find();
			if(empty($tradeReturn['return_id'])){
				$this->retError(1001,'返积分失败，错误信息：找不到订单！');
			}
			if($tradeReturn['return_status']=='TRADE_FINISHED'){
				$this->retError(1002,'返积分失败，错误信息：此订单已返还积分！');
			}
			if(empty($tradeReturn['user_id'])){
				$this->retError(1003,'返积分失败，错误信息：找不到用户信息！');
			}
			if(empty($tradeReturn['tids'])){
				$this->retError(1004,'返积分失败，错误信息：购物订单单号为空！');
			}
			if(empty($tradeReturn['return_fee'])){
				$this->retError(1005,'返积分失败，错误信息：返现金额为0！');
			}
			$checkCondition=array(
				'return_id'=>array('neq',$tradeReturn['return_id']),
				'tids'=>$tradeReturn['tids']
			);
			$checkData=array(
				'sys_check'=>'-1',
				'return_memo'=>'已返'.$tradeReturn['return_id'],'作废'
			);
			$this->modelReturn->where($checkCondition)->save($checkData);
			
			$trade=$this->modelTrade->field('tid,return_status')->where('tid IN ('.$tradeReturn['tids'].')')->select();
			if(empty($trade)){
				$this->retError(1006,'返积分失败，错误信息：找不到购物订单！');
			}
			foreach($trade as $key=>$value){
				if($value['return_status']=='TRADE_FINISHED'){
					$this->retError(1007,'返积分失败，错误信息：'.$paymentId.'存在已返现的订单！');
				}
			}
			
			$user=$this->modelAccount->field('mobile')->where('user_id='.$tradeReturn['user_id'])->find();
			$addPoint=$tradeReturn['return_fee']*100;
			if($addPoint==0){
				$this->retError(1008,'返积分失败，错误信息：返现金额为0！');
			}
					
			$return=A('Point')->pointRecharge($paymentId,$user['mobile'],$addPoint,'new');
			$ret=json_decode($return,true);
			if($ret['result']==100){
				if($ret['errcode']==0){
					$this->modelReturn->where($condition)->setInc('returned_fee',$tradeReturn['return_fee']);
					$info=$ret['data']['info'];
					$returnData=array(
						'ls_trade_no'=>$info['transno'],
						'return_status'=>'TRADE_FINISHED',
						'modifyine_time'=>time(),
						'return_time'=>time(),
						'return_memo'=>$return
					);
					$this->modelReturn->where($condition)->save($returnData);
					$tradeData=array(
						'return_status'=>'TRADE_FINISHED'
					);
					$this->modelTrade->where('tid IN ('.$tradeReturn['tids'].')')->save($tradeData);
					$smsContent='尊敬的用户：您的'.$addPoint.'福利积分已返还成功，请留意积分到账情况，如有疑问请联系客服！';
					$smsReturn=A('Sms')->send($user['mobile'],$smsContent);	
					$this->makeLog('return','smsContent:'.$smsContent.' smsReturn:'.json_encode($smsReturn));
					$this->retSuccess(array('paymentId'=>$paymentId,'rechargeNo'=>$info['transno'],'addPoint'=>$info['amount']),'返积分成功');
				}else{
					$this->makeLog('return','error:1009,data:'.json_encode($returnData));
					$this->retError(1009,'返积分失败，错误信息：'.$ret['msg']);
				}
			}else{
				$this->makeLog('return','error:1009,return:'.$return);
				$this->retError(1010,'返积分失败，错误信息：接口通讯失败');
			}			
		}else{
			$this->retError(1000,'返积分失败，错误信息：没有订单号');
		}		
	}
	
	/**
	 * 东莞移动用户，邮费计算
	 * 计算规则，东莞移动用户订单总额满99，广东省内包邮，省外按新标准计算
	 * @param array $shopList 商品列表
	 * @param number $shopTotalPrice 订单总价
	 * @param number $totalCartDelivery 邮费
	 * @param array $userAdrr 用户地址，下标，0为省级 1，市级
	 * @param array $totalCartWeight 商品总重量
	 */
	private function checkDgydPostFees(&$shopList,  $shopTotalPrice, $totalCartDelivery, $userAdrr, $totalCartWeight){
		$orderTotalPrice = $shopTotalPrice - $totalCartDelivery;
		//东莞移动用户计算方式
		//用户地址
		$addrProvince = $userAdrr[0];//省份
		$addrCity = $userAdrr[1];//市
		$gdAreaId = 19;//广东地区的jd_id
		
		if($addrProvince == $gdAreaId && $orderTotalPrice < 99){
			return;
		}
		
		//所有店铺邮费重置为0，并且标记订单总额最小的一个店铺
		$minPrice = 0;//所有订单中最小金额
		$minPriceShopId = 0;//订单中最小金额店铺id
		foreach ($shopList as &$order){
			if($order['totalPrice'] < $minPrice || $minPrice === 0){
				$minPrice = $order['totalPrice'];
				$minPriceShopId = $order['shop_id'];
			}
			$order['delivery'] = 0;
		}
		
		if($addrProvince == $gdAreaId){
			//如果是广东省内，满99包邮，不满99,还是还原来一样，不变
			//if($orderTotalPrice >= 99){
			//	$shopTotalPrice = $orderTotalPrice;
			//}
			return;
		}else{
			
			//所有店铺邮费重置为0，并且标记订单总额最小的一个店铺
// 			$minPrice = 0;//所有订单中最小金额
// 			$minPriceShopId = 0;//订单中最小金额店铺id
// 			foreach ($shopList as &$order){
// 				if($order['totalPrice'] < $minPrice || $minPrice === 0){
// 					$minPrice = $order['totalPrice'];
// 					$minPriceShopId = $order['shop_id'];
// 				}
// 				$order['delivery'] = 0;
// 			}
			
			//省外计算，重新邮费
			$totalPostage = 0;
			$where['jd_id'] = array('in',$userAdrr);
			$dgydPostageList = $this->syslogisticsDgyd
									->where($where)
									->field('jd_id, start_standard, start_fee, add_fee, unit_price')
									->select();
			$postageArr = array();
			foreach ($dgydPostageList as $postage){
				$postageArr[$postage['jd_id']] = $postage;
			}
				
			$postage = array();
			//优先计算市级费用，因为同一个省的某些城市邮费不一样，顺丰只细化到市级
			if(!empty($postageArr[$addrCity])){
				$postage = $postageArr[$addrCity];
			}else if(!empty($postageArr[$addrProvince])){
				$postage = $postageArr[$addrProvince];
			}
				
			if(empty($postage)){
				return false;
			}
			//计算邮费
			if($totalCartWeight <= 30 || $postage['unit_price'] == 0){
				//如果重量超过30KG，或者没有‘隔日送’服务（没有隔日送即unit_price=0），按次日计算
				//顺丰的隔日和次日，首重费用和续费一样，如果不一样，请更改下面计算方式
				$totalPostage += ($postage['start_fee'] + (ceil($totalCartWeight - 1) * $postage['add_fee']));
			}else{
				//
				$totalPostage += (ceil($totalCartWeight) * $postage['unit_price']);
			}
				
			//重新设置邮费，将最小订单金额的店铺邮费，修改为新的邮费
			foreach ($shopList as &$order){
				if($minPriceShopId == $order['shop_id']){
					$order['delivery'] = $totalPostage;
				}
			}
		}
	}

    //更新订单状态方法
    public function updateTrade(){
        header("Content-type: text/html; charset=utf-8"); 
        $paymentId = I('get.paymentId');
        if (empty($paymentId)) {
            echo "缺少支付单号！";
            exit();
        }
        $paymentInfo = $this->modelPayments->where('payment_id='.$paymentId)->find();
        if (empty($paymentInfo)) {
            echo "未查询到该支付单";
            exit();
        }
        if ($paymentInfo['status'] != 'succ' || empty($paymentInfo['payed_time']) || empty($paymentInfo['cur_money'])) {
            echo "该订单未支付！";
            exit();
        }
        //支付单子表
        $paymentbillList = $this->modelPaybill->where('payment_id='.$paymentInfo['payment_id'])->select();
        foreach ($paymentbillList as $key => $value) {
            $tidArr[] = $value['tid'];
        }        
        //查询订单
        $tradeList = $this->modelTrade->where(array('tid'=>array('in',implode(',',$tidArr))))->select();
        $jDshopId = 0;
        $pTshopId = 0;
        foreach ($tradeList as $key => $value) {
            if ($value['shop_id'] == C('JD_SHOP_ID')) {
                $jDshopId = 1;
            }else{
                $pTshopId = 1;
            }
        }
        //自营订单
        if ($pTshopId == 1) {
            //修改自营订单主表
            $condZy=array(
                'tid'=>array('in',implode(',',$tidArr)),
                'shop_id'=>array('neq',C('JD_SHOP_ID'))
            );
            $dataTradZy = array(
                'status' =>'WAIT_SELLER_SEND_GOODS',
                'trade_status'=>'WAIT_SELLER_SEND_GOODS',
                'payed_fee'=>array('exp','payment'),
                'pay_time'=>$paymentInfo['payed_time'],
                'modified_time'=>$paymentInfo['payed_time']
            );
            $resTradeZy = $this->modelTrade->where($condZy)->data($dataTradZy)->save(); 
            //修改自营订单子表
            $dataOrderZy = array(
                'status' =>'WAIT_SELLER_SEND_GOODS',
                'pay_time'=>$paymentInfo['payed_time'],
                'modified_time'=>$paymentInfo['payed_time']
            );
            $resOrderZy = $this->modelOrder->where($condZy)->data($dataOrderZy)->save(); 
            if ($resTradeZy && $resOrderZy) {
                echo "自营订单更新成功！";
            }else{
                echo "自营订单更新失败！";
            }
        }
        //京东订单
        if ($jDshopId == 1) {
            //修改京东订单主表
            $condJd=array(
                'tid'=>array('in',implode(',',$tidArr)),
                'shop_id'=>array('eq',C('JD_SHOP_ID'))
            );
            $dataTradJd = array(
                'status' =>'IN_STOCK',
                'trade_status'=>'IN_STOCK',
                'payed_fee'=>array('exp','payment'),
                'pay_time'=>$paymentInfo['payed_time'],
                'modified_time'=>$paymentInfo['payed_time']
            );
            $resTradeJd = $this->modelTrade->where($condJd)->data($dataTradJd)->save(); 
            echo $this->modelTrade->_sql();
            //修改京东订单子表
            $dataOrderJd = array(
                'status' =>'IN_STOCK',
                'pay_time'=>$paymentInfo['payed_time'],
                'modified_time'=>$paymentInfo['payed_time']
            );
            $resOrderJd = $this->modelOrder->where($condJd)->data($dataOrderJd)->save();
            echo $this->modelOrder->_sql();
            if ($resTradeJd && $resOrderJd) {
                echo "京东订单更新成功！";
            }else{
                echo "京东订单更新失败！";
            }
        }         
    }


    //根据支付id拆分订单至快递订单表
    public function courierTrade($paymentId){
        if (empty($paymentId)) {
            $this->makeLog('courierTrade',"error(1001) msg:没有支付单号 paymentId={$paymentId}");
            return array(0,'1001:订单拆分失败，错误信息：没有支付单号');
        }
        //根据支付id查询对应的支付子表数据
        $condition = array('payment_id'=>$paymentId);
        $field = array('tid,status,payment,user_id,payed_time');
        $paymentList = $this->modelOrd->getPaybillList($condition,$field);
        if (!$paymentList) {            
            $this->makeLog('courierTrade',"error(1002) msg:未查询到支付子表数据 paymentId={$paymentId}");
            return array(0,'1002:订单拆分失败，错误信息：未查询到支付子表数据');
        }
        //获取tid
        $tidarry =array();
        foreach ($paymentList as $key => $value) {
            $tidarry[$key] = $value['tid'];
        }
        //根据tid查询订单表数据
        unset($condition);
        unset($field);
        $condition = array('tid'=>array('in',implode(',',$tidarry)));
        $field = array('tid,user_id,com_id,payed_fee,receiver_name,created_time,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,receiver_mobile');
        $tradeInfo = $this->modelOrd->getTradeInfo($condition,$field);
        //验证是否存在订单
        if (!tradeInfo) {
            $this->makeLog('courierTrade',"error(1003) msg:未查询到订单 paymentId={$paymentId}");
            return array(0,'1003:订单拆分失败，错误信息：未查询到订单');          
        }
        //根据tid查询订单子表数据
        unset($condition);
        unset($field);
        $condition = array('tid'=>array('in',implode(',',$tidarry)),'send_type'=>array('in','1,3'));
        $field = array('oid,tid,supplier_id,total_fee,send_type');
        $tradeOrderList = $this->modelOrd->getTradeOrderList($condition,$field);
        //验证是否存在子订单
        if (!tradeOrderList) {
            $this->makeLog('courierTrade',"error(1004) msg:未查询到子订单 paymentId={$paymentId}");  
            return array(0,'1004:订单拆分失败，错误信息：未查询到子订单');     
        }
        //根据供应商id进行分组
        $result = array();
        foreach ($tradeOrderList as $key => $value) {
            $result[$value['send_type']]['send_type'] = $value['send_type'];
            $result[$value['send_type']][] = $value;
        }
        //这里把key转成了数字的，方便统一处理
        $ret = array();
        foreach ($result as $key => $value) {
            array_push($ret, $value);
        }
        $tradeOrderListArr = array();
        foreach ($ret as $key => $value) {
            foreach ($value as $k => $val) {
                $tradeOrderListArr[$key]['tradeInfo'] = $tradeInfo;
                $tradeOrderListArr[$key]['payed_fee'] += $val['total_fee'];
                $tradeOrderListArr[$key]['send_type'] =$val['send_type']; 
                $tradeOrderListArr[$key]['orderList'][$k] =$val;
                unset($tradeOrderListArr[$key]['orderList']['send_type']); 
            }           
        }
        //插入数据到供应商订单表
        $data = array();
        //开启事物
        $this->model = new \Think\Model(); 
        $this->model->startTrans();
        foreach ($tradeOrderListArr as $key => $value) {
            $num++;
            $data['stid'] = date(ymdHis).$num.$tradeInfo['user_id'];
            $data['payment_id'] = $paymentId;
            $data['send_type'] = $value['send_type'];
            $data['user_id'] = $value['tradeInfo']['user_id'];
            $data['com_id'] = $value['tradeInfo']['com_id'];
            $data['payed_fee'] = $value['payed_fee'];
            $data['receiver_name'] = $value['tradeInfo']['receiver_name'];
            $data['created_time'] = time();
            $data['receiver_state'] = $value['tradeInfo']['receiver_state'];
            $data['receiver_city'] = $value['tradeInfo']['receiver_city'];
            $data['receiver_district'] = $value['tradeInfo']['receiver_district'];
            $data['receiver_address'] = $value['tradeInfo']['receiver_address'];
            $data['receiver_zip'] = $value['tradeInfo']['receiver_zip'];
            $data['receiver_mobile'] =$value['tradeInfo']['receiver_mobile'];
            try{
                $resTrade = $this->modelOrd->addCourierTrade($data);
                if (!$resTrade) {
                    $this->model->rollback();
                    $this->makeLog('courierTrade',"error(1005) msg:订单表插入失败 paymentId={$paymentId}");
                    return array(0,'1005:订单拆分失败，错误信息：订单插入失败');
                }
            }catch(\Exception $e){ 
                $this->model->rollback();
                $this->makeLog('courierTrade',"error(1006) msg:订单表插入异常 ".$e->getMessage());
                return array(0,'1006:订单拆分失败，错误信息：订单插入异常');
            }
            foreach ($value['orderList'] as $k => $val) {
                $numOrder++;
                $da['soid'] = date(ymdHis).$num.$numOrder.$tradeInfo['user_id'];
                $da['stid'] = $data['stid'];
                $da['tid'] = $val['tid'];
                $da['oid'] = $val['oid'];
                $da['created_time'] = time();
                try{
                    $resOrder = $this->modelOrd->addCourierOrder($da);
                    if (!$resOrder) {
                        $this->model->rollback();
                        $this->makeLog('courierTrade',"error(1007) msg:订单子表插入失败 paymentId={$paymentId}");
                        return array(0,'1007:订单拆分失败，错误信息：订单插入失败');                  
                    }
                }catch(\Exception $e){ 
                    $this->model->rollback();
                    $this->makeLog('courierTrade',"error(1008) msg:订单子表插入异常 ".$e->getMessage());
                    return array(0,'1008:订单拆分失败，错误信息：订单插入异常');  
                }
            }           
        }
        $this->model->commit();
        return array(1,'订单拆分成功');
    }

    //用于外部调用
    public function apiSupplier(){
        //供应商订单支付确认
        $paymentId = I('paymentId');
        $returnSupplier=A('Supplier')->payConfirm($paymentId);
        if ($returnSupplier[0] != 1) {
            $this->retError(1001,'供应商支付确认失败！');  
        }else{
            $this->retSuccess($returnSupplier);
        }
    }
    //用于外部调用
    public function apiCourier(){
        //拆分订单至快递订单表中
        $paymentId = I('paymentId');
        $resCourier = $this->courierTrade($paymentId);
        if ($resCourier[0] != 1) {
            $this->retError(1001,'拆分订单失败！');  
        }else{
            $this->retSuccess($resCourier);
        }
    }               
                    
}