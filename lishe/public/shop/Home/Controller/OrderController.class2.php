<?php
namespace Home\Controller;
class OrderController extends  CommonController {
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
		$this->postageModel=M('syspromotion_freepostage');//包邮表
		$this->companyItemPriceModel=M('company_item_price');//公司特价
		$this->modelOrder=D('Order');
		$this->syslogisticsDgyd = M('syslogistics_dgyd');
	}
	//日志记录
    public function orderLog($data){
        return M("systrade_log")->data($data)->add();
    } 
	//特价商品--zhangrui
	public function specialPriceItem($itemInfo){
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
							if(($value['sku_id']==$values['sku_id']) && (empty($values['com_id']) || $values['com_id']==$this->comId) && ($nowTime>=$values['start_time'] && $nowTime<=$values['end_time'])){
								$itemInfo[$key]['price']=$values['price'];
							}
						}
					}
				}
			}
			return $itemInfo;
		}
	} 
	//购物车页面 20160811 开始
	public function cart(){
		//获取购物车信息
		$conditionCart = " and c.user_id=".$this->uid;
		$cartList=$this->modelOrder->getCartList($conditionCart);
		if (!$cartList) {
			$this->display('cart');
			exit();			
		}
		foreach ($cartList as $kCart => $vCart) {
			//店铺ids
			$shopIds[$kCart] = $vCart['shop_id'];
			//商品ids
			$itemIds[$kCart] = $vCart['item_id'];
			//商品skus和itemids
			//$skuIds[$kCart] = $vCart['sku_id'];
			$skuidItemid[$kCart]['itemId'] = $vCart['item_id'];
			$skuidItemid[$kCart]['skuId'] = $vCart['sku_id'];
			$skuidItemid[$kCart]['num'] = $vCart['quantity'];
		}	
		$shopIdStr=implode(',',$shopIds);
		//查询店铺信息
		$where['shop_id']=array('in',$shopIdStr);
	    $shopInfo=$this->modelOrder->getShopList($where);
        //查询商品上下架状态
        $condStatus['item_id'] = array('in',$itemIds);
        $itemStatus = $this->modelOrder->getItemStatus($condStatus);
        //查询用户默认地址
        $userAddressInfo = $this->modelOrder->getUserAddress($this->uid);
        if (!$userAddressInfo) {
        	redirect('Order/chgAddressInfo', 5, '请完善您的地址信息，页面跳转中...');
        }
        $jd_ids = str_replace('/','_',trim(strstr($userAddressInfo['area'],':'),":"));
        //调用接口查询库存
        $skuidItemidJson = json_encode($skuidItemid);
        $data = array(
            'itemsSkus' => $skuidItemidJson,
            'area' => $jd_ids
        );
        $url=C('COMMON_API').'Cart/apiCheckCartStock';       
        $return=$this->requestPost($url,$data);
        $return = trim($return, "\xEF\xBB\xBF");//去除BOM头
        $res = json_decode($return,true); 
        if ($res['result'] != 100) {
            $logoData = array(
                'rel_id' =>'1',
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '查询库存接口失败！',
                'log_time' =>time()
            );            
            $this->orderLog($logoData);
            $this->error("接口通讯失败！");
        }
        //查询库存异常
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
		//检测特价商品start -zhangrui
		$cartList=$this->specialPriceItem($cartList);
		//end
        foreach($cartList as $keyCart=>$valCart){
	        $cartList[$keyCart]['price']=round($valCart['price'],2);
	        $cartList[$keyCart]['shop_price']=round($valCart['shop_price'],2);
	        $cartList[$keyCart]['goodsTotalPrice'] = round($valCart['price'],2) * $valCart['quantity'];
	        //检查商品是否已经下架
	        foreach ($itemStatus as $kStatus => $vStatus) {
	        	if ($vStatus['item_id'] == $valCart['item_id']) {
	        		$cartList[$keyCart]['itemStatus'] = $vStatus['approve_status'];
	        	}
	        }
	        foreach ($res['data'] as $kSku => $vSku) {
	        	if ($kSku == $valCart['sku_id']) {
	        		$cartList[$keyCart]['isFreez'] = $vSku;
	        	}
	        }
	        $totalPrice += $cartInfo[$keyCart]['goodsTotalPrice'];
        }
        $this->assign('totalPrice',$totalPrice); //总价格
        $this->assign('cartInfo',$cartList);  //购物车信息
        $this->assign('shopInfo',$shopInfo);
		$this->display('cart');
	}
	//购物车页面 20160811 结束

	//得到购物车中选中商品的总价 20160812 开始
	public function getSelectPrice(){
		$cartIdStr=I('get.cartIdStr','','trim');
		if($cartIdStr){
			$cartIdStr=rtrim($cartIdStr,','); //得到购物车id
			$cartIdStr = "(".$cartIdStr.")";
			$cartInfo = $this->cartModel->table('systrade_cart c,sysitem_sku s')->where('c.sku_id = s.sku_id and c.user_id ='.$this->uid.' and c.cart_id in'.$cartIdStr)->field('c.item_id,c.sku_id,c.quantity,s.price')->select();
			
			if($cartInfo){
				//检测特价商品-zhangrui start
				$cartInfo=$this->specialPriceItem($cartInfo);
				//end	          
				foreach($cartInfo as $k=>$v){
	            	$currSelectGoodsPrice += sprintf("%.2f",floatval($cartInfo[$k]['price'])) * intval($cartInfo[$k]['quantity']);
				}
				
				$currArr=array(
					'curr_num'=> $currSelectGoodsPrice*100,
					'curr_money'=> number_format($currSelectGoodsPrice,2,'.','')
					);
				echo json_encode($currArr);
				exit;
			}
		}else{
			echo '-1';
			exit;
		}
	}

	//得到购物车中选中商品的总价 20160812 结束

	//填写购物车商品数量 20160812 开始
	public function chgCartNum(){
		$currCartId=I('get.currCartId',0,'intval');
		$currCartQuantity=I('get.currCartQuantity','','trim');
		$currCartQuantity=intval($currCartQuantity);
		if($currCartQuantity > 0){
			$where=array(
				'cart_id'=>$currCartId,
				'user_id'=>$this->uid
				);
			$data=array(
				'quantity'=>$currCartQuantity
				);
			$chgCurrNum=$this->cartModel->where($where)->data($data)->save();
			if($chgCurrNum){
				echo $currCartQuantity;
				exit;
			}
		}else{
			echo '-1';
			exit;
		}
	}
	//填写购物车商品数量 20160812 结束

	 // 购物车中货品数量的增加/减少 start 20160812
    public function opCart(){
        $op = I('get.op','','trim');
        $gid = I('get.gid',0,'intval'); 
        $num = I('get.num',0,'intval'); //当前货品的购买数量
        $sku_id=I('get.sku_id',0,'intval');
        if (empty($gid) || empty($num)) {        	
        	echo  json_encode(array(0,'出错了，请刷新页面！'));
            exit;
        }
        $where=array(
          'cart_id'=>$gid,
          'user_id'=>$this->uid
        );
        $cInfo = $this->cartModel->where($where)->find();
        $addressInfo = $this->addrModel->where('user_id='.$this->uid.' and def_addr=1')->find();
        $jd_ids = str_replace('/','_',trim(strstr($addressInfo['area'],':'),":"));
        if($op=='dec'){    //表货品数目减少
            $cartNumDec=$this->cartModel->where($where)->setDec('quantity',1);    
        }elseif($op=='inc'){  //表货品数目增加        	
        	if ($cInfo['shop_id'] == C('JD_SHOP_ID')) {			
        		$addNum = $num+1;
            	$getUrl = C('COMMON_API')."Jd/checkJdStock/item_id/".$cInfo['item_id']."/jd_ids/".$jd_ids."/num/".$addNum;
	            $jdSku = file_get_contents($getUrl);
	            $jdSku = trim($jdSku, "\xEF\xBB\xBF");//去除BOM头 
	            if ($jdSku != 33) {
                	//库存不足
                	echo  json_encode(array(0,'库存不足！'));
	                exit;
	            }

        	}else{
        		$skuArr = $this->skuStoreModel->field('store,freez')->where('sku_id='.$sku_id)->find();
	            $noFreez = $skuArr['store']-$skuArr['freez'];
	            //注意判断的时候购买数量要加1
	            if($num + 1 > $noFreez || $noFreez < 1){
	                echo  json_encode(array(0,'库存不足！'));
	                exit;
	            } 
        	}
            
            $cartNumDec=$this->cartModel->where($where)->setInc('quantity',1); 
        }elseif($op=='both'){
        	$dataInfo=array(
        		'quantity'=>$num
        		);
        	$cartNumChg=$this->cartModel->where($where)->data($dataInfo)->save();
        }
        $cartInfo = $this->cartModel->table('systrade_cart c,sysitem_sku s')->where('c.sku_id = s.sku_id and c.user_id ='.$this->uid)->field('c.cart_id,c.shop_id,c.item_id,c.sku_id,c.title,c.image_default_id,c.quantity,s.price')->select();
        
        if($cartInfo){
			//检测特价商品start -zhangrui
			$cartInfo=$this->specialPriceItem($cartInfo);
			//end          
            foreach($cartInfo as $k=>$v){
                if($v['cart_id']==$gid){
                    $goodsTotalPrice =  sprintf("%.2f",sprintf("%.2f",floatval($cartInfo[$k]['price'])) * intval($v['quantity'])); //单个商品总价
                }
                if ($v['cart_id'] == $gid) {
                	if ($v['shop_id'] == C('JD_SHOP_ID')) {
                		$minusNum = $num-1;
		            	$getUrl = C('COMMON_API')."Jd/checkJdStock/item_id/".$v['item_id']."/jd_ids/".$jd_ids."/num/".$minusNum;
			            $jdSku = file_get_contents($getUrl);
			            $jdSku = trim($jdSku, "\xEF\xBB\xBF");//去除BOM头 
			            if ($jdSku != 33) {
		                	//库存不足
		                	$isFreez = 0;
			            }else{			            	
	                    	//库存充足
	                    	$isFreez = 1; 
			            }
		        	}else{
	                	$storeSkuInfo = $this->skuStoreModel->where('sku_id ='.$v['sku_id'])->find();
	                    $noFreez = $storeSkuInfo['store'] - $storeSkuInfo['freez'];
	                    if ($noFreez < $num - 1) {
	                    	//库存不足
	                    	$isFreez = 0;
	                    }else{
	                    	//库存充足
	                    	$isFreez = 1;                    	
	                    }
                	}
                }    
                $cartInfo[$k]['goodsTotalPrice'] = sprintf("%.2f",floatval($v['price']) * intval($v['quantity']));
                $totalPrice += $cartInfo[$k]['goodsTotalPrice']; //总价格
            }
            echo json_encode(array(1,$goodsTotalPrice,sprintf("%.2f",$totalPrice),$isFreez));
        }
    }
     // 购物车中货品数量的增加/减少 end 20160812

	//删除购物商品信息 20160812 开始
	public function deleteCartId(){
		$cartId=I('get.cartId',0,'intval');
		if($cartId != 0){
			$where=array(
				'cart_id'=>$cartId,
				'user_id'=>$this->uid
				);
			$delRes=$this->cartModel->where($where)->delete();
			if($delRes){
				echo '1'; //删除成功
				exit;
			}else{
				echo '-1'; //删除失败
				exit;
			}
		}
	}
	//删除购物商品信息 20160812 结束

	//批量删除购物车信息 开始
	public function deleteMoreCartIds(){
		$selectCartMoreStr=I('get.selectCartMoreStr','','trim');
		$selectCartMoreStr=rtrim($selectCartMoreStr,',');
		if($selectCartMoreStr){
			$whereDelInfo['user_id']=$this->uid;
			$selectCartMoreStr=explode(',',$selectCartMoreStr);
			$whereDelInfo['cart_id']=array('in',$selectCartMoreStr);
			$delMoreRes=$this->cartModel->where($whereDelInfo)->delete();
			if($delMoreRes){
				echo 'delMoreSucc';
				exit;
			}else{
				echo 'delMoreFail';
				exit;
			}
		}

	}

 	//批量删除购物车信息 结束

	//空购物车 20160811 开始
	public function cartEmpty(){
		$selectCartId=$this->cartModel->where('user_id='.$this->uid)->field('cart_id')->select();
		if($selectCartId){
			 $url = __APP__.'/Order/cart';
             header("location:$url");
             exit;
		}

		$this->display('cart');
	}
	//空购物车 20160811 开始

	//提交订单页面 20160811 开始
	public function order(){
		$cartIds = rtrim(I('get.itemList','','trim'),',');
		if (!$cartIds) {
			$this->error('请选择需要购买的商品！');
		}		
		//获取购物车勾选商品的信息
		$conditionCart = " and c.user_id=".$this->uid." and c.cart_id in(".$cartIds.")";
		$cartList=$this->modelOrder->getCartList($conditionCart);
		if (!$cartList) {
			$this->error('请选择需要购买的商品！');
		}
		foreach ($cartList as $kCart => $vCart) {
			//店铺ids
			$shopIds[$kCart] = $vCart['shop_id'];
			//商品ids
			$itemIds[$kCart] = $vCart['item_id'];
			//商品skus和itemids
			$skuIds[$kCart] = $vCart['sku_id'];
			$skuidItemid[$kCart]['itemId'] = $vCart['item_id'];
			$skuidItemid[$kCart]['skuId'] = $vCart['sku_id'];
			$skuidItemid[$kCart]['num'] = $vCart['quantity'];
		}

		$shopIdStr=implode(',',$shopIds);
		//查询店铺信息
		$where['shop_id']=array('in',$shopIdStr);
	    $shopList=$this->modelOrder->getShopList($where);
	    if (!$shopList) {
	    	$this->error('店铺不存在，请重新选择商品！');
	    }
	    //查询用户默认地址
        $userAddressInfo = $this->modelOrder->getUserAddress($this->uid);
        if (!userAddressInfo) {
        	redirect('Order/chgAddressInfo', 5, '请完善您的地址信息，页面跳转中...');
        }
        $jd_ids = str_replace('/','_',trim(strstr($userAddressInfo['area'],':'),":"));
        //调用接口查询库存
        $skuidItemidJson = json_encode($skuidItemid);
        $data = array(
            'itemsSkus' => $skuidItemidJson,
            'area' => $jd_ids
        );
        $url=C('COMMON_API').'Cart/apiCheckCartStock';       
        $return=$this->requestPost($url,$data);
        $return = trim($return, "\xEF\xBB\xBF");//去除BOM头
        $res = json_decode($return,true); 
        if ($res['result'] != 100) {
            $logoData = array(
                'rel_id' =>'1',
                'op_name' =>"系统",
                'op_role' =>"system",
                'behavior' =>"cancel",
                'log_text' => '查询库存接口失败！',
                'log_time' =>time()
            );            
            $this->orderLog($logoData);
            $this->error("接口通讯失败！");
        }
        //查询库存异常
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
		//检测特价商品start -zhangrui
		$cartList=$this->specialPriceItem($cartList);
		//end
	    foreach ($cartList as $kCart => $vCart) {
	    	$cartList[$kCart]['price'] = round($vCart['price'],2);
	    	$cartList[$kCart]['shop_price'] = round($vCart['shop_price'],2);
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
	        foreach ($res['data'] as $kSku => $vSku) {
	        	if ($kSku == $vCart['sku_id']) {
	        		$cartList[$kCart]['store'] = $vSku;
	        	}
	        }

	    }
	    //店铺配送方式
	    $conditionDlytmpl['shop_id']=array('in',$shopIdStr);
	    $dlytmplList = $this->modelOrder->getDlytmpl($conditionDlytmpl);
	    $addrDefaultIdArr=explode('_',$jd_ids);
	    if (!$dlytmplList) {
	    	$this->error('店铺配送方式不存在，请重新选择商品！');
	    }	   
	    foreach ($dlytmplList as $kdp => $vtp) {
	    	$dlytmplList[$vtp['shop_id']] = $vtp;
	    	$shopDlytmpConf[$vtp['shop_id']] = unserialize($vtp['fee_conf']);
	    }

	    //包邮信息
		$totalCartDelivery="0.00";
		$reliefFee="0.00";//减免金额
		$totalCartWeight = 0;//所有商品总重量
		$conditionFreepost['shop_id'] = array('in',$shopIdStr);		
		$freePostList = $this->modelOrder->getFreePost($conditionFreepost);
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
						$this->makeLog('delivery','2: addrDefaultIdArr:'.$addrDefaultIdArr[0].' area:'.$val['area'].' shopId:'.$vshop['shop_id'].' start_fee'.$val['start_fee'].' totalWeight:'.ceil($vshop['totalWeight']).' start_standard:'.$val['start_standard'].' add_fee:'.$val['add_fee']."\r\n");
					}
					if(in_array($addrDefaultIdArr[1],$shopPressAreaArr)){ //市  $addrFeeCity
						$shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
						$this->makeLog('delivery','3: addrDefaultIdArr:'.$addrDefaultIdArr[1].' area:'.$val['area'].' shopId:'.$vshop['shop_id'].' start_fee'.$val['start_fee'].' totalWeight:'.ceil($vshop['totalWeight']).' start_standard:'.$val['start_standard'].' add_fee:'.$val['add_fee']."\r\n");
					}
					if(in_array($addrDefaultIdArr[2],$shopPressAreaArr)){ //区  $addrFeeArea
						$shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
						$this->makeLog('delivery',"4: addrDefaultIdArr:".$addrDefaultIdArr[2].' area:'.$val['area'].' shopId:'.$vshop['shop_id']." start_fee".$val['start_fee']." totalWeight:".ceil($vshop['totalWeight'])." start_standard:".$val['start_standard']." add_fee:".$val['add_fee']."\r\n");
					}	
				}else{
					if(empty($shopFeeAreaTotal)){
						$shopFeeAreaTotal = $val['start_fee'] + (ceil($vshop['totalWeight'])-$val['start_standard']) * $val['add_fee'];
	    				$this->makeLog('delivery','1: addrDefaultIdArr:'.$jd_ids.' area:'.$val['area'].' shopId:'.$vshop['shop_id'].' start_fee'.$val['start_fee'].' totalWeight:'.ceil($vshop['totalWeight']).' start_standard:'.$val['start_standard'].' add_fee:'.$val['add_fee']."\r\n");
					}
				}
	    	}
	    	$shopList[$kshop]['delivery'] = $shopFeeAreaTotal;
			$shopList[$kshop]['template_id']=$dlytmplList[$vshop['shop_id']]['template_id'];
			$this->makeLog('delivery','5: shopId:'.$vshop['shop_id'].' delivery:'.$shopFeeAreaTotal."\r\n");
			$totalCartWeight += $vshop['totalWeight'];
			$shopList[$kshop]['limitFree']=$shopFreePost[$vshop['shop_id']]['limit_money'];

			if($shopList[$kshop]['limitFree'] > $vshop['totalPrice']){
				$shopList[$kshop]['totalEndPrice'] = $vshop['totalPrice'] + $shopList[$kshop]['delivery'];
				$shopTotalPrice += $shopList[$kshop]['delivery']; //若不包邮，总价加邮费
				$totalCartDelivery += $shopList[$kshop]['delivery'];
			}else{
				$shopList[$kshop]['totalEndPrice'] = $vshop['totalPrice'];
				$shopList[$kshop]['delivery']="0.00";
			}
	    }
	   
	    //得到所有收货地址信息 开始	
       	$whereAddr=array(
        	'user_id'=>$this->uid,
        );
    	$addrList=$this->modelOrder->getAddressList($whereAddr);
    	if($addrList){
    		foreach($addrList as $key=>$value){
    			$addrArr=explode(':',$value['area']);
					$addrList[$key]['area']=rtrim($addrArr[0],'/');
					$addrList[$key]['areaID']=rtrim($addrArr[1],'/');
    		}
    		$this->assign('addrList', $addrList);
    	}
    	//订单总 
    	$orderTotalPrice = $shopTotalPrice - $totalCartDelivery;
    	//如果是东莞移动计算邮费
    	$gdydComId = '1467166836740';
    	if($this->comId == $gdydComId && $shopTotalPrice > 0){
    		//这里会计算订单总价，邮费，减免费用 
    		$this->dgydPostage($shopList, $shopTotalPrice, $totalCartDelivery, $reliefFee, $addrDefaultIdArr, $totalCartWeight);
		}
		
   		$this->assign('cartTotalPrice', $orderTotalPrice);
   		$this->assign('totalCartQuantity',$totalCartQuantity);
		$this->assign('totalCartDelivery',$totalCartDelivery);//总运费
		$this->assign('totalCartWeight',$totalCartWeight);
		$this->assign('shopList',$shopList);
		$this->assign('shopTotalPrice',$shopTotalPrice); //总价格
		$this->assign('reliefFee',$reliefFee); //减免金额
		$this->assign('cartList',$cartList);  //购物车信息
		$this->display('order');
	}
 
	/**
	 * 东莞移动用户，邮费计算
	 * 计算规则，东莞移动用户订单总额满99，广东省内包邮，省外重新计算
	 * @param array $shopList 商品列表
	 * @param number $shopTotalPrice 订单总价
	 * @param number $totalCartDelivery 邮费
	 * @param number $reliefFee 减免费用
	 * @param array $userAdrr 用户地址，下标，0为省级 1，市级
	 * @param array $totalCartWeight 商品总重量
	 */
	private function dgydPostage(&$shopList, &$shopTotalPrice, &$totalCartDelivery, &$reliefFee, $userAdrr, $totalCartWeight){
		
		$orderTotalPrice = $shopTotalPrice - $totalCartDelivery;
		//东莞移动用户计算方式
		//用户地址
		$addrProvince = $userAdrr[0];//省份
		$addrCity = $userAdrr[1];//市
		$gdAreaId = 19;//广东地区的jd_id
		
		if($addrProvince == $gdAreaId){
			//如果是广东省内，满99，不满99,还是还原来一样，不变
			if($orderTotalPrice >= 99){
				$shopTotalPrice = $orderTotalPrice;
				$reliefFee = $totalCartDelivery;
			}
			return;
		}else{
			
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
			
			//省外计算，重新邮费
			$totalPostage = 0;
			$where['jd_id'] = array('in',$userAdrr);
			$dgydPostageList = $this->syslogisticsDgyd
									->where($where)
									->field('jd_id, start_standard, start_fee, add_fee')
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
			
			$totalCartDelivery = $totalPostage;
			$shopTotalPrice = $orderTotalPrice + $totalCartDelivery;
			$reliefFee = '0.00';
			//重新设置邮费，将最小订单金额的店铺邮费，修改为新的邮费
			foreach ($shopList as &$order){
				if($minPriceShopId == $order['shop_id']){
					$order['delivery'] = $totalPostage;
					$order['totalEndPrice'] = $order['totalPrice'] + $totalPostage;
				}else{
					$order['totalEndPrice'] = $order['totalPrice'];
				}
			}
			
			//计算减免费用
			//如果原来邮费大于新邮费,减免费用即为差额
			//原来邮费小于新邮费,将减免费用置为0
			//if($totalCartDelivery > $totalPostage){
			//	$reliefFee = $totalCartDelivery - $totalPostage;
			//}else{
			//	$reliefFee = '0.00';
			//}
		}
	}

}