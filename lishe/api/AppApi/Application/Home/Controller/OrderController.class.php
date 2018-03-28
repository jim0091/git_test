<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends CommonController {
	// 获取用户中心
	public function cart() {
		// 判断用户是否登录,得到用户的id
		// 判断shop_id的值，遍历
		$data = array ();
		$shopIdInfo = M ( 'systrade_cart' )->distinct ( true )->field ( 'shop_id' )->where ( 'user_id=' . $this->uid )->select ();
		foreach ( $shopIdInfo as $k2 => $v2 ) {
			$shopIdArr [$k2] = $v2 ['shop_id'];
		}
		$shopIdStr = implode ( ',', $shopIdArr );
		if ($shopIdStr) {
			$where ['shop_id'] = array (
					'in',
					$shopIdStr 
			);
			$shopInfo = M ( 'sysshop_shop' )->where ( $where )->field ( 'shop_id,shop_name' )->select ();
			if ($shopInfo) {
				// $this->assign('shopInfo',$shopInfo);
				$data ['shopInfo'] = $shopInfo;
			}
		
		}
		$cartInfo = M ( 'systrade_cart' )->table ( 'systrade_cart c,sysitem_sku s' )->where ( 'c.sku_id = s.sku_id and c.user_id =' . $this->uid )->field ( 'c.cart_id,c.shop_id,c.item_id,c.sku_id,c.title,c.image_default_id,c.quantity,s.price,s.spec_info' )->select ();
		if (empty ( $cartInfo )) {
			$this->retError ( - 1, "购物车为空" );
			exit ();
		} else {
			$totalPrice = 0;
			foreach ( $cartInfo as $k => $v ) {
				$cartInfo [$k] ['price'] = number_format ( $cartInfo [$k] ['price'], 2, '.', '' );
				$cartInfo [$k] ['goodsTotalPrice'] = floatval ( $cartInfo [$k] ['price'] ) * intval ( $cartInfo [$k] ['quantity'] );
				$totalPrice += $cartInfo [$k] ['goodsTotalPrice'];
			}
			$data ['totalPrice'] = $totalPrice; // 总价格
			$data ['catrInfo'] = $cartInfo; // 购物车信息
		}
		$this->retSuccess ( $data );
	}
	// 购物车货品删除 start 20160718
	public function delFromCart() {
		$gid = I ( 'get.gid', 0, 'intval' );
		$where = array (
				'cart_id' => $gid,
				'user_id' => $this->uid 
		);
		$delCartNum = M ( 'systrade_cart' )->where ( $where )->delete ();
		if ($delCartNum) {
			$this->retSuccess ( array (
					'status' => "1" 
			) );
		} else {
			$this->retError ( - 1, array (
					"status" => "-1" 
			) );
		}
	}
	
	// 获取选中总价
	public function getSelectPrice() {
		$cartIds = rtrim ( I ( 'get.cartIds', '', 'trim' ), ',' );
		if (! $cartIds) {
			$this->retError ( - 1, "未选中任何商品" );
			exit ();
		}
		$condition = " and c.user_id =" . $this->uid . " and c.cart_id IN (" . $cartIds . ")";
		$cartList = D ( 'Cart' )->getCartList ( $condition );
		if (! $cartList) {
			$this->retError ( - 2, "未获取到任何商品数据" );
			exit ();
		}
		$selectTotalNum = 0;
		$selectTotalPrice = 0;
		foreach ( $cartList as $key => $value ) {
			$selectTotalNum += $value ['quantity'];
			$selectTotalPrice += round ( $value ['price'], 2 ) * intval ( $value ['quantity'] ) * 100;
		}
		$data = array (
				'totalNum' => $selectTotalNum,
				'totalPrice' => round ( $selectTotalPrice, 2 ) 
		);
		$this->retSuccess ( $data );
	}
	
	
	//点击商品的sku,查询库存数量
	public function checkSku(){
		$postData = I('post.');
		$sku_id = $postData['sku_id'];
		$address = $postData['address'];
		$item_id = $postData['item_id'];
		$shop_id = $postData['shop_id'];
// 		$address = '19/1607/3155/';
// 		$sku_id = '53240';
// 		$item_id = '51177';
// 		$shop_id = '10';
		$uid = $this->uid;
		if($sku_id && $address && $item_id && $shop_id){
			$where['item_id'] = $item_id;
			// 商品状态
			$conditionItemStatus ['item_id'] = $item_id;
			$itemStatus = D ( "Info" )->getItemStatus ( $conditionItemStatus );
			if($itemStatus){
				if ($itemStatus ['approve_status'] == "instock") {
					//$msg = array ('msg' => "商品已下架");
					$msg = "商品已下架";
					$this->retError ( "-5", $msg );
					exit;
				}else{
					$where['sku_id'] = $sku_id;
					$findItem = M('sysitem_sku')->where($where)->find();
					if($findItem){
						if($shop_id== C ( 'JD_SHOP_ID')){
							$JdClent = new CommonapiController();
							$num = 1;
							//$jd_ids = str_replace ( '/', '_', trim ( strstr ( $userAddressInfo ['area'], ':' ), ":" ) );
							$jd_ids = str_replace ( '/', '_', trim ($address));
							$result = $JdClent->checkJdStock($item_id, $jd_ids, $num);
							if($result['status']!=33){
								$receipt['status'] = -8;
								$receipt['message'] = '商品库存不足';
								$this->retError($receipt['status'],$receipt['message']);
							}else{
								//有货 //用sku查出库存数量返回
								$kucunInfo = D('Info')->getItemSkuStore($where);
								if($kucunInfo){
									$kucunNum = $kucunInfo['store']-$kucunInfo['freez'];
									if($kucunNum > $num){
										$receipt['status'] = $kucunNum;
										$receipt['message'] = '商品剩余库存'.$kucunNum.'件';
										$this->retSuccess($receipt,$receipt['message']);
									}else{
										$receipt['status'] = -6;
										$receipt['message'] = '商品库存不足';
										$this->retError($receipt['status'],$receipt['message']);
									}
								}else{
									$this->retError(-4,"没有这个商品");
								}
							}
						}else{
							//查询本地库存数量
								//有货 //用sku查出库存数量返回
							$kucunInfo = D('Info')->getItemSkuStore($where);
							if($kucunInfo){
								$kucunNum = $kucunInfo ['store'] - $kucunInfo ['freez'];
								if($kucunNum > $num){
									$receipt['status'] = $kucunNum;
									$receipt['message'] = '商品剩余库存'.$kucunNum.'件';
									$this->retSuccess($receipt,$receipt['message']);
								}else{
									$receipt['status'] = -7;
									$receipt['message'] = '商品库存不足';
									$this->retError($receipt['status'],$receipt['message']);
								}
							}else{
								$this->retError(-4,"没有这个商品的库存信息");
							}
						}
					}else{
						$this->retError(-3,"没有这个商品");
					}
				}
			}else{
				//商品已经下架
				$msg = array ('msg' => "商品已下架");
				$this->retError ( -2, $msg );
			}
		}else{
			$this->retError(-1,"参数缺失");
		}
	}
	
	
	
	
	
	// 加入购物车
	public function addItemCart() {
		$uid = $this->uid;
		$itemId = I ( 'post.itemId' );// 商品id
		$skuId = I ( 'post.skuId' );// sku_id
		$quantity = I ( 'post.quantity' );// 购买数量
		$shopId = I ( 'post.shopId' );//商铺的ID
		$address = I ( 'post.address' );//地址
		//$jd_ids = I ( 'post.jd_ids' );//京东商品的id
		if (! $itemId && ! $skuId && ! $quantity) {
			$msg = array ('msg' => "参数错误！");
			$this->retError ( "-1", $msg );
			exit ();
		}
		if (! $itemId && ! $skuId && ! $quantity) {
			$msg = array ('msg' => "参数错误！");
			$this->retError ( "-1", $msg );
			exit ();
		}
		// 查询库存详细信息
		$conditionSkuStore ['sku_id'] = $skuId;
		$skuInfo = D ( "Info" )->getItemSkuStore ( $conditionSkuStore );
		if (!$skuInfo) {
			$msg = array (
					'msg' => "超过购买数量!" 
			);
			$this->retError ( "-2", $msg );
			exit ();
		}
		// 查询用户默认地址
		$AddressClent = new Order1Controller();
		$userAddressInfo = $AddressClent->getUserAddress ($uid);
		$jd_ids = str_replace ( '/', '_', trim ( strstr ( $userAddressInfo ['area'], ':' ), ":" ) );
		if ($shopId == C ( 'JD_SHOP_ID' )) {
			$Clent = new CommonapiController();
			$jdSku = $Clent->checkJdStock($itemId,$jd_ids,$quantity);
			if ($jdSku['status'] != 33) {
				$msg = array ('msg' => "超过购买数量!");
				$this->retError ( "-3", $msg );
				exit ();
			}
		} else {
			if ($skuInfo ['store'] - $skuInfo ['freez'] < $quantity) {
				$msg = array (
						'msg' => "超过购买数量!" 
				);
				$this->retError ( "-2", $msg );
				exit ();
			}
		}
		// 商品状态
		$conditionItemStatus ['item_id'] = $itemId;
		$itemStatus = D ( "Info" )->getItemStatus ( $conditionItemStatus );
		if (! $itemStatus) {
			$msg = array (
					'msg' => "商品已下架，无法购买！" 
			);
			$this->retError ( "-3", $msg );
			exit ();
		}
		if ($itemStatus ['approve_status'] == "instock") {
			$msg = array (
					'msg' => "商品已下架，无法购买！" 
			);
			$this->retError ( "-3", $msg );
			exit ();
		}
		// 查询购物车是否有该商品，如果有的话就直接增加数量
		$conditionCart ['user_id'] = $this->uid;
		$conditionCart ['sku_id'] = $skuId;
		$cartInfo = D ( "Info" )->getCartItem ( $conditionCart );
		if ($shopId == C ( 'JD_SHOP_ID' )) {
			$addNum = $cartInfo ['quantity'] + $quantity;
			$jdSku = $Clent->checkJdStock($itemId,$jd_ids,$addNum);
			if ($jdSku['status'] != 33) {
				$msg = array ();
				$this->retError ( "-4", $msg );
				exit ();
			}
		} else {
			if ($cartInfo ['quantity'] + $quantity > $skuInfo ['store'] - $skuInfo ['freez']) {
				$msg = array ('msg' => "购物车数量超过库存数量，无法购买！");
				$this->retError ( "-4", $msg );
				exit ();
			}
		}
		if ($cartInfo) {
			$res = D ( "Info" )->updateCartItemNum ( 'cart_id = ' . $cartInfo ['cart_id'], $quantity );
			if ($res) {
				$msg = array (
						'msg' => "加入购物车成功！" 
				);
				$this->retSuccess ( $msg );
			} else {
				$msg = array (
						'msg' => "加入购物车失败！" 
				);
				$this->retError ( "-5", $msg );
			}
		} else {
			// 查询商品详细信息
			$itemInfo = D ( "Info" )->getItemInfo ( 'item_id = ' . $itemId );
			// 购物车数据
			$data ['user_ident'] = md5 ( $this->uid ); // 会员ident,会员信息和session生成的唯一值
			$data ['user_id'] = $this->uid; // 用户id
			$data ['shop_id'] = $itemInfo ['shop_id']; // 店铺ID
			$data ['obj_type'] = 'item'; // 购物车对象类型
			$data ['obj_ident'] = 'item_' . $skuInfo ['sku_id']; // item_商品id
			$data ['item_id'] = $itemId; // 商品id
			$data ['sku_id'] = $skuInfo ['sku_id']; // sku的id
			$data ['title'] = $itemInfo ['title']; // 商品标题
			$data ['image_default_id'] = $itemInfo ['image_default_id']; // 商品默认图
			$data ['quantity'] = $quantity; // 数量
			$data ['created_time'] = time (); // 加入购物车时间
			$data ['item_from'] = "iphone_APP"; // 加入购物车的来源
			
			$result = D ( "Info" )->addCartItem ( $data );
			if ($result) {
				$msg = array (
						'msg' => "加入购物车成功！" 
				);
				$this->retSuccess ( $msg );
			} else {
				$msg = array (
						'msg' => "加入购物车失败！" 
				);
				$this->retError ( "-5", $msg );
			}
		}
	}
	// 获取购物车数量
	public function getCartNum() {
		if ($this->uid <= 0) {
			$msg = array (
					'msg' => "请登录！" 
			);
			$this->retError ( "-5", $msg );
		}
		$where = array (
				'user_id' => $this->uid 
		);
		$res = M ( 'systrade_cart' )->where ( $where )->count ();
		$data = array (
				'num' => $res 
		);
		$this->retSuccess ( $data );
	}
	
	// 购物车中货品数量的增加/减少 start 20160718
	public function opCart() {
		$op = I ( 'post.op', '', 'trim' );
		$op = "dec";
		$cartId = I ( 'post.cartId', 0, 'intval' );
		$num = I ( 'post.num', 0, 'intval' ); // 当前货品的购买数量
		$sku_id = I ( 'post.sku_id', 0, 'intval' );
		$conditionCart = array (
				'cart_id' => $cartId,
				'user_id' => $this->uid 
		);
		$cartItemInfo = D ( 'Cart' )->getCartIteminfo ( " and cart_id = " . $cartId . " and user_id=" . $this->uid );
		$addressInfo = D ( 'Cart' )->getDefaultAddressInfo ( array (
				"user_id" => $this->uid,
				"def_addr" => 1 
		) );
		if (! $addressInfo) {
			$msg = array (
					'msg' => "请先添加默认地址！" 
			);
			$this->retError ( "-1", $msg );
			exit ();
		}
		$jd_ids = str_replace ( '/', '_', trim ( strstr ( $addressInfo ['area'], ':' ), ":" ) );
		if ($op == 'dec') { // 表货品数目减少
			$cartNumDec = D ( 'Cart' )->decCartNum ( $conditionCart, $num );
			if ($cartItemInfo ['shop_id'] == C ( 'JD_SHOP_ID' )) {
				$decNum = $num;
				$getUrl = C ( 'COMMON_API' ) . "Jd/checkJdStock/item_id/" . $cartItemInfo ['item_id'] . "/jd_ids/" . $jd_ids . "/num/" . $decNum;
				$jdSku = file_get_contents ( $getUrl );
				$jdSku = trim ( $jdSku, "\xEF\xBB\xBF" ); // 去除BOM头
				if ($jdSku != 33) {
					// echo json_encode(array(1,'库存不足！',34));
					$msg = array (
							'msg' => "库存不足！" 
					);
					$this->retError ( "-2", $msg );
					exit ();
				} else {
					// echo json_encode(array(1,'库存充足！',33));
					$msg = array (
							'msg' => "库存充足！",
							'num' => $num 
					);
					$this->retSuccess ( $msg );
					exit ();
				}
			} else {
				$noFreez = $cartItemInfo ['store'] - $cartItemInfo ['freez'];
				// 注意判断的时候购买数量要减1
				if ($num - 1 > $noFreez || $noFreez < 1) {
					$msg = array (
							'msg' => "库存不足！" 
					);
					$this->retError ( "-2", $msg );
					exit ();
				} else {
					$msg = array (
							'msg' => "库存充足！",
							'num' => $num 
					);
					$this->retSuccess ( $msg );
					exit ();
				}
			}
		
		} elseif ($op == 'inc') { // 表货品数目增加
			if ($cartItemInfo ['shop_id'] == C ( 'JD_SHOP_ID' )) {
				$addNum = $num + 1;
				$getUrl = C ( 'COMMON_API' ) . "Jd/checkJdStock/item_id/" . $cartItemInfo ['item_id'] . "/jd_ids/" . $jd_ids . "/num/" . $addNum;
				$jdSku = file_get_contents ( $getUrl );
				$jdSku = trim ( $jdSku, "\xEF\xBB\xBF" ); // 去除BOM头
				if ($jdSku != 33) {
					// 库存不足
					$msg = array (
							'msg' => "库存不足！" 
					);
					$this->retError ( "-2", $msg );
					exit ();
				} else {
					$msg = array (
							'msg' => "库存充足！",
							'num' => $num 
					);
					$this->retSuccess ( $msg );
				}
			} else {
				$noFreez = $cartItemInfo ['store'] - $cartItemInfo ['freez'];
				// 注意判断的时候购买数量要加1
				if ($num + 1 > $noFreez || $noFreez < 1) {
					$msg = array (
							'msg' => "库存不足！" 
					);
					$this->retError ( "-2", $msg );
					exit ();
				} else {
					$msg = array (
							'msg' => "库存充足！",
							'num' => $num 
					);
					$this->retSuccess ( $msg );
				}
			}
			$cartNumDec = D ( 'Cart' )->addCartNum ( $conditionCart );
		}
	}
	// 日志记录
	public function orderLog($data) {
		return M ( "systrade_log" )->data ( $data )->add ();
	}
	
	
	public function requestPost($url = '', $data = array()) {
		if (empty ( $url ) || empty ( $data )) {
			return false;
		}
		$o = "";
		foreach ( $data as $k => $v ) {
			$o .= "$k=" . $v . "&";
		}
		$param = substr ( $o, 0, - 1 );
		$ch = curl_init (); // 初始化curl
		curl_setopt ( $ch, CURLOPT_URL, $url ); // 抓取指定网页
		curl_setopt ( $ch, CURLOPT_HEADER, 0 ); // 设置header
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 要求结果为字符串且输出到屏幕上
		curl_setopt ( $ch, CURLOPT_POST, 1 ); // post提交方式
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $param );
		$return = curl_exec ( $ch ); // 运行curl
		curl_close ( $ch );
		return $return;
	}
	

	
	// 支付页面
	public function pay() {
		$paymentid = I ( "post.paymentid" );
		// 积分表
		$userDepositInfo = M ( 'sysuser_user_deposit' )->where ( 'user_id =' . $this->uid )->find ();
		// 支付表
		$paymentInfo = M ( "ectools_payments" )->where ( 'payment_id =' . $paymentid )->find ();
		$paymentInfo ['deposit'] = floatval ( $paymentInfo ['money'] ) * 100;
		// 同步一企一舍积分
		$data = array (
				'terminalType' => 'WAP' 
		);
		$url = C ( 'API' ) . 'pointActive/getAllPointActive';
		$res = json_decode ( $this->requestPost ( $url, $data ), true );
		$data = array (
				'paymentInfo' => $paymentInfo,
				'paymentid' => $paymentid,
				'userDepositInfo' => $userDepositInfo,
				'rules' => $res ['data'] ['info'] 
		);
		$this->retSuccess ( $data );
	}
	
	// 支付操作
	public function operPay() {
		$paymentid = I ( 'post.paymentid' );
		$pwd = I ( 'post.pwd' );
		$payType = I ( 'post.payType' );
		if (empty ( $paymentid )) {
			$this->retError ( - 1, "支付错误" );
			exit ();
		}
		
		$userDepositInfo = M ( 'sysuser_user_deposit' )->where ( 'user_id =' . $this->uid )->find ();
		// 用户登录信息表
		$userAccountInfo = M ( 'sysuser_account' )->where ( 'user_id =' . $this->uid )->find ();
		// 支付表
		$paymentInfo = M ( "ectools_payments" )->where ( 'payment_id = ' . $paymentid )->field ( 'user_id,status,money' )->find ();
		// 检查是否有足够积分
		if ($paymentInfo ['money'] > $userDepositInfo ['deposit']) {
			$this->retError ( - 1, "积分不足，请充值！" );
			exit ();
		}
		// 检查该订单是否是自己的
		if ($paymentInfo ['user_id'] != $this->uid) {
			$this->retError ( - 1, "无法为他人买单！" );
			exit ();
		}
		// 支付子表
		$paymentBillInfo = M ( 'ectools_trade_paybill' )->where ( 'payment_id = ' . $paymentid )->field ( 'payment_id,paybill_id,tid' )->select ();
		$tidarry = array ();
		foreach ( $paymentBillInfo as $key => $value ) {
			$tidarry [$key] = $value ['tid'];
		}
		// 检查是否已经支付
		if ($paymentInfo ['status'] == 'succ') {
			// 已经支付不可再次支付
			$this->retError ( - 1, "该订单已经支付！" );
			exit ();
		}
		// 支付密码不正确
		if (md5 ( $pwd ) != $userDepositInfo ['md5_password']) {
			$this->retError ( - 1, "支付密码不正确！" );
			exit ();
		}
		
		// 检查支付情况
		if (! is_array ( $paymentBillInfo )) {
			$this->retError ( - 1, "支付失败！" );
			exit ();
		}
		// 校验订单数据
		$conditionTrade = array (
				'tid' => array (
						'in',
						implode ( ',', $tidarry ) 
				) 
		);
		$tradeList = D ( 'Pay' )->getTradeList ( $conditionTrade );
		if (! $tradeList) {
			$this->retError ( - 1, "订单已超过支付期限，请重新下单！" );
			exit ();
		}
		foreach ( $tradeList as $kTrade => $vTrade ) {
			if ($vTrade ['created_time'] + 60 * 60 * 24 < time ()) {
				$this->retError ( - 1, "订单已超过支付期限，请重新下单！" );
				exit ();
			}
		}
		$conditionOrder = " and tid in (" . implode ( ',', $tidarry ) . ")";
		$orderList = D ( 'Pay' )->getOrderList ( $conditionOrder );
		if (! $orderList) {
			$this->retError ( - 1, "订单已超过支付期限，请重新下单！" );
			
			exit ();
		}
		foreach ( $orderList as $key => $value ) {
			if ($value ['approve_status'] == "instock") {
				$this->retError ( - 1, "订单中存在已下架的商品，请重新下单！" );
				
				exit ();
			}
			if ($value ['price'] != $value ['item_price']) {
				$this->retError ( - 1, "订单已超过支付期限，请重新下单！" );
				exit ();
			}
		}
		
		// 调用支付接口
		$data = array (
				'paymentId' => $paymentid 
		);
		
// 		$url = C ( 'COMMON_API' ) . 'Order/apiPayOrder';
// 		$url = 'http://localhost:8080/api.php/Order/apiPayOrder';
// 		$resPay = $this->requestPost ( $url, $data );
		$resPay = $this->apiPayOrder($data);
		$res = json_decode ($resPay,true);
		if ($res ['result'] != 100) {
			$this->makeLog ( 'payOp', 'user_id:' . $this->uid . ' operator:' . $userAccountInfo ['mobile'] . ' fee:' . $paymentInfo ['money'] . ' message:error:1000,错误信息：接口通讯失败！ time:' . time () . "\r\n" );
			$this->retError ( - 1, "接口通讯失败！" );
			exit ();
		}
		// 支付失败，日志表
		if ($res ['errcode'] > 0) {
			$this->makeLog ( 'payOp', 'user_id:' . $this->uid . ' operator:' . $userAccountInfo ['mobile'] . ' fee:' . $paymentInfo ['money'] . ' message:支付失败:' . $res ['msg'] . ' time:' . time () . "\r\n" );
			$this->retError ( - 1, $res ['msg'] );
			exit ();
		}
		$condition = array ('tid' => array ('in',implode ( ',', $tidarry )));
		$trade = M ( 'systrade_trade' )->field ( 'tid,shop_id' )->where ( $condition )->select ();
		
		foreach ( $trade as $key => $valtr ) {
			if ($valtr ['shop_id'] == C ( 'JD_SHOP_ID' )) {
				// 调用京东确认预占库存订单
				$syncData = array (
						'paymentId' => $paymentid,
						'opType' => 'pay' 
				);
				$syncUrl = C ( 'COMMON_API' ) . 'Order/apiSyncOrder';
				$syncReturn = $this->requestPost ( $syncUrl, $syncData );
				$syncReturn = trim ( $syncReturn, "\xEF\xBB\xBF" ); // 去除BOM头
				$syncRes = json_decode ( $syncReturn, true );
				if ($syncRes ['result'] != 100) {
					$logoData = array (
							'rel_id' => '1',
							'op_name' => "系统",
							'op_role' => "system",
							'behavior' => "cancel",
							'log_text' => '京东确认预占库存订单接口通讯失败！',
							'log_time' => time () 
					);
					$this->orderLog ( $logoData );
					$this->retError ( - 1, "京东接口通讯失败！" );
					exit ();
				}
				if ($syncRes ['errcode'] > 0) {
					$logoData = array (
							'rel_id' => '1',
							'op_name' => "系统",
							'op_role' => "system",
							'behavior' => "cancel",
							'log_text' => $jdRes ['msg'],
							'log_time' => time () 
					);
					$this->orderLog ( $logoData );
					$this->retError ( - 1, $jdRes ['msg'] );
				
				}
				// 暂时关闭顺丰仓库推送
				// }else{
				// //调用订单推送顺丰仓库接口
				// $syncData = array('paymentId'=>$paymentid);
				// $syncUrl=C('COMMON_API').'Order/apiPostOrderSF';
				// $syncReturn=$this->requestPost($syncUrl,$syncData);
				// $syncReturn = trim($syncReturn, "\xEF\xBB\xBF");//去除BOM头
				// $syncRes = json_decode($syncReturn,true);
				// if ($syncRes['result'] != 100) {
				// $logoData = array(
				// 'rel_id' =>'1',
				// 'op_name' =>"系统",
				// 'op_role' =>"system",
				// 'behavior' =>"cancel",
				// 'log_text' => '订单推送顺丰仓库接口通讯失败！',
				// 'log_time' =>time()
				// );
				// $this->orderLog($logoData);
				// $this->error("订单推送顺丰仓库接口通讯失败！");
				// }
				// if($syncRes['errcode'] > 0){
				// $logoData = array(
				// 'rel_id' =>'1',
				// 'op_name' =>"系统",
				// 'op_role' =>"system",
				// 'behavior' =>"cancel",
				// 'log_text' => $jdRes['msg'],
				// 'log_time' =>time()
				// );
				// $this->orderLog($logoData);
				// $this->error($jdRes['msg']);
				// }
			}
		}
// 		$ret=array(
// 				'result'=>100,
// 				'errcode'=>0,
// 				'msg'=>"操作成功",
// 				'data'=>array ('msg' => "支付成功" )
// 		);
		
		$Supplier = new SupplierController();
		$boolSup = $Supplier->payConfirm($paymentid);//确认供应商订单
		if($boolSup[0]!='1'){
			//同步供应商订单失败，写入日志
		}
		$this->retSuccess ( array ('msg' => "支付成功" ) );
		
	}
	
	
	
	
	/**
	 * @name 查询订单的收货信息
	 * @author lihongqiang 2017-01-19
	 * @param 订单的ID $tid   	
	 */
	public function receiveGoodsInfo() {
		$uid = $_SESSION['uid'];
		if($uid){
			$tid = I('post.tid');
			if ($tid) {
				$Model = M ( 'systrade_trade' );
				$where ['tid'] = $tid;
				$where ['user_id'] = $this->uid;
				$field = 'tid,user_id,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address';
				$OrderInfo = $Model->where ( $where )->field($field)->find ();
				if($OrderInfo){
					if($OrderInfo['user_id']==$uid){
						$this->retSuccess($OrderInfo, "收货信息查询成功" );
					}else{
						$this->retError ( - 1, "这不是你的订单，非法操作" );
					}
				}else{
					$this->retError ( - 1, "没有这个订单收货信息" );
				}
			} else {
				$this->retError ( - 1, "订单ID不能为空" );
			}
		}else{
			$this->retError ( - 1, "请登录" );
		}
	}
	
	/**
	 * @name 取消订单
	 * @author lihongqiang 2017-01-19
	 * @param 订单的ID $tid
	 */
	
	//判断如果已经存在支付的订单则无法取消订单
	public function checkTrade($condition){
		if (!$condition) {
			return false;
		}else{
			$TradeObj = M('systrade_trade');
			$resTrade = $TradeObj->where($condition)->field('payed_fee,pay_time')->select();
			$resurt = true;
			foreach ($resTrade as $key => $value) {
				if ($value['payed_fee'] > 0 || !empty($value['pay_time'])) {
					$resurt = false;
					break;
				}
			}
			return $resurt;
		}
	}
	
	//获取订单的支付子列表
	public function getPaymentBillList($condition){
		$uid = $_SESSION['uid'];
		if(!empty($uid)){
			if (empty($condition)) {
				return null;
			}else{
				$where['payment_id'] = $condition['payment_id'];
				$where['user_id'] = $uid;
				$tradePaybillObj = M('ectools_trade_paybill');//支付子表
				$arrData = $tradePaybillObj->where($condition)->field('tid,payment_id,status,created_time')->order('created_time DESC')->select();
				if($arrData){
					return $arrData;
				}else{
					return null;
				}
			}
		}else{
			return null;
		}
	}
	
	//修改支付主表状态
	public function updatePaymentStatus($condition){
		$uid = $_SESSION['uid'];
		if(!empty($uid)){
			if (empty($condition)) {
				return false;
			}else{
				$where['payment_id'] = $condition['payment_id'];
				$where['user_id'] = $uid;
				$PaymentObj = M ( "ectools_payments" ); // 支付主表
				$findData = $PaymentObj->where($where)->find();
				if($findData){
					$data['status'] = 'cancel';
					$bool =  $PaymentObj->where($where)->save($data);
					if($bool){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}
	
	//修改支付副表状态
	public function updatePaymentBillStatus($condition){
		$uid = $_SESSION['uid'];
		if(empty($uid)){
			return false;
		}else{
			if (empty($condition)) {
				return false;
			}else{
				$where['payment_id'] = $condition['payment_id'];
				$PaymentbillObj = M ( "ectools_trade_paybill" ); // 支付副表
				$findData = $PaymentbillObj->where($where)->find();//至少有一条记录
				if($findData){
					$data['status'] = 'cancel';
					$where['user_id'] = $uid;
					$bool =  $PaymentbillObj->where($where)->save($data);
					if($bool){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}
		}
	}
	
	//查询购买数量
	public function getOrderItemNums($condition,$field=""){
		if (!$condition) {
			return false;
		}else{
			$modelOrder = M('systrade_order');
			return $modelOrder->where($condition)->field($field)->select();
		}
	}
	
	//取消单，还原库存
	public function updateSku($condition,$num){
		if (!$condition || !$num) {
			return false;
		}else{
			return $this->modelSkuStore->where($condition)->setDec('freez',$num);
		}
	}
	
	//修改订单状态
	public function updateTrade($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			$data['status']='TRADE_CLOSED_BY_USER';
			$data['modified_time']=time();
			return $this->modelTrade->where($condition)->data($data)->save();
		}
	}
	//修改子订单状态
	public function updateOrder($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return D ( 'Order' )->where($condition)->data($data)->save();
		}
	}
	
	public function cancelOrder(){
		$paymentId = I('post.payment_id');
		$uid = $this->uid;
		if(empty($paymentId)){
			$this->retError(-1,'参数错误');
		}else{
			$Model = M('');
			$Model->startTrans();
			$condition['payment_id'] = $paymentId;
			$condition['user_id'] = $uid;
			$paymentBillList = D ( 'Order' )->getPaymentBillList($condition);
			if (empty($paymentBillList)){
				$this->retError(-2,'没有这个订单');
			}else{
				$tidarry = array();
				foreach ($paymentBillList as $key => $value) {
					$tidarry[$key] = $value['tid'];
				}
				
				//取消paymentId下所有订单
				$conditionTO = array('tid'=>array('in',implode(',',$tidarry)),'user_id'=>$uid);
				
				//如果出现已经支付的订单，则无法取消订单
				$resTrade = D ( 'Order' )->checkTrade($conditionTO);
				if(!$resTrade){
					$this->retError('-3','存在已支付的订单，无法取消！');
				}else{
					//更新支付主表status字段状态为取消
					$resPayment = D ( 'Order' )->updatePaymentStatus($condition,array('status'=>'cancel'));
					if(!$resPayment){
						$this->retError('-4','系统繁忙');//支付主表更新失败
					}else{
						//更新支付副表status字段为取消
						$resPaymentBill = D ( 'Order' )->updatePaymentBillStatus($condition,array('status'=>'cancel'));
						if (!$resPaymentBill) {
							$this->retError('-5','系统繁忙');//支付副表更新失败
						}else{
							//查询购买数量，还原库存
							$orderItemNums = D ( 'Order' )->getOrderItemNums($conditionTO,'item_id,sku_id,num');
							if(!$orderItemNums){
								$this->retError('-6','系统繁忙');//还原库存失败
							}else{
								foreach ($orderItemNums as $key => $value) {
									$skuSto['sku_id'] = $value['sku_id'];
									$resSku = D ( 'Order' )->updateSku($skuSto,$value['num']);
									if (!$resSku) {
										$this->makeLog('orderChgStatus',"sku_id:".$value['sku_id']."数量：".$value['num']."还原失败！\r\n");
									}
								}
								
								$data['status']='TRADE_CLOSED_BY_USER';
								$data['modified_time']=time();
								$res = D ( 'Order' )->updateTrade($conditionTO,$data);
								$result = D ( 'Order' )->updateOrder($conditionTO,$data);
								//用户取消操作记录
								$this->userName = empty($this->userName) ? '会员' : $this->userName;
								foreach ($tidarry as $key => $value) {
									$dataList[$key] = array('admin_userid'=>$uid,'admin_username'=>$this->userName,'created_time'=>time(),'deal_type'=>'取消订单','tid'=>$value,'memo'=>'用户取消成功','ip'=>$_SERVER["REMOTE_ADDR"]);
								}
								$resLog = D ( 'Order' )->addTradeLog($dataList);
								if ($res && $result){
									$Model->commit();
									$this->retSuccess(1,'订单取消成功！');
								}else{
									$Model->rollback();
									$this->retError(0,'订单取消失败！');
								}
							}
						}
					}
				}
			}
		}
	}
	
	
	//确认收货
	public function confirmOrder(){
		$paymentId = I('post.payment_id');
		$uid = $this->uid;
		if(!$uid){
			$this->retError(-1,'请重新登录');
		}else{
			if(empty($paymentId)){
				$this->retError(-2,'参数错误');
			}else{
				//确认收货
				//注意：paymentId获取到的是tid
				$paybillModel = M('ectools_trade_paybill');
				$where['payment_id'] = $paymentId;
				$where['user_id'] = $uid;
				$arrData = $paybillModel->where($where)->field('tid,payment_id')->select();
				if($arrData){
					$systrade_trade = M('systrade_trade');
					$systrade_order = M('systrade_order');
					$flg = false;
					foreach ($arrData as $V){
						$conditionConfirm['tid'] =$V['tid'];
						$conditionConfirm['user_id'] =$uid;
						$findtradeData = $systrade_trade->where($conditionConfirm)->find();
						if($findtradeData){
							$dataTrade=array(
									'status'=>'TRADE_FINISHED',
									'trade_status'=>'TRADE_FINISHED',
									'modified_time'=>time()
							);
							$bool1 = $systrade_trade->where($conditionConfirm)->save($dataTrade);
							if($bool1){
								$dataOrder=array(
										'status'=>'TRADE_FINISHED',
										'modified_time'=>time()
								);
								$bool2 = $systrade_order->where($conditionConfirm)->save($dataTrade);
								if($bool2){
									$flg = true;
								}else{
									$flg = false;
								}
							}else{
								$flg = false;
							}
						}else{
							$flg = false;
						}
					}
					if($flg){
						$this->retSuccess(1,'确认收货成功！');
						exit();
					}else{
						$this->retError(-100,'确认收货失败！');
						exit();
					}
				}else{
					$this->retError(-100,'服务繁忙！');
				}
				
// 				$conditionConfirm=array('tid'=>$paymentId,'user_id'=>$this->uid);
// 				$dataTrade=array(
// 						'status'=>'TRADE_FINISHED',
// 						'trade_status'=>'TRADE_FINISHED',
// 						'modified_time'=>time()
// 				);
// 				$dataOrder=array(
// 						'status'=>'TRADE_FINISHED',
// 						'modified_time'=>time()
// 				);
// 				$res = D ( 'Order' )->updateTrade($conditionConfirm,$dataTrade);
// 				$result = D ( 'Order' )->updateOrder($conditionConfirm,$dataOrder);
					
// 				//用户取消操作记录
// 				$this->userName = empty($this->userName) ? '会员' : $this->userName;
// 				$dataList[]= array('admin_userid'=>$this->uid,'admin_username'=>$this->userName,'created_time'=>time(),'deal_type'=>'确认收货','tid'=>$paymentId,'memo'=>'用户确认收货成功','ip'=>$_SERVER["REMOTE_ADDR"]);
					
// 				$resLog = D ( 'Order' )->addTradeLog($dataList);
// 				if ($res && $result) {
// 					$this->retSuccess(1,'确认收货成功！');
// 					exit();
// 				}else{
// 					$this->retError(-100,'确认收货失败！');
// 					exit();
// 				}
			}
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
	
	
	
	

	
	
	//订单支付接口封装 赵尊杰 2016-09-09 注意：后面加事务处理
	public function apiPayOrder($paymentId){
		$paymentId = $paymentId['paymentId'];
		$payType='point';//支付方式，积分支付：point，ecard：东莞移动一卡通支付
		$orderType='ios';//支付订单的类型，商城订单：shop，活动订单：activity，微送礼订单：gift，默认空是商城订单
		$postData=json_encode($_POST);
		$this->makeLog('pay','post:'.$postData);
		if(empty($paymentId)){
			$this->makeLog('pay','error:1001,错误信息：支付单号为空');
			$this->retError(1001,'支付失败，错误信息：支付单号为空');
		}
		$modelPayments = M('ectools_payments');
		$payment=$modelPayments->field('payment_id,user_id,money,cur_money,user_name,status,pay_account')->where('payment_id='.$paymentId)->find();
		if(empty($payment['payment_id'])){
			$this->makeLog('pay','error:1002,错误信息：未查到该订单');
			$this->retError(1002,'支付失败，错误信息：未查到该订单');
		}
		if (empty($payment['user_id'])) {
			$this->makeLog('pay','error:1003,错误信息：用户id为空');
			$this->retError(1003,'支付失败，错误信息：用户id为空');
		}
		$modelAccount = M('sysuser_account');
		$accountInfo = $modelAccount->field('user_id,mobile')->where('user_id='.$payment['user_id'])->find();
	
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
		$modelPaybill = M('ectools_trade_paybill');//支付子表
		$paymentBillInfo = $modelPaybill->where('payment_id = '.$paymentId)->select();
	
		if (empty($paymentBillInfo)) {
			$this->makeLog('pay','error:1007,错误信息：获取子订单失败');
			$this->retError(1007,':获取数据失败，错误信息：获取子订单失败');
		}
		foreach ($paymentBillInfo as $key => $value) {
			$tids[$key] = $value['tid'];
		}
		//积分支付
// 		if($payType=='point'){
			$payPoint=$payment['money']*100;
			$return = $this->pointPay($paymentId,$accountInfo['mobile'],$payPoint);
			$ret=json_decode($return,true);
			$retCode=$ret['result'];
			$errCode=$ret['errcode'];
			$retMsg=$ret['msg'];
			$transno=$ret['data']['info']['transno'];
// 		}elseif($payType=='e-card'){//东莞移动一卡通支付
// 			$user=A('Ecard')->getEcardUser($accountInfo['mobile']);
// 			$empCode=$user['data']['empCode'];//员工编号
// 			$posBalance=$user['data']['posBalance'];//员工餐补余额
// 			if(empty($empCode)){
// 				$this->makeLog('pay','error:1008,错误信息：未查询到该用户');
// 				$this->retError(1008,'支付失败，错误信息：未查询到该用户');
// 			}
// 			if($posBalance < $payment['money']){
// 				$this->makeLog('pay','error:1009,错误信息：账户余额不足！');
// 				$this->retError(1009,'支付失败，错误信息：账户余额不足！');
// 			}
				
// 			$return=A('Ecard')->ecardPay($paymentId,$payment['money'],$empCode,$accountInfo['mobile']);
// 			$ret=json_decode($return,true);
// 			$retCode=$ret['code'];
// 			$errCode=$ret['errCode'];
// 			$retMsg=$ret['msg'];
// 			$transno=$paymentId;
// 		}
		if($retCode==100){
			if($errCode==0){
				//支付成功，更新本地积分
				$modelDeposit = M('sysuser_user_deposit');
				if($payType=='point'){
					$modelDeposit->where('user_id ='.$payment['user_id'])->setDec('deposit',$payment['money']);
					$modelDeposit->where('user_id ='.$payment['user_id'])->setDec('balance',$payPoint);
					$modelDeposit->where('user_id ='.$payment['user_id'])->setDec('commonAmount',$payPoint);
				}
				//更新支付主表
				$paymentData = array(
						'cur_money'=>$payment['money'],
						'payed_point'=>$payment['money']*100,
						'status'=>"succ",
						//'pay_app_id'=>$payType,
						'payed_time'=>time(),
						'trade_no'=>$transno,
						'ls_trade_no'=>$transno,
						'result_memo'=>$return
				);
				
				if($payType=='e-card'){
					//e卡支付
					$paymentData['pay_name']='东莞移动E卡通';
					$paymentData['op_id']=$payment['user_id'];
					$paymentData['pay_account']=$empCode;//东莞移动员工编号
					$paymentData['pay_name']='东莞移动E卡通';
					$paymentData['currency']='CNY';
					$paymentData['tids']=$tids;
					$paymentData['pay_type']='e-card';
				}else{
					$paymentData['pay_type']='online';
				}
				$modelPayments = M('ectools_payments');
				$paymentRes = $modelPayments->where('payment_id ='.$paymentId)->save($paymentData);
				
				//更新支付副表
				$modelPaybill=M('ectools_trade_paybill');//支付子表
				$paybillSelectData =  $modelPaybill->where('payment_id ='.$paymentId)->select();
				$paybillData['status'] = 'succ';
				$paybillData['payed_time'] = time();
				$paybillData['modified_time'] = time();
				foreach ($paybillSelectData as $V){
					$paybillData['point'] = $V['payment']*100;
					$modelPaybill->where('paybill_id ='.$V['paybill_id'])->save($paybillData);
				}
				
				if($orderType == 'activity'){
					$conditionOrder = array('atid'=>array('in',$tids));
					$modelActivityOrder = M('company_activity_order');
					$aitemIds = $modelActivityOrder->where($conditionOrder)->getField('aitem_id',TRUE);
					$modelAItem = M('company_activity_item');
					$itemInfo=$modelAItem->where(array('aitem_id'=>array('in',$aitemIds)))->getField('item_info',TRUE);
					$orderList=array();
					foreach($itemInfo as $key=>$value){
						$orderList=array_merge($orderList,json_decode($value,TRUE));
					}
				}else if($orderType == 'gift'){
					$conditionOrder = array('tid'=>array('in',$tids));
					$giftTrade = M('gift_trade');
					$orderList = $giftTrade->where($conditionOrder)->select();
				}else{
					$conditionOrder = array('tid'=>array('in',$tids));
					$modelOrder = M('systrade_order');
					$orderList = $modelOrder->where($conditionOrder)->select();
				}
	
				//更新订单主表
				foreach ($paymentBillInfo as $kpb => $valpb){
					$trdeDate = array(
							'transno'=>$transno,
							'status'=>"WAIT_SELLER_SEND_GOODS",
							'payed_fee'=>$valpb['payment'],
							'payed_point'=>$valpb['payment']*100,
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
						$modelActivityTrade=M('company_activity_trade');
						$modelActivityTrade->where('atid ='.$valpb['tid'])->data($trdeDate)->save();
						$modelActivityOrder=M('company_activity_order');
						$modelActivityOrder->where('atid ='.$valpb['tid'])->save(array('status'=>'WAIT_SELLER_SEND_GOODS','pay_time'=>time(),'modified_time'=>time()));
						 
					}else{
						$trdeDate['trade_status']='WAIT_SELLER_SEND_GOODS';
						$this->makeLog('pay','tid:'.$valpb['tid'].',data:'.json_encode($trdeDate));
						$modelTrade=M('systrade_trade');
						$modelTrade->where('tid ='.$valpb['tid'])->data($trdeDate)->save();
						$modelOrder = M('systrade_order');
						$orderSelectData =  $modelOrder->where('tid ='.$valpb['tid'])->select();
						$orderData['status'] = 'WAIT_SELLER_SEND_GOODS';
						$orderData['pay_time'] = time();
						$orderData['modified_time'] = time();
						
						foreach ($orderSelectData as $V){
							$orderData['point'] = $V['payment']*100;
							$orderData['total_point'] = $V['payment']*100;
							$modelOrder->where('tid ='.$V['tid'])->save($orderData);
						}
						//$modelOrder->where('tid ='.$valpb['tid'])->save(array('status'=>'WAIT_SELLER_SEND_GOODS','pay_time'=>time(),'modified_time'=>time()));
					}
				}
	
				//更新库存
				if(!empty($orderList)){
					foreach ($orderList as $key => $value) {
						try{
							//支付减sku_store库存和预占库存
							$modelItemSkuStore = M('sysitem_sku_store');
							$modelItemSkuStore->where('sku_id='.$value['sku_id'])->setDec('freez',$value['num']);
							$modelItemSkuStore->where('sku_id='.$value['sku_id'])->setDec('store',$value['num']);
							//购买减item_store库存和预占库存
							$modelItemStore = M('sysitem_item_store');//商品库存
							$modelItemStore->where('item_id='.$value['item_id'])->setDec('freez',$value['num']);
							$modelItemStore->where('item_id='.$value['item_id'])->setDec('store',$value['num']);
							//购买增加item销量
							$modelItemCount=M('sysitem_item_count');
							$modelItemCount->where('item_id ='.$value['item_id'])->setInc('sold_quantity',$value['num']);
							//购买增加sku销量
							$modelItemSkuStore->where('sku_id ='.$value['sku_id'])->setInc('sold_quantity',$value['num']);
						}catch (\Exception $e){
							$this->makeLog('sysItemInfo',$e->getMessage());
						}
	
					}
				}
				 
				//日志表
				$logData =array('type'=>'expense','user_id'=>$payment['user_id'],'operator'=>$userAccountInfo['mobile'],'fee'=>$payment['money'],'message'=>'订单消费，支付单号：'.$paymentId,'logtime'=>time());
				$modelDepositLog = M('sysuser_user_deposit_log');
				$modelDepositLog->add($logData);
				$this->makeLog('pay','支付成功，支付信息：'.json_encode($paymentData));
				$ret=array(
						'result'=>100,
						'errcode'=>0,
						'msg'=>'succuss',
						'data'=>array('paymentId'=>$paymentId,'transno'=>$transno)
				);
				return json_encode($ret);
				//$this->retSuccess(array('paymentId'=>$paymentId,'transno'=>$transno),'succuss');
				
			}else{
				
				$this->makeLog('pay','error:1010,错误信息：'.$retMsg);
				$this->retError(1010,'支付失败，错误信息：'.$retMsg);
			}
		}else{
			if(empty($retMsg)){
				$retMsg='网络繁忙，请稍后再试';
			}
			$this->makeLog('pay','error:1000,错误信息：'.$retMsg);
			$this->retError(1000,'支付失败，错误信息：'.$retMsg);
		}
	}
	
	//积分支付接口 赵尊杰 2016-09-24
	public function pointPay($paymentId,$mobile,$payFee){
		$url = C('API').'mallPoints/payOrder';
		$sign=md5('orderno='.$paymentId.'&phoneNum='.$mobile.'&pointsAmount='.$payFee.'&pointsType=1'.C('API_KEY'));
		$param=array(
				'phoneNum'=>$mobile,
				'orderno'=>$paymentId,
				'pointsAmount'=>$payFee,
				'pointsType'=>1,
				'sign'=>$sign
		);
		$this->makeLog('pointPay','url:'.$url.' param:'.json_encode($param));
		$return=$this->requestPost($url,$param);
		$this->makeLog('pointPay','param:'.json_encode($param).' return:'.$return);
		return $return;
	}

}