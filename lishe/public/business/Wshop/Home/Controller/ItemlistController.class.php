<?php 
namespace Home\Controller;
class ItemlistController extends CommonController{
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
		$this->comActivity=M('company_activity');
		$this->comActivityCate=M('company_activity_category');
		$this->comActivityCateItem=M('company_activity_category_item');
	}
	/*
	 * 商品分类列表
	 * */
	public function classifyList(){
		$res=$this->modelGoodsCat->getCatList();
		$this->assign('list',$res);		
		$this->display();		
	}
	/*
	 * 商品列表
	 * */	
	public function itemList(){
		$catId=I('get.catId');
		$ajaxget = I('get.ajaxget');
		if($catId){
			$size = 18;
			$number=$this->modelGoodsCat->getItemCount($catId);
			if(empty($number)){
//				echo "<script charset='UTF-8'>alert('该分类暂时没有商品!');</script>";
				$url = __APP__."/Itemlist/classifyList";
				echo "<script>window.location='".$url."';</script>";
				exit;
			}
			$page = new \Think\PagePn($number, $size);
			$rollPage = 1;
			$page -> setConfig('prev' ,'上一页');
			$page -> setConfig('next' ,'下一页');
			$start = $page -> firstRow;  //起始行数
			$pagesize = $page -> listRows;   //每页显示的行数
			$limit = "$start , $pagesize";	
			$style = "custom-paginations-prev";
			$pagestr = $page -> show($style,$onclass);  //组装分页字符串	
			$res=$this->modelGoodsCat->getItemList($catId,$limit);
			//分类名
			$catName=$this->modelGoodsCat->getCatName($catId);
			$this->assign('pagestr',$pagestr);
			$this->assign('list',$res);	
			$this->assign('catId',$catId);
			$this->assign('catName',$catName);	
			if (!empty($ajaxget)) {
				$this->display('itemListAjax');	
			}else{
				$this->display();
			}
		}
		
	}
	//搜索条件
	public function condition(){
		$condition='';
		if (trim(I('get.keyword'))) {
			$condition = "title like '%".I('get.keyword')."%'";
		}
		return $condition;
	}
	/*
	 * 搜索结果列表
	 * */		
	public function searchList(){
		$where = $this->condition();
		$keyWord=I('get.keyword');
		$ajaxpost = I('get.ajaxpost');
		$size = 18;
		$number=$this->modelGoodsCat->where($where)->getSearchCount($keyWord);
		$page = new \Think\PagePn($number, $size);
		$rollPage = 1;
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";	
		$style = "custom-paginations-prev";
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串	
		$res=$this->modelGoodsCat->where($where)->getSearchRes($keyWord,$limit);
		$this->assign('pagestr',$pagestr);
		$this->assign('keyword',$keyWord);
		$this->assign('list',$res);	
		if (!empty($ajaxpost)) {
			$this->display('searchListAjax');	
		}else{
			$this->display();			
		}		
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
            //var_dump($specValue);
            //var_dump($specValueId);

            //查属性表
            if (!empty($specValue)) {
                $where['prop_id'] = array('in',$specValue);;
                $propsList = $this->propsModel->where($where)->select();
                
            }
            //var_dump($propsList);
            //查属性值表
            if (!empty($specValueId)) {
                $where['prop_value_id'] = array('in',$specValueId);
                $propValuesList = $this->propValuesModel->where($where)->select();
            }
            //var_dump($propValuesList);

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
            //var_dump($newPropsValuesList);


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
				if($aid=='8'){
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->select();	
				}else{	
					$itemList[$value['activity_config_id']]=M()->table(array('sysitem_item'=>'i','sysitem_item_status'=>'is'))->field('i.item_id,i.title,i.image_default_id,i.price,i.mkt_price,i.flag')->order('i.flag asc')->where($condition)->limit(10)->select();
				}
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

	public function listDetailActivity(){
		$aid=I('get.aid','','trim');
		if(empty($aid)){
			$aid=14; //aid不存在
		}
		if($aid){
			$actCateInfo=$this->comActivityCate->where('activity_config_id='.$aid)->field('activity_config_id,recommend,aid,cat_banner,cat_name')->select();
			if($actCateInfo){
				// foreach($actCateInfo as $k1=>$v1){
				// 	$cateArr[$k1]=$v1['activity_config_id'];
				// }
				$this->assign('actCateInfo',$actCateInfo);
				// dump($actCateInfo);
				$this->assign('cat_name',$actCateInfo[0]['cat_name']);
			}
			//var_dump($actCateInfo);
				//$items=$this->actCateInfo->

				$cateItemInfo=$this->comActivityCateItem->where('aid='.$aid)->select();
				if($cateItemInfo){
					foreach($cateItemInfo as $k=>$v){
						$cateItemArr[$k]=trim($v['recommend_id']);
					}
					//echo count($cateItemInfo);
					$info=$this->itemModel->table('sysitem_item a,company_activity_category_item b')->where('a.item_id=b.recommend_id and a.item_id in('.implode(',',$cateItemArr).')')->field('b.cate_id,a.item_id,a.title,a.image_default_id,a.price,a.mkt_price,a.flag')->select();
					if($info){
						// var_dump($info);
										
						$this->assign('info',$info);
					}
				}

		}
		$this->display('listDetailActivity');
	}

	//京东食用油 开始
	public function jdOilActivity(){//商品列表页
		$aid=I('get.aid','','trim');
		if(empty($aid)){
			$aid=8;
		}

		$activity=$this->activity($aid);
		// dump($activity['list']);
		// $this->assign('activityInfo',$activity['activity']);
		$this->assign('list',$activity['list']);
		// $this->display('Activity/jdOilActivity/index');
		$this->display();
	}


}
