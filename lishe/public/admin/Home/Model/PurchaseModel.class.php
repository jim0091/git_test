<?php  
/**
  +----------------------------------------------------------------------------------------
 *  CategoryModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐 
 * @description 店铺信息
  +-----------------------------------------------------------------------------------------
 */
 namespace Home\Model;
 use Think\Model;
 class PurchaseModel extends CommonModel{
	public function __construct(){
		$this->supplier_user=M('supplier_user');
		$this->supplier_plan=M('supplier_plan');
		$this->supplier_goods=M('supplier_order_goods');
		$this->supplier_order=M('supplier_order');
		$this->syscatgory=M('syscategory_cat');
	}


	 /*通过商品获取关联表列表*/
	 public function getFieldByGoods($supplier_goods,$field_name,$model){
		 $ids=array();
		 foreach($supplier_goods as $key => $val){
			 $ids[]=$val[$field_name];
		 }
		 $where[$field_name]=array('in',$ids);
		 $res=$model->where($where)->select();
		 $data=array();
		 foreach($res as $key => $val){
			 $data[$val[$field_name]]=$val;
		 }
		 return $data;
	 }
	//通过ID获取计划
	public function getPlanById($id){
		return $this->supplier_plan->where("plan_id=$id")->select();
	}
	 public function getCategory($pid=0){
		 $where=array();
		 if($pid>=1){
			$where['parent_id']=$pid;
		 }else{
			 $where['level']=1;
		 }
		 return $this->syscatgory->field('cat_id,cat_name')->where($where)->select();

	 }
	 //批量更新多条方法
	 private function updateMany($display_order,$table,$key="id",$field){


		 $ids = implode(',', array_keys($display_order));
		 $sql = "UPDATE $table SET $field = CASE id ";
		 foreach ($display_order as $id => $ordinal) {
			 $sql .= sprintf("WHEN %d THEN %d ", $id, $ordinal);
		 }
		 $sql .= "END WHERE $key IN ($ids)";
		 $res=$this->supplier_goods->execute($sql);
		 return $res;
	 }
	 //批量更新多条方法2
	 private function updateMany2($display_order,$table,$key="id",$field){


		 $ids = implode(',', array_keys($display_order));
		 $sql = "UPDATE $table SET $field = CASE id ";
		 foreach ($display_order as $id => $ordinal) {
			 $sql .= sprintf("WHEN %d THEN %d ", $id, $ordinal);
		 }
		 $sql .= "END WHERE $key IN ($ids)";
		 $res=$this->supplier_goods->execute($sql);
		 return $res;
	 }
	 //设置实际到货数量
	 public function setStorageNumber($arr){
		 $res=$this->updateMany($arr,'supplier_order_goods','id','storage_number');
		 return $res;
	 }
	 //设置库存
	 public function setStoreNumber($arr){
		 $res=$this->updateMany($arr,'sysitem_sku_store','sku_id','store=store+');
		 return $res;
	 }
	 //设置采购单状态
	 public function setOrderStatus($orderId,$status=5){
		 $data=array(
			 'status'=>$status
		 );
		 $res=$this->supplier_order->where("order_id=$orderId")->setField($data);
		 return $res;
	 }
	
 }
