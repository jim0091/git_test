<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐
 * @version  	$Id: RechargeModel.class.php v001 2016-12-30
 * @description 充值管理
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class RechargeModel extends CommonModel{
	public function __construct(){
		$this->zzgxExchApply=M('zzgx_exch_apply');
		$this->zzgxExchApplyCoupon=M('zzgx_exch_apply_coupon');
    $this->ectoolsPayments = M('ectools_payments');//支付表
    $this->userDeposit = M('sysuser_user_deposit');//积分表
    $this->userDepositLog=M('sysuser_user_deposit_log');
		
	}	
/*
 * 取出所有正章干洗的兑换
 * */
	public function getAllZZExchApply($where,$limit,$field){
		if(!isset($where['apply_status']) || empty($where['apply_status'])){
			$where['apply_status']=array('neq','-1');
		} 
		if($where['apply_status']==3){
			$where['apply_status']=0;
		}	
		return $this->zzgxExchApply->where($where)->field($field)->limit($limit)->order('apply_id desc')->select();
	}
/*
 * 正章干洗的数量
 * */	
 	public function getZZExchApplyCount($where){
		if(!isset($where['apply_status']) || empty($where['apply_status'])){
			$where['apply_status']=array('neq','-1');
		} 
		if($where['apply_status']==3){
			$where['apply_status']=0;
		}	
		return $this->zzgxExchApply->where($where)->count();
 	} 
/*
 *取出单条正章干洗 
 * */
 	public function getThisZZExInfo($where,$field){
		$where['apply_status']=array('neq','-1');
		return $this->zzgxExchApply->where($where)->field($field)->find();
 	}
/*
 * 修改正章干洗的数据
 * */	
 	public function updateZZExInfo($where,$data,$field){
 		$where['apply_status']=array('neq','-1');
		return $this->zzgxExchApply->where($where)->field($field)->save($data);		
 	}
/*
 * 取出所有正章干洗的兑换
 * */
	public function getAllZZExchApplyCoupon($where,$field){
		return $this->zzgxExchApplyCoupon->where($where)->field($field)->select();
	}	
/*
 * 添加支付表数据
 * */
	public function addPaymentInfo($data){
		if(empty($data)){
			return FALSE;
		}
		return $this->ectoolsPayments->add($data);
	}
/*
 * 取出支付表数据
 * */
	public function getPaymentInfo($paymentId,$field){
		if(empty($paymentId)){
			return null;
		}
		$where=array(
			'payment_id' =>$paymentId,
		);
		return $this->ectoolsPayments->where($where)->field($field)->find();
	}
/*
 * 更新支付表数据
 * */	
		public function updatePaymentInfo($paymentId,$data){
			if(empty($paymentId)){
				return null;
			}
			$where=array(
				'payment_id' =>$paymentId,
			);			
			return $this->ectoolsPayments->where($where)->save($data);
		}
	
/*
 * 更新本地积分
 * */
	public function updateLocalPoint($uid,$totalFee,$balance){
		if(empty($uid) || empty($totalFee) || empty($balance)){
			return null;
		}
		$where=array(
			'user_id' => $uid,
		);
    $this->userDeposit->where($where)->setInc('deposit',$totalFee);
    $this->userDeposit->where($where)->setInc('balance',$balance);
    $this->userDeposit->where($where)->setInc('commonAmount',$balance);		
	}
/*
 * 添加积分详情表
 * */	
	public function addPointDeatil($data){
		return $this->userDepositLog->data($data)->add();
	}
	
	
}  
?>  	