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
		$this->comActivity=M('company_activity');
        $this->modelShufflingDetail = M('mall_shuffling_figure_detail');
        $this->modelShuFigure = M("mall_shuffling_figure");
        $this->modelActivCategory = M('company_activity_category');
        $this->modelThemesFile = M('site_themes_file');//文件模板表
	}
	/*
	 * 商品分类列表
	 * */
	public function classifyList(){
		$catId = I("post.catId",0,"trim");
		$res=$this->modelGoodsCat->getCatList($catId);
		$this->assign('list',$res[0]);
		$this->assign('subCat',$res[1]);	
		$this->assign('catId',$res[2]);	
		if(!empty($catId)){
			$this->display('classifyListAjax');
		}else{
			$this->display();
		}				
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
		$keyword = $_GET['keyword']=urldecode(trim(I('get.keyword')));
		$brand = trim(I('get.brand'));
		$minPrice = trim(I('get.minPrice'));
		$maxPrice = trim(I('get.maxPrice'));
		$catId = trim(I('get.catId'));

		if ($keyword) {
			$condition['a.title'] = array('LIKE','%'.$keyword.'%');
		}
		if ($catId) {
			$condition['a.cat_id'] = $catId;
		}
		if ($brand) {
			$condition['a.brand_id'] = $brand;
		}
		if ($minPrice && $maxPrice) {
			$condition['a.price'] = array('between',array($minPrice,$maxPrice));
		}else{
			if ($minPrice) {
				$condition['a.price'] = array('egt',$minPrice);
			}
			if ($maxPrice) {
				$condition['a.price'] = array('elt',$minPrice);
			}
		}
		//产品利润率
		if($this->uid && $this->comId){			
            if ($this->comId == '-1') {
            	$profitRate = C("PROFIT_RATE");
				if (!empty($profitRate)) {
					$condition['(a.price - a.cost_price)/a.price']=array('exp','>= '.$profitRate);
				}
            }else{
				$profitRate = $this->modelGoodsCat->getCompanyConf('com_id='.$this->comId);
				if ($profitRate['profit_rate']) {
					$condition['(a.price - a.cost_price)/a.price']=array('exp','>= '.$profitRate['profit_rate']/100);
				}
			}
		}else{
			$profitRate = C("PROFIT_RATE");
			if (!empty($profitRate)) {
				$condition['(a.price - a.cost_price)/a.price']=array('exp','>= '.$profitRate);
			}
		}			
		$this->assign('minPrice',$minPrice);
		$this->assign('maxPrice',$maxPrice);
		$this->assign('keyword',$keyword);
		$this->assign('catId',$catId);
		$this->assign('brand',$brand);
		return $condition;
	}
	/*
	 * 搜索结果列表
	 * */		
	public function searchList(){
		$condition = $this->condition();		
		$size = 18;
		$number=$this->modelGoodsCat->getSearchCount($condition);
		$page = new \Think\PagePn($number, $size);
		$rollPage = 1;
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$start = $page -> firstRow;  //起始行数
		$pagesize = $page -> listRows;   //每页显示的行数
		$limit = "$start , $pagesize";	
		$style = "custom-paginations-prev";	
		$pagestr = $page -> show($style,$onclass);  //组装分页字符串
		$res=$this->modelGoodsCat->getSearchRes($condition,$limit);
		$brandList = $this->modelGoodsCat->getBrandList($condition);
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$res);	
		$this->assign('brandList',$brandList);
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
	/*
	 * 礼舍推荐页
	 * */
	public function itemRecommend(){
		//头部轮播图
        $shuFigureId = $this->modelShuFigure->where('identify = "tjindex"')->getField('shuffling_id');
        if (empty($shuFigureId)) {
            $shuFigureId = 1;
        }
        $shuDetailList = $this->modelShufflingDetail->where('shuffling_id='.$shuFigureId.' and status = 1 and is_delete = 0')->order("order_sort desc")->select();
        
		$activity=$this->activity(18);
		$first=array(
			'activity' => current($activity['activity']),
			'list'     => current($activity['list'])
		);
		$this->assign('activity',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->assign('first',$first);		
		$this->assign('shuDetailList',$shuDetailList);
		$this->display();
	}
	public function activity($aid){		
		if(empty($aid)){
			$aid=1;
		}
		$activityConfig=$this->modelActivCategory->where('activity_id='.$aid)->field('profit_rate,activity_config_id,recommend,item_ids,cat_content,cat_banner,cat_name,more_link')->order('order_sort DESC')->select();
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
	//专题
	public function project(){
		$projectId = I('get.projectId');
		if (empty($projectId)) {
			$this->error( "该专题不存在！");
		}
		$comActivInfo = $this->comActivity->where('aid='.$projectId)->field('theme_id')->find();
		$themeInfo = $this->modelThemesFile->where('id='.$comActivInfo['theme_id'])->field('filename')->find();
		if (empty($themeInfo['filename'])) {
			$this->error( "该专题不存在！");
		}
		$activity=$this->activity($projectId);	
		$this->assign('activity',$activity['activity']);
		$this->assign('list',$activity['list']);
		$this->display("Itemlist/themes/".$themeInfo['filename']);
	}
}
