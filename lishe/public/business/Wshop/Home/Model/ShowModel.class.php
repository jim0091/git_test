<?php  
namespace Home\Model;
use Think\Model;
class ShowModel extends Model{
	/*
	 *企业秀前台模型 
	 * 章锐
	 * addtime 2016/7/25 
	 * modify 2016/8/17
	 * */
	
	public function __construct(){
		$this->modelConfig=M('company_config');		
		$this->modelNewsCenter=M('company_news_center');
		$this->modelActivityPerson=M('company_activity_person');		
		//企业福利板块
		$this->modelCategory=M('company_category_config');
		$this->modelCat=M('syscategory_cat');
		$this->modelItem=M('company_item_config');
		//商品表/商品分类表
		$this->modelItems=M('sysitem_item');
		//投票表
		$this->modelVote=M('company_super_vote');
		//模板
		$this->modelTemplete=M('company_templete');
		//积分表
		$this->modelPoint=M('sysuser_user_deposit');
		//购物车
		$this->modelCart=M('systrade_cart');
		
	}
	
	/*
	 * 取出公司的信息（company——config表）
	 * */
	public function getCompanyInfo($condition,$field){
		
		return $res=$this->modelConfig->where($condition)->field($field)->find();
		
	}
	/*
	 * 企业福利首页推荐商品id
	 * */
	public function getRecommend($comId,$field="item_config_id,cat_name,recommend,cat_banner,cat_content,item_ids"){
		return $this->modelItem->where('com_id='.$comId)->field($field)->order('order_sort desc')->select();
	}
	/*
	 * 企业福利首页推荐商品列表
	 * */
	public function getRecommendItem($recommendIds,$field="10"){
		foreach($recommendIds as $key=>$value){
			if(!empty($value)){
				$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$value.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
					  ->field('a.item_id,a.shop_id,a.flag,title,price,image_default_id')->order('a.flag asc ,modified_time desc')->limit($field)->select();
				$a[$key]=$res;
			}else{
				$a[$key]="";
			}
		}
		return $a;
	}
	
	/*
	 * 企业福利首页一级分类下的二级分类
	 * */
	public function getSubCategory($comId,$configIds){
		$condition['com_id']=$comId;
		$condition['item_config_id']=array('in',implode(",", $configIds));
		$condition['disabled']=0;
		return $this->modelCategory->where($condition)->field('cat_config_id,item_config_id,cat_name')->order('order_sort desc')->select();
	}
	/*
	 * 企业福利取出指定一级分类的二级分类
	 * */
	 public function getThiCategory($comId,$configId){
		$condition['com_id']=$comId;
		$condition['item_config_id']=$configId;
		$condition['disabled']=0;
		return $this->modelCategory->where($condition)->field('cat_config_id,item_config_id,cat_name,item_ids,cat_id')->order('order_sort desc')->select();	 	
		
	 }
	
	public function getConfCartName($comId,$configIds){
		$condition['com_id']=$comId;
		$condition['item_config_id']=$configIds;
		return $this->modelItem->where($condition)->getField('cat_name');
	}
	 /*
	  * 取出指定分类中的商品
	  * */
	 public function getCatItems($comId,$catId){
		$condition['com_id']=$comId;
		$condition['cat_config_id']=$catId;
		$condition['disabled']=0;	 	
	 	return $this->modelCategory->where($condition)->field('item_ids,cat_id,cat_name')->find();
	 }	
	/**
	 *有效的商品数量 
	 * */
	public function itemCount($itemIds){
		return $res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$itemIds.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')->count();		
	}
	/*
	 * 商品列表（分页）
	 * */
	public function getItemLists($itemIds,$limit){
		return	$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$itemIds.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
				  ->field('a.item_id,a.shop_id,a.flag,title,price,image_default_id')->order('a.flag asc , modified_time desc')->limit($limit)->select();		
	}
	/*
	 * 商品列表（分页）in()解决bug
	 * */
	public function getItemListsIn($itemIds,$field="a.item_id,a.shop_id,a.flag,title,price,image_default_id"){
		return	$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$itemIds.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
				  ->field($field)->order('a.flag asc , modified_time desc')->select();		
	}
	/*
	 * 取出一定条件下上架的的所有"item_id
	 * */
	public function getItemIds($itemIds){
		return	$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.item_id in('.$itemIds.') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
					  ->field('a.item_id')->order('a.flag ASC ,a.cat_id DESC,a.profit_rate DESC')->select();				
	}
	/*
	 * 取出有效的商品列表
	 * */
	 public function getItemList($itemIds){
		return	$res=$this->modelItems->where(' item_id in('.implode(',',$itemIds).')')->field('item_id,shop_id,title,price,image_default_id,flag')->order('flag ASC ,cat_id DESC,profit_rate DESC')->select();	 	
	 }
	
