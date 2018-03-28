<?php
/**
 +------------------------------------------------------------------------------
 * GiftController
 +------------------------------------------------------------------------------
 * @author   	高龙 <goolog@126.com>
 * @version  	$Id: GiftController.class.php v001 2016-11-01
 * @description 微商城控制器
 +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;

class GiftController extends Controller {
   
	public function __construct(){
		parent::__construct();
		$this->wshopIndex = M('wshop_index');
		$this->wshopIndexItem = D('WshopIndexItem');
		$this->itemStatusModel = M('sysitem_item_status');//商品状态表
		$this->skuStoreModel=M('sysitem_sku_store');//货品的库存
		$this->modelItemStore = M('sysitem_item_store');//商品库存
		$this->sysitemItem = M('sysitem_item');
		$this->sysitemSku = M('sysitem_sku');
		$this->giftSearchTag = M('gift_search_tag');
	}
	/**
	 * 进入列表页
	 */
	public function enter(){
		//加载分类数据
		$wcatId  = I('get.wcat_id');//设置默认值 6
		$sprice  = I('get.sprice', 0, 'intval');
		$eprice  = I('get.eprice', 0, 'intval');
		cookie('trace_catid', $wcatId);
		
		$catList = $this->getAllCat();
		if(empty($wcatId)){
			$wcatId = $catList[0]['index_id'];
		}
		$this->assign("wcatId", $wcatId);
		$this->assign("sprice", $sprice);
		$this->assign("eprice", $eprice);
		$this->assign('catList', $catList);
		$this->assign('catCount', count($catList));
		//$this->assign('itemList', $itemList);
		$this->display('index');
	}
	
	//获取分类，并缓存3600秒
	public function getAllCat(){
		$catList = S('catList');
		if(empty($catList)){
			$catList = $this->wshopIndex
					->field('index_id,name,gift_pic')
					->where('is_delete=0 AND type=2')
					->order('order_sort DESC')
					->select();
			S('catList',$catList, 3600);
		}
		return $catList;
	}
	
	//搜索
	public function search(){
		$keyword = I('get.keyword', '', 'trim,strip_tags,stripslashes');
		if(empty($keyword)){
			$key = I('get.key', '', 'trim,strip_tags,stripslashes');
			$tagList = $this->giftSearchTag
							->where('status=1')
							->order('sort DESC')
							->select();
			$this->assign('tagList', $tagList);
			//$this->assign('searchHistory', $this->searchHistory());
			$this->assign('key',$key);
			$this->display('search');
		}else{
			//限制一下keyword长度
			$keyword = mb_strcut($keyword, 0, 12, 'utf8');
			$this->searchHistory($keyword);
			//检索数据库
			$this->assign('keyword', $keyword);
			$this->display('searchResult');
		}
	}
	
	//搜索历史处理
	private function searchHistory($keyword = ''){
		$searchHistrory = cookie('search_histrory');
		$searchHistrory = empty($searchHistrory) ? array() : json_decode($searchHistrory, true);
		if(!empty($keyword)){
			if(count($searchHistrory) > 9){
				array_pop($searchHistrory);
			}
			array_unshift($searchHistrory, $keyword);
			$searchHistrory = array_unique($searchHistrory);
			cookie('search_histrory', json_encode($searchHistrory));
		}
		return $searchHistrory;
	}
	
	/**
	 * 获取搜索历史
	 */
	public function pullSearchHis(){
		$searchHistrory = $this->searchHistory();
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		if(empty($searchHistrory)){
			$ret['msg'] = 'empty';
		}else{
			$ret['code'] = 1;
			$ret['msg'] = 'success';
			$ret['data'] = $searchHistrory;
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 删除所有历史
	 */
	public function delAllSearchHis(){
		cookie('search_histrory', null);
	}
	
	/**
	 * 删除某个搜索历史
	 */
	public function delSearchItem(){
		$keyword = I('post.kw');
		$searchHistrory = cookie('search_histrory');
		$searchHistrory = empty($searchHistrory) ? array() : json_decode($searchHistrory, true);
		if(!empty($searchHistrory)){
			foreach( $searchHistrory as $k=>$v) {
				if($keyword == $v) unset($searchHistrory[$k]);
			}
			cookie('search_histrory', json_encode($searchHistrory));
		}
	}
	
	
	/**
	 * 拉取数据
	 */
	public function pullArr(){
		if(!IS_AJAX) exit();
		$page = I('get.page',1,'intval'); //分页，当前第几页
		$listRow = 6; //分页，每页数据
		$wcatId = I('get.wcatId',-1,'intval');
		$sprice = I('get.sprice',-1,'intval');
		$eprice = I('get.eprice',-1,'intval');
		$keyword = I('get.keyword', '', 'trim,strip_tags,stripslashes');
		
		
		cookie('trace_catid', $wcatId);
		$ret = array('code'=>-1, 'mgs'=>'unkown error', 'data'=>array(),'page'=>$page);
		//加载商品数据
		$itemList = array();
		//判断参数
		$where = array();
		if(!is_numeric($page) && $page < 1){
			$ret['msg']= 'invalid page';
			$this->ajaxReturn($ret);
		}
		if(is_numeric($wcatId) && $wcatId > 1){
			$where['forkey_index_id'] = $wcatId;
		}else{
			$catList = $this->getAllCat();
			$catArr = array();
			foreach ($catList as $cat){
				$catArr[] = $cat['index_id'];
			}
			if(!empty($catArr)){
				$where['forkey_index_id'] = array('in', $catArr);
			}
		}
		if(is_numeric($sprice) && is_numeric($eprice) 
				&& $eprice > $sprice && $sprice >= 0){
			$where['price'] =array(array('egt',$sprice),array('elt',$eprice));
		}
		if(!empty($keyword)){
			$where['title'] = array('like',"%{$keyword}%");
		}
		
		if(empty($where)){
			$ret['msg']= 'invalid search';
			$this->ajaxReturn($ret);
		}
		//加载数据
		$where['is_delete'] = 0;
		$itemList = $this->wshopIndexItem
							->field('title,price,forkey_item_id,img_default_id')
							->where($where)
							->page($page, $listRow)
							->select();
		//返回数据
		if(!empty($itemList)){
			$ret['code'] = 1;
			$ret['mgs'] = 'success';
			$ret['data'] = $itemList;
		}else{
			$ret['mgs'] = 'empty data';
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 商品详情
	 */
	public function item(){
		header("Content-type:text/html;charset=utf-8");
		$itemId = I('get.item_id', -1, 'intval');
		if(!is_numeric($itemId) && $itemId < 1){
			exit();
		}
		$where['item_id'] = $itemId;
		$where['is_offline'] = 0;
		//查询商品
		$item = $this->sysitemItem->where($where)->find();
		if(empty($item)){
			echo "<script type='text/javascript'>alert('商品不存在！');history.go(-1);</script>";
			exit();
		}
		unset($where);
		//判断商品状态
		$approveStatus = $this->itemStatusModel->where("item_id=$itemId")->getField('approve_status');
		if($approveStatus != 'onsale'){
			echo "<script type='text/javascript'>alert('商品已下架，请重新选择！');history.go(-1);</script>";
			exit();
		}
		//商品库存
		$itemStore = $this->modelItemStore->where("item_id=$itemId")->getField('store'); //库存
		if($itemStore < 1){
			echo "<script type='text/javascript'>alert('商品已售罄，请重新选择！');history.go(-1);</script>";
		}
		//查询商品参数
		$itmeSpecArr = $this->itemSpec($itemId, $item['spec_desc']);
		
		//查询商品sku
		$where = array();
		$where['item_id'] = $itemId;
		$where['status'] = 'normal';
		$skuList = $this->sysitemSku->field('sku_id,spec_info')->where("item_id=$itemId")->select();
		
		$this->assign('itmeSpecArr',$itmeSpecArr);
		$this->assign('item',$item);
		$this->assign('skuList',$skuList);
		$this->display('item');
	}
	
	/**
	 * 商品介绍
	 */
	public function itemDesc(){
		if(!IS_AJAX) exit();
		$itemId = I('get.item_id', -1, 'intval');
		$ret = array('code'=>-1, 'msg'=>'unkown error', 'data'=>'');
		if(!is_numeric($itemId) && $itemId < 1){
			echo 'error';
			exit();
		}
		$where['item_id'] = $itemId;
		$info = M('sysitem_item_desc')->where($where)->getField('wap_desc');
		if(!empty($info)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
			$ret['data'] = stripslashes($info);
		}else{
			$ret['msg'] = 'empty';
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 商品属性
	 * @author Gaolong
	 */
	private function itemSpec($itmeId, $itemSpecField){
		$specArr = array();
		if(empty($itemSpecField)){
			return $specArr;
		}
		$specList = unserialize($itemSpecField);
		//$specList = array_values($specList);
		$specValArr = array();
		
		$propSpec = array();
		foreach ($specList as $propId => $tmp){
			$propArr[$propId] = $propId;
			foreach ($tmp as $val){
				$specValArr[$propId][] = $val['spec_value'];
			}
		}
	
		$where['prop_id'] = array('in', $propArr);
		$propList = M('syscategory_props')
						->field('prop_id, prop_name')
						->where($where)
						->select();
		$propArr = array();
		foreach ($propList as $prop){
			$propArr[$prop['prop_id']] = $prop['prop_name'];
		}
		unset($propList);
		foreach ($specValArr as $propId => $specArr){
			$propName = $propArr[$propId];
			$propSpec[$propName] = '';
			foreach ($specArr as $specName){
				$propSpec[$propName] .= "{$specName}、";
			}
			if(!empty($propSpec[$propName])){
				$propSpec[$propName] = rtrim($propSpec[$propName],'、');
			}
		}
		return $propSpec;
	}
	//推荐
	public function recom(){
		$catid = cookie('trace_catid');
		$ret = array('code'=>'-1', 'msg'=>'unkown error', 'data'=>array());
		$itemList = array();
		if(is_numeric($catid)){
			$where['forkey_index_id'] = $catid;
			$where['is_delete'] = 0;
			$itemList = $this->wshopIndexItem
				->field('title,price,forkey_item_id,img_default_id')
				->where($where)
				->order('forkey_item_id DESC')
				->limit(6)
				->select();
		}
		$ret['code'] = 1;
		$ret['msg'] = 'success'.$catid;
		$ret['data'] = $itemList;
		$this->ajaxReturn($ret);
	}
}