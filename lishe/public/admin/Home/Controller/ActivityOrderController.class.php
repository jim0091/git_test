<?php  
namespace Home\Controller;
use Org\Util\Excel;
class ActivityOrderController extends CommonController {
/*
 * 订单管理
 * 2016/8/23
 * zhangrui
 * 
 * */	
	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->dOrder=D('Order');
		$this->dActivityOrder=D('Activityorder');
		$this->dCategory=D('Category');
		$this->dShop=D('Shopinfo');	
		$this->dGoods=D('Goods');
		$this->dAftersales=D('Aftersales');	
	}
/*
 *活动订单没有写进商品标题的重新读商品表取 
 * */
 	public function repairItemTitle($orderItem){
 		if(!empty($orderItem)){
			foreach($orderItem as $key=>$value){
				if(empty($value['item_name'])){
					$itemIds[]=$value['item_id'];
				}
			}
			if(!empty($itemIds)){
				$itemInfo=$this->dOrder->getAllItem($itemIds,'item_id,cat_id,barcode,title');
				foreach($orderItem as $key=>$value){
					foreach($itemInfo as $keys=>$values){
						if($value['item_id']==$values['item_id']){
							$orderItem[$key]['cat_id']=$values['cat_id'];
							$orderItem[$key]['barcode']=$values['barcode'];
							$orderItem[$key]['item_name']=$values['title'];
						}
					}			
				}	
			}
			return $orderItem;
 		}else{
 			return null;
 		}
 	}
/*
 *程序批量拆分礼包 
 * */
 	public function batchSplitPackage(){
 		$condition=array(
			'splitOrder_id'=>array('in','-2,0'),
		);
 		$tids=$this->dActivityOrder->getFieldThisConditionOrderInfo($condition,'atid');
		if(!empty($tids)){
			foreach($tids as $key=>$tid){
				$this->splitPackage($tid);
			}
		}
 	}
/*
 * 拆分礼包---推给供应商
 * */
	public function splitPackage($tid){
		if(empty($tid)){
			$tid=I('tid');
		}
		$where=array(
			'atid'=>$tid,
			'splitOrder_id'=>array('elt',0),
		);
		$orderInfo=$this->dActivityOrder->getConThisTradeInfo($where,'order_id,splitOrder_id');
		if($orderInfo['splitOrder_id']=='-1'){
			$this->ajaxReturn(array(0,date("i:s")."拆分失败,礼包已被拆分过！"));
			$this->makeLog('ORderSplitFail',"订单号：{$tid}拆分失败,礼包已被拆分过！");
		}
		$oid=$orderInfo['order_id'];
		if(!$oid){
			$this->ajaxReturn(array(0,"拆分失败,缺少订单号!"));
			$this->makeLog('ORderSplitFail',date("i:s")."订单号：{$tid}拆分失败,礼包已被拆分过！");
		}
		$orderInfo=$this->dActivityOrder->getFieldThisTradeInfo($oid);
		if(!$orderInfo){
			$this->ajaxReturn(array(0,"未查询到订单信息！"));
			$this->makeLog('ORderSplitFail',date("i:s")."订单号：{$tid}未查询到订单信息！");
		}
		$aitemId=$orderInfo['aitem_id'];  //活动商品id
		$shopItemId=$orderInfo['item_id'];
		if(!empty($aitemId)){
			$aitemInfo=$this->dActivityOrder->getThisAItemInfo($aitemId,'item_info');
			$itemInfoArr=json_decode($aitemInfo['item_info'],TRUE);	
			foreach($itemInfoArr as $key=>$value){
				$itemIds[]=$value['item_id'];
				$skuIds[]=$value['sku_id'];
			}
			if(!$itemIds || !$skuIds){
				if(!empty($shopItemId)){
					$this->unitItemPush($shopItemId,$oid,$orderInfo['atid']);
				}else{
					$this->ajaxReturn(array(0,"拆分失败,商品信息有误,商品ID不存在!"));
					$this->makeLog('ORderSplitFail',date("i:s")."订单号：{$tid}拆分失败,商品信息有误,商品ID不存在！");
				}
			}			
			//取出商品所属供应商和发货类型
			$items=$this->dActivityOrder->getAllItem($itemIds,'item_id,supplier_id,send_type,image_default_id,title,shop_id');
			foreach($items as $key=>$value){
				$orderShopIds[$value['shop_id']]=$value['shop_id'];
				$sendTypes[$value['send_type']]=$value['send_type'];
			}
			//商品的价格
			 $skus=$this->dGoods->getSomeItemSkuInfo($skuIds);
			foreach($itemInfoArr as $key=>$value){
				foreach($items as $keys=>$values){
					if($value['item_id']==$values['item_id']){
						$itemInfoArr[$key]=array_merge($itemInfoArr[$key],$values);
					}
				}
				foreach($skus as $keyu=>$valueu){
					if($value['sku_id']==$valueu['sku_id']){
						$itemInfoArr[$key]=array_merge($itemInfoArr[$key],$valueu);
					}							
				}						
			}
			if(empty($itemInfoArr)){
				$this->ajaxReturn(array(0,"拆分失败,礼包无商品信息!"));
				$this->makeLog('ORderSplitFail',date("i:s")."订单号：{$tid}拆分失败,礼包无商品信息！");
			}
			if($orderInfo['splitOrder_id']==0){
				//写进包裹里的商品写进order表
				foreach($itemInfoArr as $key=>$value){
					$value['atid']=$orderInfo['atid'];
					$value['splitOrder_id']=$orderInfo['order_id'];
					$value['user_id']=$orderInfo['user_id'];
					$value['item_name']=$value['title'];
					$value['item_img']=$value['image_default_id'];
					$value['spec_nature_info']=$value['spec_info'];
					$value['shop_price']=$value['price'];
					$value['status']='WAIT_SELLER_SEND_GOODS';
					if(empty($value['supplier_id'])){
						$value['supplier_id']=0;
					}
					$addRes[]=$this->dActivityOrder->addsplitOrderInfo($value);
				}
			}
			if($addRes || $orderInfo['splitOrder_id']=='-2'){
				//拆分成功
				$syncRes=array(1,"");
				if(array_key_exists(10, $orderShopIds) || array_key_exists(3, $sendTypes)){
					//需要调接口同步京东订单或顺丰发货
					$syncRes=$this->syncJdOrSf($orderInfo['atid']);	
				}
				$syncMsg=$syncRes[1];
				if($syncRes[0]==1){
					$res=$this->dActivityOrder->editThisOrderInfo($oid,array('splitOrder_id'=>'-1'),'splitOrder_id');
					$this->ajaxReturn(array(1,'拆分成功'.$syncMsg));
				}else if($syncRes[0]==0){
					$res=$this->dActivityOrder->editThisOrderInfo($oid,array('splitOrder_id'=>'-2'),'splitOrder_id');	
					$this->ajaxReturn(array(0,"拆分成功,同步失败!".$syncMsg));
					$this->makeLog('ORderSplitFail',date("i:s")."订单号：{$tid}拆分成功,同步失败!".$syncMsg);
				}
			}else{
				//拆分失败
				$this->ajaxReturn(array(0,"拆分失败,礼包商品创建订单失败！"));
				$this->makeLog('ORderSplitFail',date("i:s")."订单号：{$tid}拆分失败,礼包商品创建订单失败!");
			}
		}else if(!empty($shopItemId)){
			//单商品写进供应商和发货类型
			$this->unitItemPush($shopItemId,$oid,$orderInfo['atid']);
		}else{
			$this->ajaxReturn(array(0,"拆分失败,缺少礼品套装信息!"));
			$this->makeLog('ORderSplitFail',date("i:s")."订单号：{$tid}拆分失败,缺少礼品套装信息!");
		}
		
		
	}
