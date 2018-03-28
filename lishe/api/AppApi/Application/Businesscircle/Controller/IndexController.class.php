<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[企业圈_企业商城接口];					@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[IndexController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-03-23 AM
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2017-03
 * +----------------------------------------------------------------------
 *  */
namespace Businesscircle\Controller;
use Common\Controller\RootController;
use Businesscircle\Service\IndexService;
use Think\Controller;
class IndexController extends RootController {
	//继承RootController,代表执行本控制器内的所以方法都需要登录
	//继承CommonController,代表执行本控制器内的所以方法都不需要登录
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	企业圈_企业商城_首页(访问操作)
	 * @author:	lihongqiang	2017-03-23 AM
	 * @method:	POST
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//index	BEGIN
    public function index(){
    	$this->businessShop();//索引发生跳转，便于中控
    }	//index	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_企业商城_首页(中转操作)
     * @author:	lihongqiang	2017-03-23 AM
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//businessShop	BEGIN
    private function businessShop(){
    	$_SESSION['com_id'] = '1467166836740';
    	$com_id = $_SESSION['com_id'];
    	if(empty($com_id)||$com_id<0){
    		$com_id = '1467166836740';
    	}
    	$Service = new IndexService();
    	$where['com_id'] = $com_id;
    	$CateName = $Service->selectCompanyItemConfig($where,$field = 'item_config_id,app_cat_icon,cat_name,recommend,item_ids');
    	if(empty($CateName)){
    		//企业ID存在，分类不存在，设置一个默认的item_config_id
    	}else{
    		$cateItemInfoData = $this->foreachData($CateName);
    	}
    	
    	$data['comName'] = $this->getComName();
    	$data['cateItemInfoData'] = $cateItemInfoData;
    	$successInfo['data'] = $data;
    	$this->retSuccess($successInfo);
    }	//businessShop	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	获取企业名称
     * @author:	lihongqiang	2017-03-31 PM
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//businessShop	BEGIN
    private function getComName(){
    	$where['user_id'] = $_SESSION['user_id'];
    	$Service = new IndexService();
    	$findUserDeposit = $Service->findUserDeposit($where,$field='comName');
    	if($findUserDeposit){
    		return $findUserDeposit['comName'];
    	}else{
    		return '';
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	遍历数据
     * @author:	lihongqiang	2017-03-23 AM
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//businessShop	BEGIN
    private function foreachData($CateName){
    	foreach ($CateName as &$V){
    		if(empty($V['app_cat_iocn'])){
    			$V['app_cat_iocn'] = 'http://www.lishe.cn/business/Public/gd10086/images/Mfloor3.png';
    		}
    		$field = 'cat_config_id,cat_id,item_config_id,cat_name';
    		$Service = new IndexService();
    		$where['item_config_id'] = $V['item_config_id'];
    		$where['com_id'] = $_SESSION['com_id'];
    		$where['disabled'] = 0;//未禁用
    		$towCate = $Service->selectCompanyCategoryConfig($where,$field);//取出一级分类下的二级分类名称和ID
    		if(empty($towCate)){
    			$V['towCate'] = "";//如果为空设置空字符串返回
    		}else{
    			$V['towCate'] = $towCate;
    		}
    		if(!empty($V['recommend'])){
    			$itemIdString = $V['recommend'];
    		}else{
    			$itemIdString = $V['item_ids'];
    		}
    		if(empty($itemIdString)){
    			//单个一级分类下没有推荐也没有更多
    		}else{
    			$itemIdArray = explode(',', $itemIdString);
    			$goodsInfo = $Service->getItemList($itemIdArray);
    			$V['ItemInfo'] = $goodsInfo;
    		}
    		unset($V['recommend']);//处理完毕，返回无用，清除
    		unset($V['item_ids']);//处理完毕，返回无用，清除
    	}
    	return $CateName;
    }
    
  
    
    
    

    //----------------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------------
    //----------------------------------------------------------------------------------------
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	企业圈_企业商城_二级分类页
     * @author:	lihongqiang	2017-03-23 AM
     * @method:	POST
     * @param:	item_config_id	cat_config_id//一级分类的ID
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getTowCataItemInfo	BEGIN
    public function getCataItemList(){
    	$_SESSION['com_id'] = '1469444223094';//1463542509407//1467166836740
    	$postData = I('post.');
    	if(empty($postData)){
    		$errorInfo['status'] = -1001;
    		$errorInfo['msg'] = "参数为空，没有提交任何数据";
    		$errorInfo['message'] = "服务繁忙，操作失败";
    		$this->retError($errorInfo);
    	}else{
    		$item_config_id = $postData['item_config_id'];
    		$cat_config_id = $postData['cat_config_id'];
    		$from_price = $postData['from_price'];
    		$to_price = $postData['to_price'];
    		if(empty($item_config_id)||empty($cat_config_id)){
    			$errorInfo['status'] = -1002;
    			$errorInfo['msg'] = "参数item_config_id或cat_config_id为空";
    			$errorInfo['message'] = "没有相关商品";
    			$this->retError($errorInfo);
    		}else{
    			$towCateList = $this->towCateList($item_config_id,$cat_config_id);//查询一级分类下的二级分类
    			if(!isset($cat_config_id)||$cat_config_id==0||empty($cat_config_id)){
    				$hotItemList = $this->hotItemList($item_config_id);//索引查询热门商品
    			}else{
    				$hotItemList = $this->getTowCateItemConf($cat_config_id);
    			}
    			//排序处理
//     			$b = asort($hotItemList,'price');
//     			var_dump($b);exit;
    			//判断价格过滤
    			if($from_price && $to_price){
    				$ItemListArray = array();
    				for($i=0;$i<count($hotItemList);$i++){
    					if($hotItemList[$i]['price']>=$from_price && $hotItemList[$i]['price']<=$from_price){
    						array_push($ItemListArray, $hotItemList[$i]);
    					}
    				}
    			}else{
    				if($from_price){
    					$ItemListArray = array();
    					for($i=0;$i<count($hotItemList);$i++){
    						if($hotItemList[$i]['price']>=$from_price){
    							array_push($ItemListArray, $hotItemList[$i]);
    						}
    					}
    				}
    				if($to_price){
    					$ItemListArray = array();
    					for($i=0;$i<count($hotItemList);$i++){
    						
    						if($hotItemList[$i]['price']<=$from_price){
    							array_push($ItemListArray, $hotItemList[$i]);
    						}
    					}
    				}else{
    					$ItemListArray = $hotItemList;
    				}
    			}
    			$sort = array(
    					'direction'=>'SORT_ASC',//排序规则：升序
    					'field'=>'price',		//排序字段
    					);
    			$arrSort = array();
    			foreach ($ItemListArray AS $uniqid=>$row){
    				foreach ($row as $key=>$value){
    					$arrSort[$key][$uniqid] = $value;
    				}
    			}
    			if($sort['direction']){
    				array_multisort($arrSort[$sort['field']],constant($sort['direction']),$ItemListArray);
    			}
    			
    			$data['towCateList'] = $towCateList;
    			$data['thisCateItemList'] = $ItemListArray;
    			$successInfo['data'] = $data;
    			$this->retSuccess($successInfo);
    		}
    	}
    }	//getTowCataItemInfo	END
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	传item_config_id,cat_config_id查询二级分类名称
     * @author:	lihongqiang	2017-03-23 AM
     * @method:	POST
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//towCateList	BEGIN
    private function towCateList($item_config_id,$cat_config_id){
    	$where['com_id'] = $_SESSION['com_id'];
    	$where['disabled'] = 0;//查询未禁用的
    	$where['item_config_id'] = $item_config_id;
    	$field = 'cat_config_id,item_config_id,cat_id,cat_name,recommend,item_ids,shop_id';
    	$Service = new IndexService();
    	$towCateList = $Service->selectCompanyCategoryConfig($where,$field);
    	$hot = array(
    			'cat_config_id'		=>'0',
    			'item_config_id'	=>$item_config_id,
    			'cat_id'			=>'',
    			'cat_name'			=>'热卖',
    			'recommend'			=>'',
    			'item_ids'			=>'',
    			'shop_id'			=>''
    			);
    	if(empty($towCateList)){
    		$towCateList = array();
    		array_push($towCateList, $hot);//添加进去
    	}else{
    		array_unshift($towCateList,$hot);//添加热门到数组的最前面
    	}
    	foreach ($towCateList as &$V){
    		if($V['cat_config_id'] ==$cat_config_id){
    			$V['isChecked'] = 1;//标识为选中
    		}else{
    			$V['isChecked'] = 0;
    		}
    		unset($V['recommend']);//处理完毕，返回无用，清除
    		unset($V['item_ids']);//处理完毕，返回无用，清除
    	}
    	return $towCateList;
    }	//towCateList	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	从一级分类进入二级分类列时，索引查询热门商品
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array $item_config_id 一级分类的ID
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//hotItemList	BEGIN
    private function hotItemList($item_config_id){
    	//如果一级分类下面有更多则直接返回一级的更多
    	$Service = new IndexService();
    	$where['com_id'] = $_SESSION['com_id'];
    	$where['item_config_id'] = $item_config_id;
    	$where['disabled'] = 0;//查询未禁用的
    	$field = 'item_config_id,cat_name,recommend,item_ids';
    	$findOneCateData = $Service->findCompanyItemConfig($where,$field);
    	if($findOneCateData){
    		if($findOneCateData['item_ids']){
    			$itemIdArray = explode(',', $findOneCateData['item_ids']);
    			$ItemList = $Service->getItemList($itemIdArray);
    			return $ItemList;
    		}else{
    			//一级分类下没有更多，就把这个一级分类下的二级分类下的所有推荐认为是热卖，如果没有就取更多，更多没有就判断cat_id,取cat_id下对应的商城的商品做为热卖
    			$field1 = 'item_config_id,cat_id,cat_name,recommend,item_ids';
    			$towCateData = $Service->selectCompanyCategoryConfig($where,$field1);
    			if($towCateData){
    				$ItemList = $this->disposeCateData($towCateData);
    				if($ItemList){
    					return $ItemList;
    				}else{
    					//有二级分类，但是二级分类下recommend跟item_ids都没数据或没取到数据，开始使用cat_id操作
    					return null;
    				}
    			}else{
    				//一级没有更多，二级分类数据也不存在了，特殊处理
    				return null;
    			}
    		}
    	}else{
    		return null;//服务繁忙
    	}
    }	//hotItemList	END
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	处理数据
     * @author:	lihongqiang	2017-03-23 PM
     * @method:	传参请求
     * @param:	array $towCateData 二级分类的二维数组
     * @return: boolean	or	array1
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//disposeCateData	BEGIN
    protected function disposeCateData($towCateData){
    	if(empty($towCateData)){
    		return null;
    	}else{
    		$newItemIdArray = array();
    		foreach ($towCateData as $V){
    			if (!empty($V['recommend'])){
    				$itemIdString = $V['recommend'];
    			}else{
    				if(!empty($V['item_ids'])){
    					$itemIdString = $V['item_ids'];
    				}else{
    					$itemIdString = '';//////////////////////////////开始使用cat_id查找
    				}
    			}
    			$itemIdArray = explode(',', $itemIdString);
    			for ($i=0;$i<count($itemIdArray);$i++){
    				if(!(in_array($itemIdArray[$i], $newItemIdArray))){
    					array_push($newItemIdArray, $itemIdArray[$i]);
    				}
    			}
    		}
    		if(!empty($newItemIdArray)){
    			$Service = new IndexServiceController();
    			$ItemList = $Service->getItemList($newItemIdArray);//返回一维数组的商品
    			return $ItemList;
    		}else{
    			return null;
    		}
    	}
    }	//disposeCateData END
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	//点击一级分类下的二级分类或更多
     * @author:	lihongqiang	2017-03-23 AM
     * @method:	POST
     * @param:	string	$itemIdString
     * @return:	array1	$goodsInfo
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//getHomeItemInfo	BEGIN
    private function getTowCateItemConf($cat_config_id){
    	if(empty($cat_config_id)||$cat_config_id==0){
    		return false;
    	}else{
    		$where['com_id'] = $_SESSION['com_id'];
    		$where['cat_config_id'] = $cat_config_id;
    		$field = 'cat_config_id,cat_id,item_config_id,cat_name,recommend,item_ids,shop_id';
    		$Service = new IndexService();
    		$cateTowName = $Service->findCompanyCategoryConfig($where,$field);
    		if(empty($cateTowName)){
    			return null;//二级分类不存在
    		}else{
    			if(!empty($cateTowName['recommend'])){
    				$itemIdString = $cateTowName['recommend'];
    			}else{
    				$itemIdString = $cateTowName['item_ids'];
    			}
    			if(empty($itemIdString)){
    				if($cateTowName['cat_id']!=0){
    					//取cat_id分类下对应的商城分类的商品
    					$ItemInfo = $Service->getStoreCateItem($cateTowName['cat_id']);
    				}else{
    					//二级分类下没有推荐也没有更多，且分类id为0（这是特殊情况，数据出错了，一般不存在）
    					return false;
    				}
    			}else{
    				$itemIdArray = explode(',', $itemIdString);
    				$ItemInfo = $Service->getItemList($itemIdArray);
    			}
    			
    			unset($cateTowName['recommend']);//处理完毕，返回无用，清除
    			unset($cateTowName['item_ids']);//处理完毕，返回无用，清除
    			if(empty($ItemInfo)){
    				$ItemInfo = '';
    			}
    			return $ItemInfo;
    		}
    	}
    }
    
    
    
    
    
    
    
    




    //     /**
    //      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //      * @name:	//点击一级分类下的二级分类或更多
    //      * @author:	lihongqiang	2017-03-23 AM
    //      * @method:	POST
    //      * @param:	string	$itemIdString
    //      * @return:	array1	$goodsInfo
    //      * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //      */	//getCateItemInfo	BEGIN
    //     public function getCateItemInfo($cat_config_id){
    //     	$postData = I('post.');
    //     	if(empty($postData)){
    //     		$errorInfo['status'] = -1001;
    //     		$errorInfo['msg'] = "参数为空，没有提交任何数据";
    //     		$errorInfo['message'] = "服务繁忙，操作失败";
    //     		$this->retError($errorInfo);
    //     	}else{
    //     		//$cat_config_id = $postData['cat_config_id'];
    //     		if(empty($cat_config_id)||$cat_config_id==0){
    //     			$errorInfo['status'] = -1002;
    //     			$errorInfo['msg'] = "参数cat_config_id为空";
    //     			$errorInfo['message'] = "没有相关商品";
    //     			$this->retError($errorInfo);
    //     		}else{
    //     			//$where['com_id'] = $_SESSION['com_id'];
    //     			$where['cat_config_id'] = $cat_config_id;
    //     			$field = 'cat_config_id,cat_id,item_config_id,cat_name,recommend,item_ids,shop_id';
    //     			$Service = new IndexService();
    //     			$cateTowName = $Service->findCompanyCategoryConfig($where,$field);
    //     			if(empty($cateTowName)){
    //     				$errorInfo['status'] = -1003;
    //     				$errorInfo['msg'] = "二级分类数据不存在，请检查参数是否正确传递";
    //     				$errorInfo['message'] = "没有相关商品";
    //     				$this->retError($errorInfo);
    //     			}else{
    // //     				$field1 = 'cat_name';
    // //     				$cond['item_config_id'] = $cateTowName['item_config_id'];
    // //     				$fatherCateName = $Service->find($where,$field1);
    // //     				$cateTowName['father_cate_name'] = $fatherCateName['cat_name'];//找到父级分类的名称
    // //     				if($cateTowName['cat_id']==0){
    // //     					if(!empty($cateTowName['recommend'])){
    // //     						$itemIdString = $cateTowName['recommend'];
    // //     					}else{
    // //     						$itemIdString = $cateTowName['item_ids'];
    // //     					}
    // //     					if(empty($itemIdString)){
    // //     						//二级分类下没有推荐也没有更多，且分类id为0（这是特殊情况，数据出错了，一般不存在）
    // //     					}else{
    // //     						$ItemInfo = $this->getHomeItemInfo($itemIdString);//
    // //     					}
    // //     				}else{
    // //     					//取cat_id分类下对应的商城分类的商品
    // //     					$ItemInfo = $Service->getStoreCateItem($cateTowName['cat_id']);
    // //     				}
    //     					if(!empty($cateTowName['recommend'])){
    //     						$itemIdString = $cateTowName['recommend'];
    //     					}else{
    //     						$itemIdString = $cateTowName['item_ids'];
    //     					}
    //     					if(empty($itemIdString)){
    //     						if($cateTowName['cat_id']!=0){
    //     								//取cat_id分类下对应的商城分类的商品
    //     							$ItemInfo = $Service->getStoreCateItem($cateTowName['cat_id']);
    //     						}else{
    //     							//二级分类下没有推荐也没有更多，且分类id为0（这是特殊情况，数据出错了，一般不存在）
    	
    //     						}
    //     					}else{
    //     						$ItemInfo = $this->getHomeItemInfo($itemIdString);//
    //     					}
    
    //     				unset($cateTowName['recommend']);//处理完毕，返回无用，清除
    //     				unset($cateTowName['item_ids']);//处理完毕，返回无用，清除
    // //     				$cateTowName['ItemInfo'] = $ItemInfo;
    // //     				$successInfo['data'] = $cateTowName;
    // //     				$this->retSuccess($successInfo);
    //     				if(empty($ItemInfo)){
    //     					$ItemInfo = '';
    //     				}
    //     				return $ItemInfo;
    //     			}
    //     		}
    //     	}
    //     }
        
    
    
    
    
}