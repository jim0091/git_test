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
 class SupplierModel extends CommonModel{
	const TABLE = "supplier_user";
	
	public function __construct(){
		$this->tableName = 'supplier_user';
		parent::__construct();
		$this->supplier_user=M('supplier_user');
		$this->supplier_sku=M('supplier_item_sku');
		$this->supplier_items=M("supplier_item");
		$this->supplier_push=M('supplier_push_goods');
		$this->sysitem_item=M("sysitem_item");
		$this->sysitem_sku=M("sysitem_sku");
		$this->sysshop_shop=M("sysshop_shop");
		$this->supplier_sku_prop=M("supplier_sku_prop");
		$this->syscategory_props=M('syscategory_props');
		$this->syscategory_prop_values=M('syscategory_prop_values');
		$this->syscategory_cat_rel_prop=M('syscategory_cat_rel_prop');
		$this->supplier_sku_sale_prop=M("supplier_sku_sale_prop");
		$this->modelSitemReviewed = M("supplier_item_reviewed");
		$this->modelShopCat = M("sysshop_shop_cat");
		$this->modelItemCount = M('sysitem_item_count');
		$this->modelItemStatus = M("sysitem_item_status");
		$this->modelItemStore =  M('sysitem_item_store');
		$this->modelItemSkuStore = M('sysitem_sku_store');
		$this->modelItemDesc = M('sysitem_item_desc');
		$this->modelSitemDesc=M('supplier_item_desc');
		//$this->sf_url="http://120.76.159.44:8080/lshe.framework.aoserver/api/sf/";
	}

	 /*通过商品获取关联表列表*/
	 public function getFieldByGoods($supplier_goods,$field_name,$model){
		 $ids=array();
		 foreach($supplier_goods as $key => $val){
			 $ids[]=$val[$field_name];
		 }
		 if(empty($ids)){
		 	return array();
		 }
		 $where[$field_name]=array('in',$ids);
		 $res=$model->where($where)->select();
		 $data=array();
		 foreach($res as $key => $val){
			 $data[$val[$field_name]]=$val;
		 }
		 return $data;
	 }

	 /*通过供应商列表获取对应的合同信息*/
	 public function getContractFiles($supplierUserList){
		 if(empty($supplierUserList)) return false;
		 $supplierIds = array();
		 foreach($supplierUserList as $val){
			 $supplierIds[] = $val['supplier_id'];
		 }
		 if(empty($supplierIds)){
		 	return array();
		 }
		 $where['supplier_id'] = array('in', $supplierIds);
		 $fileList = M('supplier_contract_file')
			 					->field('contract_id, name, supplier_id')
			 					->where($where)
			 					->order('supplier_id asc')->select();
		 $data = array();
		 foreach($fileList as  $val){
			 $data[$val['supplier_id']] = $val;
		 }
		 return $data;
	 }


	 public function getItemSku($id){

	 }


	 public function getAllSupplierList(){
		 return $this->supplier_user->field('supplier_id,company_name')->where('status=1')->select();
	 }
	 public function getAllList($table_name,$fields="",$where=null){
		 $m=M($table_name);
		 return $m->field($fields)->where($where)->select();
	 }
	
	 /**
	  * 查询采购计划商品时使用
	  * @param unknown $field
	  * @param unknown $where
	  */
	 public function searchSupplier4Plan($field, $where){
	 	$supplierIdList = $this->where($where)->field('supplier_id')->select();
	 	return $this->keyValRev($supplierIdList, 'supplier_id', true);
	 }
	 
	 /**
	  * 更具id返回数据
	  * @param unknown $id 供应商id
	  * @param unknown $field 字段
	  * @return boolean 
	  */
	 public function getSupplierByIds($id, $field = '*'){
	 	$result = array();
	 	if(empty($id)){
	 		return $result;
	 	}
	 	if(is_array($id)){
	 		$where['supplier_id'] = array('in',$id);
	 		$supplierList = $this->where($where)->field($field)->select();
	 		$result = $this->keyValRev($supplierList, 'supplier_id', false);
	 	}else{
	 		$result = $this->where("supplier_id=$id")->field($field)->find();
	 	}
	 	return $result;
	 }
	 /**
	  *取出所有供应商的信息 
	  */
	  public function getAllSupplier($field){
	  	$where['status']=1;
		  return $this->supplier_user->where($where)->field($field)->select();
	  }
	  	 
	 /**
	  * 二位数组val值转为数组的key
	  * @param unknown $arr
	  * @param unknown $keyValName
	  * @return unknown[]
	  */
	 private function keyValRev($arr, $keyValName, $onlyKeyVal=false){
	 	$tmpArr = array();
	 	if($onlyKeyVal){
	 		foreach ($arr as $val){
	 			$kv = $val[$keyValName];
	 			$tmpArr[$kv] = $kv;
	 		}
	 	}else{
	 		foreach ($arr as $val){
	 			$kv = $val[$keyValName];
	 			$tmpArr[$kv] = $val;
	 		}
	 	}
	 	return $tmpArr;
	 }
	 //向顺丰接口推送sku数据
	 public function pushSku($data){
	 	$url = C('API_SF').'item/push';
	 	$data['transportProperty']='1';
	 	$data['serialNumTrackInbound']="Y";
	 	$data['serialNumTrackOutbound']="Y";
	 	$data['serialNumTrackInventory']="Y";
	 	$data=array($data);
	 	$json=json_encode($data);
	 	$res=$this->curl_post($url,$json);
	 	$res=stristr($res,"{");
	 	$arr=json_decode($res);
	 	$items=$arr->data->items;
	 	foreach($data as $k => $v){
	 		$data[$k]['return_msg']=json_encode($items[$k]);
	 		$data[$k]['msg']=$items[$k]->note;
	 	}
	 	if($arr->code=="100"){
	 		if($this->supplier_push->addAll($data)){
	 			return ture;
	 		}
	 	}
	 	return false;
	 }
	 //获取店铺列表
	public function getShopList(){
		return $this->sysshop_shop->field("shop_id,shop_name")->select();
	}
	//获取供应商item表信息
	public function getItemInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->supplier_items->where($condition)->find();
		}
	}
	//添加商品到商城item表
	public function addItem($data){
		if (!$data) {
			return false;
		}else{
			return $this->sysitem_item->add($data);
		}
	}
	//修改商城商品表信息
	public function updateItem($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->sysitem_item->where($condition)->save($data);
		}
	}
	//修改供应商SKu表
	public function updateSupplierSku($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->supplier_sku->where($condition)->save($data);
		}
	}
	
	//商城item_sku表添加信息
	public function addItemSku($data){
		if (!$data) {
			return false;
		}else{
			return $this->sysitem_sku->add($data);
		}
	}
	//返回数据后，修改供应商sku表中的sku_id字段
	public function updateSitemSku($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->supplier_sku->where($condition)->save($data);
		}
	}
	//修改供应商
	//插入商品销量表
	public function addItemQuantity($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelItemCount->add($data);
		}
	}
	//插入商品状态表（上下架）
	public function addItemStatus($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelItemStatus->add($data);
		}
	}
	//修改商品状态表（上下架）
	public function updateItemStatus($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelItemStatus->where($condition)->save($data);
		}
	}
	//插入商品库存表
	public function addItemStore($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelItemStore->add($data);  
		}
	}
	//插入商城商品sku库存表
	public function addItemSkuStore($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelItemSkuStore->add($data);
		}
	}
	//修改商城商品sku库存表
	public function updateItemSkuStore($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelItemSkuStore->where($condition)->save($data);
		}
	}
	//curl_post函数
	 private function curl_post($url,$array){
	 	$curl = curl_init();
	 	$header = array(
	 			"content-type: application/x-www-form-urlencoded;
				 charset=UTF-8"
	 	);
	 	curl_setopt($curl, CURLOPT_URL, $url);
	 	curl_setopt($curl, CURLOPT_HEADER, $header);
	 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	 	curl_setopt($curl, CURLOPT_POST, 1);
	 	$post_data = $array;
	 	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	 	$data = curl_exec($curl);
	 	curl_close($curl);
	 	return $data;
	 }
	 //推送商品
	 public function pushGoods($data){
		 $url = C('API_SF').'item/push';
		 $json=json_encode($data);
		 $res=$this->curl_post($url,$json);
		 $res=stristr($res,"{");
		 $arr=json_decode($res);
		 $items=$arr->data->items;
		 foreach($data as $k => $v){
			 $data[$k]['return_msg']=json_encode($items[$k]);
			 $data[$k]['msg']=$items[$k]->note;
			 unset($data[$k]['standardDescription']);
			 $data[$k]['barCode']=$data[$k]['barcode1'];
			 unset($data[$k]['barcode1']);
		 }
		 if($arr->code=="100"){
			 if($this->supplier_push->addAll($data)){
				 return true;
			 }
		 }
		 return false;
	 }
	 public function getSkus($field="",$item_id){
		 $where=array(
			 'item_id'=>$item_id
		 );
		 $res=$this->supplier_sku->field($field)->where($where)->select();
		 foreach($res as $key =>$val){
			 //$res[$key]['transportProperty']='非禁航';
			 $res[$key]['barCode']=array(
				 'barCode1'=>$val['barcode1']
			 );
			 unset($val['barcode1']);
			 $res[$key]['serialNumTrackInbound']="Y";
			 $res[$key]['serialNumTrackOutbound']="Y";
			 $res[$key]['serialNumTrackInventory']="Y";
		 }
		 return $res;
	 }
	 //通过item列表获取sku数量
	 public function getSkuNum($list){
		 $arr=array();
		 $ids=array();
		 foreach($list as $key => $val){
			 $ids[]=$val['sitem_id'];
		 }
		 $ids=implode(",",$ids);
		 if(empty($ids)){
			 return false;
		 }
		 $skus=$this->supplier_sku->where("sitem_id in (".$ids.") AND is_reviewed != -1")->select();
		 foreach($skus as $k => $v){
			 $arr[$v['sitem_id']][]=$v;
		 }
		 $nums=array();
		 foreach($arr as $key => $val){
			 $nums[$key]=count($val);
		 }
		 return $nums;
	 }
	//合并数组key_val,插入prop表
	 public function merge_array_prop($arr1,$arr2,$sku_id){
		 $arr=array();
		 foreach($arr1 as $key => $val){
			 $arr[$key]['sku_attr']=$arr1[$key];
			 $arr[$key]['sku_attr_val']=$arr2[$key];
			 $arr[$key]['ssku_id']=$sku_id;
		 }
		 if(empty($arr)){
			 return false;
		 }
		 $res=$this->supplier_sku_prop->addAll($arr);
		 return $res;
	 }
	 //合并数组key_val
	 public function merge_array_sale($arr1,$arr2,$sku_id){
		 $arr=array();
		 foreach($arr1 as $key => $val){
			 $arr[$key]['sku_sale_attr']=$arr1[$key];
			 $arr[$key]['sku_sale_attr_val']=$arr2[$key];
			 $arr[$key]['ssku_id']=$sku_id;
		 }
		 if(empty($arr)){
			 return false;
		 }
		 $res=$this->supplier_sku_sale_prop->addAll($arr);
		 return $res;
	 }
	//通过分类获取sku的属性类别，添加商品时候有用
	public function getSkuProp($cat_id){
		if(empty($cat_id)){
			return false;
		}
		$m=M('syscategory_cat_rel_prop as sp');
		$res=$m->join("syscategory_props as sps on sp.prop_id=sps.prop_id")
				->field("sp.prop_id,prop_name")
				->where("cat_id=$cat_id")
				->select();
		return $res;
	}
	//通过属性类别获取商品属性值
	public function getSkuPropVal($list){
		$ids=array();
		foreach($list as $key => $val){
			$ids[]=$val['prop_id'];
		}
		$ids=implode(",",$ids);
		$m=M('syscategory_prop_values');
		if(empty($ids)){
			return false;
		}

		$res=$m->where("prop_id in (".$ids.")")->select();
		$arr=array();
		foreach($res as $k => $v){
			$arr[$v['prop_id']][$k]['prop_name']=$v['prop_value'];
			$arr[$v['prop_id']][$k]['prop_value_id']=$v['prop_value_id'];
		}
		return $arr;

	}
	 //新增sku，批量
	public function addSku($list){
		$specInfo=array();
		foreach($list['prop_select'] as $key => $val){
			$arr=array();
			foreach($val as $k => $v){
				$tmp=explode("_",$v);
				$arr['spec_private_value_id'][$tmp[0]]="";
				$arr['spec_value'][$tmp[0]]=$tmp[2];
				$arr['spec_value_id'][$tmp[0]]=$tmp[1];
				$str=$tmp[3].":".$tmp[2];
				$specInfo[$key][]=$str;
			}
			$specInfo[$key]=implode("、",$specInfo[$key]);
			$list['prop_select'][$key]=serialize($arr);
		}
		$arr=array();
		foreach($list['prop_select'] as $key => $val){
			$arr[$key]=array(
				'title'=>$list['title'],						//标题
				'delivery_period'=>$list['delivery_period'],	//货期
				'size'=>$list['size'],							//尺寸
				'sitem_id'=>$list['sitem_id'],					//supplier item_id
				'weight'=>$list['weight'],						//重量
				'case_count'=>$list['case_count'],				//箱包数
				'spec_info'=>$specInfo[$key],					//属性描述
				'spec_desc'=>$list['prop_select'][$key],		//属性描述格式化过得
				'bn'=>$list['bn'][$key],						//主编码
				'storge_num'=>$list['storge_num'][$key],		//数量
				'barcode'=>$list['barcode'][$key],				//条码
				'cost_price'=>$list['cost_price'][$key],		//结算价
				'mkt_price'=>$list['mkt_price'][$key],			//建议价
				'price'=>$list['price'][$key]					//销售价
			);
		}
		$count=count($arr);
		$id=$this->supplier_sku->addAll($arr);
		if(!$id){
			return false;
		}
		$attrs=array();
		$a=0;
		for($i=$id;$i<($id+$count);$i++){
			foreach($list['sku_attr'] as $key => $val){
				$attrs[$key]['sku_attr']=$val;
				$attrs[$key]['sku_attr_val']=$list['sku_attr_val'][$key];
				$attrs[$key]['ssku_id']=$i;
			}
			$a++;
			$ids=$this->supplier_sku_prop->addAll($attrs);
			if(!$ids){
				return false;
			}
		}
		return true;
	}

	 public function getBrandByCat($cat_id){
		 $m=M("syscategory_cat_rel_brand as scb");
		 $where=array(
			 'cat_id'=>$cat_id
		 );
		 $res=$m->join("syscategory_brand as sb on scb.brand_id=sb.brand_id ")->where($where)->select();
		 return $res;
	 }
	 //增加品牌
	public function addBrand($cat_id,$brand_name){
		$syscategory=M("syscategory_cat_rel_brand");
		$brand=M("syscategory_brand");
		$data=array(
			'brand_name'=>$brand_name
		);
		$res=$brand->add($data);
		if(!$res){
			return false;
		}
		$data=array(
			'cat_id'=>$cat_id,
			'brand_id'=>$res
		);
		$res=$syscategory->add($data);
		$data['brand_name']=$brand_name;
		return $data;
	}
	 //置为已推送
	public function setPushed($sitemId,$status=1){
		if($sitemId<1){
			return false;
		}
		$data=array(
			'is_push'=>$status
		);
		return $this->supplier_items->where("sitem_id=$sitemId")->save($data);
	}
	//查询供应商商品信息
	public function getSitemInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->supplier_items->where($condition)->field($field)->find();
		}
	}
	//添加审核记录
	public function addReviewed($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelSitemReviewed->add($data);
		}
	}
	//修改供应商商品
	public function updateSitem($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->supplier_items->where($condition)->save($data);
		}
	}
	//查询审核信息
	public function getReviewedList($condition,$field,$order){
		if (!$condition) {
			return false;
		}else{
			return $this->modelSitemReviewed->table('supplier_item_reviewed ir,system_admin a')->where($condition)->field($field)->order($order)->select();
		}
	}
	//sku列表
	public function getSkuList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->supplier_sku->where($condition)->select();
		}
	}
	//获取店铺分类
	public function getShopCatList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelShopCat->where($condition)->select();
		}
	}
	//查询sku列表
	public function getItemSkuList($condition,$field,$order){
		if (!$condition) {
			return false;
		}else{
			return $this->supplier_sku->where($condition)->field($field)->order($order)->select();
		}
	}
	//更新商城商品sku信息
	public function updateItemSku($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->sysitem_sku->where($condition)->save($data);
		}
	}
	//修改商品库存表
	public function UpdateItemStore($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelItemStore->where($condition)->save($data);
		}
	}
	//查看供应商商品详情
	public function getItemDescInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelSitemDesc->where($condition)->find();
		}
	}
	//修改商品详情信息
	public function updateItemDesc($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelItemDesc->where($condition)->save($data);
		}
	}
	//添加商品详情信息
	public function addItemDesc($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelItemDesc->add($data);
		}
	}

 }
