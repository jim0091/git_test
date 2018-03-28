<?php  
namespace Home\Controller;
use Think\Controller;
/**
 * 任务控制器
 */
class TaskController extends Controller{
	/**
	 *混合支付，积分支付成功未支付现金，退回积分 
	 * @param $paymentId 手动立刻退回用户积分
	 */
	public function __construct(){
		$account=array(
    		'uid' => 1,
    		'realName' => '系统服务',
    		'userName' => '系统服务',
    		'role_id' => 0,
		);		
		session('adminAccount',$account);
		cookie('adminAccount',json_encode($account));
	}
	public function mixedPayPointRet(){
		$paymentId = I('paymentId', '', 'trim,strip_tags,stripslashes');
		$ectoolsPayments = M('ectools_payments');
		$ectoolsBill = M('ectools_trade_paybill');
		$pointReturn = M('mixed_payment_point_return');
		$ret = array('code'=>0,'msg'=>'Unknown Error!');
		$map = array(
			'cash_fee' => array('gt', 0),
			'point_fee' => array('gt', 0),
			'payed_cash' => 0,
			'payed_point' => array('gt', 0),
		);
		if(!empty($paymentId) && is_numeric($paymentId)){
			//传递支付单可立即退回积分
			$map['payment_id'] = $paymentId;
		}else{
			//计划任务满24小时的才可退
			$time = time() - 24*60*60;
			$map['payed_time'] = array('elt', $time);
		}
		//支付单信息
		$paymentInfo = $ectoolsPayments->where($map)->getField('payment_id,payed_point,ls_trade_no,user_id');
		$retPaymentIds = array_keys($paymentInfo);
		$this->makeLog('returnPoint','符合可退回积分的支付单:'.implode(',', $retPaymentIds));
		if(empty($paymentInfo)){
			$ret['msg'] = '该支付单不支持退回积分!';
			$this->ajaxReturn($ret);
		}	
		//查找积分退回筛选掉已退回积分的支付单号
		$map = array(
			'payment_id' => array('in', $retPaymentIds)
		);
		$returnedIds = $pointReturn->where($map)->getField('payment_id', TRUE);
		
		$url=C('API').'mallPoints/refundOrderNew';
		$succPaymentIds = array();
		$failPaymentIds = array();
		foreach($paymentInfo as $key=>$val){
			if(in_array($val['payment_id'], $returnedIds)){
				//已退过积分
				continue;
			}
	        $sign=md5('orderno='.$val['payment_id'].'&refundAmount='.$val['payed_point'].'&transno='.$val['ls_trade_no'].C('API_KEY'));
			$param=array(
	        	'orderno'      => $val['payment_id'],
	        	'refundAmount' => $val['payed_point'],
	        	'transno'      => $val['ls_trade_no'],
	        	'sign'         => $sign
	        );		
       		$this->makeLog('returnPoint','url:'.$url.' param:'.json_encode($param));
			$return=$this->requestPost($url,$param);
    		$this->makeLog('returnPoint','param:'.json_encode($param).' return:'.$return);
			$return=json_decode(trim($return,chr(239).chr(187).chr(191)),true);
			if($return['result']==100 && $return['errcode']==0){
				//积分退回成功
				$succPaymentIds[] = $val['payment_id'];
    			$this->makeLog('returnPoint',"succ:{$val['payment_id']}");
				//积分退回记录
				$record = array(
					'payment_id' => $val['payment_id'],
					'return_point' => $val['payed_point']
				);
				$pointReturn->data($record)->add();
				//积分退回记录（积分详情表）
				$pointDeatils = array(
					'type' => 'add',
					'user_id' => $val['user_id'],
					'operator' => '系统',
					'fee' => $val['payed_point']/100,
					'logtime' => time()
				); 
				if($paymentId){
					$pointDeatils['message'] = "支付单:{$val['payment_id']}取消订单积分退回";
				}else{
					$pointDeatils['message']= "支付单:{$val['payment_id']}未完全支付成功，系统自动退回";
				}
				M('sysuser_user_deposit_log')->data($pointDeatils)->add();				
			}else{
				//积分退回失败
				$failPaymentIds[] = $val['payment_id'];
    			$this->makeLog('returnPoint',"fail:{$val['payment_id']}");
			}	
		}
		if(empty($succPaymentIds)){
			$ret['msg'] = '积分退回失败!';
			$this->ajaxReturn($ret);
		}
		M()->startTrans();
		try{
			//积分退回成功的支付单一些支付和订单的数据改回来
			$map = array(
				'payment_id' => array('in', $succPaymentIds),
			);
			//支付表
			$chaPayData = array(
				'payed_time'  => 0,
				'payed_point' => 0
			);
			$chaPayments = $ectoolsPayments->where($map)->save($chaPayData);
			//支付子表
			$chaTrData = array(
				'payed_time' => 0,
			);		
			$chaTradeBill = $ectoolsBill->where($map)->save($chaTrData);
			//取出需要改的订单号
			$tids = $ectoolsBill->where($map)->getField('tid', TRUE);
			if(empty($tids)){
				$ret['msg'] = '无法获取订单信息!';
				$this->ajaxReturn($ret);			
			}
			//订单表
			$map = array(
				'tid' => array('in', $tids)
			);
			$tradeData = array(
				'payed_fee'   => 0,
				'payed_point' => 0,
				'pay_time'    => 0
			);
			$retTrade = M('systrade_trade')->where($map)->save($tradeData);
			//订单子表
			$orderData = array(
				'pay_time' => 0		
			);
			$retOrder = M('systrade_order')->where($map)->save($orderData);
		}catch(\Exception $e){
			$this->makeLog('returnSuccChaFail','积分退回成功,数据更改失败，支付单号:'.implode(',', $succPaymentIds));
			$this->makeLog('returnSuccChaFail','错误信息:'.$e->getMessage());
			M()->rollback();
			$ret['msg'] = '积分返还失败!';
			$this->ajaxReturn($ret);			
		}
		M()->commit();
		//手动掉时
		if($paymentId && in_array($paymentId, $succPaymentIds)){
			$ret['code'] = 1;
			$ret['msg'] = '积分返还成功!';
			$this->ajaxReturn($ret);			
		}
		
	}
	/**
	 * 写日志
	 */
	public function makeLog($type='',$data=''){
		if(empty($type)){
			return false;
		}
		$this->creatDir(C('DIR_LOG').$type."/");
		@file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
	}  
	//创建目录
	private function creatDir($fileName){
		$dirArray = explode('/',$fileName);
		array_pop($dirArray);
		if(empty($dirArray)){
			return false;
		}
		if(is_dir($dirArray)){
			//目录已存在
			return false;
		}
		foreach($dirArray as $val){
			$dir .= $val.'/';
			$oldumask = umask(0);
			if(!is_dir($dir)){
				mkdir($dir,0777);
			}
			chmod($dir,0777);
			umask($oldumask);
		}
		return true;
	}
	//模拟提交
	public function requestPost($url='', $data=array()) {
        if(empty($url) || empty($data)){
            return false;
        }
        $o="";
        foreach($data as $k=>$v){
            $o.="$k=".$v."&";
        }
        $param=substr($o,0,-1);
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
/**
 * 计划任务
 * 拼团和预售推单顺丰或供应商
 */
	public function groupOrderPush(){
		$pIds = I('paymentIds');
		$modelPaybill = M('ectools_trade_paybill');
		if(!empty($pIds)){
			//手动推
			$paymentIds = explode(',', $pIds);
			$map = array(
				'payment_id' => array('in', $paymentIds)
			);
			$paymentInfo = $modelPaybill->where($map)->getField('tid,payment_id');
			$tids = array();
			$paymentIds = array();
			foreach($paymentInfo as $key=>$val){
				$tids[] = $key;
				$paymentIds[] = $val; 
			}
		}else{
			//此刻符合条件的团购或预售的activity_config_id
			$modelAcate = M('company_activity_category');
			//假设计划任务一天执行一次
			//可执行的团购
			$endTime = time()-60*60*24;
			$map = array(
				'start_time' => array('egt', time()),
				'end_time'   => array('elt', $endTime),
				'type'       => 4
			); 
			$aGroupInfo = $modelAcate->where($map)->getField('activity_config_id,achieve_num');
			//可执行的预售
			$map = array(
				'send_time' => array('egt', time()-60*60*12),
				'type'       => 5
			); 
			$aPreConfIds = $modelAcate->where($map)->getField('activity_config_id',TRUE);			
			$aConfIds = array_merge(array_keys($aGroupInfo),$aPreConfIds);
			if(empty($aConfIds)){
				exit('不存在符合的活动');
			}
			//符合的订单
			$map = array(
				'activity_id' => array('in', $aConfIds),
				'pay_time'    => array('gt', 0),
				'payed_fee'   => array('gt', 0),
				'order_status' => 'NO_APPLY'
			);
			$tradeInfo = M('systrade_trade')->where($map)->order('created_time asc')->getField('tid,activity_id');
			$atrade = array();
			$groupTids = array();    //参团成功的订单号
			$preTids = array();    //可以发货的预售商品
			foreach($tradeInfo as $key=>$val){
				$atrade[$val['activity_id']][] = $key;
			}
			foreach($atrade as $key=>$val){
				if($aGroupInfo[$key]){
					//团购
					$groupTids = array_slice(array_reverse($val),count($val)%$aGroupInfo[$key]['achieve_num']);
				}else if($aPreInfo[$key]){
					//预售
					$preTids = $val;
				}
				
			}
			$tids = array_merge($groupTids,$preTids);
			if(empty($tids)){
				exit('不存在符合的订单号');
			}
			//符合支付单
			$map = array(
				'tid' => array('in', $tids)
			);
			$paymentInfo = $modelPaybill->where($map)->getField('tid,payment_id');
			$paymentIds = array();
			foreach($paymentInfo as $key=>$val){
				$paymentIds[] = $val;
			}			
		}
		if(empty($paymentIds)){
			exit('不存在符合的支付单号');
		}
		if(empty($tids)){
			exit('不存在符合的订单号');
		}	
		//查看订单中的商品看顺丰发货还是代发
		$sendType = array(2,3);
		$map = array(
			'tid' => array('in', $tids),
			'send_type' => array('in', $sendType)
		);
		$orderInfo = M('systrade_order')->where($map)->field('tid,send_type')->select();
		//可以推送的支付单
		$pushPids = array();
		foreach($orderInfo as $key=>$val){
			$pushPids[$val['send_type']][] = $paymentInfo[$val['tid']];
		}
		//推送顺丰
		$pushSfPids = $pushPids[3];
		if(!empty($pushSfPids)){
			//推送顺丰
			$url = C('COMMON_API')."Sf/orderPostSf/";
			foreach($pushSfPids as $key=>$val){
				$data = array(
					'paymentId' => $val,
				);
				$return = $this->requestPost($url, $data);
				$resu = json_decode(trim($return,chr(239).chr(187).chr(191)), true);
				if($resu['result'] != 100 || $resu['errcode'] != 0){
					//失败
					$pushSfFails[] = $val;  //推送失败的支付单号
				}				
			}
		}
		//供应商代发
		$pushSpPids = $pushPids[2];
		if(!empty($pushSpPids)){
			//推送供应商
			$url = C('COMMON_API')."Supplier/OrderPushSupper/";
			$data = array(
				'paymentIds' => json_encode($pushSpPids),
			);
			$return = $this->requestPost($url, $data);
			$resu = json_decode(trim($return,chr(239).chr(187).chr(191)), true);
			if($resu['result'] == 100 && $resu['errcode'] == 0){
				//成功
				$pushSpFails = $resu['data'];  //推送失败的支付单号
				$this->makeLog('pushFailPids','推送失败的支付单号:'.implode(',', $pushFails)."\n");
			}else{
				//失败
				echo $resu['msg'];
			}						
		}
		$pushFails = array_merge($pushSfFails,$pushSpFails);
		if(!empty($pushFails)){
			$pushFailsStr = implode(',', $pushFails);
			$this->makeLog('pushFailPids',"推送失败的支付单号:{$pushFailsStr}\n");
			echo "推送失败支付单号：{$pushFailsStr}";
		}else{
			echo '推送成功支付单号：'.implode(',', $paymentIds);
		}
	}
/**
 * 计划任务
 * 所有拼团和预售推单顺丰或供应商
 */
	public function groupAllOrderPush(){
		$pIds = I('paymentIds');
		$modelPaybill = M('ectools_trade_paybill');
		if(!empty($pIds)){
			//手动推
			$paymentIds = explode(',', $pIds);
			$map = array(
				'payment_id' => array('in', $paymentIds)
			);
			$paymentInfo = $modelPaybill->where($map)->getField('tid,payment_id');
			$tids = array();
			$paymentIds = array();
			foreach($paymentInfo as $key=>$val){
				$tids[] = $key;
				$paymentIds[] = $val; 
			}
		}else{
			//此刻符合条件的团购或预售的activity_config_id
			$syncTypes = array(4,5); //团购、预售
			//假设计划任务一天执行一次
			$endTime = time()-60*60*24;
			$map = array(
				'type'       => array('in', $syncTypes)
			); 
			$aConfIds = M('company_activity_category')->where($map)->getField('activity_config_id',TRUE);
			if(empty($aConfIds)){
				exit('不存在符合的活动');
			}
			//符合的订单
			$map = array(
				'activity_id' => array('in', $aConfIds),
				'pay_time'    => array('gt', 0),
				'payed_fee'   => array('gt', 0),
				'order_status' => 'NO_APPLY'
			);
			$tids = M('systrade_trade')->where($map)->getField('tid',TRUE);
			if(empty($tids)){
				exit('不存在符合的订单号');
			}
			//符合支付单
			$map = array(
				'tid' => array('in', $tids)
			);
			$paymentInfo = $modelPaybill->where($map)->getField('tid,payment_id');
			$paymentIds = array();
			foreach($paymentInfo as $key=>$val){
				$paymentIds[] = $val;
			}			
		}
		if(empty($paymentIds)){
			exit('不存在符合的支付单号');
		}
		if(empty($tids)){
			exit('不存在符合的订单号');
		}	
		//查看订单中的商品看顺丰发货还是代发
		$sendType = array(2,3);
		$map = array(
			'tid' => array('in', $tids),
			'send_type' => array('in', $sendType)
		);
		$orderInfo = M('systrade_order')->where($map)->field('tid,send_type')->select();
		//可以推送的支付单
		$pushPids = array();
		foreach($orderInfo as $key=>$val){
			$pushPids[$val['send_type']][] = $paymentInfo[$val['tid']];
		}
		//推送顺丰
		$pushSfPids = $pushPids[3];
		if(!empty($pushSfPids)){
			//推送顺丰
			$url = C('COMMON_API')."Sf/orderPostSf/";
			foreach($pushSfPids as $key=>$val){
				$data = array(
					'paymentId' => $val,
				);
				$return = $this->requestPost($url, $data);
				$resu = json_decode(trim($return,chr(239).chr(187).chr(191)), true);
				if($resu['result'] != 100 || $resu['errcode'] != 0){
					//失败
					$pushSfFails[] = $val;  //推送失败的支付单号
				}				
			}
		}
		//供应商代发
		$pushSpPids = $pushPids[2];
		if(!empty($pushSpPids)){
			//推送供应商
			$url = C('COMMON_API')."Supplier/OrderPushSupper/";
			$data = array(
				'paymentIds' => json_encode($pushSpPids),
			);
			$return = $this->requestPost($url, $data);
			$resu = json_decode(trim($return,chr(239).chr(187).chr(191)), true);
			if($resu['result'] == 100 && $resu['errcode'] == 0){
				//成功
				$pushSpFails = $resu['data'];  //推送失败的支付单号
				$this->makeLog('pushFailPids','推送失败的支付单号:'.implode(',', $pushFails)."\n");
			}else{
				//失败
				echo $resu['msg'];
			}						
		}
		$pushFails = array_merge($pushSfFails,$pushSpFails);
		if(!empty($pushFails)){
			$pushFailsStr = implode(',', $pushFails);
			$this->makeLog('pushFailPids',"推送失败的支付单号:{$pushFailsStr}\n");
			echo "推送失败支付单号：{$pushFailsStr}";
		}else{
			echo '推送成功支付单号：'.implode(',', $paymentIds);
		}
	}
	/**
	 * 活动结束后sku还原库存
	 */
	public function actReductStore(){
		$type = I('get.type', ' ','strip_tags,stripslashes,trim');
		$skuIdStr = I('get.skuIds', 0,'strip_tags,stripslashes,trim');   //指定还原库存
		if($skuIdStr){
			$skuIds =explode(',', $skuIdStr);
		}
		if(empty($skuIds)){
			if($type == 'all'){
				//查找所有到期
				$map = array(
					'end_time' => array('elt',time())
				);
			}else{
				//计时任务
				$map = array(
					'end_time' => array(
						'between' => array(time()-60*60*24,time())
					)
				);			
			}
			//已结束的活动
			$map['type'] = array('not in', array(2,3,6));
			$aConfIds = M('company_activity_category')->where($map)->getField('activity_config_id',TRUE);
			if(empty($aConfIds)){
				exit('无符合的活动...');
			}
			$map = array(
				'activity_config_id' => array('in', $aConfIds)
			);
			//参与活动的sku
			$aitems = M('company_activity_item')->where($map)->getField('sku_id,item_info');
			$skuIds = array_keys($aitems);
			$itemSkuIds = array(); 
			foreach($aitems as $key=>$val){
				if(!empty($val)){
					$itemInfo = json_decode($val,TRUE);
					if(!empty($itemInfo['sku_id'])){
						$itemSkuIds[] = $itemInfo['sku_id'];
					}
				}
			}
			$skuIds = array_merge($skuIds,$itemSkuIds);	
			if(empty($skuIds)){
				exit('无需要还原库存的sku...');
			}
		}
		$map = array(
			'sku_id' => array('in', $skuIds),
			'parent_sku_id' => array('gt', 0)
		);
		$skuInfos = M('sysitem_sku')->where($map)->getField('sku_id,parent_sku_id');
		if(empty($skuInfos)){
			exit('无符合还原库存的sku...');
		}
		$skuIds = array_keys($skuInfos);
		//还原库存
		$map = array(
			'sku_id' => array('in', $skuIds)
		);
		$modelSkuStore = M('sysitem_sku_store');
		$store = $modelSkuStore->where($map)->getField('sku_id,store,item_id');
		//清除活动sku的库存
		$data = array(
			'store' => 0,
			'freez' => 0
		);
		$res = $modelSkuStore->where($map)->save($data);
		if(!$res){
			exit('活动sku库存清零成功...');
		}
		$succItemIds = array();
		foreach($store as $key=>$val){
			$map = array(
				'sku_id' => $skuInfos[$key]
			);
			$res = $modelSkuStore->where($map)->setInc('store',$val['store']);
			if($res){
				$succItemIds[] = $val['item_id'];
			}
		}
		//还原成功的商品Id
		echo '还原成功的商品ID(增加活动sku剩余库存):';
		echo '<pre>';
		var_dump($succItemIds);
		
	}
	/**
	 * 京东订单自动发货
	 */	
	public function autoSendGoodsForJd(){
		$map = array(
			'shop_id' => 10,
			'status'  => array('in', array('WAIT_SELLER_SEND_GOODS','IN_STOCK')),
			'payed_fee' => array('gt', 0),
			'created_time' => array('lt', time()-60*60)
		);
		//可发货的订单号
		$tids = M('systrade_trade')->where($map)->getField('tid',TRUE);	
		if(empty($tids)){
			exit('无符合的发货的京东订单...');
		}		
		$map = array(
			'tid' => array('in', $tids),
			'aftersales_status' => array('in', array('NO_APPLY','CANCEL_APPLY'))
		);
		//过滤订单订单中商品存在无售后的才符合
		$tids = M('systrade_order')->where($map)->getField('tid',TRUE);
		$tids = array_unique($tids);
		//京东单号
		if(empty($tids)){
			exit('无符合的发货的京东订单...');
		}
		$map = array(
			'tid' => array('in', $tids),
			'sync_confirm' => 'CONFIRMED'
		);
		$order = M('systrade_sync_trade')->where($map)->getField('tid,sync_order_id');
		if(empty($order)){
			exit('无符合的发货的京东订单2...');
		}
		foreach($order as $key=>$val){
			A('Order')->sendGoodsByJd(1,$val,$key);
		}
		
	}
	
	
	
}
