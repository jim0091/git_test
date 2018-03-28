<?php
/**
  +----------------------------------------------------------------------------------------
 *  CategoryModel
  +----------------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: CategoryModel.class.php v001 2016-02-01
 * @description 产品分类操作
  +-----------------------------------------------------------------------------------------
 */
	namespace Home\Model;
	use Think\Model;
	
    class CategoryModel extends CommonModel{
    	public function __construct(){
    		$this->modelCategory=M('syscategory_cat');
    	}
        //获取父级分类或某个父级分类下的子类
        public function getSubCategory($parentId=0){
        	$condition['parent_id']=$parentId;
        	$condition['disabled']=0;
        	return $this->modelCategory->where($condition)->select();
        }
			 public function getCateinfo($condition,$field){
        	$condition['disabled']=0;
			 		$res= $this->modelCategory->where($condition)->field($field)->select();
					return $res;
			 }
			 //取出指定分类信息
			 public function getThisAllcat($level){
			 		$condition['disabled']=0;
			 		$condition['level']=$level;
			 		return  $this->modelCategory->where($condition)->order('order_sort DESC')->select();
			 }
			 //获取分类信息一级
			 public function getCatsInfo(){
        	$condition['disabled']=0;
					$condition['level'] = 1;
					$field="cat_id,cat_name";
			 		$res= $this->modelCategory->where($condition)->field($field)->select();
					return $res;			 	
			 }
			 //获取二级三级分类
			 public function getCatTnfos($parentId,$level){
        	$condition['disabled']=0;
					$condition['level'] = $level;
					$condition['parent_id']=$parentId;
					$field="cat_id,cat_name";
			 		$res= $this->modelCategory->where($condition)->field($field)->select();
					return $res;				 	
			 }
			 //获取分类路径
			 public function getcatPath(){
        	$condition['disabled']=0;
					$field="cat_id,cat_path";
			 		$res= $this->modelCategory->where($condition)->field($field)->select();
					return $res;				 	
			 }
			public function getCatName($catId){
				$condition['cat_id']=$catId;
				$res=$this->modelCategory->where($condition)->field('cat_name')->find();
				return $res;			
			}
			//取出所有分类
			public function getAllCategory(){
					$first=$this->modelCategory->where('level=1')->order('order_sort desc')->field('cat_id,parent_id,cat_name,disabled')->select();
					$Second=$this->modelCategory->where('level=2')->order('order_sort desc')->field('cat_id,parent_id,cat_name,disabled')->select();
					$Third=$this->modelCategory->where('level=3')->order('order_sort desc')->field('cat_id,parent_id,cat_name,disabled')->select();
					foreach($Second as $key=>$value){
						foreach($Third as $keys=>$values){
							if($values['parent_id']==$value['cat_id']){
								$Second[$key]['NextCat'][]=$values;
							}
						}
					}				
					foreach($first as $key=>$value){
						foreach($Second as $keys=>$values){
							if($values['parent_id']==$value['cat_id']){
								$first[$key]['NextCat'][]=$values;
							}
						}
					}	
					return $first;			
			}
			//启用/禁用 分类
			public function dealThisCategory($catId,$disabled){
				return $this->modelCategory->where('cat_id='.$catId)->setField('disabled',$disabled);
			}
			//取出订单分类信息
			public function getThisCategory($catId){
				return $this->modelCategory->where('cat_id='.$catId)->field('cat_id,cat_name,cat_logo,order_sort')->find();
				
			}			
			//修改分类信息
			public function editThisCategory($catId,$data,$field='cat_name,cat_logo,modified_time,order_sort'){
				return $this->modelCategory->where('cat_id='.$catId)->field($field)->data($data)->save();
			}
			//添加新分类
			public function addCategory($data){
				return $this->modelCategory->where('cat_id='.$catId)->data($data)->add();
				
			}
			
    }
?>