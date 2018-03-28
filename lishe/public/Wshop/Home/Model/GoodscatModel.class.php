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
		$this->modelCatCatNav = M('syscategory_cat_nav');
		$this->modelCatCatNavDetail = M('syscategory_cat_nav_detail');
		$this->dbCompanyConf = M('company_config');
	}
	/*
	 * 获取商品分类列表
	 * */
	public function getCatList($catId){
		$catList=array();
		if (empty($catId)) {
			$catListArr = $this->modelCatCatNav->where("is_delete = 0")->order('order_sort desc')->select();
			foreach ($catListArr as $key => $value) {
				$catList[$value['cat_nav_id']]=$value;
			}
			$catId = $catListArr[0]['cat_nav_id'];
		}else{
			$catListArr = $this->modelCatCatNav->where("cat_nav_id = ".$catId)->find();
			$catList[$catId]=$catListArr;
		}
		$where = ' and cat_nav_id = '.$catId;
		$catNavList = $this->modelCatCatNavDetail->where("is_delete = 0 ".$where)->order('order_sort desc')->select();
		if (!empty($catNavList)) {
			foreach ($catNavList as $key => $value) {
				$subCatId[]=$value['cat_id'];
			}
			$cat = $this->dbCat->where("cat_id IN (".implode(',', $subCatId).")")->field('cat_id,cat_name,cat_logo')->order("field(cat_id,".implode(",", $subCatId).")")->select();
			if (!empty($cat)) {
				foreach ($cat as $key => $value) {
					$subCat[$value['cat_id']]=$value;
					if(empty($value['cat_logo'])){
						$catIds[]=$value['cat_id'];
					}					
				}
				
				if (!empty($catIds)) {
					$itemList = $this->dbItem->where('cat_id IN ('.implode(',',$catIds).')')->field('cat_id,image_default_id')->group('cat_id')->select();
					foreach ($itemList as $key => $value) {
						$subCat[$value['cat_id']]['cat_logo']=$value['image_default_id'].'_m.'.end(explode('.',$value['image_default_id']));
					}
				}
			}
		}	
		return	array($catList,$subCat,$catId);
	}
	/*
	 * 商品列表
	 * */
	public function getItemList($catId,$limit){
		$itemList=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where('a.item_id=b.item_id and a.cat_id='.$catId.' and b.approve_status="onsale" and a.disabled=0')
				  ->field('a.item_id,a.shop_id,title,price,image_default_id')->order('modified_time desc')->limit($limit)->select();
		return $itemList;
		
	}
	/*
	 * 商品数量
	 * */
	 public function getItemCount($catId){
		return $count=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where('a.item_id=b.item_id and a.cat_id='.$catId.' and b.approve_status="onsale" and a.disabled=0')->count();	
	 }
	 /*
	  * 
	  * 商品搜索
	  * */
	 public function getSearchRes($condition,$limit){	
	 	$condition['a.disabled']=0;
		$condition['a.item_id']=array('exp','=b.item_id');
		$condition['b.approve_status']='onsale'; 
		$itemList=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where($condition)
					  ->field('a.item_id,a.shop_id,title,price,image_default_id')->order('modified_time desc')->limit($limit)->select();
		return $itemList;	 	
	 }
	/*
	 * 商品搜索数量
	 * */
	public function getSearchCount($condition){
		$condition['a.disabled']=0;
		$condition['a.item_id']=array('exp','=b.item_id');
		$condition['b.approve_status']='onsale';
		$count=$this->dbItem->table('sysitem_item a,sysitem_item_status b')->where($condition)->count();
		return $count;
	}
	/*
	*获取品牌名称
	**/	
	public function getBrandList($condition){
		$condition['a.disabled']=0;
		$condition['a.item_id']=array('exp','=b.item_id');
		$condition['b.approve_status']='onsale';
		unset($condition['a.brand_id']);
		$brandList = $this->dbItem->table('sysitem_item a,sysitem_item_status b')->where($condition)->group('a.brand_id')->select();
		return $brandList;
	}
	 /*
	  * 取得单个商品的信息
	  * */
	 public function getOneInfo($itemId){
	 	$res=$this->dbItem->where('item_id='.$itemId)->field('item_id,shop_id,title,price,image_default_id,spec_desc')->find();
	 	return $res;
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
	//查询企业配置利润率
	public function getCompanyConf($condition){
		if (!condition) {
			return false;
		}else{			
			return $this->dbCompanyConf->where($condition)->field('profit_rate')->find();
		}
	}
}