/*
 * 单商品推送至供应商/京东/顺丰
 * */
 	private function unitItemPush($shopItemId,$oid,$tid){
		//单商品写进供应商和发货类型
		$itemInfo=$this->dGoods->getThisItemInfo($shopItemId,'item_id,supplier_id,send_type,shop_id');
		$itemInfo['splitOrder_id']=-1;
		$itemInfo['status']='WAIT_SELLER_SEND_GOODS';
		$res=$this->dActivityOrder->editThisOrderInfo($oid,$itemInfo,'supplier_id,send_type,splitOrder_id,shop_id,status');
		if($res){
			$syncRes=array(1,"");
			$syncMsg=$syncRes[1];
			if($itemInfo['send_type'] == 3 || $itemInfo['shop_id']==10){
				//需要调接口同步京东订单或顺丰发货
				$syncRes=$this->syncJdOrSf($tid);	
				$syncMsg=$syncRes[1];
				if($syncRes[0]==0){
					$data['splitOrder_id']='-2';
					$this->dActivityOrder->editThisOrderInfo($oid,$data,'supplier_id,send_type,splitOrder_id,shop_id,status');
				}			
			}
			if($syncRes[0]==1){
				$this->ajaxReturn(array(1,"审核成功!".$syncMsg));
			}else if($syncRes[0]==0){
				$this->ajaxReturn(array(0,"同步失败!".$syncMsg));
				$this->makeLog('ORderSplitFail',date("i:s")."订单号：{$tid}同步失败!".$syncMsg);
			}
		}else{
			$this->ajaxReturn(array(0,"失败,推送失败!".$syncMsg));
		}		
 	}
/*
 * 京东订单，顺丰发货订单同步
 * */
	private function syncJdOrSf($tid){
		$url=C('COMMON_API')."Order/syncActivityOrder/";
        $syncData = array(
            	'tid'=>$tid,
			);
		$return=$this->requestPost($url,$syncData);
		$resu=json_decode(trim($return,chr(239).chr(187).chr(191)),true);	
		if(empty($resu)){
			return array(0,'通讯接口失败!');
		}				
		$this->makeLog('syncJdOrSf',var_export($resu,TRUE));
		if($resu['result']==100 && $resu['errcode']==0){
			//同步成功
			$this->makeLog('syncThirdParty','同步京东/顺丰发货成功，订单号：'.$tid.',返回提示：'.$resu['msg']);
			return array(1,$resu['msg']);
		}else{
			$this->makeLog('syncThirdParty','同步京东/顺丰发货失败，订单号：'.$tid.',返回提示：'.$resu['msg']);
			$this->makeLog('ORderSplitFail',date("i:s")."同步京东/顺丰发货失败，!".$tid.",返回提示：".$resu['msg']);
			//同步失败
			return array(0,$resu['msg']);
		}		
	}
/*
 *取消订单0元购临时方法 
 * */
 	public function cancelZeroOrder(){
 		$tid=I('get.tid',0,'trim');
		echo '此方法仅限海核淘0元购订单！';
		echo "<hr/>";
		if(!$tid){
			exit("订单取消失败：订单号未获取到!");
		}
		$where=array(
			'activity_name'=>array('like','%海核淘%'),
			'atid'=>$tid,
		);
		$data=array(
			'pay_time'=>0,
			'status'=>'TRADE_CLOSED_BY_ADMIN'
		);
		$res=M('company_activity_trade')->where($where)->field('pay_time,status')->save($data);		
		if(!$res){
			exit('订单取消失败：请检查订单号');
		}
		unset($where['activity_name']);
		$resu=M('company_activity_order')->where($where)->field('status')->save($data);	
		if(!$resu){
			exit('订单取消失败!');
		}
		$paymentId=M('ectools_trade_paybill')->where($where)->getField('payment_id');	
		unset($where);
		$where=array(
			'paymentId'=>$paymentId
		);
		unset($data);
		$data=array(
			'status'=>'ready',
			'payed_time'=>0
		);
		M('ectools_trade_paybill')->where($where)->field('status,payed_time')->save($data);
		M('ectools_payments')->where($where)->field('status,payed_time')->save($data);
		echo '订单取消成功';
 	}
/*
 * 标记订单状态为备货中页面
 * */	
	public function toStock(){
		$tid=I('tid');
		if(!empty($tid)){
			$items=$this->dOrder->getAllActivityItems($tid);
			$items=$this->repairItemTitle($items);
			foreach($items as $key=>$value){
				$supplierIds[]=$value['supplier_id'];
			}
			if(!empty($supplierIds)){
				$supplierInfo=$this->dGoods->getConditionsupplierUser($supplierIds);
				foreach($items as $key => $value){
					foreach($supplierInfo as $keys=>$values){
						if($value['supplier_id'] == $values['supplier_id']){
							$items[$key]['supperName']=$values['company_name'];
						}
					}
				}				
			}	
			$this->assign('tid',$tid);
			$this->assign('items',$items);
			$this->display();
			
		}else{
			echo 'Can not find pay bill!';
		}
	}	
