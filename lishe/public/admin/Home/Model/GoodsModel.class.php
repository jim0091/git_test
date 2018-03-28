<?php
/**
  +----------------------------------------------------------------------------------------
 *  GoodsModel
  +----------------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: GoodsModel.class.php v001 2016-02-01
 * @description 产品操作
  +-----------------------------------------------------------------------------------------
 */
	namespace Home\Model;
	use Think\Model;
	
    class GoodsModel extends CommonModel{    	
    	public function __construct(){
    		$this->modelItem=M('sysitem_item');
				$this->modelSupplierItem=M('supplier_item');
    		$this->modelItemStatus=M('sysitem_item_status');
				$this->modelItemStore=M('sysitem_sku_store');
				$this->modelItemSku=M('sysitem_sku');
				$this->modelSupplierUser=M('supplier_user');//供应商表
				$this->modelCategoryNav=M('syscategory_cat_nav');
				$this->modelCategoryNavDeatil=M('syscategory_cat_nav_detail');
    		$this->modelCategory=M('syscategory_cat');
				$this->dbShop=M('sysshop_shop');
				$this->dbCat=M('syscategory_cat');
				$this->dbBrand=M('syscategory_brand');
				$this->dbItemDesc = M('sysitem_item_desc');//商品描述
				$this->dbCatRelBrand = M('syscategory_cat_rel_brand');
				$this->dbSupplierItem = M('supplier_item');
				$this->dbSupplierItemSku = M('supplier_item_sku');
				
    	}	    
    	
        //获取产品列表
	    public function getItemList($page,$pageSize,$condition,$orderby="item_id DESC"){
	    	if(empty($orderby)){
				$orderby="item_id DESC";
			}
	    	$start=$this->pageStart($page,$pageSize);
	    	$count=$this->modelItem->field('item_id')->where($condition)->count();
	    	$item=$this->modelItem->where($condition)->field('item_id,jd_sku,title,price,cost_price,mkt_price,bn,send_type,profit_rate,image_default_id,cat_id,shop_id,supplier_id')->order($orderby)->limit($start.','.$pageSize)->select();
	    	return array('count'=>$count,'list'=>$item);
	    }
        //获取产品列表/导出excel
	    public function getItemListExcel($condition,$orderby="item_id DESC"){
	    	$item=$this->modelItem->where($condition)->field('item_id,jd_sku,image_default_id,bn,title,price,cost_price,profit_rate,cat_id,shop_id,supplier_id')->order($orderby)->select();
	    	return array('count'=>$count,'list'=>$item);
	    }		
			//商品状态id
			public function getStatusIds($status){
				$condition['approve_status']=$status;
				return $this->modelItemStatus->where($condition)->getField('item_id',true);
			}
			//商品库存
			public function getStores($condition){
				
				return $this->modelItemStore->where($condition)->select();
			}
			//当前sku信息
			public function getThisSkuInfo($skuId){
				return $this->modelItemSku->where('sku_id='.$skuId)->field('sku_id,item_id,title,barcode,bn,price,cost_price,spec_info')->find();
			}
			//修改当前sku信息
			public function editThisSkuInfo($skuId,$data,$field){
				return $this->modelItemSku->where('sku_id='.$skuId)->field($field)->save($data);
			}
			//当前sku库存
			public function getThisSkuStores($skuId){
				return $this->modelItemStore->where('sku_id='.$skuId)->find();
			}
			//修改当前sku库存
			public function editThisSkuStore($skuId,$data,$field){
				return $this->modelItemStore->where('sku_id='.$skuId)->field($field)->save($data);
			}
			//商品状态
			public function getStatus($condition){
				
				return $this->modelItemStatus->where($condition)->field('item_id,approve_status')->select();
			}
			/*
			 * 修改商品所属分类
			 * */
			public function editItemCategory($itemId,$catId){
				return $this->modelItem->where('item_id='.$itemId)->setField('cat_id',$catId);
			}
			/*
			 * 取出商品指定信息
			 * */
			public function getThisItemInfo($itemId,$field){
				return $this->modelItem->where('item_id='.$itemId)->field($field)->find();
			}
/*
 * 修改商品的信息（所属供应商）
 * */
			public function editThisItemInfo($itemId,$data){
				return $this->modelItem->where('item_id='.$itemId)->data($data)->save();
			}
/*
 * 修改商品的信息（供应商商品表）
 * */
			public function editSupplierItemInfo($itemId,$data){
				return $this->modelSupplierItem->where('item_id='.$itemId)->data($data)->save();
			}			
/*
 * 取出所有供应商名
 * */			
			public function getAllsupplierUser($condition){
//				$condition['status']=1;
				return $this->modelSupplierUser->where($condition)->field('supplier_id,username,company_name')->select();
			}
/*
 * 取出指定条件供应商名
 * */			
			public function getConditionsupplierUser($supplierIds){
				$condition['supplier_id']=array('in',$supplierIds);
				return $this->modelSupplierUser->where($condition)->field('supplier_id,username,company_name')->select();
			}	
/*
 * 取出指定条件单个供应商名
 * */			
			public function getThissupplierUser($supplierIds){
				$condition['supplier_id']=$supplierIds;
				return $this->modelSupplierUser->where($condition)->field('supplier_id,username,company_name')->find();
			}				
/*
 *取出sku商品的多种规格 
 * */			
			public function getAllItemSkuInfo($itemIds){
				$condition['item_id']=array('in',$itemIds);
				return $this->modelItemSku->where($condition)->field('sku_id,item_id,bn,barcode,price,cost_price,spec_info,sold_quantity,mkt_price')->select();
			}
/*
 *取出sku商品的多种规格 
 * */			
			public function getSomeItemSkuInfo($skuIds){
				$condition['sku_id']=array('in',$skuIds);
				return $this->modelItemSku->where($condition)->field('sku_id,item_id,bn,barcode,price,cost_price,spec_info,weight')->select();
			}			
/*
 * 添加分类导航
 * */					
			public function addCatgoryNav($data,$field){
				return $this->modelCategoryNav->field($field)->data($data)->add();
			}
/*
 * 编辑分类导航
 * */					
			public function editCatgoryNav($navId,$data,$field){
				return $this->modelCategoryNav->where(array('cat_nav_id'=>$navId))->field($field)->data($data)->save();
			}			
/*
 * 取出一条分类导航信息
 * */			
			public function getOneCategoryNav($navId,$field){
				$condition['is_delete']=0;
				$condition['cat_nav_id']=$navId;
				return $this->modelCategoryNav->where($condition)->field($field)->find();
			}
/*
 * 取出所有分类导航信息
 * */			
			public function getAllCategoryNav($field){
				$condition['is_delete']=0;
				return $this->modelCategoryNav->where($condition)->field($field)->order('order_sort desc')->select();
			}
/*
 * 添加分类导航子分类
 * */					
			public function addCatgoryNavSon($data,$field){
				return $this->modelCategoryNavDeatil->field($field)->data($data)->add();
			}			
/*
 * 查找导航分类的所有子分类
 * */
		public function getAllCatgoryNavSon($condition,$field){
			$condition['is_delete']=0;
			return $this->modelCategoryNavDeatil->where($condition)->field($field)->order('order_sort desc')->select();			
		}
		 //获取二级三级分类名称
		 public function getCatInfos($catIds){
				$condition['cat_id'] = array('in',$catIds);
				$field="cat_id,cat_name";
		 		$res= $this->modelCategory->where($condition)->field($field)->select();
				return $res;				 	
		 }	
/*
 * 编辑分类导航子分类
 * */					
		public function editCatgoryNavSon($sonId,$data,$field){
			return $this->modelCategoryNavDeatil->where(array('detail_id'=>$sonId))->field($field)->data($data)->save();
		}			
		public function editWhereCatgoryNavSon($condition,$data,$field){
			return $this->modelCategoryNavDeatil->where($condition)->field($field)->data($data)->save();
		}			 
/*
 * 商品更改上下架状态
 * */
		public function changeItemStatus($itemId,$data){
			return $this->modelItemStatus->where(array('item_id'=>$itemId))->save($data);
		}
	 /**
	  *取出所有供应商的信息 
	  */
	public function getAllSupplier($field){
	  	$where['status']=1;
		return $this->modelSupplierUser->where($where)->field($field)->select();
	}
	
	//根据itemId获取对应的sku列表
	public function getSkuList($condition,$field){
		if (!$condition) {
			return false;
		}else{
			$condition .= ' and st.sku_id= s.sku_id';
			return $this->modelItemSku->table('sysitem_sku_store st,sysitem_sku s')->where($condition)->field($field)->select();
		}
	} 
	//获取店铺信息
	public function getShopInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbShop->where($condition)->find();
		}
	} 
	//根据分类id查询所有分类
	public function getCatAllList($condition,$field){
		if (!$condition) {
			return false;
		}else{
	        $catInfoS = $this->dbCat->where($condition)->field($field)->find();
	        if (empty($catInfoS['parent_id'])) {
	            return false;
	        }
	        $catInfo = $this->dbCat->where('cat_id ='.$catInfoS['parent_id'])->field($field)->find();
	        if (empty($catInfo['parent_id'])) {
	            return false;
	        }  
	        $catInfoB = $this->dbCat->where('cat_id ='.$catInfo['parent_id'])->field($field)->find(); 
	        $newCatInfo = array_merge_recursive(array($catInfoB),array($catInfo),array($catInfoS));         
	        if (!$newCatInfo) {
	            return false;
	        }else{
	            return $newCatInfo;
	        }
		}
	}
	//获取品牌信息
	public function getBrandInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbBrand->where($condition)->field($field)->find();
		}
	}	
	/**
	 * 取出品牌的数量
	 */
	public function getBrandCount($map){
		return $this->dbBrand->where($map)->count('brand_id');
	} 
	/**
	 * 取出所有品牌
	 */
	public function getBrands($map,$page,$size){
		return $this->dbBrand->where($map)->page("$page,$size")->order('brand_id desc')->select();
	}
	/**
	 * 添加品牌
	 */
	public function addBrand($data){
		if(empty($data)){
			return false;
		}
		return $this->dbBrand->data($data)->add();
	}
	/**
	 * 编辑品牌
	 */
	public function editBrand($brandId,$data){
		if(empty($data)){
			return false;
		}
		$map['brand_id'] = $brandId;
		return $this->dbBrand->where($map)->save($data);
	}	
	/**
	 * 品牌分类关联
	 */
	public function getCatRelBrand($brandIds){
		if(empty($brandIds)){
			return array();
		}
		$map = array(
			'brand_id' => array('in', $brandIds)
		);
		return $this->dbCatRelBrand->where($map)->getField('brand_id',TRUE);
	}
	/**
	 * 该分类下的所有品牌
	 */
	public function getCatBrand($catId){
		if(empty($catId)){
			return array();
		}
		$map = array(
			'cat_id' => $catId
		);
		return $this->dbCatRelBrand->where($map)->getField('brand_id',TRUE);		
	} 
	/**
	 * 取消关联品牌
	 */
	public function cancelRelBrand($map){
		if(empty($map) || !is_array($map)){
			return false;
		}
		return $this->dbCatRelBrand->where($map)->delete();		
	}
	/**
	 * 新建关联品牌
	 */
	  public function addRelbrands($data){
			return $this->dbCatRelBrand->data($data)->add();		
	  }
	//获取供应商信息
	public function getSupplierInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->modelSupplierUser->where($condition)->field($field)->find();
		}
	} 
	//获取商品详情
	public function getItemDesc($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->dbItemDesc->where($condition)->field($field)->find();
		}
	} 	
	//修改商品信息
	public function updateItem($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelItem->where($condition)->save($data);
		}
	}
	//修改商品sku表信息
	public function updateItemSku($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelItemSku->where($condition)->save($data);
		}
	}
	//修改供应商商品标题
	public function updateSupplierItem($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbSupplierItem->where($condition)->save($data);
		}
	}
	//修改供应商商品sku标题
	public function updateSupplierItemSku($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->dbSupplierItemSku->where($condition)->save($data);
		}
	}
	//查询商品SKU信息
	public function getSkuInfo($condition,$field){
		if (!$condition) {
			return false;
		}else{
			return $this->modelItemSku->where($condition)->find();
		}
	}	
	//修改sku信息
	public function updateSku($condition,$data){
		if (!$condition || !$data) {
			return false;
		}else{
			return $this->modelItemSku->where($condition)->save($data);
		}
	} 
	//查询sku价格最低的商品
	public function getSkuGroup($condition,$field,$group){
		if (!$condition) {
			return false;
		}else{
			return $this->modelItemSku->where($condition)->field($field)->group($group)->select();
		}
	}
		//取出订单分类信息
		public function getFieldCategory($catId,$field){
			$map['cat_id'] = $catId;
			return $this->modelCategory->where($map)->getField($field);
			
		}				
		/**
		 *查找商品信息 
		 *
		 * */
		public function fetFieItemImfo($map,$field){
			return $this->modelItem->where($map)->getField($field);
		}
			//商品过滤下架id
		public function getOnsaleItem($map){
			return $this->modelItemStatus->where($map)->getField('item_id',true);
		}		
		
		
		 		
    }
?>