	/*
	 *公司配置表中指定item_config_id的商品
	 * */
	public function getConfigItems($comId,$configId){
		$condition['com_id']=$comId;
		$condition['item_config_id']=$configId;
	 	return $this->modelItem->where($condition)->field('item_ids,cat_name')->find();		
	}
	//得到分类
	public function getThisCats($comId,$configId){
		$condition['com_id']=$comId;
		$condition['item_config_id']=$configId;
		return $res=$this->modelItem->where($condition)->field('cat_name,item_config_id')->find();
	}
	public function getThisCatsMore($comId,$configId){
		$condition['com_id']=$comId;
		$condition['item_config_id']=$configId;
		return $res=$this->modelCategory->where($condition)->field('cat_name,cat_config_id')->select();
	}	
	public function getThisCatName($comId,$catId){
		$condition['com_id']=$comId;
		$condition['cat_config_id']=$catId;
		return $res=$this->modelCategory->where($condition)->getField('cat_name');
	}
	//取出该公司的的选择模板
	public function getTemplete($tempId){
		$condition['temp_id']=$tempId;
		$condition['isdelete']=0;
		return $res=$this->modelTemplete->where($condition)->getField('temp_name');
	}
	//取出个人积分
	public function getPersonPoint($uid){
		 $res=$this->modelPoint->where('user_id='.$uid)->getField('deposit');
		 $res=$res*100;
		 $resu=intval($res);
		 return $resu;
	}
	//取出用户购物车的数量
	public function getCartNumber($uid){
		return $res=$this->modelCart->where('user_id='.$uid)->count();
	}
	//检测公司配置模板名在模板表是否存在
	public function isSetTemp($tempName){
		$condition['temp_name']=$tempName;
		$condition['isdelete']=0;
		return $res=$this->modelTemplete->where($condition)->getField('temp_id');		
	}
	//取出二级分类下的三级分类
	public function getLastCartgoey($catId){
		$categorysArr=$this->modelCat->field('cat_id')->where(array('parent_id'=>$catId))->select();
		foreach ($categorysArr as $keys => $values){
			$categorysId[] = $values['cat_id'];
		}
		return $categorysId;
	}
	//取出分类下的所有商品
	public function getCatItem($catId,$limit){
		return	$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.cat_id in('.implode(",", $catId).') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')
				  ->field('a.item_id,a.shop_id,a.flag,title,price,image_default_id')->order('a.flag asc, profit_rate desc, modified_time desc')->limit($limit)->select();		
	}
	//取出分类下的所有商品数量
	public function getCatItemCount($catId){
		return	$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.cat_id in('.implode(",", $catId).') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')->count();
	}	
	//取出分类下的所有商品itemId
	public function getThisCatItems($catId){
		$res=$this->modelItems->table('sysitem_item a,sysitem_item_status b')->where(' a.cat_id in('.implode(",", $catId).') and a.item_id=b.item_id and b.approve_status="onsale" and a.disabled=0')->field('a.item_id')->order('a.flag asc, profit_rate desc, modified_time desc')->select();		
		foreach($res as $key =>$value){
			$items[]=$value['item_id'];
		}
		return $items;
	}	
}