/*
 * 标记订单状态为备货中处理
 * */
	public function toStockDeal(){
		$tid=I('tid');
		$oids=I('oids');
		$mark=I('mark');
		if(empty($oids)){
			$this->error("请选择可备货的商品");
		}
		//加入管理员订单日志表
		$dealType="标记备货(活动)";
		$remarks="备货中,备注：".$mark;
		$oidsString=$oids;
		$logData['tid']=$tid;
		A('Order')->markTradeLog($tid,$dealType,$remarks,$oidsString);
		$nowstatus=$this->dActivityOrder->getThisOrderInfo($tid,'status');
		if($nowstatus['status'] == "WAIT_SELLER_SEND_GOODS"){
			$data['status']='IN_STOCK';
			$res=$this->dActivityOrder->editTradeInfo($tid,$data);//添加备注，订单状态改为备货中
		}		
		$condition['order_id']=array('in',$oids);
		$SendData['status']='IN_STOCK';
		$SendData['to_stork_mark']=$this->realName."&nbsp;：&nbsp;".$mark."&nbsp;&nbsp".date('Y-m-d H:i:s',time());
		$statusRes=$this->dActivityOrder->editThisConditionOrderInfo($condition,$SendData,'status,to_stork_mark');		
		if($statusRes && $res){
			$this->success('标记备货成功！');
		}else{
			$this->error('标记备货失败！');
		}
	}		
/*
 * 
 * 发货
 * */
	public function sendGoods(){
		$tid=I('tid');
		if($tid){
			//取出该订单的地址信息
			$res=$this->dActivityOrder->getThisOrderInfo($tid,'atid,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address,pay_time');
			$itemInfo=$this->dActivityOrder->getThisTradeInfo($tid);
			$itemInfo=$this->repairItemTitle($itemInfo);
			foreach($itemInfo as $key=>$value){
				$supplierIds[]=$value['supplier_id'];
			}
			if(!empty($supplierIds)){
				$supplierInfo=$this->dGoods->getConditionsupplierUser($supplierIds);
				foreach($itemInfo as $key => $value){
					foreach($supplierInfo as $keys=>$values){
						if($value['supplier_id'] == $values['supplier_id']){
							$itemInfo[$key]['supperName']=$values['company_name'];
						}
					}
				}				
			}				
			$this->assign('info',$res);
			$this->assign('item',$itemInfo);
			$this->display();
		}
	}
/*
 * 发货处理
 * */	
	public function sendGoodsDeal(){
		$logId=I('logId');
		$tid=I('tid');
		$logiNo=trim(I('logiNo'));
		$oids=I('oid'); 
		if(empty($oids)){
			$this->error('请选择发货的商品!');
		}	
		if(!empty($logId) && !empty($logiNo) && !empty($tid)){
			//快递公司信息
			$expressCom=$this->dOrder->getThisExpressInfo($logId);
			$data=$this->dActivityOrder->getThisOrderInfo($tid);
			$data['tid']=$tid;
			$data['logi_no']=$logiNo;
			$data['logi_id']=$expressCom['corp_id'];
			$data['logi_name']=$expressCom['corp_name'];
			$data['logi_code']=$expressCom['logi_no'];
			if($data['shop_id']){
				$data['seller_id']=$this->dOrder->getThisShopInfo($data['shop_id']);
			}else{
				$data['seller_id']=0;
			}
			$data['t_begin']=time();
			$data['t_send']=time();
			$data['t_confirm']=time();
			$data['status']='succ';
			$data['delivery_id']=date('ymdHis').rand(1000000,9999999);
			//发货添加发货表
			$res=$this->dOrder->sendGoodsaddExpree($data);
			if($res){
				$nowstatus=$this->dActivityOrder->getThisOrderInfo($tid,'status');
				if(in_array($nowstatus['status'], array('IN_STOCK','WAIT_SELLER_SEND_GOODS'))){
					//改变订单状态为待收货
					$dataStatus['status']='WAIT_BUYER_CONFIRM_GOODS';
					$orderRes=$this->dActivityOrder->editTradeInfo($tid,$dataStatus);
				}				
				//子订单状态改为待收货
				$orderInfo=$this->dActivityOrder->getOidsOrderInfo($oids,'order_id,num,item_id,item_name');
				$orderInfo=$this->repairItemTitle($orderInfo);
				$SendData['status']='WAIT_BUYER_CONFIRM_GOODS';
				$SendData['consign_time']=time();
				foreach($orderInfo as $key=>$value){
				//更改order表发货商品数量
					$SendData['sendnum']=$value['num'];
					$this->dActivityOrder->editThisOrderInfo($value['order_id'],$SendData,'sendnum,status,consign_time');
					$itemIds[]=$value['item_id'];
					$sendOids[]=$value['order_id'];
				} 				
				//发货添加发货详情表
				$itemInfos=$this->dOrder->getAllItem($itemIds,'jd_sku,item_id,bn');
				foreach($orderInfo as $key=>$value){
					foreach($itemInfos as $keys=>$values){
						if($value['item_id']==$values['item_id']){
							$orderInfo[$key]['sku_id']=$values['jd_sku'];
							$orderInfo[$key]['sku_bn']=$values['bn'];
						}
					}
					$orderInfo[$key]['number']=$value['num'];
					$orderInfo[$key]['sku_title']=$value['title'];
					$orderInfo[$key]['delivery_id']=$data['delivery_id'];
				}
				foreach($orderInfo as $key=>$value){
					$this->dOrder->addExpressDeatils($orderInfo[$key]);
				} 	
				if(!empty($oids)){
					$condition['oid']=array('in',$oids);
				}else{
					$condition['tid']=$tid;
				}
				//写进管理员操作订单日志表
				$oidsString=implode(',',$oids);				
				$dealType="发货(活动)";
				$remarks="配送快递：".$expressCom['corp_name'].",快递单号：".$logiNo.",配送流水号".$data['delivery_id'];
				$logData['tid']=$tid;
				A('Order')->markTradeLog($tid,$dealType,$remarks,$oidsString);
				if($res){
					echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>订单:".$tid."标记发货！<br/>配送流水号:".$data['delivery_id']."</div>";
				}
			}
		}else{
			$this->error('提交参数不完整！请重新检测输入');
		}
		
	}
/*
 * 取消商品申请退款
 * 
 * */
	public function cancelGoods(){
		$tid=I('tid');
		if($tid){
			$items=$this->dActivityOrder->getOrderItems($tid);
			$items=$this->repairItemTitle($items);
			$this->assign('tid',$tid);
			$this->assign('items',$items);
			$this->display();
		}else{
			echo 'Can not find pay bill!';
		}
	}
