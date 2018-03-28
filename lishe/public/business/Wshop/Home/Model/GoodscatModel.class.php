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
}
