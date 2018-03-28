<?php
/**
  +------------------------------------------------------------------------------
 * TaskController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰<10199720@qq.com>
 * @version  	$Id: TaskController.class.php v001 2017-02-09
 * @description 计划任务专用控制器
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class TaskController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->modelItem=M('sysitem_item');
		$this->modelSku=M('sysitem_sku_store');
		$this->modelSkuStore=M('sysitem_sku_store');
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelPayment=M('ectools_payments');
		$this->modelPaybill=M('ectools_trade_paybill');
	}
	
	//自动取消订单 1小时执行一次
	public function cancelOrder(){
		$endTime=time()-86400;
		$conditon=array(
			'status'=>'WAIT_BUYER_PAY',
			'created_time'=>array('lt',$endTime)
		);
		$trade=$this->modelTrade->field('tid')->where($conditon)->select();
		if(!empty($trade)){
			foreach($trade as $key=>$value){
				$tid[]=$value['tid'];
			}
			$bill=$this->modelPaybill->field('payment_id')->where('tid IN ('.implode(',',$tid).')')->select();
			foreach($bill as $key=>$value){
				$pid[]=$value['payment_id'];
			}		
			$data=array(
				'status'=>'TRADE_CLOSED_BY_SYSTEM',
				'modified_time'=>time()
			);
			$this->modelTrade->where($conditon)->save($data);
			$this->modelOrder->where($conditon)->save($data);
			$this->modelPaybill->where('tid IN ('.implode(',',$tid).')')->save(array('status'=>'cancel'));
			$this->modelPayment->where('payment_id IN ('.implode(',',$pid).')')->save(array('status'=>'cancel','cur_money'=>0));
			//释放预占库存
			$this->release($tid);
		}
	}
	
	//释放预占库存
	public function release($tid=array()){
		if(empty($tid)){			
			return FALSE;
		}
		$conditon=array(
			'tid'=>array('in',implode(',',$tid))
		);
		$order=$this->modelOrder->where($conditon)->select();
		if(!empty($order)){			
			foreach($order as $key=>$value){
				$sku[$value['sku_id']]+=$value['num'];
			}
			foreach($sku as $key=>$value){
				$this->modelSkuStore->where('sku_id='.$key)->setDec('freez',$value);
			}
		}
	}
	
	//自动确认收货
	public function confirmGoods(){
		$endTime=time()-86400;
		$conditon=array(
			'status'=>'WAIT_BUYER_CONFIRM_GOODS'
		);
		$this->modelTrade->where($conditon)->save($data);
	}
	
	//同步京东订单的结算价
	public function jdPrice(){
		
		
	}
}
?>