//申请退款处理
	public function cancelGoodsDeal(){
		$oids=I('oids');
		$tid=I('tid');
		$reason=I('reason');
		$allOid=I('allOid');   //全单退款
		$mark=I('mark');
		if($reason==='0'){
			$this->error('请选择售后原因！');
		}
		if(empty($mark)){
			$this->error('请填写取消理由！');
		}	
		if($allOid==1){
			$oids=$this->dActivityOrder->getFieldThisConditionOrderInfo(array('atid'=>$tid),'order_id');
		}
		if(empty($oids)){
			$this->error('请选择要取消的商品！');
		}	
		$orderItemInfo=$this->dActivityOrder->getOidsOrderInfo($oids,'order_id,num,item_id,item_name');
		$orderItemInfo=$this->repairItemTitle($orderItemInfo);
		//改变订单状态为申请退款
		$data['order_status']='REFUND';
		$data['cancel_reason']=$mark;
		$res=$this->dActivityOrder->editTradeInfo($tid,$data);
		//写进管理员操作日志表
		$oidsString=implode(',',$oids);		
		$dealType="申请退款(活动)";
		$remarks='原因：'.$reason.',描述：'.$mark;
		A('Order')->markTradeLog($tid,$dealType,$remarks,$oidsString);	
		//写进申请售后表
		$tradeInfo=$this->dActivityOrder->getThisActivityOrderInfo($tid,'user_id,atid');
		$tradeInfo['tid']=$tradeInfo['atid'];
		$tradeInfo['reason']=$reason;
		$tradeInfo['trade_source']="activity";
		$tradeInfo['description']="管理员:".$this->adminName."申请退款";
		$tradeInfo['aftersales_type']='ONLY_REFUND';
		$tradeInfo['modified_time']=time();				
		foreach($orderItemInfo as $key=>$value){
			$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
			$tradeInfo['oid']=$value['order_id'];
			$tradeInfo['title']=$value['item_name'];
			$tradeInfo['num']=$value['num'];
			$tradeInfo['trade_source']="activity";
			$this->dActivityOrder->cancelThisGoods($value['order_id'],$value['num']);  //order表改状态
			$this->dAftersales->addAftersales($tradeInfo);				
		}
		if($res){
			echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>退款申请成功，等待审核退款！</div>";
		}	
	}
/**
 * 
 *审核（退款、退货）的数据取出
 *  
 * */
 	public function getProcessData($tid,$aftersalesStatus){
		$condition['atid']=$tid;
		if($aftersalesStatus){
			$condition['aftersales_status']=$aftersalesStatus;
			$condition['aftersales_num']=array('gt',0);
		}
		$items=$this->dActivityOrder->getThisConditionOrderInfo($condition,'order_id,aftersales_num,item_id,item_name,price,num,aftersales_status,disabled');
		$items=$this->repairItemTitle($items);
		$res=$this->dActivityOrder->getThisOrderInfo($tid,'atid,cancel_reason,order_status,status,payed_fee');
		$res['serviceStatus']=A('Order')->orderStatusReturn($res['order_status']);
		$res['orderStatus']=A('Order')->orderStatus($res['status']);
		//取出退货的理由等信息
		$aftersaleCondition['tid']=$tid;
		$aftersaleCondition['trade_source']="activity";
		$aftersaleInfo=$this->dAftersales->getAllThisAftersales($aftersaleCondition,'oid,reason,description,evidence_pic,order_status');
		foreach($items as $key=>$value){
			foreach($aftersaleInfo as $keys=>$values){
				if($value['oid']==$values['oid']){
					$items[$key]['reason']=$values['reason'];
					$items[$key]['description']=$values['description'];
					$items[$key]['evidence_pic']=$values['evidence_pic'];
					$items[$key]['orderStatus']=$values['order_status'];
				}
			}
		}	
		$this->assign('orderInfo',$res);
		$this->assign('items',$items); 		
 	}	
/*
 * 退款审核
 * */	
	public function refundProcess(){
		$tid=I('tid');
		if($tid){
			$this->getProcessData($tid,"WAIT_PROCESS");
			$this->display();
		}else{
			echo '请用正确方式进入';
		}
	}
/*
 *退款审核处理 
 * */
 	public function refundProcessDeal(){
 		$content=trim(I('content'));
		$refundFee=trim(I('refundFee'));
		$tid=I('tid');
		$oids=I('oids');
		$process=I('process');
		$recoverOids=I('recoverOids');//灰复库存的商品子单号
		if(empty($oids)){
			$this->error('操作方式有误！');
		}		
		if($process!=0){
			if(empty($content)){
				$this->error('请输入处理审核意见！');
			}
			if($process==1){
				//审核通过
				if(!is_numeric($refundFee)){
					$this->error('请输入正确的金额！');
				}else if(intval($refundFee) <=0){
					$this->error('请输入正确的金额！');
				}
				$allOrderInfo=$this->dActivityOrder->getThisTradeInfo($tid,'order_id,price,num');
				$checkInfo=$this->dActivityOrder->getOidsOrderInfo($oids,'order_id,price,aftersales_num');
				$compareOrderInfo=$checkInfo;
				foreach($compareOrderInfo as $key=>$value){
					$compareOrderInfo[$key]['num']=$value['aftersales_num'];
					unset($compareOrderInfo[$key]['aftersales_num']);
				}
				$compareRes=array_diff_assoc($allOrderInfo,$compareOrderInfo);  //比较两数据
				if(!empty($compareRes)){
					//未全申请售后
					foreach($checkInfo as $key=>$value){
						$checkFee +=$value['price']*$value['aftersales_num'];
					}
					$subrFee = bccomp($refundFee,$checkFee,2);
					if($subrFee == 1){
						$this->error('金额不能大于售后商品的总值！');
					}					
				}
				//修改后面需退款的金额
				$refundData=$this->dActivityOrder->getThisOrderInfo($tid,'user_id,atid,total_fee,refund_fee,refund_time,payed_fee');
				if(I('refundsType')){
					$refundData['refunds_type']=1;
				}
				if(!empty($refundData['refund_fee']) && empty($refundData['refund_time'])){
					$refundFee=$refundFee+$refundData['refund_fee'];
				}
				if($refundFee > $refundData['payed_fee']){
					$this->error('退款金额大于支付金额，请重新输入退款金额！');
				}	
				unset($refundData['refund_fee']);
				unset($refundData['refund_time']);
				$feeData['refund_fee']=$refundFee;
				$feeData['refund_time']=0;
				$feeRes=$this->dActivityOrder->editTradeInfo($tid,$feeData);
				$status=1;
				$dealRes="REFUND_PROCESS";
				$remarks="审核结果：通过,<br/>退款金额：￥".$refundFee."<br/>,处理说明：".$content;
				//写进去退款申请表
					//读取该订单的信息
				$refundData['tid']=$refundData['atid'];
				$refundData['total_price']=$refundData['total_fee'];
				$refundData['trade_source']="activity";
				$aftersaleCondition['oid']=array('in',$oids);
				$refundInfo=$this->dAftersales->getAllThisAftersales($aftersaleCondition,'aftersales_bn,oid,shop_explanation');
				foreach($refundInfo as $key=>$value){
					$refundData['refund_bn']=date('ymdHis').rand(1000000,9999999);
					$refundData['aftersales_bn']=$value['aftersales_bn'];
					$refundData['oid']=$value['oid'];
					$refundData['refunds_reason']=$value['shop_explanation'];
					$refundData['created_time']=time();
					$refundData['trade_source']="activity";
					$this->dAftersales->addreFundData($refundData);
				}				
			}else if($process==3){
				//审核不通过
				$status=3;
				$dealRes="SELLER_REFUSE";
				$remarks="审核结果：不通过,处理说明：".$content;
			}
			//写进管理员操作订单日志表
			$dealType="退款订单审核(活动)";
			$logData['tid']=$tid;
			$oidsString=implode(',',$oids);			
			A('Order')->markTradeLog($tid,$dealType,$remarks,$oidsString);
			//改变售后申请表状态
			$condition['order_id']=array('in',$oids);
			$statusData['status']=$status;
			$statusData['shop_explanation']=$content;
			$statusData['modified_time']=time();
			$aftersaleRes=$this->dAftersales->editAftersales($condition,$statusData,'status,shop_explanation,modified_time');		
			//该订单分表的状态
			$data['aftersales_status']=$dealRes;
			$statusRes=$this->dActivityOrder->editThisConditionOrderInfo($condition,$data,'aftersales_status');
			if(!empty($aftersaleRes) && !empty($statusRes)){
				if($recoverOids){
					//恢复商品库存
					$orderItemInfo=$this->dActivityOrder->getOidsOrderInfo($recoverOids,'order_id,num,aitem_id,item_id');
					foreach($orderItemInfo as $key=>$value){
//						if($value['item_id']){  //数据库添加sku字段时使用
//							$this->dOrder->recoverGoodsStore($value['sku_id'],$value['num']);
//						}
						if($value['aitem_id']){
							$this->dActivityOrder->recoverActivityItenStore($value['aitem_id'],$value['num']);
						}
					}					
				}
				echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>审核处理成功！</div>";
			}
		}else{
			$this->error('请选择审核结果！');
		}
		
		
 	}	
