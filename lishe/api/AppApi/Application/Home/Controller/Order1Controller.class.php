<?php
namespace Home\Controller;

use Think\Controller\JsonRpcController;

use Think\Controller;
class Order1Controller extends CommonController {
	
// 	public function __construct() {
// 		header ( "content-type:text/html;charset=utf-8" );
// 	}
	public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
		}
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
	
	public function order(){
		$uid = $this->uid;
		if($uid){
			$postData = I('post.');
			$json_postdata = json_encode ( $postData );
			$this->appReceiveDataLog($json_postdata);//
			//$cartIds = rtrim ( I ( 'get.itemList', '', 'trim' ), ',' );
			$cartIdArray = $postData['itemList'];
			//$cartIdArray = (explode(",",$cartIds));
			if(!empty($cartIdArray)){
				// 获取购物车勾选商品的信息
				$cartList = $this->getCartList ($cartIdArray,$uid);
				if(!empty($cartList)){
					foreach ( $cartList as $kCart => $vCart ) {
						// 店铺ids
						$shopIds [$kCart] = $vCart ['shop_id'];
						
						// 商品ids
						$itemIds [$kCart] = $vCart ['item_id'];
						// 商品skus和itemids
						$skuIds [$kCart] = $vCart ['sku_id'];
						$skuidItemid [$kCart] ['itemId'] = $vCart ['item_id'];
						$skuidItemid [$kCart] ['skuId'] = $vCart ['sku_id'];
						$skuidItemid [$kCart] ['num'] = $vCart ['quantity'];
					}
					$new_shopIds = array();
					for ($i=0;$i<count($shopIds);$i++){
						//去除重复店铺
						if(!(in_array($shopIds[$i], $new_shopIds))){
							array_push($new_shopIds, $shopIds[$i]);
						}
					}
					// 查询商品上下架状态
					$itemStatusArrAy = array();
					for ($i=0;$i<count($itemIds);$i++){
						$item_id =$itemIds[$i];
						$ItemStatus = $this->getItemStatus($item_id);
						if(!$ItemStatus){
							$this->retError ( -3, '商品已经下架！' );
							exit;
						}else{
							array_push($itemStatusArrAy, $ItemStatus);
						}
					}
					
					$shopList = array();
					for($i=0;$i<count($new_shopIds);$i++){
						$shop_Id =$new_shopIds[$i];
						$ShopInfo = $this->getShopStatus($shop_Id);
						if(empty($ShopInfo)){
							$this->retError ( -4, '店铺不存在！' );
							exit;
						}else{
							array_push($shopList, $ShopInfo);
						}
					}
					// 查询用户默认地址
					$userAddressInfo = $this->getUserAddress ( $uid );//一维数组
					if(empty($userAddressInfo)){
						$this->retError ( -5, '请完善您的地址信息' );
					}
					$jd_ids = str_replace ( '/', '_', trim ( strstr ( $userAddressInfo ['area'], ':' ), ":" ) );
					
					// 调用接口查询库存
					$skuidItemidJson = json_encode ( $skuidItemid );
					$data['itemsSkus'] = $skuidItemidJson;
					$data['area'] = $jd_ids;
					$data['itemsSkusNew'] = json_decode ( $data ['itemsSkus'] );
					$return = $this->apiCheckCartStock($data);
					$res = json_decode($return,true);
					if ($res ['result'] != 100 || $res ['errcode'] > 0) {
						$logoData['rel_id'] = '1';
						$logoData['op_name'] =  "系统";
						$logoData['op_role'] = "system";
						$logoData['behavior'] = "cancel";
						if($res ['errcode'] > 0){
							// 查询库存异常
							$logoData['log_text'] = $res['msg'];
							$logtxt = '京东查询库存异常！';
						}else{
							$logoData['log_text'] = '查询库存接口失败！';
							$logtxt = '京东查询库存接口通讯失败！';
						}
						$logoData['log_time'] = time();
						$this->orderLog ( $logoData );
						$this->retError ( -6, $logtxt );
						exit ();
					}
					//$cartList 商品（二维数组）
					foreach ( $cartList as $kCart => $vCart ) {
						$cartList [$kCart] ['price'] = round ( $vCart ['price'], 2 );
						$cartList [$kCart] ['goodsTotalPrice'] = round ( $vCart ['price'], 2 ) * $vCart ['quantity'];
						$shopTotalPrice += $cartList [$kCart] ['goodsTotalPrice'];
						$totalCartQuantity += $vCart ['quantity'];
						foreach ( $shopList as $kShop => $vShop ) {
							if ($vShop ['shop_id'] == $vCart ['shop_id']) {
								$shopList [$kShop] ['totalPrice'] += round ( $vCart ['price'], 2 ) * $vCart ['quantity'];
								$shopList [$kShop] ['totalWeight'] += $vCart ['weight'] * $vCart ['quantity'];
								$shopList [$kShop] ['totalNum'] += $vCart ['quantity'];
							}
						}
						foreach ( $res ['data'] as $kSku => $vSku ) {
							if ($kSku == $vCart ['sku_id']) {
								$cartList [$kCart] ['store'] = $vSku;
							}
						}
						// 检查商品是否已经下架
						foreach ( $itemStatusArrAy as $kStatus => $vStatus ) {
							if ($vStatus ['item_id'] == $vCart ['item_id']) {
								$cartList [$kCart] ['itemStatus'] = $vStatus ['approve_status'];
							}
						}
					
					}
					// 店铺配送方式
					$conditionDlytmpl ['shop_id'] = array ('in',$shopIds);
					$dlytmplList = $this->getDlytmpl ( $conditionDlytmpl );
					$addrDefaultIdArr = explode ( '_', $jd_ids );
					if (! $dlytmplList) {
						$this->retError ( - 5, '店铺配送方式不存在，请重新选择商品！' );
						exit ();
					}else{
						foreach ( $dlytmplList as $kdp => $vtp ) {
							$dlytmplList [$vtp ['shop_id']] = $vtp;
							$shopDlytmpConf [$vtp ['shop_id']] = unserialize ( $vtp ['fee_conf'] );
						}
					}
					// 包邮信息
					$totalCartDelivery = 0.00;
					$conditionFreepost ['shop_id'] = array ('in',$shopIds);
					$freePostList = $this->getFreePost ( $conditionFreepost );
					foreach ( $freePostList as $kfp => $vfp ) {
						$shopFreePost [$vfp ['shop_id']] = $vfp;
					}
					foreach ( $shopList as $kshop => $vshop ) {
						$shopFeeAreaTotal = 0; // 初始化
						foreach ( $shopDlytmpConf [$vshop ['shop_id']] as $key => $val ) {
							$shopPressAreaArr = array ();
							$shopPressAreaArr = explode ( ',', $val ['area'] );
							if (! empty ( $shopPressAreaArr [0] )) {
								if (in_array ( $addrDefaultIdArr [0], $shopPressAreaArr )) { // 省
									$shopFeeAreaTotal = $val ['start_fee'] + (ceil ( $vshop ['totalWeight'] ) - $val ['start_standard']) * $val ['add_fee'];
									$this->makeLog ( 'delivery', '2: addrDefaultIdArr:' . $addrDefaultIdArr [0] . ' area:' . $val ['area'] . ' shopId:' . $vshop ['shop_id'] . ' start_fee' . $val ['start_fee'] . ' totalWeight:' . ceil ( $vshop ['totalWeight'] ) . ' start_standard:' . $val ['start_standard'] . ' add_fee:' . $val ['add_fee'] . "\r\n" );
								}
								if (in_array ( $addrDefaultIdArr [1], $shopPressAreaArr )) { // 市
									// $addrFeeCity
									$shopFeeAreaTotal = $val ['start_fee'] + (ceil ( $vshop ['totalWeight'] ) - $val ['start_standard']) * $val ['add_fee'];
									$this->makeLog ( 'delivery', '3: addrDefaultIdArr:' . $addrDefaultIdArr [1] . ' area:' . $val ['area'] . ' shopId:' . $vshop ['shop_id'] . ' start_fee' . $val ['start_fee'] . ' totalWeight:' . ceil ( $vshop ['totalWeight'] ) . ' start_standard:' . $val ['start_standard'] . ' add_fee:' . $val ['add_fee'] . "\r\n" );
								}
								if (in_array ( $addrDefaultIdArr [2], $shopPressAreaArr )) { // 区
									// $addrFeeArea
									$shopFeeAreaTotal = $val ['start_fee'] + (ceil ( $vshop ['totalWeight'] ) - $val ['start_standard']) * $val ['add_fee'];
									$this->makeLog ( 'delivery', "4: addrDefaultIdArr:" . $addrDefaultIdArr [2] . ' area:' . $val ['area'] . ' shopId:' . $vshop ['shop_id'] . " start_fee" . $val ['start_fee'] . " totalWeight:" . ceil ( $vshop ['totalWeight'] ) . " start_standard:" . $val ['start_standard'] . " add_fee:" . $val ['add_fee'] . "\r\n" );
								}
							} else {
								if (empty ( $shopFeeAreaTotal )) {
									$shopFeeAreaTotal = $val ['start_fee'] + (ceil ( $vshop ['totalWeight'] ) - $val ['start_standard']) * $val ['add_fee'];
									$this->makeLog ( 'delivery', '1: addrDefaultIdArr:' . $jd_ids . ' area:' . $val ['area'] . ' shopId:' . $vshop ['shop_id'] . ' start_fee' . $val ['start_fee'] . ' totalWeight:' . ceil ( $vshop ['totalWeight'] ) . ' start_standard:' . $val ['start_standard'] . ' add_fee:' . $val ['add_fee'] . "\r\n" );
								}
							}
						}
						//$shopList [$kshop] ['delivery'] = $shopFeeAreaTotal;
						$shopList [$kshop] ['postFree'] = $shopFeeAreaTotal;
						
						
						$shopList [$kshop] ['template_id'] = $dlytmplList [$vshop ['shop_id']] ['template_id'];
						$this->makeLog ( 'delivery', '5: shopId:' . $vshop ['shop_id'] . ' delivery:' . $shopFeeAreaTotal . "\r\n" );
						$totalCartWeight += $vshop ['totalWeight'];
						
						//$shopList [$kshop] ['postFree'] = round ( $shopFreePost [$vshop ['shop_id']] ['limit_money'], 2 );
						$shopList [$kshop] ['delivery'] = round ( $shopFreePost [$vshop ['shop_id']] ['limit_money'], 2 );
						
						//if ($shopList [$kshop] ['postFree'] > $vshop ['totalPrice']) {
						if ($shopList [$kshop] ['delivery'] > $vshop ['totalPrice']) {

							//$shopList [$kshop] ['totalEndPrice'] = $vshop ['totalPrice'] + $shopList [$kshop] ['delivery'];							
							//$shopTotalPrice += $shopList [$kshop] ['delivery']; // 若不包邮，总价加邮费
							//$totalCartDelivery += $shopList [$kshop] ['delivery'];
							
							
							$shopList [$kshop] ['totalEndPrice'] = $vshop ['totalPrice'] + $shopList [$kshop] ['postFree'];
							$shopTotalPrice += $shopList [$kshop] ['postFree']; // 若不包邮，总价加邮费
							$totalCartDelivery += $shopList [$kshop] ['postFree'];
							
							
							// 				$totalCartDelivery = $shopList [$kshop] ['delivery']+$totalCartDelivery;
						} else {
							$shopList [$kshop] ['totalEndPrice'] = $vshop ['totalPrice'];
							
							//$shopList [$kshop] ['delivery'] = 0;
							$shopList [$kshop] ['postFree'] = 0;
							
							
						}
					}
					
					
					
					$defAddrInfo = $this->getUserAddress($uid);
					if ($defAddrInfo) {
						$addrArr = explode ( ':', $defAddrInfo ['area'] );
						$defAddrInfo ['area'] = rtrim ( $addrArr [0], '/' );
						$defAddrInfo ['areaID'] = rtrim ( $addrArr [1], '/' );
						$data ['defAddrInfo'] = $defAddrInfo;
					}else{
						$data ['defAddrInfo'] = '';
					}
					$data ['cartTotalPrice'] = $shopTotalPrice;
					$data ['totalCartQuantity'] = $totalCartQuantity;
					$data ['totalCartDelivery'] = $totalCartDelivery;
					$data ['totalCartWeight'] = $totalCartWeight;
					$data ['shopList'] = $shopList;
					$data ['shopTotalPrice'] = $shopTotalPrice;
					$data ['cartList'] = $cartList;
					$this->retSuccess ( $data );
				}else{
					$this->retError ( -2, '请选择需要购买的商品！' );
				}
			}else{
				$this->retError ( - 1, '请选择需要购买的商品！' );
			}
		}else{
			$this->retError ( 0, '请重新登录！' );
		}
	}
	
	
	
	//根据用户id查询购物车数据
	public function getCartList($cartIds,$uid){
		if(empty($cartIds)){
			return null;
		}else{
			$CartList = array();
			$CartObj = M ( 'systrade_cart' );//购物车表
			for ($i =0;$i<count($cartIds);$i++){
				$where['cart_id'] = $cartIds[$i];
				$where['c.user_id'] = $uid;
				$field = 'c.cart_id,c.shop_id,c.item_id,c.sku_id,c.title,c.image_default_id,c.quantity,s.price,s.spec_info,s.weight';
				$findData = $CartObj->alias('c')->join('sysitem_sku as s ON c.sku_id= s.sku_id')->where($where)->field($field)->find();
				if(!empty($findData)){
					array_push($CartList, $findData);
				}
			}
			return $CartList;
		}
	}
	

	//根据店铺ids查询店铺信息
	public function getShopList($condition){
		if (empty($condition)) {
			return false;
		}else{
			$sysshop_shopObj = M('sysshop_shop');//店铺信息
			return $sysshop_shopObj->where($condition)->field('shop_id,shop_name,shop_logo,wangwang,shopuser_name,qq')->select();
		}
	}
	//根据用户id查询用户收货地址
	public function getUserAddress($uid){
		if (empty($uid)) {
			return false;
		}
		$modelAddr = M('sysuser_user_addrs'); //收货地址表
		$addressInfo = $modelAddr->where('def_addr = 1 and user_id='.$uid)->find();
		if (!addressInfo) {
			$addInfo = $modelAddr->where('user_id='.$uid)->find();
			if ($addInfo['addr_id']) {
				if($modelAddr->where('addr_id='.$addInfo['addr_id'])->setField('def_addr',1)){
					return $this->modelAddr->where('def_addr = 1 and user_id='.$uid)->find();
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return $addressInfo;
		}
	}
	
	

	//检测购物车库存状态 赵尊杰 2016-10-17
	public function apiCheckCartStock($data){
		$itemSku = trim($data['itemsSkus']);
		$area = $data['area'];
		$itemSku = str_replace('&quot;','"',$itemSku);
		$itemSkuParam = json_decode($itemSku,true);
		if(!empty($itemSkuParam)){
			foreach($itemSkuParam as $key=>$value){
				$itemId[$key]=$value['itemId'];
				$skuId[$key] = $value['skuId'];
				$itemNum[$value['skuId']]=$value['num'];
				$stock[$value['skuId']]=33;//默认有货
			}
		}else{
			$this->retError(1001,'必要参数不能为空');
		}
		$sku=array();
		//查商品表
		$conditionItem=array('item_id'=>array('in',$itemId));
		$sysitem_itemObj = M ( 'sysitem_item' ); // 产品表
		$checkItem = $sysitem_itemObj->where($conditionItem)->field('jd_sku,item_id')->select();
		//查库存表
		$conditionSku=array('sku_id'=>array('in',$skuId));
		$modelItemSkuStore=M('sysitem_sku_store');
		$checkSku = $modelItemSkuStore->where($conditionSku)->select();
		
		//合并array
		foreach ($checkItem as $kItem => $vItem) {
			foreach ($checkSku as $kSku => $vSku) {
				if ($vItem['item_id'] == $vSku['item_id']) {
					$newItemSku[] = array_merge($vItem,$vSku);
				}
			}
		}
		if(!empty($newItemSku)){
			foreach($newItemSku as $key=>$value){
				if($value['jd_sku']>0){
					$sku[]=array(
							'skuId'=>$value['jd_sku'],
							'num'=>$itemNum[$value['sku_id']]
					);
				}else{
					$noFreez = $value['store']-$value['freez'];
					if ($noFreez < 0 || $itemNum[$value['sku_id']] > $noFreez) {
						$stock[$value['sku_id']]=34;
					}
				}
				$jdItemId[$value['jd_sku']]=$value['sku_id'];
			}
		}
	
		if(!empty($sku)){
			$data=array(
					'skuNums'=>$sku,
					'area'=>$area
			);
			$url=C('API_AOSERVER').'jd/product/checkstock';
			$result=$this->requestJdPost($url,json_encode($data));
			$retArr=json_decode($result,true);
	
			if($retArr['code']==100){
				foreach($retArr['data'] as $key=>$value){
					$stock[$jdItemId[$value['skuId']]]=$value['stockStateId'];
					if($value['stockStateId']==33 or $value['stockStateId']==39 or $value['stockStateId']==40){
						$checkSku[]=$value['skuId'];
					}
				}
			}else{//通讯失败设置为无货
				foreach($retArr['data'] as $key=>$value){
					$stock[$jdItemId[$value['skuId']]]=34;
				}
				//$this->makeLog('checkstock','url:'.$url.',data:'.json_encode($data).',return:'.$result."\n");
			}
			//$this->makeLog('checkstock','url:'.$url.',data:'.json_encode($data).',return:'.$result."\n");
		}
	
		//验证是否可售
		if(!empty($checkSku)){
			$url=C('API_AOSERVER').'jd/product/checkRep';
			$data='{"skuIds":"'.implode(',',$checkSku).'"}';
			$result=$this->requestJdPost($url,$data);
			$retArr=json_decode($result,true);
			if($retArr['code']==100){
				unset($checkSku);
				foreach($retArr['data'] as $key=>$value){
					//如果不可售设置为无货
					if($value['saleState']==0){
						$stock[$jdItemId[$value['skuId']]]=34;
					}else{
						$checkSku[]=$value['skuId'];
					}
				}
			}else{
				$this->makeLog('checkRep','url:'.$url.',data:'.$data.',return:'.$result."\n");
			}
			//$this->makeLog('checkRep','url:'.$url.',data:'.$data.',return:'.$result."\n");
		}
	
		//验证是否支持配送
		if(!empty($checkSku)){
			$areas=explode('_',$area);
			$url=C('API_AOSERVER').'jd/product/checkAreaLimit';
			$data='{"skuIds":"'.implode(',',$checkSku).'","province":'.intval($areas[0]).',"city":'.intval($areas[1]).',"county":'.intval($areas[2]).',"town":'.intval($areas[3]).'}';
			$result=$this->requestJdPost($url,$data);
			$retArr=json_decode($result,true);
			if($retArr['code']==100){
				foreach($retArr['data'] as $key=>$value){
					//如果有区域限制设置为无货
					if($value['isAreaRestrict']==true){
						$stock[$jdItemId[$value['skuId']]]=34;
					}
				}
			}else{
				//$this->makeLog('checkAreaLimit','url:'.$url.',data:'.$data.',return:'.$result."\n");
			}
			//$this->makeLog('checkAreaLimit','url:'.$url.',data:'.$data.',return:'.$result."\n");
		}
		
		$ret=array(
				'result'=>100,
				'errcode'=>0,
				'msg'=>"操作成功",
				'data'=>$stock
		);
		return  json_encode($ret);
	}
	
	public function requestJdPost($url='', $data=''){
		if(empty($url) || empty($data)){
			return false;
		}
		$ch=curl_init();//初始化curl
		curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
		curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
		curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
		$return=curl_exec($ch);//运行curl
		curl_close($ch);
		return $return;
	}
	
	//根据店铺查询运费信息表
	public function getDlytmpl($condition){
		if (!condition) {
			return false;
		}else{
			$modelLogisticsDlytmplObj = M('syslogistics_dlytmpl');//快递信息表
			return $modelLogisticsDlytmplObj->where($condition)->field('shop_id,fee_conf,template_id')->select();
		}
	}
	
	//根据店铺查询包邮信息
	public function getFreePost($condition){
		if (!condition) {
			return false;
		}else{
			$modelFreepostageObj = M('syspromotion_freepostage');//包邮表
			return $modelFreepostageObj->where($condition)->select();
		}
	}
	
	//查询默认地址表
	public function getDefaultAddressInfo($condition){
		if (!$condition) {
			return false;
		}else{
			$modeladdressObj = M('sysuser_user_addrs'); //收货地址表
			return $modeladdressObj->where($condition)->find();
		}
	}
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	// 提交订单
	public function addUserOrder() {
		$postData ['remark'] = I ( 'post.remark' ); // 买家留言
		$postData ['shopIds'] = I ( 'post.shopIds' ); // 店铺id
		$postData ['itemIds'] = I ( 'post.itemIds' ); // 商品ids
		$postData ['skuIds'] = I ( 'post.skuIds' ); // 库存规格ids
		$postData ['dlytmplIds'] = I ( 'post.dlytmplIds'); // 配送模板id
		$postData ['postFees'] = I ( 'post.postFees' ); // 邮费
		$postData ['orderFrom'] = "APP"; // pc：心意商城，show：企业秀，app：手机app，ws微信商城
		$postData ['uid'] = $this->uid;
		if(empty($postData)){
			$this->retError(-1,'参数缺失');
		}else{
			//调用订单生成接口
			$data = json_encode ( $postData );
			$this->appReceiveDataLog($data);//
			//模拟正确的json数据
			//$data = '{"remark":["",""],"shopIds":["17","5"],"itemIds":["54164","17953"],"skuIds":["56291","19263"],"dlytmplIds":["3","1"],"postFees":["99","99"],"orderFrom":"APP","uid":"10452"}';
			//$data  = '{"remark":["","",""],"shopIds":["5","10","16"],"itemIds":["18030","5164","913","919"],"skuIds":["19340","5373","1029","1036"],"dlytmplIds":["1","4","7","4"],"postFees":["0.00","0.00","0.00","0.00"],"orderFrom":"APP","uid":"8335"}';
			//$return = $this->apiCreateOrder($data);//老接口
			$res = $this->apiCreateOrderNew($data);
			if ($res ['result'] != 100 || $res ['errcode'] > 0) {
				$logoData['rel_id'] = '2';
				$logoData['op_name'] =  "系统";
				$logoData['op_role'] = 'system';
				$logoData['behavior'] = 'cancel';
				$logoData['log_text'] = '生成订单接口通讯失败！';
				$logoData['log_time'] = time ();
				$this->orderLog ( $logoData );
				$this->retError ( - 4, '接口通讯失败！' );
				exit ();
			}else{
				if (in_array ( 10, $postData ['shopIds'] )) {
					$syncData['paymentId'] = $res ['data'] ['paymentId'];
					$syncData['opType'] = "creat";
					$syncUrl = C ( 'COMMON_API' ) . 'Order/apiSyncOrder';
					$syncReturn = $this->requestPost ( $syncUrl, $syncData );
					$syncReturn = trim ( $syncReturn, "\xEF\xBB\xBF" ); // 去除BOM头
					$syncRes = json_decode ( $syncReturn, true );
					if ($syncRes ['result'] != 100 || $syncRes ['errcode'] > 0) {
						$logoData1['rel_id'] = '2';
						$logoData1['op_name'] =  "系统";
						$logoData1['op_role'] = 'system';
						$logoData1['behavior'] = 'cancel';
						if($syncRes ['result'] != 100){
							$logoData1['log_text'] = '京东接口通讯失败！';
						}elseif($syncRes ['errcode'] > 0 || $syncRes ['errcode'] < 0){
							$logoData1['log_text'] = $syncRes['msg'];
						}
						$logoData1['log_time'] = time ();
						$this->orderLog ( $logoData1 );
						$this->retError ( - 4, $logoData1['log_text'] );
						exit();
					}
				}
// 				$Client = new jsonRPCClient($url);
				$data2['paymentId'] = $res ['data'] ['paymentId'];
				$data2['created_time'] = date("Y-m-d H:i:s", $res['data']['created_time']);
				$this->retSuccess ( $data2 ,"订单提交成功了");
				$Client = new jsonRPCClient(C('COMMON_API').'Home/Supplier');
				$Client->splitTrade($data2['paymentId']);
				exit;
			}
		}
	}
	
	//生成订单.子订单，支付子订单，支付主单
	public function apiCreateOrderNew($stringdata){
		$uid = $this->uid;
		$source = '';//商品打特价使用
		if(empty($uid)){
			$this->retError(0,"请重新登录");
		}else{
			$strData=str_replace('&quot;','"',$stringdata);
			$arrData = json_decode($strData,true);
			if($uid==$arrData['uid']){
				$shopList = $arrData['shopIds'];
				//$shopList = array();
				$systrade_cart = M('systrade_cart');
				$skuIdArray = $arrData['skuIds'];
				for($i=0;$i<count($skuIdArray);$i++){
					$where_sku['sku_id'] = $skuIdArray[$i];
					$where_sku['user_id'] = $uid;
					$skuData[$i] = $systrade_cart->where($where_sku)->field('sku_id,item_id,shop_id,quantity')->find();
					$skuData_new[$i] ['itemId'] = $skuData[$i]['item_id'];
					$skuData_new[$i] ['skuId'] = $skuData[$i]['sku_id'];
					$skuData_new[$i] ['num'] = $skuData[$i]['quantity'];
					//array_push($shopList, $skuData[$i]['shop_id']);
				};
				// 查询用户默认地址
				$userAddressInfo = $this->getUserAddress ( $uid );//一维数组
				if(empty($userAddressInfo)){
					$this->retError ( -2, '请完善您的地址信息' );
				}else{
					$jd_ids = str_replace ( '/', '_', trim ( strstr ( $userAddressInfo ['area'], ':' ), ":" ) );
					// 调用接口查询库存
					$skuidItemidJson = json_encode ( $skuData_new );
					$data['itemsSkus'] = $skuidItemidJson;
					$data['area'] = $jd_ids;
					$data['itemsSkusNew'] = json_decode ( $data ['itemsSkus'] );
					$return = $this->apiCheckCartStock($data);
					$res = json_decode($return,true);
					if ($res ['result'] != 100 || $res ['errcode'] > 0) {
						$logoData['rel_id'] = '1';
						$logoData['op_name'] =  "系统";
						$logoData['op_role'] = "system";
						$logoData['behavior'] = "cancel";
						if($res ['errcode'] > 0){
							// 查询库存异常
							$logoData['log_text'] = $res['msg'];
							$logtxt = '京东查询库存异常！';
						}else{
							$logoData['log_text'] = '查询库存接口失败！';
							$logtxt = '京东查询库存接口通讯失败！';
						}
						$logoData['log_time'] = time();
						$this->orderLog ( $logoData );
						$this->retError (-2, $logtxt );
						exit ();
					}else{
						if (empty($arrData['skuIds'])) {
							$this->makeLog('createOrder','error:1001,错误信息：店铺ID、商品ID、配送模板ID或库存ID为空');
							$this->retError(-3,':生成订单失败，错误信息：skuId为空');
						}else{
							//查询用户Deposit
							$userDepositInfo = $this->getUserDeposit($arrData['uid']);
							if (!$userDepositInfo) {
								$this->makeLog('createOrder','error:1002,错误信息：无法获取用户信息');
								$this->retError(-4,':生成订单失败，错误信息：无法获取用户信息');
							}
							$userDepositInfo = M('sysuser_user_deposit')->where('user_id ='.$arrData['uid'])->find();
							
							$skuIdsStr = implode(',', $arrData['skuIds']);
							$modelCart = M('');
							$cartList = $modelCart->table('systrade_cart cart ,sysitem_sku sku,sysitem_sku_store sto')
							->where(" cart.sku_id = sku.sku_id and sku.sku_id = sto.sku_id and cart.user_id =".$arrData['uid']." and cart.sku_id in($skuIdsStr)")->select();
							if(empty($cartList)){
								$this->makeLog('createOrder','error:1014,错误信息：未找到购物车商品信息，检查是否已成功提交');
								$this->retError(-5,'生成订单失败，错误信息：未找到购物车商品信息，检查是否已成功提交');
								exit;
							}else{
								//商品打特价 ---begin zhangrui
								if($source == 'company'){
									$cartList=$this->specialPriceItem($cartList,$userDepositInfo['comId']);
								}
								
								$addrModel=M('sysuser_user_addrs'); //收货地址表
								$addressInfo = $addrModel->where('user_id ='.$arrData['uid']." and def_addr = 1")->find();
								
								//检查购买数量是否超过库存
								foreach ($cartList as $kcl => $valcl) {
									//可购买商品数量
									$itemIds[$kcl] = $valcl['item_id'];
									$noFreez = $valcl['store']-$valcl['freez'];
									if ($noFreez < 1 || $valcl['quantity'] >  $noFreez) {
										$this->makeLog('createOrder','error:1003,错误信息：购买数量超过商品库存数量');
										$this->retError(-6,'生成订单失败，错误信息：购买数量超过商品库存数量');
										exit;
									}
								}
								//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
								//查询sku表
								$conditionSku = "sku_id IN (".implode(',', $arrData['skuIds']).")";
								
								$itemSkuList = $this->getSkuList($conditionSku);
								
								$skuIdsArray = $arrData ['skuIds'];
								$itemSkuList = array();
								$sysitem_sku = M('sysitem_sku');
								$field = 'sku_id,price,cost_price,item_id';
								for ($i=0;$i<count($skuIdsArray);$i++){
									$where['sku_id'] = $skuIdsArray[$i];
									$findsku = $sysitem_sku->where($where)->field($field)->find();
									array_push($itemSkuList, $findsku);
								}
								
								if (!$itemSkuList) {
									$this->makeLog('createOrder','error:1004,错误信息：商品sku信息有误');
									$this->retError(-7,'生成订单失败，错误信息：商品sku信息有误');
								}
								
								//校验进货价和销售价
								foreach ($itemSkuList as $key => $value) {
									if (empty($value['cost_price']) || $value['price'] <= $value['cost_price']) {
										$this->makeLog('createOrder','error:1005,错误信息：商品价格信息有误');
										$this->retError(-8,'生成订单失败，错误信息：商品价格信息有误');
									}
								}
								//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
								//查询item表
								$conditionItem = "item_id IN (".implode(',', $arrData['itemIds']).")";
								$itemItemList = $this->getItemList($conditionItem);
								
								if (!$itemItemList) {
									$this->makeLog('createOrder','error:1006,错误信息：商品信息有误');
									$this->retError(-9,'生成订单失败，错误信息：商品信息有误');
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
								$address1 = $this->checkJdAddress(trim(strstr($addressInfo['area'],':'),":"));
								$address1 = true;
								if($address1 == false){
									$this->makeLog('createOrder','error:1007,错误信息：地址信息有误');
									$this->retError(-10,'生成订单失败，错误信息：地址信息有误');
								}else{
									$newTakeAddress = explode("/",strstr($addressInfo['area'],':',true));
// 									var_dump($cartList);exit;
									//检查运费数据是否一致
									//$checkPostFree = $this->checkPostFees();
									//if (!$checkPostFree) {
									//	$this->makeLog('createOrder','error:1008,错误信息：运费数据不一致');
									//	$this->retError(-11,'生成订单失败，错误信息：运费数据不一致bac');
									//}
									//_________________________________________________
									$new_cartList = array(); 
									foreach($cartList as $k=>$v) {
										$new_cartList[$v["shop_id"]][] = $v; // $new_cartList 合并后的
									}
									$flags = false;
									$new_cartList = array_slice($new_cartList,0);//把数组的键格式化处理
									foreach ($new_cartList as $key=>$vo){
										$data1['tid'] = date(ymdHis).$key.$uid;//订单编号
										$tid[$key] = $data1['tid'];
										$data1['user_id'] = $arrData['uid'];//会员id
										$data1['com_id'] = $userDepositInfo['comId'];//员工企业id
										$data1['dlytmpl_id'] = $arrData['dlytmplIds'][$key];//配送模板id
										$data1['status'] = 'WAIT_BUYER_PAY';//订单状态
										$data1['trade_status'] = 'WAIT_BUYER_PAY';//订单状态
										//实付金额,订单最终总额
										$toallPrice=0;
										$toallNum=0;
										$toallWeight = 0;
										$modelTrade = M ( 'systrade_trade' ); // 订单主表
										foreach ($vo as $ke) {
											$toallPrice +=  sprintf("%.2f",$ke['price'])*$ke['quantity'];
											//子订单商品购买数量总数
											$toallNum = $toallNum + $ke['quantity'];
											$toallWeight = $toallWeight+ $ke['weight']*$ke['quantity'];
											$shop_id = $ke['shop_id'];
										}
										$data1['shop_id'] = $shop_id;//订单所属的店铺id
										$arrData['postFees'][$key] = empty($arrData['postFees'][$key]) ? 0 : $arrData['postFees'][$key];
										$data1['payment'] = round($toallPrice+$arrData['postFees'][$key],2);
										$data1['total_fee'] = $toallPrice;//各子订单中商品price * num的和，不包括任何优惠信息
										$data1['post_fee'] = floatval($arrData['postFees'][$key]);//邮费
										
										//$data1['cash_fee'] = 0.000;//需支付现金（0）
										$data1['point_fee'] = round($toallPrice+$arrData['postFees'][$key],2)*100;//需支付积分
										//$data1['payed_cash'] = floatval($arrData['postFees'][$key]);//已支付现金
										//$data1['payed_point'] = floatval($arrData['postFees'][$key]);//已支付积分
										
										$data1['receiver_name'] = $addressInfo['name'];//收货人姓名
										$data1['created_time'] = time();
										$data1['receiver_state'] = $newTakeAddress[0];//收货人所在省份
										$data1['receiver_city'] = $newTakeAddress[1];//收货人所在城市
										$data1['receiver_district'] = $newTakeAddress[2];//收货人所在地区
										$data1['receiver_address'] = $addressInfo['addr'];//收货人详细地址
										$data1['receiver_zip'] = $addressInfo['zip'];//收货人邮编
										$data1['receiver_mobile'] = $addressInfo['mobile'];//收货人手机号
										$data1['receiver_phone'] = $addressInfo['tel'];//收货人电话
										$data1['title'] = "订单明细介绍";//交易标题
										$data1['buyer_message'] = $arrData['remark'][$key];//买家留言
										$arrData['orderFrom'] = empty($arrData['orderFrom']) ? 'pc' : $arrData['orderFrom'];
										$data1['trade_from'] = $arrData['orderFrom'];//订单来源
										$data1['itemnum'] = $toallNum;
										if ($addressInfo['area']) {
											$areaIds = trim(strstr($addressInfo['area'],':'),":");
										}
										$data1['buyer_area'] = $areaIds;//买家下单的地区
										$data1['total_weight'] = floatval($toallWeight);
// 										var_dump($data1);exit;
										$res = $modelTrade->add($data1);
// 										var_dump($res);exit;
										if (!$res) {
											$this->makeLog('createOrder','error:1010,错误信息：主订单表插入失败');
											$this->retError(-12,'生成订单失败，错误信息：主订单表插入失败');
										}else{
											$orderFrom = $arrData['orderFrom'];
											//生成order表单的记录
											$orderCreateInfo = $this->CreateOrderOredr($uid,$data1['tid'],$new_cartList[$key],$orderFrom);
											//var_dump($new_cartList[$key]);exit;
											if($orderCreateInfo){
												$flags = true;
											}else{
												$flags = false;
											}
										}
									}
									//_________________________________________________
									//生成payment表（一条）
									if($flags){
										$paymentId = $this->NewCreatPayment($tid,$uid);
										if (empty($paymentId)){
											$this->makeLog('createOrder','error:1010,错误信息：主订单表插入失败');
											$this->retError(-13,'生成订单失败，错误信息：支付表插入失败');
										}else{
											$ret=array(
													'result'=>100,
													'errcode'=>0,
													'msg'=>"订单提交成功",
													'data'=>array('paymentId'=>$paymentId['paymentId'],'created_time'=>$paymentId['created_time']),
											);
											$Supplier = new SupplierController();
											$boolSup = $Supplier->splitTrade($paymentId['paymentId']);//拆分供应商订单
											if($boolSup[0]!='1'){
												//同步供应商订单失败，写入日志
											}
											return $ret;
										}
									}else{
										exit(json_encode(-2,"操作失败"));
									}
								}
							}
						}
					}
				}
			}else{
				$this->retError(-1,'非法操作');
			}
		}
	}
	
	//生成order表
	/**
	 * @author lihongqiang 2017-02-17
	 * @param string $tid 一个字符串
	 * @param array $arrDataArray 二维数组
	 * @return boolean
	 * //一次只生成一家店铺下面的订单
	 */
	public function CreateOrderOredr($uid,$tid,$arrDataArray,$orderFrom='APP'){
		$oidArray= array();
		$sysitem_item = M('sysitem_item');
		foreach ($arrDataArray as &$V){
			$oid = date(ymdHis).rand(100, 999).$uid;
			$da['oid'] = $oid;
			$da['tid'] = $tid;
			$where['item_id'] = $V['item_id'];
			$itemInfo = $sysitem_item->where($where)->field('supplier_id,send_type,cat_id')->find();
			//$da['supplier_id'] = empty($V['supplier_id']) ? 0 : $V['supplier_id'];
			if(empty($itemInfo['supplier_id'])){
				$itemInfo['supplier_id'] = 0;
			}
			$da['supplier_id'] = $itemInfo['supplier_id'];
			$da['send_type'] =$itemInfo['send_type'];
			$da['cat_id'] = $itemInfo['cat_id'];//名字不一样，左边是cat_id,右边是cart_id
			
			//$da['supplier_id'] = empty($V['supplier_id']) ? 0 : $V['supplier_id'];
			//$da['send_type'] =$V['send_type'];
			//$da['cat_id'] = $V['cat_id'];//名字不一样，左边是cat_id,右边是cart_id
			$da['shop_id'] = $V['shop_id'];
			$da['user_id'] = $uid;
			$da['item_id'] = $V['item_id'];
			$da['sku_id'] = $V['sku_id'];
			$da['bn'] = $V['bn'];
			$da['title'] = $V['title'];
			$da['spec_nature_info'] = $V['spec_info'];
			$da['price'] =  round($V['price'],2);
			$da['cost_price'] = $V['cost_price'];
			if (empty($V['quantity']) || $V['quantity'] < 1) {
				$da['num'] = 1;
			}else{
				$da['num'] = $V['quantity'];
			}
			$da['total_fee'] = floatval(round($V['price'],2)*$V['quantity']);
			$da['total_weight'] = floatval($V['weight']*$V['quantity']);
			$da['modified_time'] = time();
			$da['order_from'] = $orderFrom;
			$da['pic_path'] = $V['image_default_id'];
			$da['cash'] = 0.00;
			$da['point'] = floatval(round($V['price'],2)*$V['quantity'])*100;
			$da['total_cash'] = 0.00;
			$da['total_point'] = $da['total_fee']*100;
			
			$modelOrder = M ( 'systrade_order' ); // 订单附表
			$boolData = $modelOrder->data($da)->add();
			if (!$boolData) {
				$this->makeLog('createOrder','error:1011,错误信息：子订单表插入失败');
				$this->retError(1011,'生成订单失败，错误信息：子订单表插入失败');
			}else{
				array_push($oidArray, $da['oid']);
				//下单增加购买数量
				$modelItemCount = M ( 'sysitem_item_count' ); // 产品表
				//$itemCount = $this->modelItemCount->where('item_id ='.$val['item_id'])->setInc('buy_count',$val['quantity']);
				$itemCount = $modelItemCount->where('item_id ='.$V['item_id'])->setInc('buy_count',$V['quantity']);
				//下单预占sku库存
				$modelItemSkuStore = M('sysitem_sku_store');
				$resStore = $modelItemSkuStore->where('sku_id ='.$V['sku_id'])->setInc('freez',$V['quantity']);
				//下单预占item库存
				$modelItemStore = M('sysitem_item_store');
				//$resItemStore = $this->modelItemStore->where('item_id='.$val['item_id'])->setInc('freez',$val['quantity']);
				$resItemStore = $modelItemStore->where('item_id='.$V['item_id'])->setInc('freez',$V['quantity']);
				$modelCart = M('systrade_cart');
				$resCartDel = $modelCart->where('cart_id = '.$V['cart_id'])->delete();
				if (!$resStore && !$resCartDel && !$itemCount && !$resItemStore) {
					$this->makeLog('createOrder','error:1012,错误信息：预占库存、增加下单数量或删除购物车数据失败');
					$this->retError(1012,'生成订单失败，错误信息：预占库存、增加下单数量或删除购物车数据失败');
				}
			}
		}
		return $oidArray;
	}
	
	//生成支付数据
	public function NewCreatPayment($tid=array(),$uid){
		//查询用户登录信息
		$userAccountInfo = $this->getUserAccount($uid);
		//查询用户资料信息
		$userInfo = $this->getUser($uid);
		$toallPrice = 0 ;
		$modelTrade = M('systrade_trade');
		for ($i=0;$i<count($tid);$i++){
			$where['tid']  = $tid[$i];
			$where['user_id'] = $uid;
			$tradeList = $modelTrade->where($where)->find();
			$toallPrice = $toallPrice+$tradeList['payment'];
		}
		//插入支付表
		$data['payment_id'] = date(ymdHis).$uid.'1';//支付单号
		$data['money'] = round($toallPrice,2);//需要支付的金额
		$data['cur_money'] = 0;//支付货币金额
		$data['cash_fee'] = 0.000;
		$data['point_fee'] = round($toallPrice,2)*100;//需要支付的积分
		$data['payed_cash'] = 0.000;//需要支付的积分
		$data['payed_point'] = 0;//需要支付的积分
		$data['user_id'] = $this->uid;
		$data['status'] = 'ready';
		$data['user_name'] = $userAccountInfo['mobile'];
		$data['pay_app_id'] = 'point';
		$data['pay_name'] = '积分支付';
		$data['pay_from'] = "APP";//支付货币金额
		$data['op_name'] = $userInfo['username']; //操作员
		//$data['bank'] = '预存款';//收款银行
		$data['pay_account'] ='用户';//支付账号
		$data['ip'] =$_SERVER['REMOTE_ADDR'];//IP
		$data['created_time'] = time();
		$data['modified_time'] = time();
		$modelPayments=M('ectools_payments');
		$boolData = $modelPayments->add($data);
		if($boolData){
			$flags = false;
			$modelPaybill = M('ectools_trade_paybill');//支付子表
			for ($i=0;$i<count($tid);$i++){
				$where1['tid']  = $tid[$i];
				$where1['user_id'] = $uid;
				$tradeList1 = $modelTrade->where($where1)->find();
				$paybill['payment_id'] = $data['payment_id'];//主支付单编号
				$paybill['tid'] = $tid[$i];
				$paybill['user_id'] = $uid;
				$paybill['payment'] = $tradeList1['payment'];
				$paybill['point'] = $tradeList1['payment']*100;
				$paybill['created_time'] = time();
				$boolPay = $modelPaybill->add($paybill);
				if($boolPay){
					$flags = true;
				}else{
					$flags = false;
				}
			}
			if($flags){
				$retu['paymentId'] = $data['payment_id'];
				$retu['created_time'] = $data['created_time'];
				return $retu;
			}else{
				return null;
			}
		}else{
			return false;
		}
	}
	
	
	//根据id查询用户积分记录
	public function getUserDeposit($uid){
		if (empty($uid)) {
			return false;
		}
		$userDepositInfo = M('sysuser_user_deposit')->where('user_id ='.$uid)->find();
		if ($userDepositInfo) {
			return $userDepositInfo;
		}else{
			return false;
		}
	}
	
	//特价商品--zhangrui
	private function specialPriceItem($itemInfo,$comId){
		if($itemInfo){
			$companyItemPriceModel = M('company_item_price');//公司特价
			foreach($itemInfo as $key=>$value){
				$itemInfo[$key]['shop_price']=$value['price'];
				$skuIds[]=$value['sku_id'];
			}
			if(!empty($skuIds)){
				$condition['sku_id']=array('in',$skuIds);
				$condition['is_delete']=0;
				$specialInfo=$companyItemPriceModel->where($condition)->field('sku_id,price,com_id,start_time,end_time')->select();
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
	//查询商品sku
	public function getSkuList($condition){
		if (!$condition) {
			return false;
		}else{
			$modelSkuObj = M('sysitem_sku');//货品的库存
			return $modelSkuObj->where($condition)->field('sku_id,price,cost_price,item_id')->select();
		}
	}
	//查询商品
	public function getItemList($condition){
		if (!$condition) {
			return false;
		}else{
			$modelItem = M('sysitem_item');//商品表
			return $modelItem->where($condition)->field('item_id,supplier_id,send_type,cat_id,image_default_id')->select();
		}
	}
	
	//检查邮费是否被更改
	public function checkPostFees(){
	//public function checkPostFees($arrData['shopIds'],$arrData['skuIds'],$arrData['uid']){
		$cartModel = M('systrade_cart'); //购物车表
		$shopIdInfo=$cartModel->distinct(true)->field('shop_id')->where('user_id='.$this->uid)->select();
		foreach($shopIdInfo as $k2=>$v2){
			$shopIdArr[$k2] = $v2['shop_id'];
		}
		$shopIdStr=implode(',',$shopIdArr);
		if($shopIdStr){
			$where['shop_id']=array('in',$shopIdStr);
			$shopModel = M('sysshop_shop');//店铺信息
			$shopInfo = $shopModel->where($where)->field('shop_id,shop_name')->select();
			if(empty($shopInfo)){
				exit;
			}
		}
		$where1['c.user_id'] = $this->uid;
		$cartInfo=$cartModel->alias('c')->join('sysitem_sku as s ON c.sku_id= s.sku_id')->where($where1)->field('c.cart_id,c.shop_id,c.item_id,s.sku_id,c.quantity,s.price,s.weight')->select();
		
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
		$sysuser_user_addrs = M ( 'sysuser_user_addrs' ); // 收货地址表
		$addrInfo = $sysuser_user_addrs->where('user_id ='.$this->uid." and def_addr = 1")->find();
		
		
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
		$logisticsModel = M ( 'syslogistics_dlytmpl' ); // 快递信息表

		foreach($shopInfo as $k10=>$v10){
			$shopFeeAreaTotal = 0; //初始化
			$shopExpressInfo=$logisticsModel->where('shop_id='.$v10['shop_id'])->field('fee_conf,template_id')->find();
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
		$syspromotion_freepostage = M ( 'syspromotion_freepostage' ); // 包邮表
		$postInfo=$syspromotion_freepostage->select();
		
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
						$shopInfo[$k11]['delivery']='0.00';
					}
				}
			}
		}
		return $shopInfo;
	
	
	}
	
	//生成支付数据（没用到）
	public function creatPayments($tid='',$uid='',$iden=0){
		if (empty($tid) || !is_array($tid) || empty($uid)) {
			$this->makeLog('creatPayments','error:1001,错误信息：订单编号有误');
			$this->retError(1001,'生成订单失败，错误信息：订单编号有误');
		}
		//查询用户登录信息
		$userAccountInfo = $this->getUserAccount($uid);
		
		if (!$userAccountInfo) {
			$this->makeLog('creatPayments','error:1002,错误信息：无法获取用户登录信息');
			$this->retError(1002,':生成订单失败，错误信息：无法获取用户登录信息');
		}
		//查询用户资料信息
		$userInfo = $this->getUser($uid);
		if (!$userInfo) {
			$this->makeLog('creatPayments','error:1003,错误信息：无法获取用户资料信息');
			$this->retError(1003,':生成订单失败，错误信息：无法获取用户资料信息');
		}
		
		$thisRes = array();
		//获取订单表信息
		$where['tid']  = array('in',implode(',', $tid));
		$modelTrade = M('systrade_trade');
		$tradeList = $modelTrade->where($where)->select();
		$toallPrice = 0 ;
		if ($tradeList) {
			foreach ($tradeList as $key => $value) {
				$toallPrice += $value['payment'];
			}
		}
		//插入支付表
		$data['payment_id'] = date(ymdHis).$uid.'1';//支付单号
		$data['money'] = round($toallPrice,2);//需要支付的金额
		$data['cur_money'] = 0;//支付货币金额
		$data['user_id'] = $uid;
		$data['user_name'] = $userAccountInfo['mobile'];
		$data['op_name'] = $userInfo['username']; //操作员
		$data['bank'] = '预存款';//收款银行
		$data['pay_account'] ='用户';//支付账号
		$data['created_time'] = time();
		$modelPayments=M('ectools_payments');
		$result = $modelPayments->data($data)->add();
		if (!$result) {
			$this->makeLog('creatPayments','error:1004,错误信息：支付主表插入数据失败');
			$this->retError(1004,'生成订单失败，错误信息：支付主表插入数据失败');
		}
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
			$da['user_id'] = $uid;
			$da['created_time'] = time();
			$modelPaybill = M('ectools_trade_paybill');//支付子表
			$result = $modelPaybill->data($da)->add();
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
	
	//根据用户id查询用户登录表
 	public function getUserAccount($uid){
		if (empty($uid)) {
			return false;
		}
		$userAccountInfo = M('sysuser_account')->where('user_id ='.$uid)->find();
		if ($userAccountInfo) {
			return $userAccountInfo;
		}else{
			return false;
		}
	}
	
	//根据用户id查询用户信息表
	function getUser($uid){
		if (empty($uid)) {
			return false;
		}
		$userInfo = M('sysuser_user')->where('user_id ='.$uid)->find();
		if ($userInfo) {
			return $userInfo;
		}else{
			return false;
		}
	}
	
	// 日志记录
	public function orderLog($data) {
		return M ( "systrade_log" )->data ( $data )->add ();
	}
	
	public function appReceiveDataLog($postData){
		$app_receive_data = M('app_receive_data');
		$data['postData'] = $postData;
		$data['createTime'] = date('Y-m-d H:i:s');
		$boolData =$app_receive_data->add($data) ;
		return $boolData;
	}
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++
	public function getShopStatus($shopId){
		$where['shop_id'] = $shopId;
		$sysshop_shopObj = M('sysshop_shop');//店铺信息
		$shopInfo = $sysshop_shopObj->where($where)->field('shop_id,shop_name,shop_logo,wangwang,shopuser_name,qq')->find();
		if($shopInfo){
			return $shopInfo;
		}else{
			return null;
		}
	}
		
	//商品上下架状态
	public function getItemStatus($itemId){
		$where['item_id'] = $itemId;
		$sysitem_item_statusObj = M('sysitem_item_status');
		$item_status = $sysitem_item_statusObj->where($where)->field('item_id,approve_status')->find();
		if(empty($item_status)){
			return null;
		}else{
			if ($item_status['approve_status']=='onsale'){
				return $item_status;
			}else{
				return false;
			}
		}
	}
	
	//校验是否有四级地址，防止京东商品无法下单
	public function checkJdAddress($jdIds){
		if (empty($jdIds)) {
			return false;
		}
		$addressArr =  explode("/",trim($jdIds,'/'));
		if (!is_array($addressArr)) {
			return false;
		}
		if (count($addressArr) < 4) {
			$modelArea = M('site_area');
			$count = $modelArea->where('jd_pid='.$addressArr[2])->find();
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
}