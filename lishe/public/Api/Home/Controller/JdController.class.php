<?php
/**
  +------------------------------------------------------------------------------
 * JdController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: JdController.class.php v001 2016-06-02
 * @description 京东接口封装
  +------------------------------------------------------------------------------
 */

namespace Home\Controller;
class JdController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelItem=M('sysitem_item');
		$this->modelPayment=M('ectools_payments');
		$this->modelPaybill=M('ectools_trade_paybill');
		$this->modelSyncTrade=M('systrade_sync_trade');
		$this->modelActivityTrade = M('company_activity_trade');
		$this->modelActivityOrder = M('company_activity_order');
	}
	
	// 选择不同地区时调用京东接口判断库存 20160613 start
	public function checkJdStock(){
		header("Access-Control-Allow-Origin:*"); //*号表示所有域名都可以访问  
		header("Access-Control-Allow-Method:POST,GET");  
		$item_id = I('item_id','','intval'); // 得到库存的ids
		$jd_ids = I('jd_ids','','trim');
		$num = I('num',1,'intval');
		if(empty($num)){
			$num=1;
		}
		$where['item_id'] = $item_id;
		$res = $this->modelItem ->field('jd_sku')->where($where) -> find();		 
		if($res['jd_sku']>0){		
			$url=C('API_AOSERVER').'jd/product/checkstock';	
			$data='{"skuNums":[{"skuId":'.$res['jd_sku'].',"num":'.$num.'}],"area":"'.$jd_ids.'"}';
            $result = $this->requestJdPost($url,$data);        
            $retArr = json_decode($result,true);
            if($retArr['data'][0]['stockStateId']==33 or $retArr['data'][0]['stockStateId']==39 or $retArr['data'][0]['stockStateId']==40){
				$url=C('API_AOSERVER').'jd/product/checkRep';
				$data='{"skuIds":"'.$res['jd_sku'].'"}';
				$result=$this->requestJdPost($url,$data);
				$this->makeLog('checkJdStock','url:'.$url.' param:'.$data.' return:'.$result);
	            $retArr=json_decode($result,true);
	            if($retArr['code']==100){
	            	if($retArr['data'][0]['saleState']==0 or $retArr['data'][0]['isCanVAT']==0){
	            		$stock=array('status'=>34,'msg'=>'无货');
	            	}else{
	            		$url=C('API_AOSERVER').'jd/product/checkAreaLimit';
						$areas=explode('_',$jd_ids);
						$data='{"skuIds":"'.$res['jd_sku'].'","province":'.intval($areas[0]).',"city":'.intval($areas[1]).',"county":'.intval($areas[2]).',"town":'.intval($areas[3]).'}';
						$result=$this->requestJdPost($url,$data);
			            $retArr=json_decode($result,true);
			            if($retArr['code']==100){
			            	if($retArr['data'][0]['isAreaRestrict']==true){
			            		$stock=array('status'=>34,'msg'=>'无货');
			            	}else{
								$stock=array('status'=>33,'msg'=>'有货');
							}
			            }						
					}
	            }
			}else{
				$stock=array('status'=>34,'msg'=>'无货');		
			}
		}else{
			$stock=array('status'=>33,'msg'=>'有货');
		}		
		echo $stock['status'];
	}
	 
	//新版京东订单接口，第一部先预占库存，第二部确认出库并扣款 赵尊杰 2016-09-24 
	public function syncJdReserved($tid,$paymentId=0,$invoiceType=2){
		$condition=array(
			'tid'=>$tid
		);
		$trade=$this->modelTrade->field('tid,pay_time,payed_fee,buyer_area,receiver_name,receiver_address,receiver_mobile,receiver_phone,buyer_message,payment')->where($condition)->find();
		if(empty($trade['tid'])){
			return array('code'=>-1001,'msg'=>'unfinded trade');
		}
		$trade['payed_fee'] = intval($trade['payed_fee']);
		//如果订单存在并且未支付
		if(empty($trade['pay_time']) && empty($trade['payed_fee'])){
			list($province,$city,$county,$town)=explode('/',$trade['buyer_area']);
			$order=$this->modelOrder->field('item_id,num,price,cost_price')->where('disabled=0 AND tid='.$trade['tid'])->select();
			$totalCostPrice=0;
			foreach($order as $key=>$value){
				$itemId[]=$value['item_id'];
				$num[$value['item_id']]=$value['num'];
				$price[$value['item_id']]=$value['price'];
				$costPrice[$value['item_id']]=$value['cost_price'];
				$totalCostPrice+=$value['cost_price']*$value['num'];
			}
			$jdSkuArr=$this->modelItem->field('jd_sku,item_id')->where('item_id IN ('.implode(',',$itemId).')')->select();
			foreach($jdSkuArr as $key=>$value){
				$sku[]=array(
					'id'=>$value['jd_sku'],
					'num'=>$num[$value['item_id']],
					'price'=>round($costPrice[$value['item_id']],2),
					'salePrice'=>round($price[$value['item_id']],2),
				);
			}
			$param = array(
				"shopOrderNo"=>$trade['tid'],//第三方的订单单号
				"sku"=>$sku,
				"name"=>$trade['receiver_name'],//收货人
				"province"=>intval($province),//一级地址
				"city"=>intval($city),//2级地址
				"county"=>intval($county),//3级地址
				"town"=>intval($town),//4级地址
				"address"=>$trade['receiver_address'],//详细地址
				"zip"=>'100000',//邮编
				"phone"=>$trade['receiver_phone'],//座机号
				"mobile"=>$trade['receiver_mobile'],//手机号
				"email"=>'severs@lishe.cn',//必选 //邮箱
				"remark"=>$trade['buyer_message'],//备注
				"invoiceType"=>$invoiceType,//发票类型，1-普票，2-增值税发票
				"submitState"=>0,//是否预占库存，0是预占库存（需要调用确认订单接口），1是不预占库存
				"supplierId"=>'jd',
				"totalPrice"=>$totalCostPrice,//订单总结算价
				"totalSalePrice"=>$trade['payment'],//订单总销售价
				"type"=>C("TEST_SETTING"),//调用类型  type： 0：test测试，1或空：pro生产
				"resultType"=>0
			);
			$url=C('API_AOSERVER').C('JD_ORDER_URL');
			$result=$this->requestJdPost($url,json_encode($param));
			$this->makeLog('syncJdReserved','url:'.$url.' param:'.json_encode($param).' return:'.$result);
        	$ret=json_decode($result,true);        	
        	
        	if(empty($paymentId)){
				$paybill=$this->modelPaybill->field('payment_id')->where('tid='.$trade['tid'])->find();
        		$paymentId=$paybill['payment_id'];
			}
			
        	//记录日志
        	$log=array(
    			'payment_id'=>$paymentId,
    			'tid'=>$trade['tid'],
    			'log_type'=>'synsJdOrder',
    			'code'=>$ret['code'],
    			'detail'=>$result,
    			'modified_time'=>time()
    		);
			
        	if($ret['code']==100){
        		if($ret['errCode']==0){			
	        		$data=array(
	        			'payment_id'=>$paymentId,
	        			'tid'=>$trade['tid'],
	        			'sync_order_id'=>$ret['data']['jdOrderId'],
	        			'modified_time'=>time()
	        		);
	        		$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'success','sync_tid'=>$ret['data']['jdOrderId'],'sync_memo'=>$result));
					$this->modelSyncTrade->add($data);
					
					$log['sync_order_id']=$ret['data']['jdOrderId'];
					$this->makeSqlLog($log);
					return array('code'=>$ret['data']['jdOrderId'],'msg'=>'success');
				}else{
					$log['code']=$ret['errCode'];
					$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'failure','sync_memo'=>$result));
					$this->makeSqlLog($log);
					return array('code'=>-1002,'msg'=>$ret['msg'].$ret['data']);
				}
			}else{
				$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'failure','sync_memo'=>$result));
				$this->makeSqlLog($log);
				return array('code'=>-1003,'msg'=>$ret['msg'].$ret['data']);
			}
		}else{
			$this->makeSqlLog($log);
			return array('code'=>-1000,'msg'=>'订单已支付');
		}
	}
	
	//京东订单同步接口，只限活动使用
	//注意目前一个payment_id和tid，是一对一关系
	public function syncActivityJd($paymentId = -1, $tid = -1){
		$ret = array('code'=>-1, 'msg'=>'unkown message');
		$this->makeLog('syncActivityJd',"mark payment_id={$paymentId} tid={$tid}");
		if(!is_numeric($paymentId) || $paymentId < 1){
			//订单id错误
			$this->makeLog('syncActivityJd',"error(1001) msg:paymentId参数错误 payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1001;
			$ret['msg'] = "参数paymenId错误";
			return $ret;
		}
		if(!is_numeric($tid) || $tid < 1){
			$this->makeLog('syncActivityJd',"error(1002) msg:tid错误 payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1002;
			$ret['msg'] = "参数tid错误";
			return $ret;
		}
		//payment检索条件
		$where = array();
		$where['payment_id'] = $paymentId;
		$where['tid'] = $tid;
		//检索支付单
		$payment = $this->modelPaybill->field('payment_id, tid, status')->where($where)->find();
		if(empty($payment)){
			//不存在支付信息 TODO
			$this->makeLog('syncActivityJd',"error(1003) msg:未找到支付记录 payment_id={$paymentId} tid={$tid}");
			$$ret['code'] = 1003;
			$ret['msg'] = "京东支付记录为空";
			return $ret;
		}
		//判断否支付成功
		if('succ' != $payment['status']){
			//未成功支付 TODO
			$this->makeLog('syncActivityJd',"error(1004) msg:未支付订单 payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1004;
			$ret['msg'] = "京东订单未支付";
			return $ret;
		}
		//查询该订单的所有数据
		$jdShopId = C('JD_SHOP_ID');
		$where = array();
		$where['atid'] = $tid;
		$where['shop_id'] = $jdShopId;
		$orderList = $this->modelActivityOrder
							->field('order_id, item_id, sku_id, price, cost_price, num')
							->where($where)
							->select();
		if(empty($orderList)){
			$this->makeLog('syncActivityJd',"errro(1005) msg:没有检索到需要同步的订单  payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1005;
			$ret['msg'] = "没有检索到京东同步订单";
			return $ret;
		}
		
		$itemIdArr = array(); //item_id集合
		$orderSkuList = array(); //订单sku集合
		$totalCostPrice = 0;
		//遍历order表，查询该订单的商品 jd_sku, num, price, salePrice, totalPrice(订单总结算价), totalSalePrice(订单总销售价)
		foreach ($orderList as $order){
			if($order['sync_status'] == 1){
				$this->makeLog('syncActivityJd',"notice(1011) msg:此订单已同步，移除 order_id={$order['order_id']} payment_id={$paymentId} tid={$tid}");
				continue;
			}
			$itemIdArr[] = $order['item_id']; 
			//添加订单sku数据
			$tmpSku = array();
			$tmpSku['item_id'] = $order['item_id'];;
			$tmpSku['num'] = $order['num'];
			$tmpSku['price'] = $order['cost_price'];
			$tmpSku['salePrice'] = $order['price'];
			$orderSkuList[] = $tmpSku;
			//计算总成本价
			$totalCostPrice += $order['cost_price']*$order['num'];
		}
		if(empty($orderSkuList)){
			$this->makeLog('syncActivityJd',"errro(1010) msg:没有符合状态的同步订单  payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1010;
			$ret['msg'] = "没有符合状态的同步订单";
			return $ret;
		}
		
		//查询jd_sku
		$where = array();
		$where['item_id'] = array('in',$itemIdArr);
		$itemList = $this->modelItem->field('item_id, jd_sku')->where($where)->select();
		$jdSkuMap = array();
		foreach ($itemList as $item){
			$jdSkuMap[$item['item_id']] = $item['jd_sku']; //映射 item_id和jd_sku关系
		}
		
		//将 jd_sku 添加入$orderSkuList集合
		foreach ($orderSkuList as &$sku){
			$sku['id'] = $jdSkuMap[$sku['item_id']];
			unset($sku['item_id']);//释放orderSkuList中的item_id列
		}
		
		//检索trade表
		$field = 'payment,receiver_name,receiver_state,receiver_city,receiver_district,receiver_address,
					receiver_mobile,receiver_phone,buyer_area,buyer_message';
		$where = array();
		$where['atid'] = $tid;
		$trade = $this->modelActivityTrade->field($field)->where($where)->find();
		
		list($province, $city, $county, $town) = explode('/', $trade['buyer_area']);
		
		$rqParam = array(
			"shopOrderNo"=> $tid,//第三方的订单单号
			"sku"		 => $orderSkuList,
			"name"		 => $trade['receiver_name'],//收货人
			"province"	 => intval($province),//一级地址
			"city"		 => intval($city),//2级地址
			"county"	 => intval($county),//3级地址
			"town"		 => intval($town),//4级地址
			"address"	 => $trade['receiver_address'],//详细地址
			"zip"		 => '100000',//邮编
			"phone"		 => $trade['receiver_phone'],//座机号
			"mobile"	 => $trade['receiver_mobile'],//手机号
			"email"		 => 'severs@lishe.cn',//必选 //邮箱
			"remark"	 => $trade['buyer_message'],//备注
			"invoiceType"=> 2,//发票类型，1-普票，2-增值税发票
			"submitState"=> 1,//是否预占库存，0是预占库存（需要调用确认订单接口），1是不预占库存
			"supplierId" =>'jd',
			"totalPrice" => $totalCostPrice,//订单总结算价
			"totalSalePrice"=>$trade['payment'],//订单总销售价
			"type"		 => C("TEST_SETTING"),//调用类型  type： 0：test测试，1或空：pro生产
			"resultType" => 0
		);
		$url = C('API_AOSERVER').C('JD_ORDER_URL');
		$rqParam = json_encode($rqParam);
		$result = $this->requestJdPost($url, $rqParam);
		$this->makeLog('syncActivityJd',"payment_id={$paymentId} tid={$tid} url:{$url} param:{$rqParam} return:{$result}");
		$retArr = json_decode($result, true);
		//记录日志
		$log=array(
			'payment_id'=>$paymentId,
			'tid'=>$tid,
			'log_type'=>'synsActJdOrder',
			'code'=>$retArr['code'],
			'detail'=>$result,
			'modified_time'=>time()
		);
		
		if($retArr['code'] != 100){
			$this->makeLog('syncActivityJd',"error(1007) msg:接口调用失败 payment_id={$paymentId} tid={$tid} result=$result");
// 			$data = array(
// 				'sync_trade_status'=>'failure',
// 				'sync_memo'=>$result,
// 			);
// 			$this->modelActivityTrade->where('tid='.$tid)->save($data);
			$this->makeSqlLog($log);
			$ret['code'] = 1007;
			$ret['msg'] = $result;
			return $ret;
		}
		
		if($retArr['errCode']==0){
			$this->makeLog('syncActivityJd',"success msg:同步京东订单成功  payment_id={$paymentId} atid={$tid} result=$result");
			//写日志
			$log['sync_order_id'] = $retArr['data']['jdOrderId'];
			$this->makeSqlLog($log);
			//保存京东订单号
			$data=array(
				'payment_id'=>$paymentId,
				'tid'=>$tid,
				'sync_order_id'=>$retArr['data']['jdOrderId'],
				'modified_time'=>time()
			);
			$this->modelSyncTrade->add($data);
			//修改订单状态
			$where = array();
			$where['atid'] = $tid;
			//$this->modelActivityOrder->where($where)->setField('sync_status',1);
			//修改trade
			$this->modelActivityTrade->where($where)->save(array('status'=>'IN_STOCK'));
			//修改order
			$where['shop_id'] = $jdShopId;
			$where['disabled'] = 0;
			$this->modelActivityOrder->where($where)->save(array('status'=>'IN_STOCK','sync_status'=>1));
			
// 			$data = array(
// 				'sync_trade_status'=>'success',
// 				'sync_tid'=>$retArr['data']['jdOrderId'],
// 				'sync_memo'=>$result,
// 			);
// 			$this->modelActivityTrade->where('atid='.$tid)->save($data);
			
			$ret['code'] = 100;
			$ret['msg'] = 'success';
			return $ret;
		}else{
			$this->makeLog('syncActivityJd',"error(1009) msg:接口调用失败 payment_id={$paymentId} atid={$tid} result=$result");
			
			$log['code'] = $retArr['errCode'];
			$this->makeSqlLog($log);
			
// 			$data = array(
// 				'sync_trade_status'=>'failure',
// 				'sync_memo'=>$result,
// 			);
// 			$this->modelActivityTrade->where('atid='.$tid)->save($data);
			
			$ret['code'] = 1009;
			$ret['msg'] = $retArr['msg'];
			return $ret;
		}
		
	}
	
	//新版京东订单预占库存确认接口，确认出库并扣款 赵尊杰 2016-09-24
	public function syncJdConfirm($tid){
		$condition=array(
			'tid'=>$tid
		);
		$trade=$this->modelTrade->field('tid,sync_tid,pay_time,payed_fee')->where($condition)->find();
		if(empty($trade['tid'])){
			return array('code'=>-1001,'msg'=>'unfinded trade');
		}
		//如果订单存在并且已支付
		if(!empty($trade['pay_time']) && !empty($trade['payed_fee'])){
			if(empty($trade['tid'])){
				return array('code'=>-1001,'msg'=>'unfinded trade');
			}
			if(empty($trade['sync_tid'])){
				$syncTrade=$this->modelSyncTrade->field('sync_order_id,payment_id')->where('sync_order_id>0 AND tid='.$tid)->find();
				if(empty($syncTrade['sync_order_id'])){
					return array('code'=>-1002,'msg'=>'unfinded tradeNo');
				}
				$trade['sync_tid']=$syncTrade['sync_order_id'];
				$paymentId=$syncTrade['payment_id'];
				$this->modelTrade->where($condition)->save(array('sync_tid'=>$trade['sync_tid']));
			}
			$param=array('jdOrderId'=>$trade['sync_tid'],'type'=>C("TEST_SETTING"));
			$url=C('API_AOSERVER').'jd/order/occupyStockConfirm';
			$result=$this->requestJdPost($url,json_encode($param));
			$this->makeLog('syncJdConfirm','url:'.$url.' param:'.json_encode($param).' return:'.$result);
	        $ret=json_decode($result,true);
	        
	        //记录日志
	    	if(empty($paymentId)){
				$paybill=$this->modelPaybill->field('payment_id')->where($condition)->find();
	    		$paymentId=$paybill['payment_id'];
			}
				
	        $log=array(
				'payment_id'=>$paymentId,
				'tid'=>$trade['tid'],
				'sync_order_id'=>$trade['sync_tid'],
				'log_type'=>'synsJdConfirm',
				'code'=>$ret['code'],
				'detail'=>$result,
				'modified_time'=>time()
			);
			if($ret['code']==100){
	    		if($ret['errCode']==0){
	    			//记录确认状态，并置订单状态为备货中
	        		$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_confirm'=>'CONFIRMED','sync_memo'=>$result,'status'=>'IN_STOCK'));
	        		$this->modelOrder->where('tid='.$trade['tid'])->save(array('status'=>'IN_STOCK'));
					$this->modelSyncTrade->where('tid='.$trade['tid'])->save(array('sync_confirm'=>'CONFIRMED'));
					$this->makeSqlLog($log);
					return array('code'=>$trade['sync_tid'],'msg'=>'success');
				}else{
					$log['code']=$ret['errCode'];
					$this->makeSqlLog($log);
					return array('code'=>-1003,'msg'=>$ret['msg'].$ret['data']);
				}
			}else{
				$this->makeSqlLog($log);
				return array('code'=>-1004,'msg'=>$ret['msg'].$ret['data']);
			}
		}else{
			$this->makeSqlLog($log);
			return array('code'=>-1000,'msg'=>'订单未支付');
		}      
	}
	
	//新版京东订单预占库存取消接口，取消出库 赵尊杰 2016-09-24
	public function syncJdCancel($tid){		
		$condition=array(
			'tid'=>$tid
		);
		$trade=$this->modelTrade->field('tid,sync_tid')->where($condition)->find();
		if(empty($trade['tid'])){
			return array('code'=>-1001,'msg'=>'unfinded trade');
		}
		if(empty($trade['sync_tid'])){
			$syncTrade=$this->modelSyncTrade->field('sync_order_id,payment_id')->where('sync_order_id>0 AND tid='.$tid)->find();
			if(empty($syncTrade['sync_order_id'])){
				return array('code'=>-1002,'msg'=>'unfinded tradeNo');
			}
			$trade['sync_tid']=$syncTrade['sync_order_id'];
			$paymentId=$syncTrade['payment_id'];
			$this->modelTrade->where($condition)->save(array('sync_tid'=>$trade['sync_tid']));
		}
		$param=array('jdOrderId'=>$trade['sync_tid']);
		$url=C('API_AOSERVER').'jd/order/cancelJdOrder';
		$result=$this->requestJdPost($url,json_encode($param));
		$this->makeLog('syncJdCancel','url:'.$url.' param:'.json_encode($param).' return:'.$result);
        $ret=json_decode($result,true);
        
        //记录日志
    	if(empty($paymentId)){
			$paybill=$this->modelPaybill->field('payment_id')->where($condition)->find();
    		$paymentId=$paybill['payment_id'];
		}
			
        $log=array(
			'payment_id'=>$paymentId,
			'tid'=>$trade['tid'],
			'sync_order_id'=>$trade['sync_tid'],
			'log_type'=>'synsJdCancel',
			'code'=>$ret['code'],
			'detail'=>$result,
			'modified_time'=>time()
		);
		if($ret['code']==100){
    		if($ret['errCode']==0){
        		$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_confirm'=>'CANCEL'));
				$this->modelSyncTrade->where('tid='.$trade['tid'])->save(array('sync_confirm'=>'CANCEL'));
				$this->makeSqlLog($log);
				return array('code'=>$trade['sync_tid'],'msg'=>'success');
			}else{
				$log['code']=$ret['errCode'];
				$this->makeSqlLog($log);
				return array('code'=>-1003,'msg'=>$ret['msg'].$ret['data']);
			}
		}else{
			$this->makeSqlLog($log);
			return array('code'=>-1000,'msg'=>$ret['msg'].$ret['data']);
		}
	}
	
	//旧版同步订单到京东接口封装，下订单与扣款同步完成，后面启用预占库存接口 赵尊杰 2016-06-15 
	//$invoiceType 发票类型，1-普票，2-增值税发票
	public function syncJdOrder($tid,$paymentId=0,$invoiceType=2){
		$condition=array(
			'tid'=>array('in',''.implode(',',$tid).'')
		);
		$trade=$this->modelTrade->field('tid,pay_time,payed_fee,buyer_area,receiver_name,receiver_address,receiver_mobile,receiver_phone,buyer_message,payment')->where($condition)->find();
		
		if(empty($trade['tid'])){
			return array('code'=>-1001,'msg'=>'unfinded trade');
		}
		//如果订单存在并且已支付
		if(!empty($trade['tid']) && !empty($trade['pay_time']) && !empty($trade['payed_fee'])){
			list($province,$city,$county,$town)=explode('/',$trade['buyer_area']);
			$order=$this->modelOrder->field('item_id,num,price,cost_price')->where('disabled=0 AND tid='.$trade['tid'])->select();
			$totalCostPrice=0;
			foreach($order as $key=>$value){
				$itemId[]=$value['item_id'];
				$num[$value['item_id']]=$value['num'];
				$price[$value['item_id']]=$value['price'];
				$costPrice[$value['item_id']]=$value['cost_price'];
				$totalCostPrice+=$value['cost_price']*$value['num'];
			}
			$jdSkuArr=$this->modelItem->field('jd_sku,item_id')->where('item_id IN ('.implode(',',$itemId).')')->select();
			foreach($jdSkuArr as $key=>$value){
				$sku[]=array(
					'id'=>$value['jd_sku'],
					'num'=>$num[$value['item_id']],
					'price'=>$costPrice[$value['item_id']],
					'salePrice'=>$price[$value['item_id']],
				);
			}
			
			$param = array(
				"shopOrderNo"=>$trade['tid'],//第三方的订单单号
				"sku"=>$sku,
				"name"=>$trade['receiver_name'],//收货人
				"province"=>intval($province),//一级地址
				"city"=>intval($city),//2级地址
				"county"=>intval($county),//3级地址
				"town"=>intval($town),//4级地址
				"address"=>$trade['receiver_address'],//详细地址
				"zip"=>'100000',//邮编
				"phone"=>$trade['receiver_phone'],//座机号
				"mobile"=>$trade['receiver_mobile'],//手机号
				"email"=>'severs@lishe.cn',//必选 //邮箱
				"remark"=>$trade['buyer_message'],//备注
				"invoiceType"=>$invoiceType,//发票类型，1-普票，2-增值税发票
				"submitState"=>1,//是否预占库存，0是预占库存（需要调用确认订单接口），1是不预占库存
				"supplierId"=>'jd',
				"totalPrice"=>$totalCostPrice,//订单总结算价
				"totalSalePrice"=>$trade['payment'],//订单总销售价
				"type"=>C("TEST_SETTING"),//调用类型  type： 0：test测试，1或空：pro生产
				"resultType"=>0
			);
			//京东下单地址
			$url=C('API_AOSERVER').C('JD_ORDER_URL');//'jd/order/uniteSubmitNew';
			$result=$this->requestJdPost($url,json_encode($param));
			$this->makeLog('syncJdOrder','url:'.$url.' param:'.json_encode($param).' return:'.$result);
        	$ret=json_decode($result,true);
        	
        	//记录日志
        	if(empty($paymentId)){
				$paybill=$this->modelPaybill->field('payment_id')->where('tid='.$trade['tid'])->find();
        		$paymentId=$paybill['payment_id'];
			}
			
        	if($ret['code']==100){
        		if($ret['errCode']==0){
					$data=array(
	        			'payment_id'=>$paymentId,
	        			'tid'=>$trade['tid'],
	        			'sync_order_id'=>intval($ret['data']['jdOrderId']),
	        			'modified_time'=>time()
	        		);
	        		$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'success','sync_pay_status'=>'success','sync_memo'=>$result,'status'=>'IN_STOCK'));
	        		$this->modelOrder->where('tid='.$trade['tid'])->save(array('status'=>'IN_STOCK'));
					$this->modelSyncTrade->add($data);
					return array('code'=>$ret['data']['jdOrderId'],'msg'=>'success');
				}else{
					$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'failure','sync_pay_status'=>'failure','sync_memo'=>$result));
					return array('code'=>-1002,'msg'=>$ret['msg'].$ret['data']);
				}   		
			}else{
				$this->modelTrade->where('tid='.$trade['tid'])->save(array('sync_trade_status'=>'failure','sync_pay_status'=>'failure','sync_memo'=>$result));
				return array('code'=>-1002,'msg'=>$ret['msg'].$ret['data']);
			}						
		}else{
			return array('code'=>-1000,'msg'=>'订单未支付');
		}
	}
	
	
	//查询京东订单详情 赵尊杰 2016-06-23
	//$orderId 京东订单号
	public function getJdOrderInfo($orderId){
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/queryJdOrderInfo','{"jdOrderId":'.$orderId.'}');
		return json_decode($result,true);
	}
	
	//查询京东订单物流 赵尊杰 2016-06-23
	public function getJdExpress($orderId){
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/queryOrderTrack','{"jdOrderId":'.$orderId.'}');
		return json_decode($result,true);
	}
	
	public function getJdAfterSale(){
		$orderId=I('get.orderId');//京东子订单号，即物流单号，不是京东订单号
		$sku=I('get.sku');
		$param=array(
			'jdOrderId'=>$orderId,
			'skuId'=>$sku
		);
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/afterSale/getCustomerExpectComp',json_encode($param));
        $ret=json_decode($result,true);
        echo "<pre>";	
    	print_r($ret);
    	echo "</pre>";
	}
	
	//通过商城订单号查询京东订单号
	public function getJdOrderId(){
		$tid=I('get.tid');
		if(empty($tid)){
			return array('code'=>-1000,'msg'=>'没有订单号');
		}
		$param=array(
			'thirdOrder'=>$tid
		);
		$result=$this->requestJdPost(C('API_AOSERVER').'jd/order/queryByThirdOrder',json_encode($param));
        //return json_decode($result,true);
        echo "<pre>";	
    	print_r(json_decode($result,true));
    	echo "</pre>";
	}
	
	public function addJdAfterSale(){
		$param=array(
			'jdOrderId'=>'',//京东子订单号，即物流单号，不是京东订单号
			'questionDesc'=>'',
			'questionDesc'=>'',//问题描述，必填项
		);
	}
}