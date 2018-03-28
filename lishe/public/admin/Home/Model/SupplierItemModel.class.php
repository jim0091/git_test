<?php

namespace Home\Model;
use Think\Model;

/**
 * 供应商商品model
 * @author Gaolong
 */
class SupplierItemModel extends Model{
	
	/**
	 * 获取采购计划商品列表
	 */
	public function getSupplierItem4Plan($page, $pageCount, $where=''){
		
		//查询item表
		$where['send_type'] = array('in','1,3');//1.自发 2.代发 3.顺丰
		$where['is_reviewed'] = 1; //是能检索审核通过的商品
		
		$itemList = $this->field('sitem_id, supplier_id, cat_id, brand_id, item_id, title, status')
							->where($where)
							->order("sitem_id desc")
							->page($page.",$pageCount")
							->select();
		//print_r($this->getLastSql());
		$count = $this->where($where)->count('sitem_id');
							
		//查询类型和品牌
		$categoryIdArr = array();
		$brandIdsArr = array();
		$sitemIdsArr = array();
		$itemIdsArr = array();
		$supplierIdsArr = array();
		
		foreach ($itemList as $item){
			$sitemIdsArr[]  =  $item['sitem_id'];
			$itemIdsArr[] = $item['item_id'];
			$categoryIdArr[] = $item['cat_id'];
			$brandIdsArr[] = $item['brand_id'];
			$supplierIdsArr[] = $item['supplier_id'];
		}
		
		$data = array();
		$data['count'] = $count;
		$data['itemList'] = &$itemList;
		$data['sitemIdArr'] = &$sitemIdsArr;
		$data['itemIdsArr'] = array_unique($itemIdsArr);
		$data['supplierIdsArr'] = array_unique($supplierIdsArr);
		$data['categoryIdArr'] = array_unique($categoryIdArr);
		$data['brandIdsArr'] = array_unique($brandIdsArr);
		
		unset($sitemIdsArr);
		unset($categoryIdArr);
		unset($brandIdsArr);
		
		return $data;
	}
	
}
