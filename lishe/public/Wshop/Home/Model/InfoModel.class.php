<?php   
namespace Home\Model;
use Think\Model;
class InfoModel extends Model{
	public function __construct(){
		$this->modelCart=M('systrade_cart'); //购物车表	
		$this->modelItemStatus = M('sysitem_item_status');//商品上下架表
		$this->modelSku=M('sysitem_sku');//货品的库存
		$this->modelSkuStore=M('sysitem_sku_store');//货品的库存
		$this->modelItem=M('sysitem_item');//商品信息
		$this->dbCompanyConf = M('company_config');
	}

	//查询单个商品库存
	public function getItemSkuStore($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelSkuStore->where($condition)->find();
		}
	}
	//查询商品上下架状态
	public function getItemStatus($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelItemStatus->where($condition)->find();
		}		
	}
	//查询购物车是否有该商品，如果有的话就直接增加数量
	public function getCartItem($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelCart->where($condition)->find();	
		}
	}
	//增加购物车商品数量
	public function updateCartItemNum($condition,$num){
		if (!$condition || !$num) {
			return false;
		}else{
			return $this->modelCart->where($condition)->setInc('quantity',$num);
		}
	}
	//查询商品信息
	public function getItemInfo($condition){
		if (!$condition) {
			return false;
		}else{
			return $this->modelItem->where($condition)->find();
		}
	}
	//增加购物车记录
	public function addCartItem($data){
		if (!$data) {
			return false;
		}else{
			return $this->modelCart->data($data)->add();
		}
	}
	//查询企业配置利润率
	public function getCompanyConf($condition){
		if (!condition) {
			return false;
		}else{			
			return $this->dbCompanyConf->where($condition)->field('profit_rate')->find();
		}
	}
        
}
