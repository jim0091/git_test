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
		$this->modelOrder=D('Order');
	}
	//日志记录
    public function orderLog($data){
        return M("systrade_log")->data($data)->add();
    } 
	//购物车页面 20160811 开始
	public function cart(){
		header("Content-type: text/html; charset=utf-8"); 
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
			//活动ids
			$aconfigIds[$kCart] = $vCart['activity_config_id'];
			$skuidItemid[$kCart]['itemId'] = $vCart['item_id'];
			$skuidItemid[$kCart]['skuId'] = $vCart['sku_id'];
			$skuidItemid[$kCart]['num'] = $vCart['quantity'];
		}	
		//查询活动限购数量
		unset($condition);
		unset($field);
		$condition['activity_config_id'] = array('in',implode(',',$aconfigIds));
		$field = "activity_config_id,max_join_num";
		$aconfigList = $this->modelOrder->getAconfigList($condition,$field);

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
        	redirect(C('TMPL_PARSE_STRING.__LISHE_URL__').C('TMPL_PARSE_STRING.__USER__').'/Address/addAddress', 2, '请完善您的收货地址信息，跳转中，请稍后...');
        }
        if (!$userAddressInfo['area'] || !$userAddressInfo['addr']) {
        	redirect(C('TMPL_PARSE_STRING.__LISHE_URL__').C('TMPL_PARSE_STRING.__USER__').'/Address/addAddress', 2, '请完善您的收货地址信息，跳转中，请稍后...');
        }
        $addressArea = substr($userAddressInfo['area'],0,strpos($userAddressInfo['area'],':'))."&nbsp;".$userAddressInfo['addr'];
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

        foreach($cartList as $keyCart=>$valCart){
	        $cartList[$keyCart]['price']=round($valCart['price'],2);
	        $cartList[$keyCart]['goodsTotalPrice'] = round($valCart['price'],2) * $valCart['quantity'];
	        $cartList[$keyCart]['goodsTotalCash'] = round($valCart['cash'],2) * $valCart['quantity'];
			$cartList[$keyCart]['goodsTotalPoint'] = $valCart['point'] * $valCart['quantity'];
	        
	        //检查商品是否已经下架
	        foreach ($itemStatus as $kStatus => $vStatus) {
	        	if ($vStatus['item_id'] == $valCart['item_id']) {
	        		$cartList[$keyCart]['itemStatus'] = $vStatus['approve_status'];
	        	}
	        }
	        //检查库存
	        foreach ($res['data'] as $kSku => $vSku) {
	        	if ($kSku == $valCart['sku_id']) {
	        		$cartList[$keyCart]['isFreez'] = $vSku;
	        	}
	        }
	        //检查限购数量
	        if ($aconfigList) {
		        foreach ($aconfigList as $key => $value) {
		        	if ($value['activity_config_id'] == $valCart['activity_config_id']) {
		        		if ($value['max_join_num'] < $valCart['quantity'] && $value['max_join_num'] != 0) {
		        			$cartList[$keyCart]['maxNum'] = 1;
		        		}
		        	}
		        }	        	
	        }
	        $totalPrice += $cartInfo[$keyCart]['goodsTotalPrice'];
        }
        $this->assign('totalPrice',$totalPrice); //总价格
        $this->assign('cartInfo',$cartList);  //购物车信息
        $this->assign('shopInfo',$shopInfo);
        $this->assign('addressArea',$addressArea);
		$this->display('cart');
	}
	//购物车页面 20160811 结束

	//得到购物车中选中商品的总价 20160812 开始
	public function getSelectPrice(){
		$cartIdStr=I('get.cartIdStr','','trim');
		if($cartIdStr){
			$cartIdStr=rtrim($cartIdStr,','); //得到购物车id
			$cartIdStr = "(".$cartIdStr.")";
			$cartInfo = $this->cartModel->table('systrade_cart c,sysitem_sku s')->where('c.sku_id = s.sku_id and c.user_id ='.$this->uid.' and c.cart_id in'.$cartIdStr)->field('c.item_id,c.sku_id,c.quantity,s.price,s.cash,s.point')->select();
			
			if($cartInfo){
				foreach($cartInfo as $k1=>$v1){
					$itemInfoArr[$k1]=trim($v1['sku_id']);    
	            }
	            $itemInfoStr=array(
	            	'sku_id'=>array('in',$itemInfoArr),
	            	'com_id'=>$this->comId
	            );
	            $companyItemInfo=$this->modelOrder->getCompanyItemPrice($itemInfoStr);          
				foreach($cartInfo as $k=>$v){
					if($companyItemInfo){
						foreach($companyItemInfo as $k2=>$v2){

							if($v['item_id']==$v2['item_id']){
								if(floatval($v2['condition']) == 0){
            						$cartInfo[$k]['price']=floatval($v2['price']);
	            				}else{	
	            					$cartInfo[$k]['price']=floatval($v['price']);
	            				}
							break;
							}
						}
				    }
	            	$currSelectGoodsPrice += sprintf("%.2f",floatval($cartInfo[$k]['price'])) * intval($cartInfo[$k]['quantity']);
	            	$totalCash += round($cartInfo[$k]['cash'],2) * $cartInfo[$k]['quantity'];
	            	$totalPoint += $cartInfo[$k]['point'] * $cartInfo[$k]['quantity'];
				}
				
				$currArr=array(
					'curr_num'=> $currSelectGoodsPrice*100,
					'curr_money'=> number_format($currSelectGoodsPrice,2,'.',''),
					'totalCash'=> $totalCash,
					'totalPoint'=>$totalPoint
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
        
		$where = " and user_id=".$this->uid." and cart_id =".$gid;
        $cInfo=$this->modelOrder->getCartList($where);
        //获取组合购的商品sku列表
        if ($cInfo[0]['type'] == '7' && !empty($cInfo[0]['aitem_id'])) {
        	unset($condition);
        	unset($field);
        	$condition = array('type'=>7,'aitem_id'=>$cInfo[0]['aitem_id']);
        	$field = 'sku_id';
    		$skuList = $this->modelOrder->getSkuList($condition,$field);
    		if (is_array($skuList) && !empty($skuList)) {
    			foreach ($skuList as $key => $value) {
    				$skuIds[$key] = $value['sku_id'];
    			}
    		}
    		$skuIdStr = implode(',', $skuIds);
    	}
        $addressInfo = $this->addrModel->where('user_id='.$this->uid.' and def_addr=1')->find();
        $jd_ids = str_replace('/','_',trim(strstr($addressInfo['area'],':'),":"));
        if($op=='dec'){    //表货品数目减少
        	unset($where);
        	if ($cInfo[0]['type'] == '7' && !empty($cInfo[0]['aitem_id']) && !empty($skuIdStr)) {
        		$where = 'user_id='.$this->uid.' and sku_id IN('.$skuIdStr.')';
        	}else{
        		$where = "user_id=".$this->uid." and cart_id =".$gid;
        	}

            $cartNumDec=$this->cartModel->where($where)->setDec('quantity',1);    
        }elseif($op=='inc'){  //表货品数目增加      
        	$skuArr = $this->skuStoreModel->field('store,freez')->where('sku_id='.$sku_id)->find();  	
        	//查询活动商品购买限制
        	$skuInfo = $this->modelOrder->getSkuInfo('sku_id='.$sku_id,'sku_id,activity_config_id');
        	unset($condition);
        	unset($field);
        	$condition['activity_config_id'] = $skuInfo['activity_config_id'];
        	$field = 'activity_config_id,max_join_num';
        	$aconfigInfo = $this->modelOrder->getAconfigInfo($condition,$field);
        	if ($num+1 > $aconfigInfo['max_join_num'] && $aconfigInfo['max_join_num'] != 0) {
        		echo  json_encode(array(0,'超过限制购买数量！'));
	            exit;
        	}
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
	            $noFreez = $skuArr['store']-$skuArr['freez'];
	            //注意判断的时候购买数量要加1
	            if($num + 1 > $noFreez || $noFreez < 1){
	                echo  json_encode(array(0,'库存不足！'));
	                exit;
	            } 
        	}  
        	unset($where);
        	if ($cInfo[0]['type'] == '7' && !empty($cInfo[0]['aitem_id']) && !empty($skuIdStr)) {
        		$where = 'user_id='.$this->uid.' and sku_id IN('.$skuIdStr.')';
        	}else{
        		$where = "user_id=".$this->uid." and cart_id =".$gid;
        	}     
            $cartNumDec=$this->cartModel->where($where)->setInc('quantity',1); 
        }elseif($op=='both'){
        	$dataInfo=array(
        		'quantity'=>$num
        		);
        	$cartNumChg=$this->cartModel->where($where)->data($dataInfo)->save();
        }
        $cartInfo = $this->cartModel->table('systrade_cart c,sysitem_sku s')->where('c.sku_id = s.sku_id and c.user_id ='.$this->uid)->field('c.cart_id,c.shop_id,c.item_id,c.sku_id,c.title,c.image_default_id,c.quantity,s.price,s.cash,s.point')->select();
        
        if($cartInfo){
        	foreach($cartInfo as $k1=>$v1){
					$itemInfoArr[$k1]=trim($v1['sku_id']);    
	        }
            $itemInfoStr=array(
            	'sku_id'=>array('in',$itemInfoArr),
            	'com_id'=>$this->comId
            );
	        $companyItemInfo=$this->modelOrder->getCompanyItemPrice($itemInfoStr);	          
            foreach($cartInfo as $k=>$v){
                if($v['cart_id']==$gid){
                	if($companyItemInfo){
	                	foreach($companyItemInfo as $k2=>$v2){
	                		if($v['item_id']==$v2['item_id']){
								if(floatval($v2['condition']) == 0){
	        						$cartInfo[$k]['price']=sprintf("%.2f",floatval($v2['price']));
	            				}else{	
	            					$cartInfo[$k]['price']=sprintf("%.2f",floatval($v['price']));
	            				}
								break;
							}

	                	}
                	}
                    $goodsTotalPrice =  sprintf("%.2f",sprintf("%.2f",floatval($cartInfo[$k]['price'])) * intval($v['quantity'])); //单个商品总价
                	$skuTotalCash = round($cartInfo[$k]['cash'] * $v['quantity'],2);
                	$skuTotalPoint = $cartInfo[$k]['point'] * $v['quantity'];
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
                $cartInfo[$k]['skuTotalCash'] = $v['cash'] * $v['quantity'];
                $cartInfo[$k]['skuTotalPoint'] = $v['point'] * $v['quantity'];

                $totalPrice += $cartInfo[$k]['goodsTotalPrice']; //总价格 
                $totalCash += $cartInfo[$k]['skuTotalCash'];//总销售价 
                $totalPoint += $cartInfo[$k]['skuTotalPoint'];//总积分 
            }
            echo json_encode(array(1,$goodsTotalPrice,sprintf("%.2f",$totalPrice),$isFreez,$skuTotalCash,$totalCash,$skuTotalPoint,$totalPoint));
        }
    }
     // 购物车中货品数量的增加/减少 end 20160812

	//删除购物商品信息 20160812 开始
	public function deleteCartId(){
		$cartId=trim(I('get.cartId'),',');
		if($cartId != 0){
			$where = "cart_id in($cartId) and user_id = $this->uid";
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
			//skuIds 
			$skuIds[$kCart] = $vCart['sku_id'];
			//商品skus和itemids
			$skuIds[$kCart] = $vCart['sku_id'];
			$skuidItemid[$kCart]['itemId'] = $vCart['item_id'];
			$skuidItemid[$kCart]['skuId'] = $vCart['sku_id'];
			$skuidItemid[$kCart]['num'] = $vCart['quantity'];

			if (!empty($vCart['type'])) {
				$type[$vCart['shop_id']] = $vCart['type'];
			}
			if (!empty($vCart['activity_config_id'])) {
				$aitemId[$vCart['shop_id']] = $vCart['activity_config_id'];
			}
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
        if (!$userAddressInfo) {
        	redirect(C('TMPL_PARSE_STRING.__LISHE_URL__').C('TMPL_PARSE_STRING.__USER__').'/Address/addAddress', 2, '请完善您的收货地址信息，跳转中，请稍后...');
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
        //查询活动商品购买限制
	    unset($condition);
	    unset($field);
	    $skuIdsStr=implode(',',$skuIds);
		$condition['sku_id']=array('in',$skuIdsStr);
		$field = 'sku_id,activity_config_id';
	    $skuList = $this->modelOrder->getSkuList($condition,$field);
	    if (is_array($skuList)) {
	    	foreach ($skuList as $key => $value) {
	    		$aconfigIds[$key] = $value['activity_config_id'];
	    	}
	    }
	    unset($condition);
	    unset($field);
	    if ($aconfigIds) {
	    	$aconfigIdsStr = implode(',', $aconfigIds);
		    $condition['activity_config_id'] = array('in',$aconfigIdsStr);
		    $field = 'activity_config_id,max_join_num';
			$aconfigList = $this->modelOrder->getAconfigList($condition,$field);
	    }

	    foreach ($cartList as $kCart => $vCart) {
	    	$cartList[$kCart]['price'] = round($vCart['price'],2);
	    	$cartList[$kCart]['goodsTotalPrice'] = round($vCart['price'],2) * $vCart['quantity'];
	        $cartList[$kCart]['goodsTotalCash'] = round($vCart['cash'],2) * $vCart['quantity'];
	        $cartList[$kCart]['goodsTotalPoint'] = $vCart['point'] * $vCart['quantity'];
	    	$shopTotalPrice += $cartList[$kCart]['goodsTotalPrice'];
	    	$shopTotalCash += $cartList[$kCart]['goodsTotalCash'];
	    	$shopTotalPoint += $cartList[$kCart]['goodsTotalPoint'];
	    	$totalCartQuantity += $vCart['quantity'];
	    	foreach ($shopList as $kShop => $vShop) {
	    		if ($vShop['shop_id'] == $vCart['shop_id']) {
	    			$shopList[$kShop]['totalPrice'] += round($vCart['price'],2) * $vCart['quantity']; 
	    			$shopList[$kShop]['totalWeight'] += $vCart['weight'] * $vCart['quantity']; 
	    			$shopList[$kShop]['totalNum'] += $vCart['quantity'];
	    			$shopList[$kShop]['totalCash'] += round($vCart['cash'],2) * $vCart['quantity'];
	    			$shopList[$kShop]['totalPoint'] += $vCart['point'] * $vCart['quantity'];
	    		}
	    	}
	        foreach ($res['data'] as $kSku => $vSku) {
	        	if ($kSku == $vCart['sku_id']) {
	        		$cartList[$kCart]['store'] = $vSku;
	        	}
	        }
	        if ($aconfigList) {
		        foreach ($aconfigList as $key => $value) {
		        	if ($value['activity_config_id'] == $vCart['activity_config_id']) {
		        		if ($value['max_join_num'] < $vCart['quantity'] && $value['max_join_num'] != 0) {
		        			$cartList[$kCart]['store'] = 34;
		        		}
		        	}
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
			$shopList[$kshop]['postFree']=$shopFreePost[$vshop['shop_id']]['limit_money'];

			if($shopList[$kshop]['postFree'] > $vshop['totalPrice']){
				$shopList[$kshop]['totalEndPrice'] = $vshop['totalPrice'] + $shopList[$kshop]['delivery'];
				$shopList[$kshop]['totalEndPoint'] = $vshop['totalPoint'] + $shopList[$kshop]['delivery']*100;
				$shopTotalPrice += $shopList[$kshop]['delivery']; //若不包邮，总价加邮费
				$shopTotalPoint += $shopList[$kshop]['delivery']*100;
				$totalCartDelivery += $shopList[$kshop]['delivery'];
			}else{
				$shopList[$kshop]['totalEndPrice'] = $vshop['totalPrice'];
				$shopList[$kshop]['totalEndPoint'] = $vshop['totalPoint'];
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
    		$this->assign('addrList',$addrList);
    	}
   		$this->assign('cartTotalPrice',$shopTotalPrice);
   		$this->assign('totalCartQuantity',$totalCartQuantity);
		$this->assign('totalCartDelivery',$totalCartDelivery);
		$this->assign('totalCartWeight',$totalCartWeight);
		$this->assign('shopList',$shopList);
		$this->assign('shopTotalPrice',$shopTotalPrice); //总价格
		$this->assign('shopTotalCash',$shopTotalCash);//总售价		
		$this->assign('shopTotalPoint',$shopTotalPoint);//总积分
		$this->assign('cartList',$cartList);  //购物车信息
		$this->assign('type',$type);//订单类型
		$this->assign('aitemId',$aitemId);//活动id
		$this->display('order');
	}
 



}