//申请退货
	public function returnItem(){
		$tid=I('tid');
		if($tid){
			$items=$this->dActivityOrder->getOrderItems($tid);
			$items=$this->repairItemTitle($items);
			$res=$this->dActivityOrder->getThisOrderInfo($tid,'cancel_reason');
			$this->assign('mark',$res['cancel_reason']);
			$this->assign('tid',$tid);
			$this->assign('items',$items);
			$this->display();
		}else{
			echo 'Can not find pay bill!';
		}
	}
//申请退货添加
	public function saveReturnItem(){
		$tid=I('tid');
		$mark=I('mark');
		$order=I('order');
		$orderStatus=I('orderStatus');
		$reason=I('reason');
		$logi=trim(I('logi'));
		$logiNo=trim(I('logiNo'));
		$orderArr=explode("#", $order);
		foreach($orderArr as $key=>$value){
			$valueArr=explode("-", $value);
			$item[$key]['oid']=$valueArr[0];
			$item[$key]['num']=$valueArr[1];
			$item[$key]['title']=$valueArr[2];
			$oids[]=$valueArr[0];
			$details[]=$valueArr[1].'x'.$valueArr[2];
		}
		switch($orderStatus){
			case 1:
				$situation="未收到货";
				break;
			case 2:
				$situation="已收到货";
				break;
			case 3:
				$situation="已拒收";
				break;								
		}		
		if($tid){
			$condition['oid']=array('in',$oids);
			$tradeInfo=$this->dActivityOrder->getThisActivityOrderInfo($tid,'user_id,atid');
			$tradeInfo['tid']=$tradeInfo['atid'];
			$tradeInfo['description']=$mark;
			$tradeInfo['aftersales_type']='REFUND_GOODS';
			$tradeInfo['modified_time']=time();
			$data['order_status']="RETURN";
			$data['cancel_reason']="收货情况：".$situation.",管理员:".$this->realName."代用户申请<br/>描述：".$mark;
			$res=$this->dActivityOrder->editTradeInfo($tid,$data);
		}
		//写进售后申请表、改变订单子表
		$tradeInfo['order_status']=$orderStatus;
		if($orderStatus != 2){
			$logiArr['content']="管理员:".$this->realName."代用户申请";
		}else{
			$logiArr['logi']=$logi;
			$logiArr['logi_no']=$logiNo;
			$logiArr['content']="收货情况：".$situation.",管理员".$this->realName."代用户申请";
		}
		$logiJson=json_encode($logiArr);
		$tradeInfo['sendback_data']=$logiJson;
		$tradeInfo['reason']=$reason;
		foreach($item as $key=>$value){
			if($value['oid']){
				$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
				$tradeInfo['oid']=$value['oid'];
				$tradeInfo['title']=$value['title'];
				$tradeInfo['num']=$value['num'];
				$tradeInfo['status']=1;
				$tradeInfo['trade_source']="activity";
				$this->dActivityOrder->editOrderForConfirmGoods($value['oid'],$value['num']);
				$this->dAftersales->addAftersales($tradeInfo);
			}
		}
		//写进管理员操作日志表
		$oidsString=implode(',',$oids);		
		$dealType="申请退货(活动)";
		$logData['tid']=$tid;
		$remarks="收货情况：".$situation.'原因:'.$reason.'描述:'.$mark.'  详情:'.implode(',', $details).'回寄信息：'.$logi.':'.$logiNo;
		A('Order')->markTradeLog($tid,$dealType,$remarks,$oidsString);		
		echo json_encode($res);
	}
/*
 *售后订单页搜索条件条件类型
 * */
 	public function aftersaleCondition(){
 		$aftersaleType=I('aftersaleType');
 		if($aftersaleType!=0){
			$condition['aftersales_type']=$aftersaleType;
 		}
		$condition['trade_source']="activity";
		return $condition;
 	}
