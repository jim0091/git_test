<?php
/**
  +----------------------------------------------------------------------------------------
 *  AdminModel
  +----------------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: AdminrModel.class.php v001 2015-10-27
 * zhangrui 2016/9/20
 * @description 管理后台数据库操作管理员功能部分
  +-----------------------------------------------------------------------------------------
 */
	namespace Home\Model;
	use Think\Model;
class BannerModel extends CommonModel{
	public function __construct(){
		//$this->sysuserUser=M('sysuser_user');
		$this->mallShufflingFigure=M('mall_shuffling_figure');
		$this->mallShufflingFigureDetail=M('mall_shuffling_figure_detail');
		$this->Aindex=M('wshop_index');
		$this->recommendItem=M('app_recommend_item');
		$this->sysItem=M('sysitem_item');
		$this->AindexItem=M('wshop_index_item');
	}
	//获取APP的banner
	public function getAppBanner(){
		$where=array(
			'identify'=>'ios'
		);
		return $this->mallShufflingFigure->where($where)->getField('shuffling_id');
	}
	//获取banner列表
	public function getAppBannerList($id,$cate_id){
		if(empty($id)){
			return false;
		}
		$where=array(
			'shuffling_id'=>$id,
			'cate_id'=>$cate_id
		);
		return $this->mallShufflingFigureDetail->where($where)->select();
	}
	//获取首页楼层
	public function getIndexFloor($cate_id){
		if(empty($cate_id)){
			$cate_id=1;
		}
		$where=array(
			'type'=>'3',
			'cate_id'=>$cate_id,
			'status'=>'1',
			'is_delete'=>'0'
		);
		$res=$this->Aindex->where($where)->order('order_sort desc')->select();
		return $res;
	}
	//获取楼层商品列表。
	public function getIndexFloorItem($floorList){
		if(!$floorList){
			return false;
		}
		$ids=array();
		foreach($floorList as $key => $val){
			$ids[]=$val['index_id'];
		}
		$ids=implode(",",$ids);
		$res=$this->AindexItem->field("*,forkey_item_id as item_id")->where("forkey_index_id in (".$ids.")")->select();
		$arr=array();
		foreach($res as $k => $v){
			$arr[$v['forkey_index_id']][]=$v;
		}
		return $arr;
	}
	//获取首页分类推荐商品
	public function getIndexRecommendItem($cate_id){
		$where=array(
			'cate_id'=>$cate_id,
			'status'=>1
		);
		$res=$this->recommendItem->where($where)->select();
		$ids=array();
		foreach($res as $key =>$val){
			$ids[]=$val['item_id'];
		}
		$ids=implode(",",$ids);
		if(empty($ids)){
			return [];
		}
		$res=$this->sysItem->where("item_id in (".$ids.")")->select();
		//echo $this->sysItem->getLastSql();
		//var_dump();
		return $res;
	}

	//获取首页分类列表
	public function getIndexCategoryList(){

	}


			
}