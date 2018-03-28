<?php   
/**
  +----------------------------------------------------------------------------------------
 *  OrderModel
  +----------------------------------------------------------------------------------------
 * @author   	章锐
 * @version  	$Id: MallconfigureModel.class.php v001 2016-10-31
 * @description 商城配置
  +-----------------------------------------------------------------------------------------
 */
namespace Home\Model;
use Think\Model;
class MallconfigureModel extends CommonModel{
	public function __construct(){
		$this->modelItem=M('sysitem_item');
		$this->modelWIndex=M('wshop_index');
		$this->modelWIndexItem=M('wshop_index_item');
		$this->modelIndexActivity=M('syspromotion_activity');
		$this->modelIndexActivityItem=M('syspromotion_activity_item');
		$this->modelShuffingFigure=M('mall_shuffling_figure');
		$this->modelShuffingFigureDeatil=M('mall_shuffling_figure_detail');
		$this->modelfulldiscount=M('syspromotion_fulldiscount');
		$this->modelfulldiscountCompany=M('syspromotion_fulldiscount_company');
		$this->modelfulldiscountItem=M('syspromotion_fulldiscount_item');
		$this->modelfulldiscountRole=M('syspromotion_fulldiscount_rule');
		$this->modelCompanyPrice=M('company_item_price');
		$this->modelAdGroup=M('ad_group');
		$this->modelAdPostion=M('ad_postion');
		$this->modelAdRelation=M('ad_relation');
		$this->modelAdArea=M('ad_area');
		$this->modelAdModule=M('ad_module');
		$this->modelAdTemplate=M('ad_template');
		$this->modelCompany=M('company_config');
		
	}	
	/*
	 * 取出赠送情景、ws首页配置
	 * */
	public function getIndexInfo($type){
		return $this->modelWIndex->where(array('type'=>$type,'is_delete'=>0))->order('order_sort desc')->select();
	}
	/*
	 * 取出单条赠送情景、ws首页配置
	 * */
	public function getThisIndexInfo($indexId){
		return $this->modelWIndex->where(array('index_id'=>$indexId))->find();
	}	
	/*
	 * 添加赠送情景、ws首页配置
	 * */
	public function addIndexInfo($data){
		return $this->modelWIndex->data($data)->add();
	} 
	/*
	 * 编辑赠送情景、ws首页配置
	 * */
	public function editIndexInfo($indexId,$data){
		return $this->modelWIndex->where(array('index_id'=>$indexId))->data($data)->save();
	} 
	/*
	 * 取出赠送情景、ws首页配置
	 * */
	public function getIndexItemInfo($indexId){
		return $this->modelWIndexItem->where(array('forkey_index_id'=>$indexId,'is_delete'=>0))->order('order_sort desc')->select();
	}	
	/*
	 * 添加赠送情景、ws首页配置
	 * */
	public function addIndexItemInfo($data){
		return $this->modelWIndexItem->data($data)->add();
	}			
	/*
	 * 编辑赠送情景、ws首页配置
	 * */
	public function saveIndexItemInfo($itemId,$data){
		return $this->modelWIndexItem->where(array('item_id'=>$itemId))->data($data)->save();
	}			
	/*
	 * 编辑指定条件赠送情景、ws首页配置
	 * */
	public function eqitIndexItemInfo($condition,$data){
		return $this->modelWIndexItem->where($condition)->data($data)->save();
	}			
	/*
	 * 取出、ws商品配置
	 * */
	public function getThisIndexItemInfo($itemId,$field){
		return $this->modelWIndexItem->where(array('item_id'=>$itemId))->field($data)->find();
	}		
	/*
	 * 取出首页抢购商品
	 * */
	public function getRushInfo(){
		$condition['activity_id']=15;
		$res=$this->modelIndexActivity->where($condition)->find();
		$res['items']=$this->modelIndexActivityItem->where($condition)->select();
		return $res;
	}
	/*
	 * 取出指定抢购
	 * */
	public function getActivityRush(){
		$condition['activity_id']=15;
		return $this->modelIndexActivity->where($condition)->find();
	}
	/*
	 * 修改指定抢购
	 * */
	public function saveActivityRush($data){
		$condition['activity_id']=15;
		return $this->modelIndexActivity->where($condition)->data($data)->save();
	}	
	/*
	 * 取出指定抢购商品
	 * */
	public function getActivityRushItem($id){
		$condition['id']=$id;
		return $this->modelIndexActivityItem->where($condition)->find();
		
	}
	/*
	 * 修改指定抢购商品
	 * */
	public function saveActivityRushItem($id,$data){
		$condition['id']=$id;
		return $this->modelIndexActivityItem->where($condition)->data($data)->save();
	}		
	/*
	 * 添加轮播图位置说明
	 * */
	public function addshuffling($data){
		return $this->modelShuffingFigure->data($data)->add();
	}
	/*
	 * 取出所有的轮播图位置说明
	 * */
	public function getAllshuffling(){
		return $this->modelShuffingFigure->select();
	}	
	/*
	 * 添加轮播图
	 * */
	public function addshufflingFigure($data){
		return $this->modelShuffingFigureDeatil->data($data)->add();
	}
	/*
	 * 编辑轮播图
	 * */
	public function saveshufflingFigure($figureId,$data,$field){
		return $this->modelShuffingFigureDeatil->where(array('figure_id'=>$figureId))->field($field)->data($data)->save();
	}
	/*
	 * 取出指定条件的轮播图
	 * */
	public function getshufflingFigure($condition){
		$condition['is_delete']=0;
		return $this->modelShuffingFigureDeatil->where($condition)->order('shuffling_id desc,order_sort desc')->select();
	}	
	/*
	 * 取出指定条件的轮播图位置说明
	 * */
	public function getThisshufflingFigure($condition){
		$condition['is_delete']=0;
		return $this->modelShuffingFigureDeatil->where($condition)->find();
	}	
/*
 * 促销规则表添加记录
 * */
	public function addFulldiscount($data){
		return $this->modelfulldiscount->add($data);
	}
/*
 * 修改促销规则表记录
 * */	
	public function modifyFulldiscount($condition,$data,$field){
		return $this->modelfulldiscount->where($condition)->field($field)->save($data);
	}
/*
 * 查找促销规则表记录数量
 * */	
	public function getCountFulldiscount($condition){
		return $this->modelfulldiscount->where($condition)->field('fulldiscount_id')->count();
	}		
/*
 * 查找促销规则表记录
 * */	
	public function getAllFulldiscount($condition,$limit,$field){
		return $this->modelfulldiscount->where($condition)->field($field)->order('created_time desc')->limit($limit)->select();
	}	
/*
 * 查找单条促销规则表记录
 * */	
	public function getThisFulldiscount($condition,$field){
		return $this->modelfulldiscount->where($condition)->field($field)->find();
	}		
/*
 * 促销公司表添加记录
 * */
	public function addFulldiscountCom($data){
		return $this->modelfulldiscountCompany->add($data);
	}
/*
 * 修改公司规则表记录
 * */	
	public function modifyFulldiscountCom($condition,$data,$field){
		return $this->modelfulldiscountCompany->where($condition)->field($field)->save($data);
	} 
/*
 * 查找公司规则表记录
 * */	
	public function getFieldFulldiscountCom($condition,$field){
		return $this->modelfulldiscountCompany->where($condition)->getField($field,TRUE);
	} 	
	public function getSomeFulldiscountCom($condition,$field){
		return $this->modelfulldiscountCompany->where($condition)->field($field)->select();
	}	
	public function getThisFulldiscountCom($condition,$field){
		return $this->modelfulldiscountCompany->where($condition)->getField($field,TRUE);
	}		
/*
 * 删除公司规则表记录
 * */	
	public function delFulldiscountCom($condition){
		return $this->modelfulldiscountCompany->where($condition)->delete();
	} 	
/*
 * 促销规则表添加记录
 * */
	public function addFulldiscountRole($data){
		return $this->modelfulldiscountRole->add($data);
	}
/*
 * 修改促销规则表记录
 * */	
	public function modifyFulldiscountRole($condition,$data,$field){
		return $this->modelfulldiscountRole->where($condition)->field($field)->save($data);
	} 
/*
 * 查找促销规则表记录
 * */	
	public function getFulldiscountRole($condition,$field){
		return $this->modelfulldiscountRole->where($condition)->field($field)->select();
	} 	
/*
 * 查找促销规则表记录
 * */	
	public function getFieldFulldiscountRole($condition,$field){
		return $this->modelfulldiscountRole->where($condition)->getField($field,TRUE);
	} 	
/*
 * 删除促销规则表记录
 * */	
	public function delFulldiscountRole($condition){
		return $this->modelfulldiscountRole->where($condition)->delete();
	} 		
/*
 * 促销商品表添加记录
 * */
	public function addFulldiscountItem($data){
		return $this->modelfulldiscountItem->add($data);
	}
/*
 * 修改商品规则表记录
 * */	
	public function modifyFulldiscountItem($condition,$data,$field){
		return $this->modelfulldiscountItem->where($condition)->field($field)->save($data);
	}  
/*
 * 删除商品规则表记录
 * */	
	public function delFulldiscountItem($condition){
		return $this->modelfulldiscountItem->where($condition)->delete();
	} 
/*
 * 查找商品规则表记录
 * */	
	public function getFieldFulldiscountItem($condition,$field){
		return $this->modelfulldiscountItem->where($condition)->getField($field,TRUE);
	} 	
	public function getSomeFulldiscountItem($condition,$field){
		return $this->modelfulldiscountItem->where($condition)->field($field)->select();
	} 			 
	/*
	 * 取出商品信息
	 * */
	public function getItemsInfo($condition,$field){
		return $this->modelItem->where($condition)->field($field)->select();
	}	
	/*
	 * 特价商品个数
	 * */
	public function getComapnyItemsCount($condition){
		$condition['is_delete']=0;
		return $this->modelCompanyPrice->where($condition)->field('item_price_id')->count();
	}		 
	/*
	 * 取出特价商品
	 * */
	public function getComapnyItems($condition,$limit,$field){
		$condition['is_delete']=0;
		return $this->modelCompanyPrice->where($condition)->field($field)->order('add_time desc')->limit($limit)->select();
	}
	/*
	 * 取出指定特价商品
	 * */
	public function getThisComapnyItems($condition,$field){
		$condition['is_delete']=0;
		return $this->modelCompanyPrice->where($condition)->field($field)->find();
	}	
/*
 * 添加特价商品
 * */
	public function addCompanyItem($data){
		return $this->modelCompanyPrice->add($data);
	}
/*
 * 修改特价商品
 * */	
	public function modifyCompanyItem($condition,$data,$field){
		return $this->modelCompanyPrice->where($condition)->field($field)->save($data);
	}
/*
 * 添加广告组
 * */
	public function addAdGroup($data){
		return $this->modelAdGroup->data($data)->add();
	}
/*
 * 更新广告组
 * */
 	public function updateAdGroup($where,$data){
		return $this->modelAdGroup->where($where)->data($data)->save();
 	}
/*
 * 更新广告位
 * */
	public function updateAdPostion($where,$data){
		return $this->modelAdPostion->where($where)->data($data)->save();
	}
	
/*
 * 取出广告组单条数据
 * */
	public function getAdGroup($where,$field,$isGetField){
		if($isGetField==1){
			if(strpos($field,',')){
				return FALSE;
			}
			return $this->modelAdGroup->where($where)->getField($field);
		}else{
			return $this->modelAdGroup->where($where)->field($field)->find();
		}
	}
/*
 * 取出指定条件广告组所有数据
 * */
	public function selectAdGroup($where,$field,$limit){
		return $this->modelAdGroup->where($where)->field($field)->limit($limit)->order('group_id desc')->select();
	}
/*
 * 添加广告位
 * */
	public function addAdPostion($data){
		if(empty($data)){
			return FALSE;
		}
		return $this->modelAdPostion->data($data)->add();
	}
/*
 * 取出广告位数
 * */
	public function getAdPostionCount($where){
		$where['is_delete']=0;
		return $this->modelAdPostion->where($where)->field('position_id')->count();
	}
/*
 * 取出广告组数量
 * */	
 	public function getAdGroupCount($where){
		$where['is_delete']=0;
		return $this->modelAdGroup->field('group_id')->count();
	}
/*
 * 取出广告位内容
 * */	
	 public function getAllAdPostion($where,$limit,$field="*"){
			$where['is_delete']=0;
			return $this->modelAdPostion->where($where)->field($field)->limit($limit)->order('position_id desc')->select();
	 }
/*
 * 取出单条广告位内容
 * */
	public function getThisAdPostion($where,$field="*"){
			$where['is_delete']=0;
			return $this->modelAdPostion->where($where)->field($field)->find();	
	}
	 
/*
 * 取出广告组
 * */
	 public function getAllAdGroup($where,$limit,$field="*"){
			$where['is_delete']=0;
			return $this->modelAdGroup->where($where)->field($field)->limit($limit)->order('group_id desc')->select();
	 }	
	 
/*
 * 取出所有广告位
 * */		
		public function getAdCondPostion($where,$field="*",$order='position_id desc'){
			$where['is_delete']=0;
			return $this->modelAdPostion->where($where)->field($field)->order($order)->select();
		}
/*
 * 删除广告位
 * */		
		public function delADPostion($postionId){
			$where['position_id']=$postionId;
			return $this->modelAdPostion->where($where)->delete();
		}
/*
 * 取出所有广告区
 * */		
		public function getAllAdArea($where){
			$where['is_delete']=0;
			return $this->modelAdArea->where($where)->select();			
		}
/*
 * 取出广告区的单条数据
 * */		
 		public function getThisAdArea($where){
 			$where['is_delete']=0;
			return $this->modelAdArea->where($where)->find();					
 		}
/*
 * 取出指定广告模块
 * */		
		public function getAllmodule($where){
			$where['is_delete']=0;
			return $this->modelAdModule->where($where)->select();				
		}
/*
 * 取出单条广告模块
 * */		
 		public function getThismodule($where){
			$where['is_delete']=0;
			return $this->modelAdModule->where($where)->find();				
		}
/*
 * 取出指定广告模板
 * */		
		public function getAlltemp($where){
			$where['is_delete']=0;
			return $this->modelAdTemplate->where($where)->select();					
		}
/*
 * 取出单条广告模板
 * */		
 		public function getThistemp($where){
			$where['is_delete']=0;
			return $this->modelAdTemplate->where($where)->find();				
		}		
	//所有公司
	public function getCompanys($condition,$field='com_id,com_name'){
		$condition['is_delete']=0;
		return $this->modelCompany->where($condition)->field($field)->select();
	}	
/*
 * 取出广告组广告位关联
 * */
	public function getGroupWithPos($condition){
		return	$this->modelAdRelation->where($condition)->select();
	}
/*
 * 取出广告组关联的广告位
 * */
	public function getGroupFieldPos($condition,$field='position_id'){
		return	$this->modelAdRelation->where($condition)->getField($field,TRUE);
	}	
/*
 * 广告组广告位关联添加多条记录
 * */	
	public function addAllAdRelation($data){
		return	$this->modelAdRelation->addAll($data);
	}
/*
 * 删除广告位关联
 * */	
 	public function delAdrelation($where){
		return	$this->modelAdRelation->where($where)->delete();
 	}
		
}  
?>  	