/*
 *售后订单页（退、换、货） 处理订单
 * */
	public function aftersaleDealIndex(){
		$aftersaleTypeCondition=$this->aftersaleCondition();
		$aftersaleTids=$this->dAftersales->getAllThisAftersalesOrder($aftersaleTypeCondition);
 		$aftersalesStatus=I('aftersalesStatus');
		$roleId=$this->roleId;
		if(empty($aftersalesStatus) && $roleId!=0){
			if($this->checkPowerNode("refundearlyprocess")){
				//待初审权限
				$aftersalesStatus="WAIT_EARLY_PROCESS";
			}else if($this->checkPowerNode("refundprocess") || $this->checkPowerNode("returnprocess")){
				//待审核权限
				$aftersalesStatus="WAIT_PROCESS";
			}else if($this->checkPowerNode("returnsendfororder") || $this->checkPowerNode("waitreturnfororder")){
				//待确认收货
				$aftersalesStatus="WAIT_SELLER_CONFIRM_GOODS";
			}
		}
 		$onlyType=I('onlyType');
		$this->aftersaleOrderPageDeal($condition,$aftersaleTids,$aftersalesStatus,"aftersale",$onlyType);
		$this->display();
	}	
/*
 *售后订单页（退、换、货） 
 * */
	public function aftersaleOrderIndex(){
		$aftersaleTypeCondition=$this->aftersaleCondition();
		$aftersaleTids=$this->dAftersales->getAllThisAftersalesOrder($aftersaleTypeCondition);
 		$aftersalesStatus=I('aftersalesStatus');
		$this->aftersaleOrderPageDeal($condition,$aftersaleTids,$aftersalesStatus,"aftersale");
		$this->display();
	}
/*
 *退款管理页 
 * */
 	public function refundOrderIndex(){
 		$aftersalesStatus=I('aftersalesStatus');
		$aftersaleTypeCondition=$this->aftersaleCondition();
 		$aftersaleTids=$this->dAftersales->getAllThisRefundOrder($aftersaleTypeCondition);
		$this->aftersaleOrderPageDeal($condition,$aftersaleTids,$aftersalesStatus,"redund");
		$this->display();
 	}
/*
 *售后高级搜索条件
 * */
 	public function searchMoreConditionData($tids){
		if($_GET['goods']){
			$_GET['goods']=urldecode($_GET['goods']);
		} 		
 		$data=I('');
		if($data && !empty($tids)){
	 		$searchData=$data;
			//时间条件(售后申请时间)
			$condition['tid']=array('in',$tids);
			if(!empty($data['startTime']) && !empty($data['endTime'])){
				$data['start']=strtotime($data['startTime']);
				$data['end']=strtotime($data['endTime']." +24 hours");
				$str=$data['start'].','.$data['end'];
				$condition['created_time']=array('between',$str);
			}
			//最后处理时间
			if(!empty($data['dealStartTime']) && !empty($data['dealEndTime'])){
				$data['dealStart']=strtotime($data['dealStartTime']);
				$data['dealEnd']=strtotime($data['dealEndTime']." +24 hours");
				$dealStr=$data['dealStart'].','.$data['dealEnd'];
				$condition['modified_time']=array('between',$dealStr);
			}			
			//退换货类型
			if(!empty($data['aftersaleType'])){
				$condition['aftersales_type']=$data['aftersaleType'];
				$searchData['thisAfterType']=A('Order')->serachName($data['aftersaleType'],"afterType");
			}
			//售后单号
			if(!empty($data['aftersalesBn'])){
				$condition['aftersales_bn']=trim($data['aftersalesBn']);
			}
			if(!empty($condition['created_time']) || !empty($condition['modified_time']) || !empty($condition['aftersales_type']) || !empty($condition['aftersales_bn'])){
				$condition['trade_source']="activity";
				$tids=array_unique($this->dAftersales->searchMoreAfterOrder($condition,$tids));
			}
			if(!empty($tids)){
				//退款订单页退款创建时间
				if(!empty($data['refundStartTime']) && !empty($data['refundEndTime'])){
					$refundCondition['tid']=array('in',$tids);
					$data['refundStart']=strtotime($data['refundStartTime']);
					$data['refundEnd']=strtotime($data['refundEndTime']." +24 hours");
					$refundStr=$data['refundStart'].','.$data['refundEnd'];
					$refundCondition['created_time']=array('between',$refundStr);
					$refundCondition['trade_source']="activity";
					$tids=array_unique($this->dAftersales->searchMoreRefundOrder($refundCondition,$tids));
				}					
			}else{
				$tids=0;
			}
			if(!empty($tids)){
				$orderCondition['atid']=array('in',$tids);
				//订单号
				if(!empty($data['tid'])){
					$orderCondition['atid']=trim($data['tid']);
				}
				//审核进度
				if(!empty($data['aftersaleProcess'])){
					$orderCondition['aftersales_status']=$data['aftersaleProcess'];
					$searchData['thisProcess']=A('Order')->serachName($data['aftersaleProcess'],"afterProcess");
				}
				//商品名条件
				if(!empty($data['goods'])){
					$orderCondition['title']=array('like','%'.$data['goods'].'%');
				}
				if(!empty($orderCondition['atid']) || !empty($orderCondition['aftersaleProcess']) || !empty($orderCondition['title'])){
					$orderCondition['trade_source']="activity";
					$tids=array_unique($this->dActivityOrder->getFieldConditionOrderTids($orderCondition,$tids));
				}	
			}else{
				$tids=0;
			}
			$this->assign('searchData',$searchData);
		}
		return 	$tids;
 	}
