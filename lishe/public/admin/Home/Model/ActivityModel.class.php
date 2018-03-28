<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐
 * @version  	$Id: OrderModel.class.php v001 2016-09-08
 * @description 订单操作
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class ActivityModel extends CommonModel{
	public function __construct(){
		$this->modelItem=M('sysitem_item');
		$this->modelSku = M('sysitem_sku');
		$this->modelItemStatus=M('sysitem_item_status');
		$this->modelAccount=M('sysuser_account');
		$this->modelCompany=M('company_config');
		$this->modelACateGory=M('company_activity_category');
		$this->modelActivity=M('company_activity');
		$this->modelIConfig=M('company_item_config');
		$this->modelAItem=M('company_activity_item');
		$this->modelCConfig=M('company_category_config');
		
		$this->modelTrade=M('systrade_trade');
		$this->modelOrder=M('systrade_order');
		$this->modelReturn=M('systrade_return');
		
		$this->modelPaybill=M('ectools_trade_paybill');
		$this->modelPayment=M('ectools_payments');
		
		$this->modelSiteThemeFile=M('site_themes_file');
	}	
//取出活动个数
	public function getACount(){
		
		return $this->modelActivity->count();
	}	
//取出全部活动
	public function getAllActivitys($limit){
		
		return $this->modelActivity->order('aid desc')->limit($limit)->select();
	}	
//取出指定的活动信息
	public function getThisInfo($aid){
		return $this->modelActivity->where('aid='.$aid)->find();
	}
//取出指定的活动信息
	public function getFieldAInfo($map,$field){
		return $this->modelActivity->where($map)->getField($field);
	}
//修改首页活动
	public function saveThisActivity($aid,$data){
		return $this->modelActivity->where('aid='.$aid)->data($data)->save();
	}
//添加首页活动
	public function addThisActivity($data){
		return $this->modelActivity->data($data)->add();
		
	}	
 //取出活动个数
	public function getActivityCount(){
		
		return $this->modelACateGory->count();
	}	
//取出全部专题
	public function getThisActivitys($aid,$field){
		
		return $this->modelACateGory->where('aid='.$aid)->field($field)->order('order_sort desc')->select();
	}
//取出指定活动Id的内容
	public function getThisActivity($id,$field){
		
		return $this->modelACateGory->where('activity_config_id='.$id)->field($field)->find();
	}	
//取出指定活动Id的指定内容
	public function getThisActivityField($id,$field){
		
		return $this->modelACateGory->where('activity_config_id='.$id)->getField($field);
	}		
//取出type=2活动的信息
	public function getThisActivityItem($aid){
		return $this->modelAItem->where('aid='.$aid)->select();
	}
//取出type=2活动的信息
	public function getSomeActivityItem($condition,$field){
		return $this->modelAItem->where($condition)->field($field)->order('aitem_id desc')->select();
	}	
//修改活动内容
	public function updateThisActivity($id,$data){
		if(empty($data['post_fee'])){
			unset($data['post_fee']);
		}
		return $this->modelACateGory->where('activity_config_id='.$id)->data($data)->save();
		
	}
//修改活动
	public function editAcategory($map,$data){
		if(empty($map)){
			return false;
		}
		return $this->modelACateGory->where($map)->save($data);
	}	
//添加指定活动专题内容	
	public function addActivityInfo($data){
		return $this->modelACateGory->data($data)->add();	
	}	
//取出企业个数
	public function getCompanyCount($where = array()){
		$where['is_delete'] = 0;
		return $this->modelCompany->where($where)->count();
	}	
//取出企业信息
	public function getCompany($where = array(), $limit){
		$where['is_delete'] = 0;
		return $this->modelCompany->field('config_id,com_name,com_id,creat_time')->where($where)->limit($limit)->select();
	}
//取出企业的信息
	public function getThisCompanyInfo($confId){
		
		return $this->modelCompany->where('is_delete=0 and config_id='.$confId)->find();
	}	
	
//取出该该公司的所有专区配置信息
	public function getThisItemCompany($comId){
		return $this->modelIConfig->where('com_id='.$comId)->order('order_sort desc ,modifyine_time desc')->select();
	}
//取出指定专区配置信息
	public function getThisItemConf($itemConfId){
		return $this->modelIConfig->where('item_config_id='.$itemConfId)->find();
	}
//修改指定专区配置信息
	public function editThisItemConf($itemConfId,$data){
		return $this->modelIConfig->where('item_config_id='.$itemConfId)->data($data)->save();
		
	}	
//彻底删除该分类
	public function delItemConf($itemConfId){
		return $this->modelIConfig->where('item_config_id='.$itemConfId)->delete();
		
	}	
//添加专区
	public function addItemConf($data){
		return $this->modelIConfig->data($data)->add();
		
	}		
//取出专区配置下的分类信息
	public function getThisCategoryInfo($itemConfId){
		return $this->modelCConfig->where('item_config_id='.$itemConfId)->order('order_sort desc')->select();
	}	
//取出指定分类的信息
	public function getThisCategoryConf($catConfId){
		return $this->modelCConfig->where('cat_config_id='.$catConfId)->field('cat_config_id,cat_id,cat_name,recommend,cat_ids,item_ids,order_sort,shop_id,profit_rate,disabled')->find();
	}
//修改指定分类的值
	public function editThisCatgory($catConfId,$data,$field="shop_id,recommend,cat_ids,profit_rate,cat_id,cat_name,item_ids,order_sort,disabled"){
		return $this->modelCConfig->where('cat_config_id='.$catConfId)->field($field)->data($data)->save();
		
	}	
//添加分类
	public function addCatgory($data){
		return $this->modelCConfig->data($data)->add();
		
	}
//彻底删除该分类
	public function delCategory($catConfId){
		return $this->modelCConfig->where('cat_config_id='.$catConfId)->delete();
		
	}
//取出指定活动信息
	public function getThisActivityItemInfo($aitemId){
		return $this->modelAItem->where('aitem_id='.$aitemId)->find();
	}	
//修改指定活动的信息
	public function editThisActivityItemInfo($aitemId,$data){
		return $this->modelAItem->where('aitem_id='.$aitemId)->data($data)->save();
	}	
//修改指定条件下的活动信息
	public function editAidActivityItemInfo($aid,$data){
		return $this->modelAItem->where('aid='.$aid)->data($data)->save();
		
	}
//添加活动商品信息
	public function addActivityItemInfo($data){
		return $this->modelAItem->data($data)->add();
	}
//得到指定商品的某些信息
	public function getThisItemInfo($itemId){
		return $this->modelItem->where('item_id='.$itemId)->field('price,cost_price,mkt_price,weight')->find();		
	}
	/**
	 * 取得指定商品的指定信息
	 */
	public function getItemInfo($map,$field){
		return $this->modelItem->where($map)->getField($field);		
	}
	/**
	 * 取得指定商品sku的指定信息
	 */
	public function getSkuInfo($map,$field){
		return $this->modelSku->where($map)->getField($field);		
	}	
	//更改sku信息
	public function updateSku($map,$data){
		if(empty($map) || empty($data)){
			return false;
		}
		return $this->modelSku->where($map)->setField($data);		
	}
	//计算满返活动订单的返还金额 赵尊杰 2016-10-11
	public function getActivityReturn($comId,$aid,$startDate,$endDate,$rule,$itemIds='',$limit='0,30'){
		$condition=array(
			//'status' => 'TRADE_FINISHED',
			//'trade_status' => 'TRADE_FINISHED',
			//'modified_time' => array('lt', time() - (7*24*3600)), //7天之前
			'return_status'=>'NO_APPLY',
			'payed_fee'=>array('gt',0),
			'pay_time'=> array(array('gt',$startDate), array('lt',$endDate)) ,
		);
		if (!empty($comId)) {
			$condition['com_id'] = $comId;
		}
		
		$count=$this->modelTrade->where($condition)->count('tid');
		$trade=$this->modelTrade->field('tid')->where($condition)->order('pay_time ASC')->limit($limit)->select();
		if(!empty($trade)){
			foreach($trade as $key=>$value){
				$tid[]=$value['tid'];
			}			
			$billCondition=array(
				'tid'=>array('in',''.implode(',',$tid).''),
				'status' => 'succ',
			);			
			$paybill=$this->modelPaybill->field('payment_id,tid,user_id')->where($billCondition)->select();
			foreach($paybill as $key=>$value){
				$paymentId[]=$value['payment_id'];
			}
			
			$paymentId=array_flip(array_flip($paymentId));
			$payCondition=array(
				'payment_id'=>array('in',''.implode(',',$paymentId).''),
				'status' => 'succ',
			);
			$payments=$this->modelPayment->field('payment_id,status,cur_money')->where($payCondition)->select();
			foreach($payments as $key=>$value){
				$payStatus[$value['payment_id']]=$value['status'];
				$payMoney[$value['payment_id']]=$value['cur_money'];
			}
			
			foreach($paybill as $key=>$value){
				$payment[$value['payment_id']]['payment_id']=$value['payment_id'];
				$payment[$value['payment_id']]['tid'][]=$value['tid'];
				$payment[$value['payment_id']]['user_id']=$value['user_id'];
				$payment[$value['payment_id']]['payment']=$payMoney[$value['payment_id']];
				$payment[$value['payment_id']]['status']=$payStatus[$value['payment_id']];
			}
			
			$skuIds = array();
			//排除活动
			if (!empty($itemIds)) {
				$map = array(
					'item_id' => array('in', $itemIds),
					'parent_sku_id' => 0
				);
				$skuIds = M('sysitem_sku')->where($map)->getField('sku_id',true);
				if (is_array($skuIds)) {
					$skuIds = array_flip($skuIds);
				}
			}
			
			foreach($payment as $key=>$value){
				$tids=implode(',',$value['tid']);
				$tradeCondition=array(
					'payed_fee'=>array('gt',0),
					'tid'=>array('in',$tids)
				);
				
				$trade=$this->modelTrade->field('payment,refund_fee')->where($tradeCondition)->select();
				$totalPay=0;
				$totalRefund=0;
				foreach($trade as $keys=>$values){
					$totalPay+=$values['payment'];
					$totalRefund+=$values['refund_fee'];
				}
//				$orderCondition=array();
// 				if(!empty($itemIds)){
// 					$orderCondition=array(
// 						'item_id'=>array('in', $itemIds),
// 						'tid'=>array('in', $tids)
// 					);
// 				}else{
					$orderCondition=array(
						'tid'=>array('in',$tids)
					);
// 				}
				$order=$this->modelOrder->field('sku_id,price,cost_price,num,aftersales_num')->where($orderCondition)->select();
				
				if(empty($order)){
					continue;
				}
				//如果限制活动商品，查询订单中参与活动的商品
				$item=array();
				$activityFee=0;//参与活动的商品的金额
				$paymentCost = 0;
				$activityFeeCost = 0;
				foreach($order as $okey=>$ovalue){
					$skuId = $ovalue['sku_id'];
					$num=$ovalue['num']-$ovalue['aftersales_num']; //满返有效数量
					//$tid = $ovalue['tid'];
					$costPrice = $ovalue['cost_price'];
					//支付总成本价
					$paymentCost += $costPrice * $num;
					//限制满返活动商品成本价
					if (isset($skuIds[$skuId])) {
						$itemFee=$ovalue['price']*$num; //限制满返活动商品总金额 
						$activityFee+=$itemFee; //限制满返活动的总价格
						$item[]=array($itemId,$num,$itemFee); //限制满返活动商品
						$activityFeeCost += ($costPrice * $num); //限制满返活动总成本价
					}
				}
				
				if ($activityFee <= 0) {
					continue ;
				}
				$returnFee=0;
				//根据规则结算返还的金额
				foreach($rule as $rkey=>$rvalue){
					if($activityFee>=$rvalue[0] && $activityFee<$rvalue[1]){
						$returnFee=$rvalue[2];
					}
				}
				//根据规则结算返还的金额
				if ($returnFee <= 0) {
					continue ;
				}
				
				$checkReturn=array();
				$checkReturn=$this->modelReturn->field('return_id,join_activity,return_fee,return_status,sys_check')->where('payment_id='.$value['payment_id'])->find();
				if($checkReturn['return_id']>0){
					$returnId[]=$checkReturn['return_id'];
					if ($checkReturn['sys_check'] <= 0) {
						continue;
					}
					$returnStatus=$checkReturn['return_status'];
					if ($returnStatus == 'TRADE_FINISHED') {
						continue;
					}
					//返还的金额取最大值
					//if($returnFee<$checkReturn['return_fee']){
					//	$returnFee=$checkReturn['return_fee'];
					//	$returnStatus='WAIT_PROCESS';
					//}
					$join=json_decode($checkReturn['join_activity'],TRUE);
					$join[$aid]=array(
						'activity_fee'=>$activityFee,
						'payed_fee'=>$totalPay,
						'refund_fee'=>$totalRefund,
						'return_fee'=>$returnFee,
						'item'=>$item//参与活动中的商品
					);
					$data=array(
						'payment'=>$value['payment'],
						'payment_cost' => $paymentCost,
						'activity_fee'=>$activityFee,
						'activity_fee_cost' => $activityFeeCost,
						'payed_fee'=>$totalPay,
						'refund_fee'=>$totalRefund,
						'return_fee'=>$returnFee,
						//'return_status'=>$returnStatus,
						'aid'=>$aid,
						'join_activity'=>json_encode($join),
						'modifyine_time'=>time()
					);
					$this->modelReturn->where('return_id='.$checkReturn['return_id'])->save($data);
				}else{
					$join[$aid]=array(
						'activity_fee'=>$activityFee,
						'payed_fee'=>$totalPay,
						'refund_fee'=>$totalRefund,
						'return_fee'=>$returnFee,
						'item'=>$item//参与活动中的商品
					);
					$data=array(
						'payment_id'=>$value['payment_id'],
						'payment'=>$value['payment'],
						'payment_cost' => $paymentCost,
						'activity_fee'=>$activityFee,
						'activity_fee_cost' => $activityFeeCost,
						'payed_fee'=>$totalPay,
						'refund_fee'=>$totalRefund,
						'return_fee'=>$returnFee,
						'aid'=>$aid,
						'join_activity'=>json_encode($join),
						'user_id'=>$value['user_id'],
						'pay_status'=>$value['status'],
						'tids'=>$tids,
						'created_time'=>time(),
						'modifyine_time'=>time()
					);
					$returnId[]=$this->modelReturn->add($data);
				}
			}
			return array('count'=>$count,'returnId'=>$returnId);			
		}
		return NULL;
	}
	
	public function checkActivityReturn($aid){
		$returnCondition=array(
			'aid'=>$aid,
			'pay_status'=>'ready',
			'sys_check'=>1,
			'return_id'=>array('gt',1535)
		);
		$return=$this->modelReturn->field('return_id,tids')->where($returnCondition)->order('payment_id ASC')->limit($limit)->find();
		if(!empty($return)){
			if(count(explode(',',$return['tids']))==1){
				$this->modelReturn->where('return_id='.$return['return_id'])->save(array('sys_check'=>0));
				return array($return['return_id']);
			}
			$tidCondition=array(
				'pay_status'=>'succ',
				'tids'=>array('in',$return['tids'])
			);
			$returnSucc=$this->modelReturn->field('payment')->where($tidCondition)->select();
			if(!empty($returnSucc)){
				$payment=0;
				foreach($returnSucc as $skey=>$svalue){
					$payment+=$svalue['payment'];
				}
				$this->modelReturn->where('return_id='.$return['return_id'])->save(array('payment'=>$payment,'sys_check'=>2));
				$stCondition=array(
					'tids'=>array('in',$return['tids']),
					'sys_check'=>1
				);
				$this->modelReturn->where($stCondition)->save(array('sys_check'=>0));
			}
			return array($return['return_id']);
		}		
		return NULL;
	}
/*
 * 添加活动/专题模板
 * */
	public function addThemes($data){
		return $this->modelSiteThemeFile->data($data)->add();
	}
/*
 * 取出所有活动/专题模板
 * */
	public function getAllThemes(){
		return $this->modelSiteThemeFile->where(array('theme'=>'activity'))->field('id,filename,preview')->select();
	}
/*
 * 取出所有活动/专题模板
 * */
	public function getFieldThemes($field){
		return $this->modelSiteThemeFile->where(array('theme'=>'activity'))->getField($field,TRUE);
	}	
	
	
}  
?>  	