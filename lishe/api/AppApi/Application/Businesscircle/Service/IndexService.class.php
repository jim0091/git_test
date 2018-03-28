<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈接口服务];						@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Service];
 * +----------------------------------------------------------------------
 * |@Name:			[IndexService.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2016-2017 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Service;
use Think\Controller;
class IndexService extends Controller {
	//根据企业的com_id去取出企业内部商城的数据
//     public function index($com_id){
//     	$ModelObj = D('CompanyItemConfig');
//     	$where['com_id'] = $com_id;
//     	$cateName = $ModelObj->where($where)->select();
//     	return $cateName;
//     }
    
//     //查询多条数据
//     public function select($where,$field = ''){
//     	$ModelObj = D('CompanyItemConfig');
//     	$arrData = $ModelObj->where($where)->field($field)->select();
//     	return $arrData;
//     }
    
//     //查询单条数据
//     public function find($where,$field = ''){
//     	$ModelObj = D('CompanyItemConfig');
//     	$findData = $ModelObj->where($where)->field($field)->find();
//     	return $findData;
//     }
    
//     //传商品ID的数组，返回商品的二维数组
//     public function getItem($recommendArray){
//     	$goodsInfo = array();
//     	$itemfield = 'item_id,shop_id,cat_id,title,price,image_default_id';
//     	for ($i=0;$i<count($recommendArray);$i++){
//     		$cond['item_id'] = $recommendArray[$i];
//     		$itemInfo = $this->findItemInfo($cond,$itemfield);
//     		if($itemInfo){
//     			array_push($goodsInfo, $itemInfo);
//     		}
//     	}
//     	return $goodsInfo;
//     }
    
//     //传商品的ID查询商品信息（单条）
//     public function findItemInfo($where,$field = ''){
//     	$ModelObj = D('SysitemItem');
//     	$findData = $ModelObj->where($where)->field($field)->find();
//     	return $findData;
//     }
    
   
    
//     //查询二级分类
//     public function getTowCate($item_config_id,$field){
//     	$where['item_config_id'] = $item_config_id;
//     	$where['com_id'] = $_SESSION['com_id'];
//     	$where['disabled'] = 0;//未禁用
//     	$goodsIdConf = $this->selectCompanyCategoryConfig($where,$field);
//     	return $goodsIdConf;
//     }
    
//     public function getTowCate($item_config_id,$com_id){
//     	$where['item_config_id'] = $item_config_id;
//     	$where['com_id'] = $com_id;
//     	$where['disabled'] = 0;//未禁用
//     	$field = 'cat_config_id,cat_id,item_config_id,cat_name,recommend,item_ids,shop_id';
//     	$goodsIdConf = $this->getTowCateConf($where,$field);
//     	return $goodsIdConf;
//     }
    
//     //查询二级分类（多条）
//     public function getTowCateConf($where,$field){
//     	$ModelObj = D('CompanyCategoryConfig');
//     	$arrData = $ModelObj->where($where)->field($field)->select();
//     	return $arrData;
//     }
    
//     //查询二级分类（单条）
//     public function getFindTowCateConf($where,$field){
//     	$ModelObj = D('CompanyCategoryConfig');
//     	$arrData = $ModelObj->where($where)->field($field)->find();
//     	return $arrData;
//     }
    
    
    
    
    //----------------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------------
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	查询企业积分信息表
	 * @author:	lihongqiang	2017-03-31 PM
	 * @method:	传参请求
	 * @param:	array	$where
	 * @param:	string	$field
	 * @return:	boolean	or 	null or array1
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//selectCompanyCategoryConfig	BEGIN
	public function findUserDeposit($where,$field=''){
		if(empty($where)){
    		return false;
    	}else{
    		$ModelObj = D('SysuserUserDeposit');
    		$accountInfo = $ModelObj->where($where)->field($field)->find();
    		if($accountInfo){
    			return $accountInfo;
    		}else{
    			return false;
    		}
    	}
	}
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询二级分类配置表（多条）
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array	$where
     * @param:	string	$field
     * @return:	boolean	or 	null or array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectCompanyCategoryConfig	BEGIN
    public function selectCompanyCategoryConfig($where,$field = ''){
    	if($where){
    		$ModelObj = D('CompanyCategoryConfig');
    		$arrData = $ModelObj->where($where)->field($field)->select();
    		if($arrData){
    			return $arrData;
    		}else{
    			return null;
    		}
    	}else{
    		return false;
    	}
    }	//selectCompanyCategoryConfig	BEGIN
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询二级分类配置表（单条）
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array	$where
     * @param:	string	$field
     * @return:	boolean	or 	null or array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectCompanyCategoryConfig	BEGIN
    public function findCompanyCategoryConfig($where,$field = ''){
    	if($where){
    		$ModelObj = D('CompanyCategoryConfig');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		if($findData){
    			return $findData;
    		}else{
    			return null;
    		}
    	}else{
    		return false;
    	}
    }	//selectCompanyCategoryConfig	BEGIN
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询一级分类配置表（多条）
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array	$where
     * @param:	string	$field
     * @return:	boolean	or 	null or array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectCompanyItemConfig	BEGIN
    public function selectCompanyItemConfig($where,$field = ''){
    	if ($where){
    		$ModelObj = D('CompanyItemConfig');
    		$arrData = $ModelObj->where($where)->field($field)->select();
    		if($arrData){
    			return $arrData;
    		}else{
    			return null;
    		}
    	}else{
    		return false;
    	}
    }	//selectCompanyItemConfig	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询多条数据
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array	$where
     * @param:	string	$field
     * @return:	boolean	or 	null or array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findCompanyItemConfig	BEGIN
    public function findCompanyItemConfig($where,$field = ''){
    	if($where){
    		$ModelObj = D('CompanyItemConfig');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		if ($findData){
    			return $findData;
    		}else{
    			return null;
    		}
    	}else{
    		return false;
    	}
    }	//findCompanyItemConfig	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	传商品ID的数组，返回商品的一维数组
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array	$where
     * @param:	string	$field
     * @return:	array	$itemInfoArray
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getItemList	BEGIN
    public function getItemList($itemIdArray){
    	$itemInfoArray = array();
    	$itemfield = 'item_id,shop_id,cat_id,title,price,image_default_id';
    	for ($i=0;$i<count($itemIdArray);$i++){
    		$cond['item_id'] = $itemIdArray[$i];
    		$itemInfo = $this->findItemInfo($cond,$itemfield);
    		if($itemInfo){
    			array_push($itemInfoArray, $itemInfo);
    		}
    	}
    	return $itemInfoArray;
    }	//getItemList	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询商品表（单条）
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array	$where
     * @param:	string	$field
     * @return:	boolean	or 	null or array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//findItemInfo	BEGIN
    public function findItemInfo($where,$field = ''){
    	if ($where){
    		$ModelObj = D('SysitemItem');
    		$findData = $ModelObj->where($where)->field($field)->find();
    		if($findData){
    			return $findData;
    		}else{
    			return null;
    		}
    	}else{
    		return false;
    	}
    }	//findItemInfo	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	查询商品表（多条）
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array	$where
     * @param:	string	$field
     * @return:	boolean	or 	null or array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectItemInfo	BEGIN
    public function selectItemInfo($where,$field = ''){
    	if ($where){
    		$ModelObj = D('SysitemItem');
    		$arrData = $ModelObj->where($where)->field($field)->select();
    		if($arrData){
    			return $arrData;
    		}else{
    			return null;
    		}
    	}else{
    		return false;
    	}
    }	//selectItemInfo	END
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	取商城下的分类的商品
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array	$where
     * @param:	string	$field
     * @return:	array2
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//selectItemInfo	BEGIN
    public function getStoreCateItem($cat_id){
    	$ModelObj = D('SyscategoryCat');
    	$where['parent_id'] = $cat_id;
    	$cateArr = $ModelObj->where($where)->field('cat_id,cat_name')->select();
    	$itemfield = 'item_id,shop_id,cat_id,title,price,image_default_id';
    	foreach ($cateArr as &$V){
    		$cond['cat_id'] = $V['cat_id'];
    		$V['cat_items_info'] = $this->selectItemInfo($cond,$itemfield);
    	}
    	return $cateArr;
    }
    
    
    
    
    
    

}