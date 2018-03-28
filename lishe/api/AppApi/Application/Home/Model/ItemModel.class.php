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
class ItemModel extends CommonModel
{
	public function __construct()
	{
		//$this->sysuserUser=M('sysuser_user');
		$this->sysItem = M('sysitem_item');
		$this->sysSku = M('sysitem_sku');
		$this->sysStatus = M('sysitem_item_status');
		$this->itemDescModel = M('sysitem_item_desc');//商品描述
		$this->itemStatusModel = M('sysitem_item_status');//商品状态表
	}
	//获取商品列表
	public function getItemList($pageNow=0, $count=20,$cat_id,$fields=""){
		$start=$pageNow*$count;
		$res=$this->sysItem->field($fields)->where("cat_1=$cat_id")->limit($start,$count)->order("item_id desc")->select();
		return $res;
	}
	//通过商品列表获取下架状态
	public function filterItem($items){
		if(empty($items)){
			return false;
		}
		$ids=array();
		foreach($items as $key => $val){
			$ids[]=$val['item_id'];
		}
		$map['item_id']=array('in',$ids);
		$res=$this->sysStatus->where($map)->select();
		$status=array();
		foreach($res as $key => $val){
			$status[$val['item_id']]=$val;
		}
		foreach($items as $key => $val){
			$items[$key]['approve_status']=$status[$val['item_id']]['approve_status'];
		}
		return	$items;
	}
	//获取商品信息
	public function getItemInfo($itemId,$fields){
		$itemId=(int)$itemId;
		if($itemId<1){
			return false;
		}
		$itemInfo = $this->sysItem->field($fields)->where('item_id = '.$itemId)->find();
		return $itemInfo;
	}
	//获取商品属性
	public function getItemDesc($itemId){
		$itemId=(int)$itemId;
		if($itemId<1){
			return false;
		}
		return $this->itemDescModel->where('item_id = '.$itemId)->find();
	}
	//获取商品sku列表
	public function getSkuList($itemId){
		$itemId=(int)$itemId;
		if($itemId<1){
			return false;
		}
		return M('sysitem_sku')->field("sku.item_id,sku.sku_id,sku.title,sku.price,sku.mkt_price,sku.status,sku.sold_quantity,(store.store-store.freez) as store,sku.spec_desc,sku.spec_desc")->table('sysitem_sku sku,sysitem_sku_store store')->where('sku.sku_id = store.sku_id and sku.item_id = '.$itemId)->select();
	}
	//获取精选商品列表
	public function getRecommendItemList($itemInfo,$limit=10){
		if(empty($itemInfo)){
			return false;
		}
		$where['a.cat_id'] = $itemInfo['cat_id'];
		$where['b.approve_status'] = array('EQ',"onsale");
		return $this->sysItem->alias('a')->join('sysitem_item_status as b ON a.item_id= b.item_id')->where($where)->field("a.item_id,a.cat_id,a.title,a.bn,a.price,a.mkt_price,a.weight,a.image_default_id,a.list_image,a.spec_desc,b.approve_status")->order('a.profit_rate desc')->limit($limit)->select();
	}
	//获取商品状态
	public function getItemStatus($itemInfo){
		return $this->itemStatusModel->where('item_id='.$itemInfo['item_id'])->find();
	}
}
			
