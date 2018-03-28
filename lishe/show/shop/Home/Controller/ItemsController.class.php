<?php 
namespace Home\Controller;
class ItemsController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->modelGoodsCat=D('Goodscat');
        $this->itemModel = M('sysitem_item');//商品表
        $this->itemDescModel = M('sysitem_item_desc');//商品描述
        $this->freepostageItemModel = M('syspromotion_freepostage_item');//包邮信息表
        $this->freepostageModel = M('syspromotion_freepostage');//包邮信息表
        $this->propsModel = M('syscategory_props');//属性表
        $this->propValuesModel = M('syscategory_prop_values');//属性值表
        $this->itemSkuModel = M("sysitem_sku");//库存表
        $this->siteAreaModel = M('site_area');//地区表
        $this->userAddressModel = M('sysuser_user_addrs');//用户地址表
        $this->cartModel = M('systrade_cart');//购物车表
		$this->modelActivityConfig=M('company_activity_config');
	}
	
	//搜索条件
	public function condition(){
		$condition='';
		$brandId = trim(I('get.brandId'));
		$minVal = I('minVal', 0, 'strip_tags,stripslashes');
		$maxVal = I('maxVal', 0, 'strip_tags,stripslashes');		
		if (!empty($brandId)) {
			$condition = " AND brand_id=".$brandId;
		}
		$keyword = trim(I('get.keyword'));
		if ($keyword) {
			$condition .=" AND (title like '%".$keyword."%' or keywords like '%".$keyword."%')";
		}		
		//产品利润率
		if($this->uid && $this->comId){
			if ($this->comId == '-1') {
				$profitRate = C("PROFIT_RATE");
				if (!empty($profitRate)) {
					$condition .=" AND (price-cost_price)/price >=".$profitRate;
				}
			}else{
				$profitRate = $this->modelGoodsCat->getCompanyConf('com_id='.$this->comId);
				if (!empty($profitRate['profit_rate'])) {
					$condition .=" AND (price-cost_price)/price >=".$profitRate['profit_rate']/100;
				}
			}
		}else{
			$profitRate = C("PROFIT_RATE");
			if (!empty($profitRate)) {
				$condition .=" AND (price-cost_price)/price >=".$profitRate;
			}
		}
		//金额
		if($minVal && $maxVal){
			$condition = "$condition and price >= $minVal and price <= $maxVal";
		}else if(!$minVal && $maxVal){
			$condition = "$condition and price <= $maxVal";
		}else if($minVal && !$maxVal){
			$condition = "$condition and price >= $minVal";
		}
		$this->assign('keyword',$keyword);
		return $condition;
	}
	/*
	 * 商品列表
	 * */	
	public function itemList(){
		$where = $this->condition();
		$catId=I('get.catId');
		$level = I('get.level', 3,'strip_tags,stripslashes,intval');
		$nowPage = I('p', 1, 'intval');
		$orderBy = I('orderBy', 'normal', 'strip_tags,stripslashes');  //筛选条件排序
		$sort = I('sort', 'desc', 'strip_tags,stripslashes');
		if(!in_array($orderBy, array('normal','sales','price','onsaleTime'))){
			$orderBy = 'normal';
		}
		if(!in_array($sort, array('desc','asc'))){
			$sort = 'desc';
		}
		if(!in_array($level, array(1,2,3))){
			$this->error('分类有误!');
		}
		$modelCategory = M('syscategory_cat');
		//获取导航分类
        $resCatList = getCatInfo($catId,$level);
        //$this->assign('keyword',$resCatList[2]['cat_name']);
        //获取品牌    
        if ($catId) {
			//所有分类的所有品牌
			if($level == 3){
				$catIds = array($catId);
			}else{
				//一级或二级取出三级
				$map = array(
					'parent_id' => $catId
				);
				$catIds = $modelCategory->where($map)->getField('cat_id',TRUE);
				if($level == 1){
					$map = array(
						'parent_id' => array('in', $catIds)
					);
					$catIds = $modelCategory->where($map)->getField('cat_id',TRUE);				
				}
			} 
			if($catIds){
				$map = array(
					'cat_id' => array('in', $catIds)
				);          
		    	$catBrandArr = M('syscategory_cat_rel_brand')->where($map)->getField('brand_id',TRUE);
			} 
	    	if ($catBrandArr) {
	    		$map = array(
					'brand_id' => array('in', $catBrandArr),
					'disabled'  => 0
				);
	    		$brandArr = M('syscategory_brand')->where($map)->field('brand_id,brand_name')->select();
	    	}
	        //分类列表
	        $catLevName = array(
				1 => '一级分类',
				2 => '二级分类',
				3 => '三级分类',
			);
			$catLevelList = array();
			//下一级
			if($level < 3){
		       	$map = array(
					'parent_id' => $catId,
					'disabled'  => 0
				);			
		   		$catLevelList[$level+1]['catInfo'] = $modelCategory->where($map)->field('cat_id,cat_name,level,parent_id')->order('order_sort DESC')->select();			
				$catLevelList[$level+1]['name'] = $catLevName[$level+1];		
			}
			$map = array(
				'cat_id' => $catId
			);		
			$catParentId = $modelCategory->where($map)->getField('parent_id');
	       	$map = array(
				'parent_id' => $catParentId,
				'disabled'  => 0
			);
	       	$catLevelList[$level]['catInfo'] = $modelCategory->where($map)->field('cat_id,cat_name,level,parent_id')->order('order_sort DESC')->select();
			$catLevelList[$level]['name'] = $catLevName[$level];
			if($catParentId){
				$map = array(
					'cat_id' => $catParentId
				);
				$catPreId = $modelCategory->where($map)->getField('parent_id');			
				//上一级分类
		       	$map = array(
					'parent_id' => $catPreId,
					'disabled'  => 0
				);
	       		$catLevelList[$level-1]['catInfo'] = $modelCategory->where($map)->field('cat_id,cat_name,level,parent_id')->order('order_sort DESC')->select();			
				$catLevelList[$level-1]['name'] = $catLevName[$level-1];
			}
			if($catPreId){
				//上一级分类
				$map = array(
					'cat_id' => $catPreId
				);
				$catPrevId = $modelCategory->where($map)->getField('parent_id');			
				//上一级分类
		       	$map = array(
					'parent_id' => $catPrevId,
					'disabled'  => 0
				);
		       	$catLevelList[$level-2]['catInfo'] = $modelCategory->where($map)->field('cat_id,cat_name,level,parent_id')->order('order_sort DESC')->select();				
				$catLevelList[$level-2]['name'] = $catLevName[$level-2];
			}
			asort($catLevelList,1);
		    //属性值
	//	        $propsArr = array();
	//	        if (getTableList('syscategory_cat_rel_prop','cat_id ='.$catId)) {
	//	        	$catPropsArr = getTableList('syscategory_cat_rel_prop','cat_id ='.$catId);
	//	        	if ($catPropsArr) {
	//	            	foreach ($catPropsArr as $kcp => $valcp) {
	//	            		$propsArr[$kcp] = getTableRow('syscategory_props','prop_id',$valcp['prop_id'],'prop_id,prop_name');
	//	            		$propsArr[$kcp]['propValue'] = getTableList('syscategory_prop_values','prop_id ='.$valcp['prop_id']);
	//	            	}            		
	//	        	}
	//	        }
			if($level == 1){
	    		$where .= " and a.cat_1=".$catId;
			}else if($level == 2){
	    		$where .= " and a.cat_2=".$catId;
			}else if($level == 3){
	    		$where .= " and a.cat_id=".$catId;
			}
	    	$randCatItemList  = $this->modelGoodsCat->randCatItem($catId,$level);        	
		}  
		$size = 20;
		$number=$this->modelGoodsCat->getItemCount($where);
    	$page = new \Think\Page($number,$size);
		$page -> setConfig('first' ,'首页');
		$page->lastSuffix = false;
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page->firstRow;  //起始行数
		$pagesize = $page->listRows;   //每页显示的行数
		$limit = "$start,$pagesize";
		$style = "badge";
    	$onclass = "pageon";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$totalPages = ceil($number/$size)==0 ? 1 : ceil($number/$size);    	
		$res=$this->modelGoodsCat->getItemList($where,$limit,$orderBy,$sort);
		foreach ($res as $kres => $valres) {
			$shopInfo = getTableRow('sysshop_shop','shop_id',$valres['shop_id']);
			$res[$kres]['shopName'] = $shopInfo['shop_name'];
			$res[$kres]['shopType'] = C(strtoupper($shopInfo['shop_type']));
		}
		$pageInfo = array(
			'num' => $number,
			'totalPage' => $totalPages,
			'nowPage' => $nowPage
		);	
		$this->assign('pageInfo',$pageInfo);			
		//店铺推荐
		$this->assign('pagestr',$pagestr);
		$this->assign('level',$level);
		$this->assign('totalPages',$totalPages);
		$this->assign('list',$res);	
		$this->assign('catLevelList',$catLevelList);	
        $this->assign('resCatList',$resCatList);
        $this->assign('brandArr',$brandArr);
        $this->assign('propsArr',$propsArr);
        $this->assign('randCatItemList',$randCatItemList);
        $this->assign('catId',$catId);
        $this->assign('ky',trim(I('get.keyword')));
        
        if (I('get.ajaxpost')){            	
			$this->display('itemListAjax');
		}else{
			$this->display();
		}		
	}
	/*
	 * 搜索结果列表
	 * */		
	public function searchList(){
		$keyWord=I('keyword');
		$size = 18;
		$number=$this->modelGoodsCat->getSearchCount($keyWord);
		$page = new \Think\PagePn($number, $size);
		$rollPage = 1;
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";	
		$style = "custom-paginations-prev";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串	
		$res=$this->modelGoodsCat->getSearchRes($keyWord,$limit);
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$res);			
		$this->display();
	}
	/*
	 * 
	 *列表页加入购物车显示商品属性等
	 * 
	 * */
	 public function jionShow(){
    	$itemId = I('get.itemId');
        if (!empty($itemId)) {
            //商品信息
            $itemInfo = $this->itemModel->where('item_id = '.$itemId)->find();
            //商品描述
            $itemDesc = $this->itemDescModel->where('item_id = '.$itemId)->find();
            
            //字符串转数组方便调用
            if (!empty($itemInfo['list_image'])) {
                $newItemInfoImage = explode(',',$itemInfo['list_image']);
                $itemInfo['new_list_images'] = $newItemInfoImage;            
            }
	    	$itemId = I('get.itemId');
	        if (!empty($itemId)) {
	            //商品信息
	            $itemInfo = M('sysitem_item')->where('item_id = '.$itemId)->find();
	            //商品描述
	            $itemDesc = M('sysitem_item_desc')->where('item_id = '.$itemId)->find();
	            
	            //字符串转数组方便调用
	            if (!empty($itemInfo['list_image'])) {
	                $newItemInfoImage = explode(',',$itemInfo['list_image']);
	                $itemInfo['new_list_images'] = $newItemInfoImage;            
	            }

	            //优惠信息（包邮）
	            if ($itemInfo['shop_id']) {
	                $freepostageInfo = $this->freepostageModel->where('shop_id = '.$itemInfo['shop_id'])->find();
	                if ($freepostageInfo['freepostage_id']) {
	                    $freepostageItemInfo = $this->freepostageItemModel->where('freepostage_id = '.$freepostageInfo['freepostage_id'])->find();
	                    if ($freepostageItemInfo['item_id'] == 0) {
	                        $freepostageLimitMoney = "全场满&nbsp;".sprintf("%.2f",$freepostageInfo['limit_money'])."包邮";
	                    }else{
	                        $freepostageLimitMoney = "满 ".sprintf("%.2f",$freepostageInfo['limit_money'])." 包邮";
	                    }
	                }
	            }

	            //商品属性
	            $arrItemSpecDesc = unserialize($itemInfo['spec_desc']);
	            
	            $specValue;
	            $specValueId;
	            if (is_array($arrItemSpecDesc)) {
	                foreach ($arrItemSpecDesc as $key => $value) {
	                    $specValue .= $key.",";
	                    foreach ($value as $k => $val) {
	                        $specValueId .= $k.",";
	                    }
	                }
	            }

	            //查属性表
	            if (!empty($specValue)) {
	                $where['prop_id'] = array('in',$specValue);;
	                $propsList = $this->propsModel->where($where)->select();
	                
	            }
	            //查属性值表
	            if (!empty($specValueId)) {
	                $where['prop_value_id'] = array('in',$specValueId);
	                $propValuesList = $this->propValuesModel->where($where)->select();
	            }

	            //合并两个数组
	            $newPropsValuesList = array();
	            foreach ($propsList as $key => $value) {
	                $newPropsValuesList[$key] = $value;
	                foreach ($propValuesList as $k => $val) {
	                    if ($val['prop_id'] == $value['prop_id']) {                    
	                        $newPropsValuesList[$key]['item'][$k] = $val;
	                    }
	                }	                
	            }
	            //查询库存表
	            $sKuList = $this->itemSkuModel->table('sysitem_sku sku,sysitem_sku_store store')->where('sku.sku_id = store.sku_id and sku.item_id = '.$itemId)->select();

	            //更多精选商品
	            $itemList = $this->itemModel->order('item_id desc')->limit(10)->select();

	            //地区
	            $area = $this->siteAreaModel->where(array('jd_pid' => 0))->select();

	            //收货地址查询
	            $userAddressList = $this->userAddressModel->where('user_id ='.$this->uid)->select();
	            $newUserAddressList = array();
	            foreach ($userAddressList as $key => $value) {
	                $newUserAddressList[$key] = $value;
	                $bNewadd = strstr($value['area'],':',true);
	                $newaddId = str_replace('/','_', trim(strstr($value['area'],':'),':'));
	                $newUserAddressList[$key]['newadd'] =  $bNewadd.$value['addr'];
	                $newUserAddressList[$key]['newaddid'] = $newaddId;
	            }


	            $this->assign('itemInfo',$itemInfo);
	            $this->assign('itemDesc',$itemDesc);
	            $this->assign('freepostageLimitMoney',$freepostageLimitMoney);

	            $this->assign('propsList',$propsList);
	            $this->assign('propsListEmpty','<span class="js-sku-s1">请选择</span>');

	            $this->assign('propValuesList',$propValuesList);
	            $this->assign('newPropsValuesList',$newPropsValuesList);

	            $this->assign('sKuList',$sKuList);

	            $this->assign('itemList',$itemList);

	            $this->assign('area',$area);

	            $this->assign("newUserAddressList",$newUserAddressList);
				$this->display();
			 }	 
		 }
	}
	/*
	 * 礼舍推荐页
	 * */
	public function itemRecommend(){
		$activity=$this->activity(3);	
		$first=array(
			'activity' => current($activity['activity']),
			'list'     => current($activity['list'])
		);
		$this->assign('activity',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->assign('first',$first);		
		$this->display();
	}
	public function activity($aid){
		if(empty($aid)){
			$aid=1;
		}
		$activityConfig=$this->modelActivityConfig->where('activity_id='.$aid)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->order('order_sort DESC')->select();
		foreach($activityConfig as $key=>$value){
			$activity[$value['activity_config_id']]=array(
				'id'=>$value['activity_config_id'],
				'name'=>$value['cat_name'],
				'banner'=>$value['cat_banner'],					
				'content'=>$value['cat_content'],
				'item_ids'=>$value['item_ids'],
				'more_link'=>$value['more_link']
			);
			if(!empty($value['recommend'])){
				$condition='i.item_id IN('.$value['recommend'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';	
				$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->limit(10)->select();
			}
		}		
		return array('activity'=>$activity,'list'=>$itemList);
	}
	/*
	 * 礼舍推荐更多页
	 * */
	public function recommendMore(){
		$activityId=I('activityId');
		if($activityId){
			$activityConfig=$this->modelActivityConfig->where('activity_config_id='.$activityId)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->find();
			$activity=array(
				'id'=>$activityConfig['activity_config_id'],
				'name'=>$activityConfig['cat_name'],
				'banner'=>$activityConfig['cat_banner'],					
				'content'=>$activityConfig['cat_content'],
				'item_ids'=>$activityConfig['item_ids'],
				'more_link'=>$activityConfig['more_link']
			);
			if(!empty($activityConfig['item_ids'])){
				$size=20;
				$condition='i.item_id IN('.$activityConfig['item_ids'].') AND i.item_id=is.item_id AND is.approve_status=\'onsale\'';
				$number=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->where($condition)->count();
				$page = new \Think\PagePn($number, $size);
				$rollPage = 1;
				$page -> setConfig('prev' ,'上一页');
				$page -> setConfig('next' ,'下一页');
				$start = $page -> firstRow;  //起始行数
				$pagesize = $page -> listRows;   //每页显示的行数
				$limit = "$start , $pagesize";	
				$style = "custom-paginations-prev";
				$itemIdArr=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id')->where($condition)->order('i.flag ASC,i.cat_id DESC,i.profit_rate DESC')->select();
				foreach($itemIdArr as $key=>$value){
					$itemId[]=$value['item_id'];
				}
				$itemId=array_slice($itemId,$start,$pagesize);
				$condition='item_id IN('.implode(',',$itemId).')';
				$itemList=$this->itemModel->field('item_id,title,image_default_id,price,mkt_price,flag')->where($condition)->select();
				$pagestr = $page -> show($style,$onclass); 
				$this -> assign('pagestr',$pagestr);					
			}
			$first=array('activity'=>$activity,'list'=>$itemList);		
			$this->assign('first',$first);
			$this->display();		
		}
	}


}
?>
