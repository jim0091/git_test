<?php  
/**
  +----------------------------------------------------------------------------------------
 *  CategoryModel
  +----------------------------------------------------------------------------------------
 * @author   	awen
 * @description 报价单
  +-----------------------------------------------------------------------------------------
 */
 namespace Home\Model;
 use Think\Model;
 class QuotationModel extends CommonModel{
	
	public function __construct(){
		$this->dbQuotation = M('supplier_quotation');
		$this->dbQuotationGoods = M('supplier_quotation_goods');
		$this->dbSitemSku = M('supplier_item_sku');
		$this->dbSitem=M("supplier_item");
		$this->dbCat = M('syscategory_cat');
		$this->dbBrand = M('syscategory_brand');
		$this->dbQuotationAudit = M("supplier_quotation_audit");
		$this->dbItemSku = M('sysitem_sku');
	}
	//报价单数量统计
	public function getQuotationCount($condition){		
		if (empty($condition['status'])) {
			unset($condition);
		}
		return $this->dbQuotation->where($condition)->count('quotation_id');
	}
	//报价单列表
	public function getQuotationList($condition,$limit,$field){
		return $this->dbQuotation->table('supplier_quotation as q,supplier_user as u')->where("q.supplier_id = u.supplier_id".$condition)->limit($limit)->field($field)->order("create_time Desc")->select();		
	}
	//获取报价单中的所有商品sku
	public function getQSkuList($condition,$field="*"){
		if (!$condition) {
			return false;
		}else{
			return $this->dbQuotationGoods->where($condition)->field($field)->select();
		}
	}
	//查询供应商sku信息
	public function getSskuList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbSitemSku->where($condition)->select();
		}
	}	
	//获取报价单中的所有商品数量
	public function getSitemCount($quotationId){
		if (empty($quotationId)) {
			return false;
		}
		return $this->dbSitem
					->where('EXISTS(select 1 from supplier_quotation_goods q where q.sitem_id = supplier_item.sitem_id and q.quotation_id = %d)', array($quotationId))
					->field('sitem_id')
					->count();
		
	}
	//查询供应商商品信息
	public function getsitemList($quotationId,$field,$order,$limit){
		if (empty($quotationId)) {
			return false;
		}
		return $this->dbSitem
				->where('EXISTS(select 1 from supplier_quotation_goods q where q.sitem_id = supplier_item.sitem_id and q.quotation_id = %d)', array($quotationId))
				->field($field)
				->limit($limit)
				->order($order)
				->select();
	}
	//查询分类信息
	public function getScatList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbCat->where($condition)->field($field)->select();
		}
	}
	//查询商品品牌
	public function getSbrandList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbBrand->where($condition)->field($field)->select();
		}
	}
	//查询报价单审核商品
	public function getGoodesInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbQuotationGoods->where($condition)->field($field)->find();
		}
	}
	//提交审核商品备注
	public function updateGoods($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbQuotationGoods->where($condition)->save($data);
		}
	}
	//报价单审核操作
	public function addAudit($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbQuotationAudit->add($data);
		}
	}
	//检查报价单是否有不通过的报价商品(存在不通过的商品则审核不通过)
	public function getQuGoodsInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbQuotationGoods->where($condition)->find();
		}
	}
	//修该报价单表的审核状态
	public function updateQuotation($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbQuotation->where($condition)->save($data);
		}
	}
	//修改供应商表中的sku的状态
	public function updateSitemSku($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbSitemSku->where($condition)->save($data);
		}
	}
	//修改供应商商品表的状态
	public function updateSitem($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbSitem->where($condition)->save($data);	
		}
	}
	//查询供应商商品表和商品sku表信息
	public function getSitemAndSsku($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbSitemSku->table('supplier_item_sku as sku,supplier_item as i,supplier_quotation_goods as g')->where($condition)->field($field)->select();
		}
	}
	//审核日志
	public function getSsitemQuotationList($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->dbQuotationAudit->where($condition)->select();
		}
	}
	//修改供应商sku信息
	public function updateSsku($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbSitemSku->where($condition)->save($data);
		}
	}
	//获取已经推送的sku列表
	public function getPushedSitemSkuList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbSitemSku->where($condition)->field($field)->select();
		}
	}
	//修改商城sku表价格
	public function updateSkuPrice($data){
		if (!$data) {
			return false;
		}else{
			return $this->dbItemSku->save($data); 
		}
	}
}
