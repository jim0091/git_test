<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐
 * @version  	$Id: AftersalesModel.class.php v001 2016-09-08
 * @description 售后-----
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class AftersalesModel extends CommonModel{
	public function __construct(){
		$this->modelItem=M('sysitem_item');
		$this->modelAccount=M('sysuser_account');
		$this->modelAftersales=M('systrade_aftersales');//售后申请表-----售后处理成功	加入退款申请表
		$this->modelRefunds=M('systrade_refunds');	//退款申请表
		$this->modelRefund=M('systrade_refund');	//退款记录表
	}	
	/*
	 *添加售后信息到售后申请表 
	 * */
	public function addAftersales($data){
	 	return $this->modelAftersales->data($data)->add();
	}
	/*
	 查找一条指定条件的售后信息
	 * */
	public function findAftersales($condition,$field){
		$condition['is_delete']=0;
	 	return $this->modelAftersales->where($condition)->field($field)->find();
	}
	/*
	 查询指定条件的多条售后信息
	 * */
	public function getAllThisAftersales($condition,$field){
		$condition['is_delete']=0;
	 	return $this->modelAftersales->where($condition)->field($field)->order('modified_time desc')->select();
	}	
	/**
	 * 编辑售后信息
	 * */	
	public function editAftersales($condition,$data,$field){
	 	return $this->modelAftersales->where($condition)->data($data)->field($field)->save();
		
	}
	/*
	 *退款申请表添加数据
	 */
	public function addreFundData($data){
		return $this->modelRefunds->data($data)->add();
	}
	/*
	 * 编辑退款申请表数据
	 * */
	public function editrefundInfo($condition,$data,$field){
		return $this->modelRefunds->where($condition)->data($data)->field($field)->save();
	}
	/*
	* 查询指定条件的多条售后信息(售后订单页)
	 * */
	public function getAllThisAftersalesOrder($condition){
		$condition['is_delete']=0;
	 	return $this->modelAftersales->where($condition)->order('modified_time desc,created_time desc')->getField('tid',TRUE);
	}
	/*
	* 查询指定条件的多条售后信息(售后订单页)高级搜索
	 * */
	public function searchMoreAfterOrder($condition,$tids){
		$condition['is_delete']=0;
			foreach($tids as $key=>$value){
					$tids[]="'".$value."'";
			}
			if($tids){
				$order="field(tid,".implode(",", $tids).")";
			}				
	 	return $this->modelAftersales->where($condition)->order($order)->getField('tid',TRUE);
	}	
	/*
	* 查询指定条件的多条售后数量
	 * */
	public function getAllThisAftersalesCount($aftersaleType){
		$condition['aftersales_type']=$aftersaleType;
		$condition['is_delete']=0;
	 	return $this->modelAftersales->where($condition)->count();
	}			
	/*
		* 查询指定条件的多条退款信息(售后订单页)
	 * */
	public function getAllThisRefundOrder($condition){
	 	return $this->modelRefunds->where($condition)->order('created_time desc')->getField('tid',TRUE);
	}			
	/*
	 查询指定条件的多条售后信息退款
	 * */
	public function getAllRefundOrder($condition,$field){
	 	return $this->modelRefunds->where($condition)->field($field)->select();
	}		
	//取得指定订单已退款的信息
	public function getRefundOrderInfo($tids){
		return $this->modelRefund->where(array('tid'=> array('in',$tids)))->field('refund_fee,tid')->select();
	}
	/*
	* 查询指定条件的多条退款订单高级搜索
	 * */
	public function searchMoreRefundOrder($condition,$tids){
			foreach($tids as $key=>$value){
					$tids[]="'".$value."'";
			}
			if($tids){
				$order="field(tid,".implode(",", $tids).")";
			}				
	 	return $this->modelRefunds->where($condition)->order($order)->getField('tid',TRUE);
	}		
	
}  
?>  	