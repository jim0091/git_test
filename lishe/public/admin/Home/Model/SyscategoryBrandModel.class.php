<?php

namespace Home\Model;
use Think\Model;

/**
 * 品牌类别 
 * @author Gaolong
 */
class SyscategoryBrandModel extends Model{
	
	/**
	 * 根据品牌id数组获取品牌
	 * @param array $brandIdsArr
	 */
	public function getBrandByIds($brandIds, $field='*'){
		
		$result =array();
		if(empty($brandIds)){
			return $result;
		}
		
		if(is_array($brandIds)){
			$brandList = $this->table('syscategory_brand')
							->field($field)
							->where(array('brand_id'=>array('in',$brandIds)))
							->select();
			
			foreach ($brandList as $val){
				$result[$val['brand_id']] = $val['brand_name'];
			}
		}else if(is_string($brandIds)){
			$result = $this->table('syscategory_brand')
						->field($field)
						->where("brand_id=$brandIds")
						->find();
		}
		
		return $result;
	}
	
	/**
	 * 更具商品类别获取商品品牌
	 * @param number $catId 分类id
	 * @param number $field 需要查询的字段
	 * @param number $catLevel 分类级别
	 * @return mix
	 * @author Gaolong
	 */
	public function getBrandsByCatId($catId, $catLevel){
		if(empty($catId) || !is_numeric($catId)){
			return false;
		}
		
		$sql = "";
		
		/////////////////////以下查询，需要优化
		
		if($catLevel == 1){
			$sql = "SELECT
						b.`brand_id`,
						b.`brand_name` 
					FROM `syscategory_cat` AS c1
					LEFT JOIN `syscategory_cat` AS c2 ON c1.`cat_id`=c2.`parent_id`
					LEFT JOIN `syscategory_cat` AS c3 ON c2.`cat_id`=c3.`parent_id`
					LEFT JOIN `syscategory_cat_rel_brand` AS r ON c3.`cat_id`=r.`cat_id`
					LEFT JOIN `syscategory_brand` AS b ON r.`brand_id`=b.`brand_id`
					WHERE c1.`cat_id`=$catId AND b.`brand_id` IS NOT NULL";
		}else if($catLevel == 2){
			#TODO
		}else if($catLevel == 3){
			#TODO
		}
		if(empty($sql)){
			return false;
		}
		$result = $this->query($sql);
		
		$data = array();
		foreach ($result as $brand){
			$data[$brand['brand_id']] = $brand['brand_name'];
		}
		//这里没有排序
		return $data;
	}
}
