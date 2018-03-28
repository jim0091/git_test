<?php 


namespace Home\Controller;

class ShopController extends CommonController{
	
	private $sysshopShopCat;
	private $sysitemItem;
	
	public function __construct(){
		parent::__construct();
		$this->modelGoodsCat=D('Goodscat');
		$this->sysitemItem = M('sysitem_item');
		$this->sysshopShopCat = M('sysshop_shop_cat');
	}
	
	//空操作
	public function _empty($name){
		//$this->redirect(U(''))
		exit();
	}
	
	//店铺商品列表
	public function showList(){
		$shopId = I('get.shopId', -1, 'intval');
		$shopCat = I('get.shopCat', -1, 'intval');
		$shopCatChild = I('get.shopCatChild', -1, 'intval');
		
		$pagesize = 30;
		if(!is_numeric($shopId) || $shopId < 1){
			exit();
		}
		//1.查询店铺名称 sysshop_shop
		$where = array();
		$where['shop_id'] = $shopId;
		$shop = M('sysshop_shop')->where($where)->find();
		
		//2.查询主分类  sysshop_shop_cat
		$where = array();
		$where['shop_id'] = $shopId;
		$where['disabled'] = 0;
		$shopCatList = $this->sysshopShopCat->where($where)->order('order_sort')->select();
		$shopCatArr1 = array();
		$shopCatArr2 = array();
		foreach ($shopCatList as $cat){
			if($cat['level'] == 1){
				$shopCatArr1[] = $cat;
			}else if($cat['level'] == 2){
				$shopCatArr2[$cat['parent_id']][] = $cat;
			}
		}
		
		$this->assign('shopId', $shopId);
		$this->assign('shop', $shop);
		$this->assign('shopCat', $shopCat);
		$this->assign('shopCatArr1', $shopCatArr1);
		$this->assign('shopCatArr2', $shopCatArr2);
		$this->assign('shopCat', $shopCat);
		$this->assign('shopCatChild', $shopCatChild);
		$this->display('showList');
	}
	
	//加载商品
	public function itemList(){
		$shopId = I('get.shopId',-1,'intval');
		$shopCat = I('get.shopCat',-1,'intval');
		$shopCatChild = I('get.shopCatChild', -1, 'intval');
		$page = I('get.p', 1, 'intval');
		$pagesize = 30;
		//shopId
		if(!is_numeric($shopId) || $shopId < 1){
			exit();
		}
		//shopId
		if(!is_numeric($shopCat) || $shopCat < -1){
			exit();
		}
		//当前页
		if(!is_numeric($page) || $page < 0){
			exit();
		}
		//3.查询商品 sysitem_item ，sysitem_item_status b approve_status="onsale"
		$where = array();
		$where['shop_id'] = $shopId;
		$where['approve_status'] = 'onsale';
		$itemIdList = M('sysitem_item_status')->field('item_id')->where($where)->select();
		$itemIdArr = array();
		foreach ($itemIdList as $item){
			$itemIdArr[] = $item['item_id'];
		}
		if(empty($itemIdArr)){
			exit();
		}
		$where = array();
		$where['item_id'] = array('in', $itemIdArr);
		$where['shop_id'] = $shopId;
		$where['is_offline'] = 0;
		$where['disabled'] = 0;
		//产品利润率
		if($this->uid && $this->comId){
			$profitRate = $this->modelGoodsCat->getCompanyConf('com_id='.$this->comId);
			if ($profitRate['profit_rate']) {
				$where['(price - cost_price)/price']=array('exp','>='.$profitRate['profit_rate']/100);	
			}
		}else{
			$profitRate = C("PROFIT_RATE");
			if (!empty($profitRate)) {
				$where['(price - cost_price)/price']=array('exp','>='.$profitRate);	
			}
		}
		
		if($shopCat != -1){
			if(is_numeric($shopCatChild) && $shopCatChild > 0){
				$where['shop_cat_id'] = array('in',array("{$shopCatChild}",",{$shopCatChild},"));
			}else if($shopCatChild == -1){
				//以下代码有严重的性能问题，如果你找到更好的方法，请及时改正
				//2016-12-17 18:07
				$shopCatChildList = $this->sysshopShopCat
										->field('cat_id')
										->where(array('parent_id'=>$shopCat,'level'=>2))
										->order('order_sort')
										->select();
				$shopCatChildList[] = array('cat_id'=>$shopCat);
				$catChildArr = array();
				foreach ($shopCatChildList as $cat){
					$catChildArr[] = $cat['cat_id'].'';
					$catChildArr[] = ",{$cat['cat_id']},";
				}
				if(!empty($catChildArr)){
					$where['shop_cat_id'] = array('in',$catChildArr);
				}else{
					echo '<div style="height:350px;text-align:center;"><p style="margin-top: 20px;">没有找到你需要的商品信息</p></div>';
					exit();
				}
			}
		}
		
		$itemList = $this->sysitemItem->field('item_id,title,image_default_id,price')->where($where)->page($page,$pagesize)->select();
		if(empty($itemList)){
			echo '<div style="height:350px;text-align:center;"><p style="margin-top: 20px;">没有找到你需要的商品信息</p></div>';
			exit();
		}
		$itemCount = $this->sysitemItem->where($where)->count('item_id');
		$totalPages = $itemCount / $pagesize;
		
		$page = new \Think\Page($itemCount, $pagesize);
		$page -> setConfig('first' ,'首页');
		$page->lastSuffix = false;
		$page -> setConfig('last' ,'尾页');
		$page -> setConfig('prev' ,'上一页');
		$page -> setConfig('next' ,'下一页');
		$style = "badge";
		$onclass = "pageon";
		$pagestr = $page -> show($style, $onclass);  //组装分页字符串
		
		$this->assign('pagestr',$pagestr);
		$this->assign('totalPages',ceil($totalPages));
		$this->assign('itemList', $itemList);
		$this->display('itemList');
	}
}
?>
