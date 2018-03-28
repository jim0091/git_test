<?php   
namespace Home\Model;
use Think\Model;
class GoodscatModel extends Model{
	public function __construct(){
		$this->dbCat=M('syscategory_cat');
		$this->dbItem=M('sysitem_item');
		$this->dbItemStatus=M('sysitem_item_status');
		$this->dbPropValues=M('syscategory_prop_values');
		$this->dbProps=M('syscategory_props');
		$this->dbCompanyConf = M('company_config');
	}
	/*
	 * 获取商品分类列表
	 * */ 
	public function getCatList(){
		$allCat=$this->dbCat->where('level=2 and disabled =0')->field('cat_id,cat_name')->order('parent_id ASC,cat_id DESC')->select();
		$threeCat=$this->dbCat->where('level=3 and disabled =0')->field('cat_id,cat_name,parent_id')->order('cat_id asc')->select();
		foreach($allCat as $key=>$value){
			foreach($threeCat as $keys=>$values){
				if($values['parent_id']==$value['cat_id']){
					$allCat[$key]['threeCat'][]=$values;
				}
			}
		}
		return	$allCat;
	}
	/**
	 * 商品列表
	 * @param $orderBy 'normal','sales','price','onsaleTime' 排序
	 * @param $sort排序升序降序
	 * */
	public function getItemList($where,$limit,$orderBy,$sort='desc'){
		$tables = 'sysitem_item a,sysitem_item_status b';
		$map = 'a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0'.$where;
		$field = 'a.item_id,a.shop_id,title,price,cash,point,image_default_id';
		$order = 'modified_time desc';
		if($orderBy == 'sales'){
			//销量排序
			$tables = "$tables ,sysitem_item_count c";
			$map = "$map and a.item_id=c.item_id";
			$order = 'c.sold_quantity desc';
		}else if($orderBy == 'price'){
			//价格排序
			$order = "a.price $sort";
		}else if($orderBy == 'onsaleTime'){
			//上架时间
			$order = 'b.list_time desc';
		}
		$itemList=$this->dbItem->table($tables)->where($map)->field($field)->order($order)->limit($limit)->select();
		return $itemList;
		
	}
	/*
	 * 商品数量
	 * */
	 public function getItemCount($where){
		return $count=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where('a.item_id=b.item_id  and b.approve_status="onsale" and a.disabled=0 '.$where)->count();	
	 }
	 /*
	  * 
	  * 商品搜索
	  * */
	 public function getSearchRes($keyWord,$limit){
	 	if(!empty($keyWord)){
			$itemList=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where('a.item_id=b.item_id and a.title like "%'.$keyWord.'%" and b.approve_status="onsale" and a.disabled=0')
					  ->field('a.item_id,a.shop_id,title,price,image_default_id')->order('modified_time desc')->limit($limit)->select();
	 		
	 	}else{
			$itemList=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where('a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
					  ->field('a.item_id,a.shop_id,title,price,image_default_id')->order('modified_time desc')->limit($limit)->select();	 		
	 	}
		return $itemList;	 	
	 }
	/*
	 * 商品搜索数量
	 * */
	 public function getSearchCount($keyWord){
	 	if(!empty($keyWord)){
			return $count=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where('a.item_id=b.item_id and a.title like "%'.$keyWord.'%" and b.approve_status="onsale" and a.disabled=0')->count();	
	 	}else{
			return $count=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where('a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')->count();	
	 	}
	 }	
	 /*
	  * 取得单个商品的信息
	  * */
	 public function getOneInfo($itemId){
	 	return $res=$this->dbItem->where('item_id='.$itemId)->field('item_id,shop_id,title,price,image_default_id,spec_desc')->find();
	 }
	 /*
	  * 获取商品的属性
	  * */
	public function getAttribute($valueIds){
		$res=$this->dbPropValues->table('syscategory_prop_values a,syscategory_props b')->where('prop_value_id in('.$valueIds.') and a.prop_id=b.prop_id')->field('b.prop_name,a.prop_value_id')->select();
		return $res;
	}
	/*
	 * 
	 * 取得分类名
	 * */
	 public function getCatName($catId){
		return $this->dbCat->where('cat_id='.$catId)->getField('cat_name');
	 }
	/**
	*随机取10条同类商品记录
	**/
	public function randCatItem($catId,$level){
		if($level == 1){
    		$map = array(
				'cat_1' => $catId
			);
		}else if($level == 2){
    		$map = array(
				'cat_2' => $catId
			);			
		}else if($level == 3){
    		$map = array(
				'cat_id' => $catId
			);				
		}		
		return $this->dbItem->where($map)->field('item_id,title,image_default_id,price')->limit(6)->order('rand()')->select();
	}

	//查询企业配置利润率
	public function getCompanyConf($condition){
		if (!condition) {
			return false;
		}else{			
			return $this->dbCompanyConf->where($condition)->field('profit_rate')->find();
		}
	}
		 /*
	  * 查询商品详细信息
	  * */
	 public function getItemInfo($condition,$field){
	 	if (!$condition) {
	 		return false;
	 	}
	 	return $this->dbItem->where($condition)->field($field)->find();
	 }
}
