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
class SfController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelItem=M('sysitem_item');
		$this->modelPayment=M('ectools_payments');
		$this->modelPaybill=M('ectools_trade_paybill');
		$this->modelSyncTrade=M('systrade_sync_trade');
		$this->modelSupStorageItem=M('supplier_storage_items');
		$this->modelActivityTrade = M('company_activity_trade');
		$this->modelActivityOrder = M('company_activity_order');
        $this->modelSiteArea = M('site_area');//地址级联
		$this->modelSf=D('Sf');
	}
	
	public function index(){
		$this->retError(1001,'请输入有效的方法');
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
	
	public function sendOrderStatus(){
		$paymentId=I('post.paymentId',0,'intval');
		$this->makeLog("syncSf",$paymentId);
		if($paymentId<=0){
			echo '{"result":100, "errcode":1002, "data":"订单号不能为空！"}';
			exit;
		}
		$data=$this->orderSendStatus($paymentId);
		//echo '{"result":100, "errcode":0, "msg":"成功"} ';
		$this->retSuccess($data);
		exit;
	}
	
	//入库接口
	public function orderSendStatus($paymentId){
		$url = C('API_SF').'order/querySaleOrderStatus';
		$arr=array(
			'erpOrder'=>"$paymentId"
		);
		$json=json_encode($arr);
		$res=$this->curl_post($url,$json);
		$this->makeLog("orderStatusJson",$json."\r\n");
		$this->makeLog("orderStatusJson",$res."\r\n");
		$res=stristr($res,"{");
		return $res;
	}
	
	//推送已支付成功的订到到顺丰仓库
	public function orderPostSf(){
		$paymentId = I('paymentId');
		if (empty($paymentId)) {
	    	$this->makeLog('syncSf',"error(1001) msg:没有支付单号 paymentId={$paymentId}");
			$this->retError(1001,'订单推送失败，错误信息：没有支付单号');
		}
		$where = array();
		$where['payment_id'] = $paymentId;
		//判断订单是否已经支付
		$paymentInfo = $this->modelPayment->field('cur_money, payed_time')->where($where)->find();
		if (empty($paymentInfo['cur_money']) || empty($paymentInfo['payed_time'])) {
	    	$this->makeLog('syncSf',"error(1002) msg:订单未支付 paymentId={$paymentId}");
	    	$this->retError(1002,'订单未支付');
		}
		//查询支付子表
		$paybillList = $this->modelPaybill->where($where)->field('tid')->select();
		if(empty($paybillList)){
			$this->makeLog('syncSf',"error(1003) msg:未查询到支付子表 paymentId={$paymentId}");
            $this->retError(1003,'未查询到支付子表');
        }
        
        $tidArr = array();
        foreach($paybillList as $key=>$value){
            $tidArr[]=$value['tid'];
        }
        //判断是否为空
        if(empty($tidArr)){
        	$this->makeLog('syncSf',"error(1011) msg:订单号为空 paymentId={$paymentId}");
        	$this->retError(1011,'订单号为空');
        }
        
        //查询订单主表
        $condTrade=array(
            'tid'=>array('in',implode(',',$tidArr)),
            'shop_id'=>array('neq',C('JD_SHOP_ID'))
        );
		$tradeList = $this->modelTrade->where($condTrade)->select();
		if(empty($tradeList)){
			$this->makeLog('syncSf',"error(1004) msg:未查询到主订单 paymentId={$paymentId}");
            $this->retError(1004,'未查询到主订单');
        }
		//根据jdids获取对应地址信息
		$buyerAres = $tradeList[0]['buyer_area'];
		if (empty($buyerAres)) {			
			$this->makeLog('syncSf',"error(1005) msg:未查询到配送信息");
            $this->retError(1005,'未查询到配送信息');
		}
		$areas = str_replace('/',',',$buyerAres);
        $conditionArea['jd_id'] = array('in',$areas);
        $areaList = $this->modelSiteArea->where($conditionArea)->order('level ASC')->select();
        if (!$areaList) {
			$this->makeLog('syncSf',"error(1006) msg:未查询到地址级联信息");
            $this->retError(1006,'未查询到地址级联信息');
        }
        $areaInfo = '';
        if ($areaList) {
            foreach ($areaList as $key => $value) {
                $areaInfo .= $value['name'];
            }
        }
//      $tradeInfo = array();
        $buyerMessage = ''; //，买家留言
//         $tradeInfo['buyer_message'] = '';//初始化
        foreach ($tradeList as $key => $value) {
//         	$tradeInfo['buyer_message'] .=$value['buyer_message'];
        	$buyerMessage .= $value['buyer_message'];
        }
        $receiverName 	= $tradeList[0]['receiver_name']; //收货人姓名
        $receiverMobile = $tradeList[0]['receiver_mobile'];//收货人手机
        // $receiverState 	= $tradeList[0]['receiver_state']; //收货人地址-省级
        // $receiverCity 	= $tradeList[0]['receiver_city']; //收货人地址-市级
        // $receiverDistrict = $tradeList[0]['receiver_district']; //收货人地址-街道，县，镇
        $receiverAddr 	= $tradeList[0]['receiver_address']; //详细地址
        
        unset($tradeList);
        
        $receiverInfo = array();
        $receiverInfo['receiverName'] = $receiverName;
        $receiverInfo['receiverMobile'] = $receiverMobile;
        $receiverInfo['receiverAddress'] = $areaInfo.$receiverAddr;
        
//         $tradeInfo['receiver_name'] = empty($value[0]['receiver_name']) ? $value[1]['receiver_name'] : $value[0]['receiver_name'];
//         $tradeInfo['receiver_mobile'] = empty($value[0]['receiver_mobile']) ? $value[1]['receiver_mobile'] : $value[0]['receiver_mobile'];
//         $tradeInfo['receiver_state'] = empty($value[0]['receiver_state']) ? $value[1]['receiver_state'] : $value[0]['receiver_state'];
//         $tradeInfo['receiver_city'] = empty($value[0]['receiver_city']) ? $value[1]['receiver_city'] : $value[0]['receiver_city'];
//         $tradeInfo['receiver_district'] = empty($value[0]['receiver_district']) ? $value[1]['receiver_district'] : $value[0]['receiver_district'];
//         $tradeInfo['receiver_address'] = empty($value[0]['receiver_district']) ? $value[1]['receiver_district'] : $value[0]['receiver_district'];
        
		//查询订单子表		
        $condOrder=array(
            'tid'=>array('in',implode(',',$tidArr)),
            'send_type'=>3,
            'disabled'=>0
        );
		$orderList = $this->modelOrder->where($condOrder)->field('title,num,sku_id')->select();
		if(empty($orderList)){
	    	$this->makeLog('syncSf',"error(1007) msg:未查询到子订单 paymentId={$paymentId}");
	    	$this->retSuccess(array(),'success');
        }
        foreach ($orderList as $key => $value) {
        	$skuArr[] = $value['sku_id'];
        };
        //检查是否存在子集skuId
        unset($condition);
        unset($field);
        $condition = array('sku_id'=>array('in',implode(',',$skuArr)));
        $field = array('sku_id','parent_sku_id');
        $skuList = $this->modelSf->getSkuList($condition,$field);
        $newSkuList = array();
        if (!empty($skuList)) {
        	foreach ($skuList as $key => $value) {
        		$newSkuList[$value['sku_id']] = $value;
        	}
        }
        foreach ($skuArr as $key => $value) {
        	if ($value == $newSkuList[$value]['sku_id'] && !empty($newSkuList[$value]['parent_sku_id'])) {
        		$skuArr[$key] = $newSkuList[$value]['parent_sku_id'];
        	}
        }
        foreach ($orderList as $key => $value) {
        	if ($value['sku_id'] == $newSkuList[$value['sku_id']]['sku_id'] && !empty($newSkuList[$value['sku_id']]['parent_sku_id'])) {
        		$orderList[$key]['sku_id'] = $newSkuList[$value['sku_id']]['parent_sku_id'];
        	}
        }
        //查询入库表
        $condStorage = array(
        	'skuNo'=>array('in',implode(',',$skuArr))
        );
        $supStorageItemList = $this->modelSupStorageItem->where($condStorage)->field('lot')->group('skuNo')->order('create_time ASC')->select();
        if (empty($supStorageItemList)) {
        	$this->makeLog('syncSf',"error(1008) msg:未查询到入库记录 paymentId={$paymentId}");
        	$this->retError(1008,'未查询到入库记录');
        }	
        foreach ($supStorageItemList as $k => $val) {
			$lotList[$val['skuNo']] = $val['lot']; 			
		} 
		foreach ($orderList as $key => $value) {
			$itemList[$key]['itemName'] = $value['title'];
			$itemList[$key]['itemQuantity'] = $value['num'];
			$itemList[$key]['lot'] = $lotList[$value['sku_id']];  
			$itemList[$key]['skuNo'] = $value['sku_id']; 
		} 
		$paramArr = array(
			'erpOrder'	=>$paymentId,
			'sfOrderType'=>'销售订单',
			'orderNote'	=>$buyerMessage,
			'isInvoice'	=>'N',
			'orderItems'=>$itemList,
			'orderReceiverInfo'=>$receiverInfo,
			'priority'	=>'3'//订单优先级3  正常，2加急 ，1特急
		);
		$url = C('API_SF').'order/sale';
		$param = json_encode($paramArr);
		$result = $this->requestJdPost($url,$param);
	    $ret = json_decode($result,true);	    
	    if($ret['code'] == 100){
	    	if($ret['errCode'] == 0){	    		
	    		$shipmentId = $ret['data']['shipmentId']; //顺丰订单号	    		
	    		$where=array(
	    			'tid'=>array('in', $tidArr),
	    		);
	    		//修改trade
	    		$data = array(
	    			'status'=>'IN_STOCK',
	    			'sync_trade_status'=>'success',
	    			'sync_tid'=>$shipmentId,
	    			'sync_confirm'=>'CONFIRMED'
	    		);
	    		$this->modelTrade->where($where)->save($data);
	    		//修改order
	    		$where['send_type'] = 3;
	    		$where['disabled'] = 0;
	    		$this->modelOrder->where($where)->save(array('status'=>'IN_STOCK'));
	    		
	    		$this->makeLog('syncSf',"success(1009) msg:推送成功 paymentId={$paymentId} result={$result}");
        		$this->retSuccess(array('erpOrder'=>$ret['data']['erpOrder'],'shipmentId'=>$shipmentId),'推送成功！');
	    	}else{
	    		$this->makeLog('syncSf',"error(1010) msg:推送失败 paymentId={$paymentId} result={$result}");
	    		$this->retError(1010,$ret['msg']);       
	    	}
	    }else{
	    	$this->makeLog('syncSf',"error(1011) msg:推送失败 paymentId={$paymentId} result={$result}");
	    	$this->retError(1011,$ret['msg']);     
	    }
	}
	
	//推送已支付成功的活动订到到顺丰仓库
	public function activityOrderPostSf($paymentId, $tid){
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		//判断为空
		if (empty($paymentId)) {
			$this->makeLog('syncActivitySf',"error(1001) msg:没有支付单号  payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1001;
			$ret['msg'] = '没有支付单号';
			return $ret;
		}
		//检索支付单条件
		$where = array();
		$where['payment_id'] = $paymentId;
		//判断订单是否已经支付
		$paymentInfo = $this->modelPayment->field('payment_id, status')->where($where)->find();
		if (empty($paymentInfo) || 'succ' != $paymentInfo['status']) {
			$this->makeLog('syncActivitySf',"error(1002) msg:订单未支付  payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1002;
			$ret['msg'] = '订单未支付';
			return $ret;
		}
		//查询支付子表
		$where = array();
		$where['payment_id'] = $paymentId;
		$where['tid'] = $tid;
		$paybillList = $this->modelPaybill->where($where)->field('tid')->select();
		
		$tidArr = array();//订单集合
		foreach($paybillList as $key=>$value){
			$tidArr[] = $value['tid'];
		}
		if(empty($tidArr)){
			$this->makeLog('syncActivitySf',"error(1003) msg:未查询到支付子表  payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 100;
			$ret['msg'] = '未查询到支付子表';
			return $ret;
		}
		$jdShopId = C('JD_SHOP_ID');
		//查询订单主表
		$condTrade=array(
			'atid'=>array('in', $tidArr),
			'shop_id'=>array('neq', $jdShopId)
		);
		
		$tradeList = $this->modelActivityTrade->where($condTrade)->select();
		if(empty($tradeList)){
			$this->makeLog('syncActivitySf',"error(1004) msg:未查询到主订单  payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 100;
			$ret['msg'] = '未查询到主订单';
			return $ret;
		}
		//根据jdids获取对应地址信息
		$buyerAres = $tradeList[0]['buyer_area'];
		if (empty($buyerAres)) {			
			$this->makeLog('syncSf',"error(1005) msg:未查询到配送信息");
            $this->retError(1005,'未查询到配送信息');
		}
		$areas = str_replace('/',',',$buyerAres);
        $conditionArea['jd_id'] = array('in',$areas);
        $areaList = $this->modelSiteArea->where($conditionArea)->order('level ASC')->select();
        if (!$areaList) {
			$this->makeLog('syncSf',"error(1006) msg:未查询到地址级联信息");
            $this->retError(1006,'未查询到地址级联信息');
        }
        $areaInfo = '';
        if ($areaList) {
            foreach ($areaList as $key => $value) {
                $areaInfo .= $value['name'];
            }
        }
		$buyerMessage = ''; //，买家留言
		foreach ($tradeList as $key => $value) {
			$buyerMessage .= $value['buyer_message'];
		}
		$receiverName 	= $tradeList[0]['receiver_name']; //收货人姓名
		$receiverMobile = $tradeList[0]['receiver_mobile'];//收货人手机
		// $receiverState 	= $tradeList[0]['receiver_state']; //收货人地址-省级
		// $receiverCity 	= $tradeList[0]['receiver_city']; //收货人地址-市级
		// $receiverDistrict = $tradeList[0]['receiver_district']; //收货人地址-街道，县，镇
		$receiverAddr 	= $tradeList[0]['receiver_address']; //详细地址
	
		unset($tradeList);
	
		$receiverInfo = array();
		$receiverInfo['receiverName'] = $receiverName;
		$receiverInfo['receiverMobile'] = $receiverMobile;
		$receiverInfo['receiverAddress'] = $areaInfo.$receiverAddr;
		//查询订单子表
		$condOrder=array(
			'atid'=>array('in', $tidArr),
			'send_type'=>3,
			'disabled'=>0
		);
		$orderList = $this->modelActivityOrder->where($condOrder)->field('order_id, item_name, num, sku_id, sync_status')->select();
		if(empty($orderList)){
			$this->makeLog('syncActivitySf',"error(1007) msg:未查询到子订单 payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1007;
			$ret['msg'] = '未查询到主订单';
			return $ret;
		}
		foreach ($orderList as $key => $value) {
			if(empty($value['sku_id'])){
				$this->makeLog('syncActivitySf',"error(1008) msg:订单sku_id为空order_id={$value['order_id']} payment_id={$paymentId} tid={$tid}");
				$ret['code'] = 1008;
				$ret['msg'] = "订单sku_id为空（order_id={$value['order_id']}）";
				return $ret;
			}else if($value['sync_status'] == 1){
				$this->makeLog('syncActivitySf',"notice(1009) msg:此订单已经同步，跳过 order_id={$value['order_id']} payment_id={$paymentId} tid={$tid}");
				unset($orderList[$key]);
				continue;
			}else{
				$skuArr[] = $value['sku_id'];
			}
		};
		//查询入库表
		$condStorage = array(
			'skuNo'=>array('in',implode(',',$skuArr))
		);
		$supStorageItemList = $this->modelSupStorageItem->where($condStorage)->field('lot')->group('skuNo')->order('create_time ASC')->select();
		if (empty($supStorageItemList)) {
			$this->makeLog('syncActivitySf',"error(1010) msg:未查询到入库记录 payment_id={$paymentId} tid={$tid}");
			$ret['code'] = 1010;
			$ret['msg'] = '未查询到入库记录';
			return $ret;
		}
		foreach ($supStorageItemList as $k => $val) {
			$lotList[$val['skuNo']] = $val['lot'];
		}
		foreach ($orderList as $key => $value) {
			$itemList[$key]['itemName'] = $value['title'];
			$itemList[$key]['itemQuantity'] = $value['num'];
			$itemList[$key]['lot'] = $lotList[$value['sku_id']];
			$itemList[$key]['skuNo'] = $value['sku_id'];
		}
		$paramArr = array(
				'erpOrder'	=>$paymentId,
				'sfOrderType'=>'销售订单',
				'orderNote'	=>$buyerMessage,
				'isInvoice'	=>'N',
				'orderItems'=>$itemList,
				'orderReceiverInfo'=>$receiverInfo,
				'priority'	=>'3'//订单优先级3  正常，2加急 ，1特急
		);
		$url = C('API_SF').'order/sale';
		$param = json_encode($paramArr);
		$result = $this->requestJdPost($url,$param);
		$this->makeLog('syncActivitySf',"payment_id={$paymentId} tid={$tid} url:{$url} param:{$param} return:{$result}");
		$ret = json_decode($result,true);
		if($ret['code'] == 100){
			if($ret['errCode']==0){
				$this->makeLog('syncActivitySf',"success msg:同步顺丰成功 payment_id={$paymentId} tid={$tid} return=".json_encode($ret));
				$ret['code'] = 100;
				$ret['msg'] = 'success';
				//修改订单状态
				$where=array(
					'atid'=>array('in', $tidArr),
				);
				//$this->modelActivityOrder->where($where)->setField('sync_status',1);
				//修改trade
				$this->modelActivityTrade->where($where)->save(array('status'=>'IN_STOCK'));
				//修改order
				$where['send_type'] = 3;
				$where['disabled'] = 0;
				$this->modelActivityOrder->where($where)->save(array('status'=>'IN_STOCK','sync_status'=>1));
				return $ret;
			}else{
				$this->makeLog('syncActivitySf',"error(1011) msg:同步顺丰失败({$ret['msg']})  payment_id={$paymentId} tid={$tid} return=".json_encode($ret));
				$ret['code'] = 1011;
				$ret['msg'] = $ret['msg'];
				return $ret;
			}
		}else{
			$this->makeLog('syncActivitySf',"error(1012) msg:调用接口失败({$ret['msg']})  payment_id={$paymentId} tid={$tid} return=".json_encode($ret));
			$ret['code'] = 1012;
			$ret['msg'] = $ret['msg'];
			return $ret;
		}
	}
	
	//取消顺丰仓库订单
	public function cancelPostSf(){
		$paymentId = I('paymentId');
		if (empty($paymentId)) {
	    	$this->makeLog('syncSf',"cancel error(1001) msg:没有支付单号 paymentId={$paymentId}");
			$this->retError(1001,'订单推送失败，错误信息：没有支付单号');
		}
		$url = C('API_SF').'order/cancelSale';
		$paramArr=array(
			'erpOrder'=>$paymentId
		);
		$param = json_encode($paramArr);
		$result = $this->requestJdPost($url,$param);
		$this->makeLog('syncSf',"cancel payment_id:{$paymentId} url:{$url} param:{$param} return:{$result}");
		$ret = json_decode($result,true);
		if($ret['code'] == 100){
			if($ret['errCode']==0){
	    		$this->retSuccess(array('paymentId'=>$paymentId),'推送成功！');
			}else{
				$this->retError(1002,$ret['msg']);
			}
		}else{
			$this->retError(1003,$ret['msg']);
		}
	}
/**
 * 推送商品sku
 */
	public function pushItemSkuSF(){
		$skuId = I('skuId');
		$errorRet=array(
			'code' => 100,
			'errCode' => 0,
			'msg' => 'Unkuow'
		);
		if(empty($skuId)){
			$errorRet['errCode'] = 1000;
			$errorRet['msg'] = '无法获取sku...';
			$this->ajaxReturn($errorRet);			
		}		
		if(!is_numeric($skuId)){
			$errorRet['errCode'] = 1001;
			$errorRet['msg'] = 'sku格式有误...';
			$this->ajaxReturn($errorRet);			
		}	
		$map = array(
			'sku_id' => $skuId
		);
		$skuInfo = M('sysitem_sku')->where($map)->find();
		if(!$skuInfo){
			$errorRet['errCode'] = 1002;
			$errorRet['msg'] = '无法获取sku信息...';
			$this->ajaxReturn($errorRet);
		}	
		$url = C('API_AOSERVER').'sf/item/push';
		$paramArr[] = array(
			'skuNo' => $skuInfo['sku_id'],
			'itemName' => $skuInfo['title'],
			'standardDescription' => $skuInfo['spec_info'],
			'weight' => $skuInfo['weight'],
			'transportProperty' => '1',
			'serialNumTrackInbound' => 'Y',
			'serialNumTrackOutbound' => 'Y',
			'serialNumTrackInventory' => 'Y',
			'barCode' => array(
					'barCode1' => $skuInfo['barcode']
			),
		);
		$param = json_encode($paramArr);
		$result = $this->requestJdPost($url,$param);
		$this->makeLog('syncSf',"推送商品信息到顺丰系统  url:{$url} param:{$param} return:{$result}");
		echo $result;
	}




}