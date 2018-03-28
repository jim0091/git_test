<?php  
namespace Home\Model;
use Think\Model;
/**
  +------------------------------------------------------------------------------
 * OrderHandleModel
  +------------------------------------------------------------------------------
 * @author   	zhangrui
 * @version  	2016-12-23
 * @description 订单处理
  +------------------------------------------------------------------------------
 */
class OrderHandleModel extends Model{
	public function __construct(){
		$this->modelAdminTradeLog=M('system_admin_trade_log');
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelAftersales=M('systrade_aftersales');//售后申请表-----售后处理成功	加入退款申请表
		
			
	}
	/**
	 * 管理员操作记录日志表
	 * *
	 */
	public function markTradeLog($data) {
		$ip=get_client_ip();
		$data['created_time']=time();
		$data['ip']=$ip;
		return $this->modelAdminTradeLog->data ($data)->add();
	}
	/*
	 *添加售后信息到售后申请表 
	 * */
	public function addAftersales($data){
	 	return $this->modelAftersales->data($data)->add();
	}		
//申请退款等退款/退货/换货/维修
	public function editOrderStatus($tid,$datas,$type='REFUND'){
		 $datas['order_status']=$type;
		 $condition['tid']=$tid;
		 return $this->modelTrade->where($condition)->data($datas)->save();
	}
//订单表单个字段的值	
	public function getFieldOrderVal($tid,$field){
		 $condition['tid']=$tid;
		 return $this->modelTrade->where($condition)->getField($field);
	}
//取出指定modelTrade表单条数据
	public function findThisTradeInfo($tid,$field){
		$condition['tid']=$tid;
		return $this->modelTrade->where($condition)->field($field)->find();		
	}	
//取出指定systrade_order表数据
	public function getThisTradeInfo($tid,$field){
		$condition['tid']=$tid;
		return $this->modelOrder->where($condition)->field($field)->select();		
	}	
//修改systrade_order表至待审核
	public function editOrderInfo($oid,$num){
		$data['aftersales_status']="WAIT_EARLY_PROCESS";
		$data['aftersales_num']=$num;
		$condition['oid']=$oid;
		return $this->modelOrder->where($condition)->field('aftersales_status,aftersales_num')->data($data)->save();
	}
//修改systrade_trade表
	public function editTradeInfo($tid,$data,$field){
		$condition['tid']=$tid;
		return $this->modelTrade->where($condition)->data($data)->field($field)->save();		
	}	
//查找订单条件systrade_order表多条数据
	public function getThisConditionOrderInfo($condition,$field){
		return $this->modelOrder->where($condition)->field($field)->select();
	}
//修改systrade_order表多条数据
	public function editThisConditionOrderInfo($condition,$data,$field){
		return $this->modelOrder->where($condition)->data($data)->field($field)->save();
	}	
	/**
	 * 编辑售后信息
	 * */	
	public function editAftersales($condition,$data,$field){
	 	return $this->modelAftersales->where($condition)->data($data)->field($field)->save();
		
	}		
		
	
}
