<?php  
namespace Home\Controller;
/**
  +------------------------------------------------------------------------------
 * OrderHandleController
  +------------------------------------------------------------------------------
 * @author   	zhangrui
 * @version  	2016-12-23
 * @description 订单处理接口
  +------------------------------------------------------------------------------
 */
class OrderHandleController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->dOrder=D('OrderHandle');
	}	
	/*
	 * 
	 * 写进管理员订单日志表
	 * */
	private function markTradeLog($userId,$tid,$dealType,$remarks,$oidsString){
		//写进管理员操作日志表
		$logData['admin_username']="用户";
		$logData['admin_userid']=$userId;
		$logData['deal_type']=$dealType;
		$logData['tid']=$tid;
		$logData['memo']=$remarks;
		$logData['oids']=$oidsString;
		$this->dOrder->markTradeLog($logData);		
	}
/**
 * 申请售后时当是全单申请时status改为SUCCESS
 */
 	private function checkOrderChange($tid){
 		if(empty($tid)){
 			return false;
 		}
		$map = array(
			'tid' => $tid,
			'aftersales_status'    => array('in', array('CANCEL_APPLY','NO_APPLY'))
		);
 		$isAll = $this->dOrder->getOrderInfo($map);
		if(empty($isAll)){
			$data['status'] = 'SUCCESS';
			$this->dOrder->editTradeInfo($tid,$data,'status');				
		}
 	}	
	/**
	 * 申请退款接口
	 * @param $tid订单号
	 * @param $reason申请原因
	 * @param $mark 退款说明
	 **/
	public function applyRefund(){
		$tid=I('post.tid',0,'trim');
		$reason=I('post.reason');
		$mark=I('post.mark');	
		if(!$tid){
			$this->retError(1000,'订单号缺失！');
		}	
		if($reason==='0'){
			$this->retError(1001,'售后原因缺失!');
		}
		if(empty($mark)){
			$this->retError(1002,'退款说明缺失！');
		}				
		//用户id
		$tradeInfo=$this->dOrder->findThisTradeInfo($tid,'user_id,shop_id,tid,order_status,status');
		if(!$tradeInfo){
			$this->retError(1004,'订单号有误！');
		}
		if($tradeInfo['order_status'] != 'NO_APPLY' || $tradeInfo['status'] != 'WAIT_SELLER_SEND_GOODS'){
			$this->retError(1005,'该订单不支持申请退款！');
		}
		$data['cancel_reason']=$mark;
		$data['status']='SUCCESS';
		//修改主订单状态
		$res=$this->dOrder->editOrderStatus($tid,$data);
		if(!$res){
			//保存失败
			$this->retError(1006,'退款申请失败！');
		}
		//修改子订单状态
		$where['tid']=$tid;
		$oidData=array(
			'aftersales_status'=>'WAIT_EARLY_PROCESS',
			'aftersales_num'=>array('exp','num'),
		);
		$resu=$this->dOrder->editThisConditionOrderInfo($where,$oidData,'aftersales_status,aftersales_num');		
		//写进管理员操作日志表
		$dealType="申请退款";
		$remarks='原因：'.$reason.',描述：'.$mark;
		$this->markTradeLog($tradeInfo['user_id'],$tid,$dealType,$remarks,$oidsString);
		unset($tradeInfo['order_status']);
		unset($tradeInfo['status']);
		//写进申请售后表
		$tradeInfo['description']="退款说明：".$mark;
		$tradeInfo['aftersales_type']='ONLY_REFUND';
		$tradeInfo['modified_time']=time();	
		$tradeInfo['created_time']=time();
		$tradeInfo['reason']=$reason;			
		$itemInfo=$this->dOrder->getThisTradeInfo($tid,'oid,title,num');
		foreach($itemInfo as $key=>$value){
			$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
			$tradeInfo['oid']=$value['oid'];
			$tradeInfo['title']=$value['title'];
			$tradeInfo['num']=$value['num'];
			$this->dOrder->addAftersales($tradeInfo);				
		}
		$this->retSuccess("","退款申请提交成功!");
	}	
