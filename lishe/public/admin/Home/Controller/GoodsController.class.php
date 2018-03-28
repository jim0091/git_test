<?php
/**
  +------------------------------------------------------------------------------
 * GoodsAction
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: GoodsAction.class.php v001 2016-02-01
 * @description 产品操作管理
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Org\Util\Excel;
class GoodsController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->dGoods=D('Goods');
		$this->dCategory=D('Category');
		$this->dShop=D('Shopinfo');
	}
	
		//搜索条件
	public function condition(){
		$condition = array();
		
		if($_REQUEST['status'] != 0){
			$condition['status'] = $_REQUEST['status'];
		}
		if($_REQUEST['payType'] != 0){
			$condition['payTypeId'] = $_REQUEST['payType'];
		}
		if(!empty($_GET['start'])){
			$_GET['start']=str_replace(array('%2B','%3A'),array(' ',':'),str_replace('+',' ',$_GET['start']));
		}
		if(!empty($_GET['end'])){
			$_GET['end']=str_replace(array('%2B','%3A'),array(' ',':'),str_replace('+',' ',$_GET['end']));
		}else{
			$_GET['end']=date('Y-m-d H:i');
		}
		$start=trim($_GET['start']);
		$end=trim($_GET['end']);
		if(!empty($start)){
			$condition['createTime']=array('between',''.$start.','.$end.'');
		}else{
			$condition['createTime']=array('elt',$end);
		}
		$this->assign('start',$start);
		$this->assign('end',$end);
		$cp=trim($_GET['cp']);
		if($cp=='new'){
			$condition['createTime']=array('gt',date('Y-m-d 00:00:00'));
		}
    	if($cp=='cancel'){
			$condition['status']=array('eq',50);
		}
		if($cp=='agent'){
			$condition['userType']=array('eq',2);
		}
		if($cp=='shop'){
			$condition['userType']=array('eq',1);
		}
		if(!empty($_GET['keywords'])){
			$_GET['keywords']=urldecode($_GET['keywords']);
			$keywords=trim($_GET['keywords']);
			$this->assign('keywords',$keywords);
		}
		
		if(!empty($_REQUEST['searchType'])){
			switch($_REQUEST['searchType']){
				case 'orderNumber':
					$condition['orderNumber|tradeNo'] = array('like','%'.$keywords.'%');
				break;
				case 'userName':
					$orderIdArr = M('order_address')->field('orderId')->where(array('userName'=>array('like', '%'.$keywords.'%')))->select();
					if(!empty($orderIdArr)){
						foreach($orderIdArr as $key=>$value){
							$orderId[] = $value['orderId'];
						}
						$condition['orderId'] = array('in', implode(',',$orderId));						
					}
				break;
				case 'mobile':
					$orderIdArr = M('order_address')->field('orderId')->where(array('userPhone|userTel'=>array('like', '%'.$keywords.'%')))->select();
					if(!empty($orderIdArr)){
						foreach($orderIdArr as $key=>$value){
							$orderId[] = $value['orderId'];
						}
						$condition['orderId'] = array('in', implode(',',$orderId));						
					}
				break;
				case 'address':
					$orderIdArr = M('order_address')->field('orderId')->where(array('address|fullPath'=>array('like', '%'.$keywords.'%')))->select();
					if(!empty($orderIdArr)){
						foreach($orderIdArr as $key=>$value){
							$orderId[] = $value['orderId'];
						}
						$condition['orderId'] = array('in', implode(',',$orderId));						
					}
				break;
				case 'business':
					$business = $this->dBusiness->searchBusiness($keywords,$userType);
					if(!empty($business)){
						foreach($business as $key=>$value){
							$businessId[] = $value['businessId'];
						}
						$condition['userId'] = array('in', implode(',',$businessId));
					}else{
						$condition['userId']=0;
					}
				break;
				case 'goods':
					$goods = $this->dGoods->searchGoods($keywords);
					if(!empty($goods)){
						foreach($goods as $key=>$value){
							$goodsId[] = $value['goodsId'];
						}
						$order = $this->dOrder->searchGoodsOrder($goodsId);
						if(!empty($order)){
							foreach($order as $key=>$value){
								$orderId[] = $value['orderId'];
							}
							$condition['orderId'] = array('in', implode(',',$orderId));
						}else{
							$condition['orderId']=0;
						}
					}else{
						$condition['orderId']=0;
					}
				break;
			}
		}
		if($_REQUEST['userId'] != 0){
			$condition['userId'] = $_REQUEST['userId'];
		}
		return $condition;
	}

	//产品列表
		public function index(){
			$page=empty($_GET['p'])?1:intval($_GET['p']);
			$pageSize=20;
			$goods=array();
			if(I()){
				$conditions =I();
	
				//毛利率
				if($conditions['srate'] && $conditions['erate']){
					
					$condition['profit_rate']=array('between',''.$conditions['srate'].','.$conditions['erate'].'');
				}
				//进货价
				if($conditions['jsrate'] && $conditions['jerate']){
					$condition['cost_price']=array('between',''.$conditions['jsrate'].','.$conditions['jerate'].'');
				}
				//销售价
				if($conditions['csrate'] && $conditions['cerate']){
					$condition['price']=array('between',''.$conditions['csrate'].','.$conditions['cerate'].'');
				}
				//关键词
				if($conditions['keywords']){
					$condition['title']=array('like','%'.$conditions['keywords'].'%');
				}
				//状态
				if($conditions['status']){
						if($conditions['status']=="onsale"){
							//上架
							$status="onsale";
						}else if($conditions['status']=="instock"){
							//下架
							$status="instock";
						}
							$res=$this->dGoods->getStatusIds($status);
							$condition['item_id']=array('in',$res);
				}
				//商品分类条件
				$catPath=$this->dCategory->getcatPath();
				foreach($catPath as $key => $value){
					$catPath[$key]['cat_path'] = explode(",", $value['cat_path']);
				}
				//毛利率排序
			if($conditions['order'] ==1){
				$orderby="profit_rate desc";
			}
				if($conditions['catone']){
					if($conditions['cattwo']){
						if($conditions['catthree']){
							//分类项到3级
								$condition['cat_id']=$conditions['catthree'];
						}else{
							//分类项到两级
							foreach($catPath as $key => $value){
									if(in_array($conditions['cattwo'], $value['cat_path'])){
											$searchCatIDs[]=$value['cat_id'];
									}
							}
						}				
					}else{
							//分类在一级
							foreach($catPath as $key => $value){
									if(in_array($conditions['catone'], $value['cat_path'])){
											$searchCatIDs[]=$value['cat_id'];
									}
							}		
											
					}		
					if(!empty($searchCatIDs)){
						
						$condition['cat_id']=array('in',$searchCatIDs);
					}
						
				}
					//用于搜索后显示搜索条件begin		
				$this->assign('condition',$conditions);		
				//用于搜索后显示搜索条件end		
			}
			$condition['disabled']=0;
			if(I('execlType') && I('execlType')=="exportExcel"){
				//导出execl
				$execlData=$this->dGoods->getItemListExcel($condition,$orderby);
				$execlData=$this->cartDeal($execlData);
				$execlData=$execlData['list'];				
				$this->exportExcel($execlData);
					
			}		
			$goods=$this->dGoods->getItemList($page,$pageSize,$condition,$orderby);
			$goods=$this->cartDeal($goods);
			$pageObj=new \Think\Page($goods['count'],$pageSize);
			$show=$pageObj->show();// 分页显示输出
			$parentCategory=$this->dCategory->getSubCategory();
			$this->assign('list',$goods['list']);
			$this->assign('parentCategory',$parentCategory);
			$this->assign('page',$show);// 赋值分页输出
			$this->display();
		}
		public function cartDeal($goods){
			if(!empty($goods['list'])){
				foreach($goods['list'] as $key => $value){
					$value['cat_id']=str_replace(",", "", $value['cat_id']);
					$catIds[]=$value['cat_id'];
					$shopIds[]=$value['shop_id'];
					$itemIds[]=$value['item_id'];
				}
				//库存
				if(!empty($itemIds)){
					unset($condition);
					$itemIds=implode(",", $itemIds);
					$condition['item_id'] = array('in',$itemIds);		
					$store=$this->dGoods->getStores($condition);
					//商品状态
					$status=$this->dGoods->getStatus($condition);
					foreach($goods['list'] as $key => $value){
						foreach($store as $keys=>$values){
							if($value['item_id'] == $values['item_id']){
							
								$goods['list'][$key]['store']=$values['store'];
							}
						}
						foreach($status as $keyu=>$valueu){
							if($value['item_id'] == $valueu['item_id']){
							
								$goods['list'][$key]['status']=$valueu['approve_status'];
							}
						}					
						
					}
				}
				//所属分类 begin
				if(!empty($catIds)){
					unset($condition);
					$catIds=implode(",", $catIds);
					$condition['cat_id'] = array('in',$catIds);
					$field="cat_name,cat_id";
					$catname=$this->dCategory->getCateinfo($condition,$field);
					foreach($goods['list'] as $key => $value){
						foreach($catname as $keys=>$values){
							if($value['cat_id'] == $values['cat_id']){
								$goods['list'][$key]['cat_name']=$values['cat_name'];
							}
						}
					}
				//分类为空给于分类ID展示
					foreach($goods['list'] as $key => $value){
							if(empty($value['cat_name'])){
								$goods['list'][$key]['cat_name']="暂无分类(ID：".$value['cat_id'].")";
							}
					}
				
				
				}
			//所属分类 end
				if(!empty($shopIds)){
					unset($condition);
					unset($field);
						$shopIds=implode(",", $shopIds);
						$condition['cat_id'] = array('in',$shopIds);
						$field="shop_id,company_name";
						$shopname=$this->dShop->getShopInfo($condition,$field);
						foreach($goods['list'] as $key => $value){
							foreach($shopname as $keys=>$values){					
								if($value['shop_id'] == $values['shop_id']){
									$goods['list'][$key]['company_name']=$values['company_name'];
								}
							}
						}		
					//店铺为空给于店铺ID展示
						foreach($goods['list'] as $key => $value){
								if(empty($value['company_name'])){
									$goods['list'][$key]['company_name']="暂无(店铺ID：".$value['shop_id'].")";
								}
						}							
				}	

			}
			return $goods;
		}	
//导出execl
		public function exportExcel($execlData){
			header("Content-type:text/html;charset=utf-8");
			foreach($execlData as $key=>$value){
				unset($execlData[$key]['cat_id']);
				unset($execlData[$key]['shop_id']);
				if($value['status']=="onsale"){
					$execlData[$key]['status']="上架中";
				}else{
					$execlData[$key]['status']="已下架";
				}
				if($value['jd_sku']!=0){
					$execlData[$key]['jd_sku']="http://item.jd.com/".$value['jd_sku'].".html";
				}else{
					$execlData[$key]['jd_sku']="无";
				}
			}
			$ex=new Excel;
			$columnName=array('商品ID','京东链接','图片','标题','销售价(元)','进货价(元)','毛利率(%)','库存(件)','商品状态','分类','店铺');
			$ex->getExcel($execlData,$columnName,"商品列表(".date('Y年m月d日H时i分',time()).")");		
			
		}

//获取一级分类
		public function getCatOne(){
			$res=$this->dCategory->getCatsInfo();
			echo json_encode($res);
		}
	//获取相应二级分类
		public function getCatTwo(){
			$parentId = I('parent_id');
			$level=I('level');
			$res=$this->dCategory->getCatTnfos($parentId,$level);
			echo json_encode($res);
			
		}		
	
	
	
	
}