/*
 *售后订单页面处理 
 * */
 	public function aftersaleOrderPageDeal($condition,$aftersaleTids,$aftersalesStatus,$type,$onlyType){
		//售后的进度
		if($onlyType){
			$detailTids=$this->dOrder->getFieldThisConditionTradeInfo($afterStatusTids,$onlyType);
		}
		if(!empty($aftersalesStatus) && !empty($aftersaleTids)){
			$afterTid=$this->dOrder->getFieldOrderInfoOrder($aftersaleTids,$aftersalesStatus,'tid');
			$afterStatusTids=array_unique($afterTid);
		}else{
			$afterStatusTids=array_unique($aftersaleTids);
		} 
		if(I('serachIdentity')==1){
			$afterStatusTids=$this->searchMoreConditionData(array_unique($aftersaleTids));	
		}
		//比较导出及搜索数据的差值begin
//		foreach(array_unique($aftersaleTids) as $k=>$v){
//			if(!in_array($v, $afterStatusTids)){
//				$diffTids[]=$v;
//			}
//		}
		//比较导出及搜索数据的差值end
 		$size = 20;
		if(I('execlType') && I('execlType')=="exportExcel"){
			//导出execl
			$condition['tid']=array('in',$afterStatusTids);
			$orderExecl=$this->dOrder->getOrder($condition);   //另一份导出execl  方法orderExportExcel
			if(!empty($orderExecl)){
				$execlData=$this->OrderDeal($orderExecl,"execl");	
				$this->orderExportExcel($execlData);   				
			}
		}
		$number=count($afterStatusTids);	
		//该状态下的个数
		$allTids=array_unique($aftersaleTids);
		$count['all']=count($allTids);
		$count['earlyProcess']=$this->getafterCount("WAIT_EARLY_PROCESS",$allTids);
		$count['process']=$this->getafterCount("WAIT_PROCESS",$allTids);
		$count['buyerSend']=$this->getafterCount("WAIT_BUYER_SEND_GOODS",$allTids);
		$count['getGoods']=$this->getafterCount("WAIT_SELLER_CONFIRM_GOODS",$allTids);
		$count['sendGoods']=$this->getafterCount("SELLER_SEND_GOODS",$allTids);
		$count['pass']=$this->getafterCount("SELLER_REFUSE",$allTids);
		$count['waitRefund']=$this->getafterCount("REFUND_PROCESS",$allTids);
		$count['sucess']=$this->getafterCount("SUCCESS",$allTids);
		$page = new \Think\Page($number,$size);
		$rollPage = 20; //分页栏显示的页数个数；
		$page -> setConfig('first' ,'首页');
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$OrderTids=array_slice($afterStatusTids,$start,$pagesize);
		if(!empty($OrderTids)){
			$condition['atid']=array('in',$OrderTids);
		}else{
			$condition['atid']=0;
		}
		$orderRes=$this->dActivityOrder->getAftersaleOrder($condition,$OrderTids);
		if(!empty($orderRes)){
			$orderRes=A('Order')->activityOrderDeal($orderRes);
		}
		if(!empty($OrderTids)){
			$afterBnCondition['tid']=array('in',$OrderTids);
			$afterBnCondition['trade_source']="activity";
		}
		if($type=="aftersale"){
			$afterInfo=$this->dAftersales->getAllThisAftersales($afterBnCondition,'aftersales_bn,tid,created_time,modified_time');
		}else if($type=="redund"){
			$afterInfo=$this->dAftersales->getAllRefundOrder($afterBnCondition,'tid,created_time,modified_time');
		}
		if($afterInfo){
			foreach($orderRes as $key=>$value){
				foreach($afterInfo as $keys=>$values){
					if($value['atid']==$values['tid']){
						$orderRes[$key]['afterInfo']=$values;
					}
				}			
			}			
		}
		foreach($orderRes as $key=>$value){
			foreach($value['items'] as $keys=>$values){
				$orderRes[$key]['items'][$keys]['aftersalesStatus']=A('Order')->orderStatusLastReturn($values['aftersales_status']);
			}
		}
		$style = "pageos";
		$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$this -> assign('pagestr',$pagestr);	
		$this->assign('list',$orderRes);
		$this->assign('count',$number);
		$this->assign('nowLocal',$aftersalesStatus);
		$this->assign('number',$count);
		
 	}
/*计算改状态下的数量*/
	public function getafterCount($afterProcess,$tids){
		if($tids){
			$condition['aftersales_status']=$afterProcess;
			$condition['tid']=array('in',$tids);
			$res=$this->dOrder->getOrderItemsToNum($condition);
			$afterStatusTids=array_unique($res);
			return count($afterStatusTids);
		}else{
			return 0;
		}
	}
/*
 * 回寄审核
 * */	
	public function returnSendProcess(){
		$tid=I('tid');
		$afterType=I('afterType');//如存在为换修 需商家回寄
		if(!empty($afterType)){
			//收货人信息
			$res=$this->dActivityOrder->getThisOrderInfo($tid,'atid,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address');
			$this->assign('info',$res);			
		}
		if($tid){
			$this->getProcessData($tid,"WAIT_SELLER_CONFIRM_GOODS");
			$oids=$this->dActivityOrder->getFieldThisConditionOrderInfo(array('aftersales_status'=>'WAIT_SELLER_CONFIRM_GOODS','atid'=>$tid),'order_id');
			$afterCondition['tid']=$tid;
			if(!empty($oids)){
				$afterCondition['oid']=array('in',$oids);
				$afterInfo=$this->dAftersales->getAllThisAftersales($afterCondition,'shop_explanation,sendback_data');
				foreach($afterInfo as $key=>$value){
					$addrJson=$value['shop_explanation'];
					$sendJson=$value['sendback_data'];
					break;
				}
				$addrInfo=json_decode($addrJson,TRUE);
				$sendInfo=json_decode($sendJson,TRUE);
			}
			$this->assign('addrInfo',$addrInfo); 		
			$this->assign('sendInfo',$sendInfo); 
			$this->assign('afterType',$afterType);
			$this->display();
		}else{
			echo '请用正确方式进入';
		}
	} 