/**
 * 申请退换货接口
 * @param $tid 订单号
 * @param $type 售后类型,"return"退货,"change"换货,"repair"修货;
 * @param $oidJson申请售后的商品
 * @param $reason申请原因
 * @param $mark 退款说明
 * @param $evidencePic图片地址
 **/	
		public function applyReturnOrChange(){
			$tid=I('post.tid',0,'trim');
			$type=I('post.type');
			$oidJson=I('post.oids');
			$reason=I('post.reason');
			$mark=I('post.mark');
			$evidencePic=I('post.evidencePic');
			if(!$tid){
				$this->retError(1000,'订单号缺失！');
			}	
			if(empty($type)){
				$this->retError(1001,'无法获取申请的售后类型！');
			}	
			if($reason==='0'){
			$this->retError(1002,'售后原因缺失!');
			}
			if(empty($mark)){
				$this->retError(1003,'退款说明缺失！');
			}			
    	$oidJson=str_replace('&quot;','"',$oidJson);
			$oidArr=json_decode($oidJson,TRUE);
			if(!is_array($oidArr) || empty($oidArr)){
					$this->retError(1004,'申请商品有误！');
			}
			$where=array(
				'tid'=>$tid,
				'oid'=>array('in',$oidArr),
			);
			$itemInfo=$this->dOrder->getThisConditionOrderInfo($where,'title,oid,num,aftersales_status');
			if(empty($itemInfo)){
				$this->retError(1006,'申请商品不存在或订单号有误！');
			}
			foreach($itemInfo as $key=>$val){
				if($val['aftersales_status'] != 'NO_APPLY'){
					$this->retError(1007,'申请商品存在已申请售后的商品！');
					break;
				}
			}
			$tradeInfo=$this->dOrder->findThisTradeInfo($tid,'user_id,shop_id,tid,status,order_status');
			if($evidencePic){
				$tradeInfo['evidence_pic']=$evidencePic;
			}
			$supportStatus=array('WAIT_COMMENT','TRADE_FINISHED');
			if($type=="return"){
				$supportStatus=array('WAIT_COMMENT','TRADE_FINISHED','WAIT_BUYER_CONFIRM_GOODS');
				$tradeInfo['aftersales_type']='REFUND_GOODS';
				$data['order_status']="RETURN";
				$dealType="申请退货";
			}else if($type=="change"){
				$tradeInfo['aftersales_type']='EXCHANGING_GOODS';
				$data['order_status']="EXCHANGE";
				$dealType="申请换货";
			}else if($type=="repair"){
				$tradeInfo['aftersales_type']='REPAIRING_GOODS';
				$data['order_status']="REPAIR";
				$dealType="申请维修";
			}else{
				$this->retError(1009,'售后类型标识不存在！');
			}
			if(!in_array($tradeInfo['status'],$supportStatus) || $tradeInfo['order_status'] != 'NO_APPLY'){
				$this->retError(1010,'该订单不支持申请售后！');
			}	
			unset($tradeInfo['status']);	
			unset($tradeInfo['order_status']);						
			$tradeInfo['description']=$mark;
			$tradeInfo['reason']=$reason;			
			$tradeInfo['modified_time']=time();
			$tradeInfo['created_time']=time();
			$tradeInfo['order_status']=2;//收货情况1-未收到  2-已收货 3-已拒收
			$data['cancel_reason']="申请描述：".$mark;
			//修改主订单状态
			$res=$this->dOrder->editTradeInfo($tid,$data,'order_status,cancel_reason');
			//修改子订单状态
			$oidData=array(
				'aftersales_status'=>'WAIT_EARLY_PROCESS',
				'aftersales_num'=>array('exp','num'),
			);
			$resu=$this->dOrder->editThisConditionOrderInfo($where,$oidData,'aftersales_status,aftersales_num');
			//写进管理员操作日志表
			$oidsString=implode(',', $oidArr);		
			$logData['tid']=$tid;
			$remarks='申请描述:'.$mark;
			$this->markTradeLog($tradeInfo['user_id'],$tid,$dealType,$remarks,$oidsString);		
			foreach($itemInfo as $key=>$val){
					$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
					$tradeInfo['oid']=$val['oid'];
					$tradeInfo['title']=$val['title'];
					$tradeInfo['num']=$val['num'];
					$this->dOrder->addAftersales($tradeInfo);
			}	
			//全单申请时改变trade表status为SUCCESS
			$this->checkOrderChange($tid);							
			$this->retSuccess("","售后申请提交成功!");
		}
