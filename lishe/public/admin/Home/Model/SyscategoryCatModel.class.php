<?php

namespace Home\Model;
use Think\Model;

/**
 * 商品分类模型 
 * @author Gaolong
 */
class SyscategoryCatModel extends Model{
	
	/**
	 * 获取商品分类
	 * @param number $pid
	 * @return mix
	 */
	public function getCategory($parentId = 0){
		$where=array();
		if($pid>=1){
			$where['parent_id']=$pid;
		}else{
			$where['level']=1;
		}
		//这里没有排序
		return $this->field('cat_id, cat_name')->where($where)->select();
	}
	
	/**
	 * 跟据分类id数组获取分类
	 * @param array $categoryIdArr
	 */
	public function getCategoryByIds($categoryIds, $field='*'){
		$result = array();
		if(empty($categoryIds)){
			return $result;
		}
		if(is_array($categoryIds)){
			$catList = $this->table('syscategory_cat')
						->field($field)
						->where(array('cat_id'=>array('in',$categoryIds)))
						->select();
				
			foreach ($catList as $val){
				$result[$val['cat_id']] = $val['cat_name'];
			}
			
		}else if(is_string($categoryIds)){
			$result = $this->table('syscategory_cat')
						->field($field)
						->where("cat_id=$categoryIds")
						->find();
		}
		
		return $result;
	}
}