/*
 *回寄审核处理 
 * */
 	public function returnSendProcessDeal(){
		$afterType=I('afterType');//存在时为审核换货、维修，不存在时为审核退货
 		$content=trim(I('content'));
		$tid=I('tid');
		$oids=I('oids');
		$refundFee=I('refundFee');
		$logi=trim(I('logi'));
		$logiNo=trim(I('logi_no'));
		if(empty($oids)){
			$this->error('操作方式有误！');
		}		
		if(!empty($afterType)){
			if(empty($logi) || empty($logiNo)){
				$this->error('请输入快递名称或快递单号！');
			}
		}
		if(empty($content)){
			if(empty($afterType)){
				$this->error('请输入处理审核意见！');
			}else{
				$this->error('请输入发货说明！');
			}
		}
		if(empty($afterType) && $refundFee <=0){
			$this->error('请输入正确的金额！');
		}
		if(empty($afterType)){
			//退货
			//修改后面需退款的金额
			if(!is_numeric($refundFee)){
				$this->error('请输入正确的金额！');
			}else if(intval($refundFee) <=0){
				$this->error('请输入正确的金额！');
			}			
//			$checkInfo=$this->dOrder->getOidsOrderInfo($oids,'oid,price,aftersales_num');
//			foreach($checkInfo as $key=>$value){
//				$checkFee +=$value['price']*$value['aftersales_num'];
//			}	
//			$checkFee=round($checkFee,2);
			$refundFee=round($refundFee,2);			
//			$subrFee = bccomp($refundFee,$checkFee,2);
//			if($subrFee == 1){
//				$this->error('金额不能大于售后商品的总值！');
//			}							
			$refundData=$this->dActivityOrder->getThisOrderInfo($tid,'user_id,atid,total_fee,payed_fee,refund_fee,refund_time');
			if(I('refundsType')){
				$refundData['refunds_type']=1;
			}
			if(!empty($refundData['refund_fee']) && empty($refundData['refund_time'])){
				$refundFee=$refundFee+$refundData['refund_fee'];
			}
			$totalFee=round($refundData['payed_fee'],2);			
			if($refundFee > $totalFee){
				$this->error('退款金额大于支付金额，请重新输入退款金额！');
			}
			unset($refundData['refund_fee']);
			unset($refundData['refund_time']);
			$feeData['refund_fee']=$refundFee;
			$feeData['refund_time']=0;
			$feeRes=$this->dActivityOrder->editTradeInfo($tid,$feeData);	
			//写进去退款申请表
				//读取该订单的信息
			$refundData['total_price']=$refundData['total_fee'];
			$aftersaleCondition['oid']=array('in',$oids);
			$refundInfo=$this->dAftersales->getAllThisAftersales($aftersaleCondition,'aftersales_bn,oid,shop_explanation');
			foreach($refundInfo as $key=>$value){
				$refundData['refund_bn']=date('ymdHis').rand(1000000,9999999);
				$refundData['aftersales_bn']=$value['aftersales_bn'];
				$refundData['oid']=$value['oid'];
				$refundData['refunds_reason']=$value['shop_explanation'];
				$refundData['created_time']=time();
				$refundData['trade_source']="activity";
				$this->dAftersales->addreFundData($refundData);
			}	
			$remarks="确认收到回寄商品：管理员备注".$content;
			$statusData['shop_explanation']=$content;
			$data['aftersales_status']="REFUND_PROCESS";
			$resultSay="处理成功,等待财务退款！";
		}else{
			//换货、维修
			$remarks="回寄成功:,快递名称：".$logi.",快递单号：".$logiNo."发货说明：".$content;
			$data['aftersales_status']="SELLER_SEND_GOODS";
			$ExpressArr['logi']=$logi;
			$ExpressArr['logi_no']=$logiNo;
			$ExpressArr['content']=$content;
			$ExpressJson=json_encode($ExpressArr);				
			$statusData['sendconfirm_data']=$ExpressJson;
			//收货状态已处理
			$statusData['status']=2;
			$resultSay="回寄成功，等待用户收货！";
		}
		//写进管理员操作订单日志表
		$oidsString=implode(',',$oids);		
		$dealType="回寄审核";
		$logData['tid']=$tid;
		A('Order')->markTradeLog($tid,$dealType,$remarks,$oidsString);
		//改变售后申请表状态/内容
		$condition['oid']=array('in',$oids);
		$statusData['modified_time']=time();
		$aftersaleRes=$this->dAftersales->editAftersales($condition,$statusData,'shop_explanation,sendconfirm_data,status');		
		//该订单分表的状态
		$statusRes=$this->dActivityOrder->editThisConditionOrderInfo($condition,$data,'aftersales_status');
		if(!empty($aftersaleRes) && !empty($statusRes)){
			echo "<div style='padding-left:26%;padding-top:22%;color:#5a98de;font-weight: bolder;font-size: x-large;'>".$resultSay."</div>";
		}
	}
/*
 * 申请换货/维修
 * 
 * */
	public function changeItem(){
		$tid=I('tid');
		if($tid){
			$items=$this->dActivityOrder->getOrderItems($tid);
			$items=$this->repairItemTitle($items);
			$res=$this->dActivityOrder->getThisOrderInfo($tid,'cancel_reason');
			$this->assign('mark',$res['cancel_reason']);
			$this->assign('tid',$tid);
			$this->assign('items',$items);
			$this->display();
		}else{
			echo 'Can not find pay bill!';
		}
	}
/*
 * 申请换货/维修处理
 * */
	public function savechangeItem(){
		$tid=I('tid');
		$mark=I('mark');
		$order=I('order');
		$applyType=I('applyType'); //申请类型----1换货   2维修
		$reason=I('reason');
		$logi=trim(I('logi'));
		$logiNo=trim(I('logiNo'));		
		$orderArr=explode("#", $order);
		array_pop($orderArr);
		foreach($orderArr as $key=>$value){
			$valueArr=explode("-", $value);
			$item[$key]['oid']=$valueArr[0];
			$item[$key]['num']=$valueArr[1];
			$item[$key]['title']=$valueArr[2];
			$oids[]=$valueArr[0];
			$details[]=$valueArr[1].'x'.$valueArr[2];
		}
		if($tid){
			$tradeInfo=$this->dActivityOrder->getThisActivityOrderInfo($tid,'user_id,atid');
			$tradeInfo['tid']=$tradeInfo['atid'];
			$tradeInfo['description']=$mark;
			$tradeInfo['aftersales_type']='REFUND_GOODS';
			$tradeInfo['modified_time']=time();			
			if($applyType==1){
				//换货
				$data['order_status']="EXCHANGE";
				$tradeInfo['aftersales_type']='EXCHANGING_GOODS';
				$logType="申请换货";
			}else if($applyType==2){
				//维修
				$data['order_status']="REPAIR";
				$tradeInfo['aftersales_type']='REPAIRING_GOODS';
				$logType="申请维修";
			}
			$data['cancel_reason']="管理员:".$this->realName."代用户申请<br/>描述：".$mark;
			$res=$this->dActivityOrder->editTradeInfo($tid,$data);
		}
		//写进售后申请表、改变订单子表
		$logiArr['logi']=$logi;
		$logiArr['logi_no']=$logiNo;
		$logiArr['content']="管理员:".$this->realName."代用户申请";
		$logiJson=json_encode($logiArr);
		$tradeInfo['sendback_data']=$logiJson;
		$tradeInfo['reason']=$reason;		
		foreach($item as $key=>$value){
			if($value['oid']){
				$tradeInfo['aftersales_bn']=date('ymdHis').rand(100000,999999);
				$tradeInfo['oid']=$value['oid'];
				$tradeInfo['title']=$value['title'];
				$tradeInfo['num']=$value['num'];
				$tradeInfo['trade_source']="activity";
				$this->dActivityOrder->editOrderForConfirmGoods($value['oid'],$value['num']);
				$this->dAftersales->addAftersales($tradeInfo);
			}
		}
		//写进管理员操作日志表
		$oidsString=implode(',', $oids);		
		$dealType=$logType;
		$logData['tid']=$tid;
		$remarks='申请理由:'.$mark.'  详情:'.implode(',', $details);
		A('Order')->markTradeLog($tid,$dealType,$remarks,$oidsString);
		echo json_encode($res);
	}			




	
 
 
}