/**
 *用户回寄 换修商品
 * @param $tid 订单号
 * @param $logi 快递名称
 * @param $logiNo 快递单号
 * @param $oidJson申请售后的商品
 * @param $mark 退货说明
 * @param $evidencePic图片地址
 **/	
		public function returnGoodsForOrder(){
			$tid=I('post.tid',0,'trim');
			$expressName=I('post.expressName');
			$oidJson=I('post.oids');
			$mark=I('post.mark');	
			$evidencePic=I('post.evidencePic');
			$logiArr['logi']=I('post.logi','','trim');
			$logiArr['logi_no']=I('post.logiNo','','trim');	
			if(!$tid){
				$this->retError(1000,'订单号缺失！');
			}	
			if(empty($logiArr['logi'])){
				$this->retError(1002,'参数快递名称为空！');
			}	
			if(empty($logiArr['logi_no'])){
				$this->retError(1002,'参数快递单号为空！');
			}	
    	$oidJson=str_replace('&quot;','"',$oidJson);
			$oidArr=json_decode($oidJson,TRUE);
			if(!is_array($oidArr) || empty($oidArr)){
					$this->retError(1004,'申请商品有误！');
			}
			$where=array(
				'tid'=>$tid,
				'oid'=>array('in',$oidArr),
			);
			$itemInfo=$this->dOrder->getThisConditionOrderInfo($where,'title,oid,num,aftersales_status');
			if(!empty($itemInfo)){
					$this->retError(1005,'提交商品不存在或订单号有误！');
			}
			foreach($itemInfo as $key=>$val){
				if($val['aftersales_status'] != 'WAIT_BUYER_SEND_GOODS'){
					$this->retError(1005,'申请商品存在进度不在待用户回寄的商品！');
					break;
				}
			}
			$tradeInfo=$this->dOrder->findThisTradeInfo($tid,'user_id,shop_id,tid,status,order_status');
			$logiArr['content']=$mark;
			$logiJson=json_encode($logiArr);
			$statusData['sendback_data']=$logiJson;			
			if($evidencePic){
				$tradeInfo['evidence_pic']=$evidencePic;
			}
			unset($tradeInfo['status']);	
			unset($tradeInfo['order_status']);						
			$tradeInfo['description']=$mark;
			$tradeInfo['modified_time']=time();
			$tradeInfo['reason']=$reason;			
			//修改子订单状态
			$oidData=array(
				'aftersales_status'=>'WAIT_SELLER_CONFIRM_GOODS',
				'modified_time'=>time(),
			); 
			$statusRes=$this->dOrder->editThisConditionOrderInfo($where,$oidData,'aftersales_status,modified_time');
			//写进管理员操作日志表
			$oidsString=implode(',', $oidArr);		
			$logData['tid']=$tid;
			$remarks='配送快递：'.$logiArr['logi'].',快递单号：'.$logiArr['logi_no'].',回寄说明:'.$mark;
			$dealType="商品回寄";
			$this->markTradeLog($tradeInfo['user_id'],$tid,$dealType,$remarks,$oidsString);			
			//改变售后申请表状态/内容
			$condition['oid']=array('in',$oids);
			$statusData['modified_time']=time();
			$aftersaleRes=$this->dOrder->editAftersales($where,$statusData,'sendback_data,modified_time');			
			$this->retSuccess("","success!");			
		}
	
	
	
}
