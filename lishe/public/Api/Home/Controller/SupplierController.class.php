<?php
/**
  +------------------------------------------------------------------------------
 * JdController
  +------------------------------------------------------------------------------
 * @author   	zzw
 * @version  	$Id: SupplierController.class.php v001 2017-02-10
 * @description 供应商接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Model;
class SupplierController extends CommonController{
	public function __construct(){
		parent::__construct();		
		$this->modelSupplier=D('Supplier');
	}
	//根据支付id拆分订单至供应商订单表
	public function splitTrade($paymentId){
		if (empty($paymentId)) {
	    	$this->makeLog('splitTrade',"error(1001) msg:没有支付单号 paymentId={$paymentId}");
			return array(0,'1001:订单拆分失败，错误信息：没有支付单号');
		}
		//检查供应商表是否存在该支付单的数据
		$condition = array('payment_id'=>$paymentId);
		$field = array('payment_id');
		$supplierInfo = $this->modelSupplier->getSupplierInfo($condition,$field);
		if ($supplierInfo) {
	    	$this->makeLog('splitTrade',"error(1002) msg:供应商订单已存在该记录，无法再次添加。 paymentId={$paymentId}");
			return array(0,'1002:订单拆分失败，错误信息：供应商订单已存在该记录，无法再次添加。');			
		}
		//根据支付id查询对应的支付子表数据
		unset($condition);
		unset($field);
		$condition = array('payment_id'=>$paymentId);
		$field = array('tid,status,payment,user_id,payed_time');
		$paymentList = $this->modelSupplier->getPaybillList($condition,$field);
		if (!$paymentList) {			
	    	$this->makeLog('splitTrade',"error(1002) msg:未查询到支付子表数据 paymentId={$paymentId}");
			return array(0,"1002:订单拆分失败，错误信息：未查询到支付子表数据 paymentId={$paymentId}");
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
		$tradeInfo = $this->modelSupplier->getTradeInfo($condition,$field);
		//验证是否存在订单
		if (!tradeInfo) {
	    	$this->makeLog('splitTrade',"error(1003) msg:未查询到订单 paymentId={$paymentId}");
			return array(0,'1003:订单拆分失败，错误信息：未查询到订单');			
		}
		//根据tid查询订单子表数据
		unset($condition);
		unset($field);
		$condition = array('tid'=>array('in',implode(',',$tidarry)),'send_type'=>2,'supplier_id'=>array('neq',0),'shop_id'=>array('neq',10));
		$field = array('oid,tid,supplier_id,total_fee');
		$tradeOrderList = $this->modelSupplier->getTradeOrderList($condition,$field);
		//验证是否存在子订单
		if (!tradeOrderList) {
	    	$this->makeLog('splitTrade',"error(1004) msg:未查询到子订单 paymentId={$paymentId}");	
			return array(0,'1004:订单拆分失败，错误信息：未查询到子订单');		
		}
		//根据供应商id进行分组
		$result = array();
        foreach ($tradeOrderList as $key => $value) {
            $result[$value['supplier_id']]['supplier_id'] = $value['supplier_id'];
            $result[$value['supplier_id']][] = $value;
        }
        //这里把key转成了数字的，方便统一处理
        $ret = array();
        foreach ($result as $key => $value) {
            array_push($ret, $value);
        }
        $tradeOrderListArr = array();
        foreach ($ret as $key => $value) {
        	unset($value['supplier_id']);
        	foreach ($value as $k => $val) {
        		$tradeOrderListArr[$key]['tradeInfo'] = $tradeInfo;
        		$tradeOrderListArr[$key]['payed_fee'] += $val['total_fee'];
        		$tradeOrderListArr[$key]['supplier_id'] =$val['supplier_id']; 
        		$tradeOrderListArr[$key]['orderList'][$k] =$val;
        		unset($tradeOrderListArr[$key]['orderList']['supplier_id']); 
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
			$data['supplier_id'] = $value['supplier_id'];
			$data['user_id'] = $value['tradeInfo']['user_id'];
			$data['com_id'] = $value['tradeInfo']['com_id'];
			$data['payed_fee'] = $value['payed_fee'];
			$data['receiver_name'] = $value['tradeInfo']['receiver_name'];
			$data['created_time'] = $value['tradeInfo']['created_time'];
			$data['receiver_state'] = $value['tradeInfo']['receiver_state'];
			$data['receiver_city'] = $value['tradeInfo']['receiver_city'];
			$data['receiver_district'] = $value['tradeInfo']['receiver_district'];
			$data['receiver_address'] = $value['tradeInfo']['receiver_address'];
			$data['receiver_zip'] = $value['tradeInfo']['receiver_zip'];
			$data['receiver_mobile'] =$value['tradeInfo']['receiver_mobile'];
			try{
				$resTrade = $this->modelSupplier->addsupplierTrade($data);
				if (!$resTrade) {
					$this->model->rollback();
					$this->makeLog('splitTrade',"error(1005) msg:订单表插入失败 paymentId={$paymentId}");
					return array(0,'1005:订单拆分失败，错误信息：订单插入失败');
				}
			}catch(\Exception $e){ 
				$this->model->rollback();
				$this->makeLog('splitTrade',"error(1006) msg:订单表插入异常 ".$e->getMessage());
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
					$resOrder = $this->modelSupplier->addsupplierOrder($da);
					if (!$resOrder) {
						$this->model->rollback();
						$this->makeLog('splitTrade',"error(1007) msg:订单子表插入失败 paymentId={$paymentId}");
						return array(0,'1007:订单拆分失败，错误信息：订单插入失败');					
					}
				}catch(\Exception $e){ 
					$this->model->rollback();
					$this->makeLog('splitTrade',"error(1008) msg:订单子表插入异常 ".$e->getMessage());
					return array(0,'1008:订单拆分失败，错误信息：订单插入异常');	
				}
			}			
		}
		$this->model->commit();
		return array(1,'订单拆分成功');
	}
	//供应商支付确认(根据支付id修改供应商表中的支付状态)
	public function payConfirm($paymentId){
		if (empty($paymentId)) {
	    	$this->makeLog('payConfirm',"error(1001) msg:没有支付单号 paymentId={$paymentId}");
			return array(0,'1001:供应商支付确认失败，错误信息：没有支付单号');
		}
		//验证订单是否已经支付
		$condition = array('payment_id'=>$paymentId);
		$field = 'money,cur_money,status,payed_time';
		$paymentInfo = $this->modelSupplier->getPaymentInfo($condition,$field);
		if (empty($paymentInfo)) {
			$this->makeLog('payConfirm',"error(1002) msg:未查询到订单 paymentId={$paymentId}");
			return array(0,'1002:供应商支付确认失败，错误信息：未查询到订单');
		}
		if (empty($paymentInfo['cur_money']) || $paymentInfo['status'] != 'succ' || empty($paymentInfo['payed_time']) || $paymentInfo['cash_fee'] != $paymentInfo['payed_cash']) {
			$this->makeLog('payConfirm',"error(1002) msg:订单未支付或订单支付异常 paymentId={$paymentId}");
			return array(0,'1002:供应商支付确认失败，错误信息：订单未支付或订单支付异常');
		}
		//修改供应商订单状态
		$data['status'] = 'WAIT_SELLER_SEND_GOODS';
		$data['payed_time'] = $paymentInfo['payed_time'];
		try{
			$updateTrade = $this->modelSupplier->updateTrade($condition,$data);
		}catch(\Exception $e){
			$this->makeLog('payConfirm',"error(1003) msg:供应商订单状态修改失败 ".$e->getMessage());
			return array(0,'1003:供应商支付确认失败，错误信息：供应商订单状态修改失败');
		}
		return array(1,'供应商订单状态修改成功');
	}
/**
 * 推送订单供应商
 * Zhangrui 2017.5.19
 */
	public function OrderPushSupper(){
		$paymentIdsJson = I('paymentIds');
		if(!empty($paymentIdsJson)){
			$paymentIdsJson=str_replace('&quot;','"',$paymentIdsJson);
			$paymentIds = json_decode($paymentIdsJson, TRUE);
		}else{
			$pids = I('pids');
			$paymentIds = explode(',', $pids);
		}
		if(empty($paymentIds)){
	    	$this->retError(1000,'支付单号不能为空');
		}
		$confirmFails = array();
		$splitFails = array();
		foreach($paymentIds as $key=>$val){
			$splitRes = $this->splitTrade($val);
			if($splitRes[0] == 1){
				//拆分成功
				$confirmRes = $this->payConfirm($val);
				if($confirmRes[0] == 0){
					//记录失败的支付单号
					$confirmFails[] = $val;
				}
			}else{
				//记录失败的支付单号
				$splitFails[] = $val;
			}
		}
//		返回推送失败的支付单号
		$pushFails = array_merge($confirmFails,$splitFails);
		$this->retSuccess($pushFails,'success');
	}


}
