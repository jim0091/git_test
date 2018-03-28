<?php
namespace Home\Controller;
use Think\Think;
use Org\Util\Excel;
/*
 * 2016/10/14
 *	王子铖
 * 采购控制器
 * */
class PurchaseController extends CommonController{

	private $supplier_user;

	public function __construct(){
		parent::__construct();
		if(empty($this->adminId)){
			header("Location:".__APP__."/Login");
		}
		$this->supplier_plan = D('SupplierPlan');
		$this->supplier_goods = D('SupplierPlanGoods');
		//$this->supplier_item = M('supplier_item');
		//$this->syscategory_brand = M('syscategory_brand');
		$this->category = D('SyscategoryCat');
		$this->brand = D('SyscategoryBrand');
		$this->supplier = D('Supplier');
		$this->purchase = D('Purchase');
		$this->supplier_item = D('SupplierItem');
		$this->supplier_item_sku = D('SupplierItemSku');
		$this->supplier_warehouse = M('supplier_warehouse');
		$this->supplier_order = D('SupplierOrder');
		$this->supplier_order_goods = M('supplier_order_goods');
		$this->system_admin = M('system_admin');
		$this->sysitem_sku_store = D('SysitemSkuStore');
		$this->sysitem_sku = D('SysitemSku');
	}
	/*
	 * 首页
	 * */
	public function index(){
		$this->display();
	}
	
	/**
	 * 全部采购计划
	 * @author Gaolong
	 */
	public function allPlanList(){
		$status 	= I('get.status', -99);//全部
		$where=array();
		if($status == -99){
			$where['status'] = array('neq',-2);
		}else{
			$where['status'] = $status;
		}
		$this->assign('status', $status);
		$this->planList($where, 1);
	}
	
	/**
	 * 已审核采购计划
	 * @author Gaolong
	 */
	public function handlePlanList(){
		$status = I('get.status', -99, 'intval');
		$where=array();
		if($status == 2){
			$where['status'] = array('in',array(2, 3));
		}else if($status == -1){
			$where['status'] = -1;
		}else if($status == -99){
			$where['status'] = array('in',array(-1, 2, 3));
		}
		$this->assign('status', $status);
		$this->planList($where, 2);
	}
	/**
	 * 已取消采购计划
	 * @author Gaolong
	 */
	public function cancelPlanList(){
		$where=array();
		$where['status'] = -2;//已取消
		$this->assign('status', -2);
		$this->planList($where, 3);
	}
	
	/**
	 * 此方法可以和 allPlanList、handlePlanList、cancelPlanList 合并 <br/>
	 * 这里为了做权限控制，拆分为3个方法
	 * @param unknown $where 查询条件
	 * @param unknown $page 当前为第几页
	 * @param unknown $from 1.全部计划 2.已处理计划 3.已取消计划
	 * @author Gaolong
	 */
	private function planList($where, $from){
		//参数部分
		$page 		  = I('get.p', 1);
		$startdate 	  = I('get.startdate','','trim,urldecode');
		$enddate 	  = I('get.enddate','','trim,urldecode');
		$build_people = I('get.build_people','','trim,urldecode');
		
		//搜索部分
		$timeCondition = array();
		if(!empty($startdate) && !empty($enddate)){
			$timeCondition = array(array('egt', $startdate), array('elt', $enddate));
		}else{
			if(!empty($startdate)){
				$_GET['startdate'] = $startdate;
			}
			if(!empty($enddate)){
				$_GET['enddate'] = $enddate;
			}
		}
		if(!empty($timeCondition)){
			$_GET['startdate'] = $startdate;
			$_GET['enddate'] = $enddate;
			$where['build_time'] = $timeCondition;
		}
		
		if(!empty($build_people)){
			$_GET['build_people'] = $build_people; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$where['build_people'] = array('like', "%{$build_people}%");
		}
		
		//查询数据
		$listRow = 10;//每页显示的数据总条数
		$count = $this->supplier_plan->where($where)->count('plan_id');
		$list = $this->supplier_plan->where($where)
						->order('plan_id DESC')
						->page($page, $listRow)
						->select();
		//print_r($this->supplier_plan->getLastSql());exit();
		$userIdArr = array();
		//查询采购人
		foreach ($list as &$val){
			$userIdArr[] = $val['uid'];
			$val['total_price'] = $this->supplier_plan->totalPrice($val['plan_id']);
		}
		//获取管理员
		$adminArr = $this->getSystemAdminById($userIdArr, 'admin_id, real_name');
		
		$warehouseList = $this->getWarehouseList('warehouse_id, name', null, true);
		$page=new \Think\Page($count, $listRow);
	
		$this->assign('page',$page->show());
		
		$this->assign('list',$list);
		$this->assign('count',$count);
		$this->assign('startdate',$startdate);
		$this->assign('enddate',$enddate);
		$this->assign('build_people',$build_people);
		$this->assign('warehouseList',$warehouseList);
		$this->assign('adminArr',$adminArr);
		$this->assign('from', $from);
		$this->display('planList');
	}
	
	/**
	 * 取消采购计划
	 * @author Gaolong
	 */
	public function cancelPlan(){
		$planId = I('post.plan_id',-1,'intval');
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		if(!is_numeric($planId) || $planId < 1){
			$ret['msg'] = 'invalid pland_id';
			$this->ajaxReturn($ret);
		}
		
		$where['plan_id'] = $planId;
		$where['status'] = 0;
		
		$result = $this->supplier_plan->where($where)->setField('status',-2);
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = 'fail';
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 增加采购计划
	 * @author Gaolong
	 */
	public function addPlan(){
		if(IS_POST){
			$ret = array('code'=>-1, 'msg'=>'unkown error');
			$data = I("post.");
			if(!isset($data['build_people'])){
				$ret['msg']  = 'no build_people';
				$this->ajaxReturn($ret);
			}
			$data['uid']=$this->adminId;
			$this->supplier_plan->add($data);
			$planId = $this->supplier_plan->getLastInsID();
			if(is_numeric($planId) && $planId > 0){
				$ret['code'] = 1;
				$ret['msg'] = 'success';
				$ret['planId'] = $planId;
			}else{
				$ret['msg'] = $this->supplier_plan->getError();
			}
			$this->ajaxReturn($ret);
		}
// 		else{
// 			$houseList = $this->getWarehouseList('warehouse_id, name');
// 			$this->assign('houseList',$houseList);
// 			$this->display('addPlan');
// 		}
	}

	/**
	 * 添加采购计划商品
	 * @author Gaolong
	 */
	public function addPlanGoods(){

		$planId 	= I('get.plan_id','-1','intval');
		$cname 		= I('get.cname','','trim,urldecode');
		$gname 		= I('get.gname','','trim,urldecode');
		$barcode 	= I('get.barcode','','trim');
		$categoryId = I('get.category_id', -99, 'intval');
		$brandId 	= I('get.brand_id', -99, 'intval');
		$status 	= I('get.status', -99, 'intval');
		$page 		= I("get.p", 0, 'intval');

		//分类
		$sysCatList = $this->category->getCategory(0);
		$sysBrandList = array();
		if($categoryId > 0){
			$sysBrandList =$this->brand->getBrandsByCatId($categoryId, 1);
		}
		//查询所有商品
		$pageCount = 30;
		$where = array();
		if(!empty($barcode)){
			//查询商品条码
			$sitemIdArr = $this->supplier_item_sku->getItemIdByCode($barcode);
			if(empty($sitemIdArr)){
				$where['sitem_id'] = -1;
			}else{
				$where['sitem_id'] = array('in',$sitemIdArr);
			}
		}
		if(!empty($cname)){
			$_GET['cname'] = $cname; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$sWhere['company_name'] = array('like',"%$cname%");
			$supplierIdArr = $this->supplier->searchSupplier4Plan('supplier_id', $sWhere);
			if(!empty($supplierIdArr)){
				$where['supplier_id'] = array('in',$supplierIdArr);
			}
		}
		if($categoryId >= 0){
			$where['cat_1'] = $categoryId;
		}
		if($brandId >= 0){
			$where['brand_id'] = $brandId;
		}
		if($status >= -3 && $status <= 3){
			$where['status'] = $status;
		}
		if(!empty($gname)){
			$_GET['gname'] = $gname; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$where['title'] = array('like',"%$gname%");
		}
		//查询数据
		$itemInfo = $this->supplier_item->getSupplierItem4Plan($page, $pageCount, $where);
		$count			= $itemInfo['count']; 		//总数
		$itemList	 	= $itemInfo['itemList'];	//item数据集合 
		$sitemIdArr	 	= $itemInfo['sitemIdArr'];	//sitem_id集合
		$itemIdsArr	 	= $itemInfo['itemIdsArr'];	//item_id集合
		$supplierIdsArr	= $itemInfo['supplierIdsArr'];	//供应商数组
		$categoryIdArr 	= $itemInfo['categoryIdArr'];//分类id集合
		$brandIdsArr 	= $itemInfo['brandIdsArr'];  //品牌集合
		//加载sku商品数据
		$skuInfo = $this->supplier_item_sku->getSku4Plan($sitemIdArr);
		$skuList = $skuInfo['skuList'];
		$skuIdArr = $skuInfo['skuIdArr'];
		//加载销量
		$soldList = $this->sysitem_sku->getSoldBySkuIdArr($skuIdArr);
		//加载库存
		$storeList = $this->sysitem_sku_store->getStoreByItemIdArr($itemIdsArr);
		//加载分类
		$catList 	= $this->category->getCategoryByIds($categoryIdArr,'cat_id,cat_name');
		//加载品牌
		$brandList 	= $this->brand->getBrandByIds($brandIdsArr,'brand_id,brand_name');
		//加载供应商
		$supplierList = $this->supplier->getSupplierByIds($supplierIdsArr,'supplier_id, company_name');
		
		$page=new \Think\Page($count, $pageCount);
		$this->assign('planId',$planId);
		$this->assign('page',$page->show());
		$this->assign("count", $count);
		$this->assign("itemList", $itemList);
		$this->assign("skuList", $skuList);
		$this->assign("catList", $catList);
		$this->assign("brandList", $brandList);
		$this->assign("storeList", $storeList);
		$this->assign("soldList", $soldList);
		$this->assign("supplierList", $supplierList);
		$this->assign("barcode", $barcode);
		$this->assign('categoryId',$categoryId);
		$this->assign('gname',$gname);
		$this->assign('cname',$cname);
		$this->assign('brandId',$brandId);
		$this->assign("status", $status);
		$this->assign("sysCatList", $sysCatList);
		$this->assign("sysBrandList", $sysBrandList);

		$this->display('addPlanGoods');
	}

	/**
	 * 获取品牌分类
	 * @author Gaolong
	 */
	public function getBrandList(){

		$categoryId = I('get.category_id','-1', 'intval');
		$result = array('code' => -1, 'msg' => 'unknown error' ,'data' => null);

		if(!is_int($categoryId)){
			$result['msg'] = 'invalid category_id';
			$this->ajaxReturn($result);
		}
		//获取商品列表
		$brandList = $this->brand->getBrandsByCatId($categoryId, 1);
		if(is_array($brandList)){
			$result['code'] = 1;
			$result['msg'] = 'success';
			$result['data'] = $brandList;
		}

		$this->ajaxReturn($result);
	}

	/**
	 * 提交采购商品
	 * @author Gaolong
	 */
	public function submitOrder(){

		$ret = array('code'=>-1, 'msg'=>'unknow error');
		$planId = I('post.plan_id', -1, 'intval');
		$orderStr = I('post.orderstr');

		if(!IS_POST){
			$ret['msg'] = 'invalid post';
			$this->ajaxReturn($ret);
		}

		if(!is_numeric($planId) || $planId < 1){
			$ret['msg'] = 'invalid plan_id';
			$this->ajaxReturn($ret);
		}

		$orderArr = explode('|', $orderStr);
		if(empty($orderArr)){
			$ret['msg'] = '没有选中任何商品';
			$this->ajaxReturn($ret);
		}

		array_pop($orderArr);
		$itemIdArr 	= array();
		$itemArr	= array();
		foreach ($orderArr as $item){
			$item = explode('_', $item);
			$sskuId = $item[0];
			$number = $item[1];
			if($number <= 0){
				continue;//数量小于0 ，跳过不添加
			}
			$itemArr[$sskuId]['number'] = $number;
			$itemIdArr[] = $sskuId;
		}
		if(empty($itemIdArr)){
			$ret['msg'] = '没有添加任何商品';
			$this->ajaxReturn($ret);
		}
		//加载已经选过的商品ssku_id,以防止重复添加
		$planSskuIdList = $this->supplier_goods->field('ssku_id')->where("plan_id=$planId")->select();
		$myPlanGoods = array();
		foreach ($planSskuIdList as $val){
			$myPlanGoods[$val['ssku_id']] = 1;
		}
		unset($planSskuIdList);
		//排除已添加的商品
		foreach ($itemIdArr as $key => $sskuId){
			if($myPlanGoods[$sskuId] === 1){
				unset($itemIdArr[$key]);
			}
		}
		if(empty($itemIdArr)){
			$ret['msg'] = '重复添加商品';
			$this->ajaxReturn($ret);
		}
		//查询商品
		$itemList = $this->supplier_item_sku->getItem4Plan($itemIdArr);
		//添加
		$result = false;
		if(!empty($itemList)){
			$result = $this->supplier_goods->batchAddForPlan($planId, $itemList, $itemArr);
		}

		if($result){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = '添加失败';
		}
		$this->ajaxReturn($ret);
	}

	/**
	 * 选择商品数量iframe
	 * @author Gaolong
	 */
	public function itemNumIframe(){
		$planId = I('get.plan_id', -1, 'intval');
		$orderStr = I('get.orderstr'); //ssku_id|ssku_id 形式的字符串

		if(!is_numeric($planId) || $planId < 1){
			echo "invalid plan_id";
			exit();
		}

		$sskuIdArr = explode('|', $orderStr);
		if(empty($sskuIdArr)){
			echo "没有选中任何商品";
			exit();
		}

		$itemList = $this->supplier_item_sku
					->field('ssku_id, sitem_id, item_id, sku_id, price, cost_price, spec_info')
					->where(array('ssku_id'=>array('in',$sskuIdArr)))
					->select();
		
		//转换成数组方便前端页面取出
		$itemArr = array();
		$itemIdArr = array();
		$skuIdArr = array();
		$sitemIdArr = array();
		foreach ($itemList as $item){
			$skuIdArr[$item['sku_id']] = $item['sku_id'];
			$itemIdArr[$item['item_id']] = $item['item_id'];
			$sitem_id = $item['sitem_id'];
			$sitemIdArr[$sitem_id] = $sitem_id;
			$itemArr[$sitem_id]['title'] = $item['title'];
			$itemArr[$sitem_id]['sku'][] = $item;
		}
		unset($itemList);
		//加载已经选过的商品ssku_id,以防止重复添加
		$planSskuIdList = $this->supplier_goods->field('ssku_id, order_price, number')->where("plan_id=$planId")->select(); 
		$planSskuIdArr = array();
		foreach ($planSskuIdList as $val){
			$planSskuIdArr[$val['ssku_id']]['order_price'] = $val['order_price'];
			$planSskuIdArr[$val['ssku_id']]['number'] = $val['number'];
		}
		
		//加载库存
		$storeList = $this->sysitem_sku_store->getStoreByItemIdArr($itemIdArr);
		//加载销量
		$soldList = $this->sysitem_sku->getSoldBySkuIdArr($skuIdArr);
		//加载供应商
		$supplierIdArr = array();
		$itemNameArr = array();
		if(!empty($sitemIdArr)){
			$itemList = $this->supplier_item->field('sitem_id,supplier_id,title')
								->where(array('sitem_id'=>array('in',$sitemIdArr)))
								->select();
			foreach ($itemList as $item){
				$sitemid = $item['sitem_id'];
				$supplierIdArr[$sitemid] = $item['supplier_id'];
				$itemNameArr[$sitemid] = $item['title'];
			}
		}
		$supplierList = $this->supplier->getSupplierByIds($supplierIdArr,'supplier_id, company_name');
		$this->assign("plan_id", $planId);
		$this->assign("itemArr", $itemArr);
		$this->assign("planSskuIdArr", $planSskuIdArr);
		$this->assign('storeList', $storeList);
		$this->assign('soldList', $soldList);
		$this->assign('supplierIdArr', $supplierIdArr);
		$this->assign('itemNameArr', $itemNameArr);
		$this->assign('supplierList', $supplierList);
		$this->display('itemNumIframe');
	}

	/**
	 * 编辑采购计划
	 * @author Gaolong
	 */
	public function editPlan(){
		$planId = I('get.plan_id','','intval');
		$page 	= I("get.p", 0, 'intval');
		$listRows = 10; //每页显示的条数
		//查询订购计划信息
		$supplierPlan = $this->supplier_plan->where("plan_id=$planId")->find();
		
		//查询订购单中的商品
		$field = 'id, plan_id, sitem_id, item_id, sku_id, supplier_id, ssku_id, title, price, cost_price, spec_info, number';
		$planGoodsList = $this->supplier_goods->field($field)
						->where("plan_id=$planId")
						->page($page, $listRows)
						->select();
		//转换数据，方便模板页循环遍历
		$planGoodsArr = array();
		$itemIdArr = array();
		$skuIdArr = array();
		$supplierIdArr = array();
		foreach ($planGoodsList as $goods){
			$itemIdArr[$goods['item_id']] =  $goods['item_id'];
			$skuIdArr[$goods['sku_id']] =  $goods['sku_id'];
			$supplierIdArr[$goods['supplier_id']] = $goods['supplier_id'];
			$sitem_id = $goods['sitem_id'];
			$planGoodsArr[$sitem_id]['supplier_id'] = $goods['supplier_id'];
			$planGoodsArr[$sitem_id]['title'] = $goods['title'];
			$planGoodsArr[$sitem_id]['data'][] = $goods;
		}
		unset($planGoodsList);
		//查询商品总条数
		$count = $this->supplier_goods->where("plan_id=$planId")->count('id');
		//查询仓库信息,这个数据可以被缓存（S）,或者换成异步加载
		$houseList = $this->getWarehouseList('warehouse_id, name, address, contacts, tel');
		//转换方便前端取数据
		$houseArr = array();
		foreach ($houseList as $val){
			$houseArr[$val['warehouse_id']] = $val;
		}
		unset($houseList);
		//查询仓库地址和联系人
		//加载库存
		$storeList = $this->sysitem_sku_store->getStoreByItemIdArr($itemIdArr);
		//加载销量
		$soldList = $this->sysitem_sku->getSoldBySkuIdArr($skuIdArr);
		//加载供应商
		$supplierList = $this->supplier->getSupplierByIds($supplierIdArr,'supplier_id, company_name');
		//查询货期   
		$page=new \Think\Page($count, $listRows);
		$this->assign('page',$page->show());
		$this->assign('planId', $planId);
		$this->assign('count' , $count);
		$this->assign('supplierPlan', $supplierPlan);
		$this->assign('planGoodsArr', $planGoodsArr);
		$this->assign('storeList', $storeList);
		$this->assign('soldList', $soldList);
		$this->assign('supplierList', $supplierList);
		$this->assign('houseArr', $houseArr);
		$this->display('editPlan');
	}

	/**
	 * 保存采购计划信息
	 * @author Gaolong
	 */
	public function savePlan(){
		$plan_id = I('post.plan_id', -1, 'intval');
		$data = I('post.');
		$ret = array('code'=>-1, 'msg'=>'unknow error');

		if(!is_numeric($plan_id) || $plan_id < 1){
			$ret['msg'] = 'invalid plan_id';
			$this->ajaxReturn($ret);
		}
		
		if(empty($data)){
			exit();
		}
		$data['uid'] = $this->adminId;
		$result = $this->supplier_plan->where("plan_id=$plan_id")->save($data);

		if(is_numeric($result)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}
		$this->ajaxReturn($ret);
	}

	/**
	 * 保存采购计划中的单个商品修改
	 * @author Gaolong
	 */
	public function savePlanGoods(){
		$itemid = I('post.itemid',0,'intval');
		$number = I('post.number',0,'intval');
		$ret = array('code'=>-1, 'msg'=>'unknow error');
		if(!is_numeric($itemid) || $itemid < 0){
			$ret['msg'] = 'invalid id';
			$this->ajaxReturn($ret);
		}
		if(!is_numeric($number) || $number < 0){
			$ret['msg'] = '数量有误';
			$this->ajaxReturn($ret);
		}
		$result = $this->supplier_goods->where("id=$itemid")->setField('number',$number);
		if(is_numeric($result)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}
		$this->ajaxReturn($ret);
	}
	/**
	 * 删除单个采购计划中的的商品
	 * @author Gaolong
	 */
	public function delPlanGoods(){
		$itemid = I('post.itemid',0,'intval');
		$ret = array('code'=>-1, 'msg'=>'unknow error');
		if(!is_numeric($itemid) || $itemid < 0){
			$ret['msg'] = 'invalid id';
			$this->ajaxReturn($ret);
		}
		//删除
		$result = $this->supplier_goods->where("id=$itemid")->delete();

		if(is_numeric($result) && $result > 0){//删除成功
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}
		$this->ajaxReturn($ret);
	}
	/**
	 * 批量删除采购计划中的多个商品
	 * @author Gaolong
	 */
	public function delSelectedPlanGoods(){
		$itemIdArr = I('post.itemIdArr');
		$ret = array('code'=>-1, 'msg'=>'unknow error');
		if(empty($itemIdArr[0])){
			$ret['msg'] = 'empty id array';
			$this->ajaxReturn($ret);
		}
		//排除非数字的id
		foreach ($itemIdArr as $key => $id){
			if(!is_numeric($id)){
				unset($itemIdArr[$key]);
			}
		}
		if(empty($itemIdArr)){
			$ret['msg'] = 'empty id (removed unint)';
			$this->ajaxReturn($ret);
		}

		//批量删除
		$where['id'] = array('in',$itemIdArr);
		$result = $this->supplier_goods->where($where)->delete();
		if(is_numeric($result) && $result > 0){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}

		$this->ajaxReturn($ret);
	}
	/**
	 * 查看采购计划
	 * @author Gaolong
	 */
	public function showPlan(){
		$planId = I('get.plan_id','','intval');
		$like = I('get.like','','trim');
		$page 	= I("get.p", 0, 'intval');
		$listRows = 10; //每页显示的条数
		
		//查询订购计划信息
		$supplierPlan = $this->supplier_plan->where("plan_id=$planId")->find();
		//查询订购单中的商品
		$where = array(
			'plan_id' => $planId,
			'title|bn' => array('like',"%$like%"),
		);
		$field = 'id, title, sitem_id, item_id, sku_id, supplier_id, spec_info, price, cost_price, number';
		$planGoodsList = $this->supplier_goods->field($field)
								->where($where)
								->page($page, $listRows)
								->select();
		
		//转换数据，方便模板页循环遍历
		$planGoodsArr = array();
		$itemIdArr = array();
		$skuIdArr = array();
		$supplierIdArr = array();
		foreach ($planGoodsList as $goods){
			$sitem_id = $goods['sitem_id'];
			$itemIdArr[$goods['item_id']] =  $goods['item_id'];
			$skuIdArr[$goods['sku_id']] =  $goods['sku_id'];
			$supplierIdArr[$goods['supplier_id']] = $goods['supplier_id'];
			$planGoodsArr[$sitem_id]['title'] = $goods['title'];
			$planGoodsArr[$sitem_id]['supplier_id'] = $goods['supplier_id'];
			$planGoodsArr[$sitem_id]['data'][] = $goods;
		}
		unset($planGoodsList);
		//查询商品总条数
		$count = $this->supplier_goods->where($where)->count('id');
		//查询仓库名称
		$warehouse_id = $supplierPlan['warehouse_id'];
		if(!empty($warehouse_id)){
			$warehouse = $this->supplier_warehouse->field('name,address,contacts,tel')->where("warehouse_id=$warehouse_id")->find();
			$this->assign('warehouse', $warehouse);
		}
		//查询仓库地址和联系人 todo
		//加载库存
		$storeList = $this->sysitem_sku_store->getStoreByItemIdArr($itemIdArr);
		//加载销量
		$soldList = $this->sysitem_sku->getSoldBySkuIdArr($skuIdArr);
		//加载供应商
		$supplierList = $this->supplier->getSupplierByIds($supplierIdArr,'supplier_id, company_name');
		
		$page=new \Think\Page($count, $listRows);
		$this->assign('page',$page->show());
		$this->assign('planId', $planId);
		$this->assign('like', $like);
		$this->assign('count' , $count);
		$this->assign('supplierPlan', $supplierPlan);
		$this->assign('planGoodsArr', $planGoodsArr);
		$this->assign('storeList', $storeList);
		$this->assign('soldList', $soldList);
		$this->assign('supplierList', $supplierList);
		$this->display('showPlan');
	}
	
	/**
	 * 生成采购单
	 * @author Gaolong
	 */
	public function createPurchaseOrder(){
		
		$planid = I('post.planid','-1','intval');
		$ret = array('code'=>-1, 'msg'=>'unknow error');
		
		if(!is_numeric($planid) || $planid < 1){
			$ret['msg'] = 'invalid plan_id';
			$this->ajaxReturn($ret);
		}
		
		$where['plan_id'] = $planid;
		$status = $this->supplier_plan->where($where)->getField('status');
		if($status != 2){
			$ret['msg'] = '该采购购计划状态 不符合！';
			$this->ajaxReturn($ret);
		}
		
		//查询采购计划商品列表
		$planGoodsList = $this->supplier_goods->where($where)->select();
		
		if(empty($planGoodsList)){
			$ret['msg'] = '该采购购计划没有任何商品！';
			$this->ajaxReturn($ret);
		}
		//查询采购计划
		$plan = $this->supplier_plan->where($where)->find();
		if(empty($plan)){
			$ret['msg'] = '该采购购计划不存在';
			$this->ajaxReturn($ret);
		}
		//生成采购单
		$result = $this->supplier_order->addOrder($plan, $planGoodsList);
		
		if($result){
			//改变订购计划状态
			$this->supplier_plan->where($where)->setField('status',3);
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = '生成采购订购单失败';
		}
		$this->ajaxReturn($ret);
	}
	/**
	 * 查看采购单
	 * @author Gaolong
	 */
	public function showOrder(){
		$orderId = I('get.order_id','-1','intval');
		$page 	= I("get.p", 0, 'intval');
		$like	= I("get.like",'','trim');
		if($orderId == -1 || !is_numeric($orderId)){
			exit();
		}
		$listRows = 10; //每页显示的条数
		//查询订购计划信息
		$supplierOrder = $this->supplier_order->where("order_id=$orderId")->find();
		//查询订购单中的商品
		$field = 'title, sitem_id, item_id, sku_id, number,
					storage_number, plan_number, price, cost_price, order_price, spec_info,barcode';
		$where=array(
			'order_id'=>$orderId,
			'title|bn'=>array('like',"%$like%")
		);
		$supplierGoodsList = $this->supplier_order_goods->field($field)
								->where($where)
								->page($page, $listRows)
								->select();
		
		//转换数据，方便模板页循环遍历
		$orderGoodsArr = array();
		$itemIdArr = array();
		$skuIdArr = array();
		foreach ($supplierGoodsList as $goods){
			$itemIdArr[$goods['item_id']] = $goods['item_id'];
			$skuIdArr[$goods['sku_id']] = $goods['sku_id'];
			$sitem_id = $goods['sitem_id'];
			$orderGoodsArr[$sitem_id]['title'] = $goods['title'];
			$orderGoodsArr[$sitem_id]['data'][] = $goods;
		}
		unset($supplierGoodsList);
		//查询商品总条数
		$count = $this->supplier_order_goods->where($where)->count('id');
		//查询仓库名称
		$warehouse_id = $supplierOrder['warehouse_id'];
		$warehouse = $this->supplier_warehouse->field('name,address,contacts,tel')->where("warehouse_id=$warehouse_id")->find();
		//查询仓库地址和联系人
		//加载库存
		$storeList = $this->sysitem_sku_store->getStoreByItemIdArr($itemIdArr);
		//加载销量
		$soldList = $this->sysitem_sku->getSoldBySkuIdArr($skuIdArr);
		//加载供应商
		$supplier = $this->supplier->getSupplierByIds($supplierOrder['supplier_id'],'company_name');
		//查询货期
		$page=new \Think\Page($count, $listRows);
		
		$this->assign('like', $like);
		$this->assign('orderId', $orderId);
		$this->assign('page', $page->show());
		$this->assign('count', $count);
		$this->assign('supplierOrder', $supplierOrder);
		$this->assign('orderGoodsArr', $orderGoodsArr);
		$this->assign('storeList', $storeList);
		$this->assign('soldList', $soldList);
		$this->assign('supplier', $supplier);
		$this->assign('warehouse', $warehouse);
		$this->display('showOrder');
	}
	//入库接口
	public function storage(){
		$arr=I("post.");
		$orderId=$arr['orderId'];
		unset($arr['orderId']);
		$this->purchase->setStorageNumber($arr);
		$status='6';
		$res=$this->purchase->setOrderStatus($orderId,$status);
		if($res){
			$this->success("更新成功");
		}else{
			$this->error("更新失败");
		}
	}
	/**
	 * 采购单入库
	 * @author Wangzicheng
	 */
	public function orderStorage(){
		$orderId = I('get.order_id','-1','intval');
		$page 	= I("get.p", 0, 'intval');
		if($orderId == -1 || !is_numeric($orderId)){
			exit();
		}
		$listRows = 10; //每页显示的条数
		//查询订购计划信息
		$supplierOrder = $this->supplier_order->where("order_id=$orderId")->find();
		//查询订购单中的商品
		$field = 'id,title, sitem_id, item_id, sku_id, number,storage_number, price, cost_price, order_price, spec_info';
		$supplierGoodsList = $this->supplier_order_goods->field($field)
			->where("order_id=$orderId")
			->page($page, $listRows)
			->select();
		//var_dump($supplierGoodsList);
		//转换数据，方便模板页循环遍历
		$orderGoodsArr = array();
		$itemIdArr = array();
		$skuIdArr = array();
		foreach ($supplierGoodsList as $goods){
			$itemIdArr[$goods['item_id']] = $goods['item_id'];
			$skuIdArr[$goods['sku_id']] = $goods['sku_id'];
			$sitem_id = $goods['sitem_id'];
			$orderGoodsArr[$sitem_id]['title'] = $goods['title'];
			$orderGoodsArr[$sitem_id]['data'][] = $goods;
		}
		unset($supplierGoodsList);
		//查询商品总条数
		$count = $this->supplier_order_goods->where("order_id=$orderId")->count('id');
		//查询仓库名称
		$warehouse_id = $supplierOrder['warehouse_id'];
		$warehouse = $this->supplier_warehouse->field('name,address,contacts,tel')->where("warehouse_id=$warehouse_id")->find();
		//查询仓库地址和联系人
		//加载库存
		$storeList = $this->sysitem_sku_store->getStoreByItemIdArr($itemIdArr);
		//加载销量
		$soldList = $this->sysitem_sku->getSoldBySkuIdArr($skuIdArr);
		//加载供应商
		$supplier = $this->supplier->getSupplierByIds($supplierOrder['supplier_id'],'company_name');
		//查询货期
		$page=new \Think\Page($count, $listRows);
		$this->assign('page',$page->show());
		$this->assign('orderId', $orderId);
		$this->assign('count' , $count);
		$this->assign('supplierOrder', $supplierOrder);
		$this->assign('orderGoodsArr', $orderGoodsArr);
		$this->assign('storeList', $storeList);
		$this->assign('soldList', $soldList);
		$this->assign('supplier', $supplier);
		$this->assign('warehouse', $warehouse);
		$this->display('orderStorage');
	}
	/**
	 * 采购计划提交审批
	 * @author Gaolong
	 */
	public function submitPlanApprove(){
		$planid = I('post.planid','-1','intval');
		$ret = array('code'=>-1, 'msg'=>'unknow error');
		
		if(!is_numeric($planid) || $planid < 1){
			$ret['msg'] = 'invalid plan_id';
			$this->ajaxReturn($ret);
		}
		$where['plan_id'] = $planid;
		//查看采购计划是否添加过商品
		$result = $this->supplier_goods->where($where)->getField('id');
		if(!$result){
			$ret['msg'] = '该采购计划没有添加任何商品，禁止提交审核！';
			$this->ajaxReturn($ret);
		}
		
		$where['status'] = 0;
		$result = $this->supplier_plan->where($where)->setField('status',1);
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = '采购计划提交审核失败！';
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 采购计划审批列表
	 * @author Gaolong
	 */
	public function planApproveList(){
		$p 			  = I("get.p",1);
		$status 	  = I("get.status", 1, 'intval');
		$startdate 	  = I('get.startdate','','trim,urldecode');
		$enddate 	  = I('get.enddate','','trim,urldecode');
		$build_people = I('get.build_people','','trim,urldecode');
		if(!is_numeric($status)){
			exit();
		}
		
		//搜索部分
		$where = array();
		$timeCondition = array();
		if(!empty($startdate) && !empty($enddate)){
			$timeCondition = array(array('egt', $startdate), array('elt', $enddate));
		}else{
			if(!empty($startdate)){
				$_GET['startdate'] = $startdate;
			}
			if(!empty($enddate)){
				$_GET['enddate'] = $enddate;
			}
		}
		if(!empty($timeCondition)){
			$_GET['startdate'] = $startdate;
			$_GET['enddate'] = $enddate;
			$where['build_time'] = $timeCondition;
		}
		
		if(!empty($build_people)){
			$_GET['build_people'] = $build_people; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$where['build_people'] = array('like', "%{$build_people}%");
		}
		if(!empty($build_people)){
			$_GET['build_people'] = $build_people; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$where['build_people'] = array('like', "%{$build_people}%");
		}		
		$listRows = 10;//每页显示的数据条数
		$field = 'plan_id, uid, settlement_method, warehouse_id, remarks, build_people, build_time, status';
		$planList =$this->supplier_plan->field($field)
						->page($p, $listRows)
						->where($where)
						->order('plan_id DESC')
						->select();
		//print_r($this->supplier_plan->getLastSql());exit();
		$count = $this->supplier_plan->where($where)->count('plan_id');
		
		//遍历对应的id
		$adminIdArr = array();
		foreach ($planList as &$plan){
			$adminIdArr[] = $plan['uid'];
			$plan['total_price'] =$this->supplier_plan->totalPrice($plan['plan_id']);
		}
		//获取管理员
		$adminArr = $this->getSystemAdminById($adminIdArr, 'admin_id, real_name');
		//获取仓库
		$warehouseList = $this->getWarehouseList('warehouse_id, name', null, true);
		
		$page=new \Think\Page($count, $listRows);
		$this->assign('count', $count);
		$this->assign('page',$page->show());
		$this->assign('startdate',$startdate);
		$this->assign('enddate',$enddate);
		$this->assign('build_people',$build_people);
		$this->assign('status', $status);
		$this->assign('planList', $planList);
		$this->assign('adminArr', $adminArr);
		$this->assign('warehouseList', $warehouseList);
		$this->display('planApproveList');
	}
	
	/**
	 * 采计划审批
	 * @author Gaolong
	 */
	public function planApprove(){
		$planId= I('post.plan_id', -1, 'intval');
		$remark = I('post.remark', '','strip_tags');
		$approve = I('post.approve', 0, 'intval'); //-1.未通过 2.通过
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		
		if($approve !== -1 && $approve !== 2){
			$ret['msg'] = "审批失败(error approve)";
			$this->ajaxReturn($ret);
		}
	
		if(!is_numeric($planId) || $planId < 1){
			$ret['msg'] = "审批失败(error plan_id)";
			$this->ajaxReturn($ret);
		}
		
		if(empty($remark)){
			$ret['msg'] = "请填写审核意见";
			$this->ajaxReturn($ret);
		}
		$where['plan_id'] = $planId;
		$where['status'] = 1;
		$data = array();
		$data['auditor_uid'] = $this->adminId;
		$data['auditor'] = $this->getRealName($this->adminId);
		$data['audit_remarks'] = $remark;
		$data['audit_date'] = date("Y-m-d H:i:s",time()); //当前时间
		$data['status'] = $approve;
		$result = $this->supplier_plan->where($where)->save($data);
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = "success";
		}else{
			$ret['msg'] = "审核操作失败";
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 所有订单列表
	 * @author Gaolong
	 */
	public function allOrderList(){
		$status = I("get.status", -99, 'intval');
		if(!is_numeric($status)){
			exit();
		}
		$where = array();
		if($status != -99){ //-99为全部数据
			$where['status'] = $status;
		}
		$this->assign('status', $status);
		$this->orderList($where, 1);
	}
	
	/**
	 * 已处理订单
	 * @author Gaolong
	 */
	public function handleOrderList(){
		$status = I("get.status", -99, 'intval');
		if(!is_numeric($status)){
			exit();
		}
		$where = array();
		if($status == -99){
			$where['status'] = array('in', array(3,4,5));
		}else{
			$where['status'] = $status;
		}
		$this->assign('status', $status);
		$this->orderList($where, 2);
	}
	
	/**
	 * 已取消订单
	 * @author Gaolong
	 */
	public function cancelOrderList(){
		$status = I("get.status", -99, 'intval');
		if(!is_numeric($status)){
			exit();
		}
		$where = array();
		$where['status'] = -2;
		$this->assign('status', -2);
		$this->orderList($where, 3);
	}
	/*
	 *导出采购单
	 * 161222 
	 * */
	 private function exportPurchaseOrder($where){
		$field = 'order_id, uid, supplier_id, settlement_method, warehouse_id, remarks, build_people, build_time, status';
		$orderInfo = $this->supplier_order->field($field)->where($where)->order('order_id DESC')->select();	
		unset($field);
		foreach($orderInfo as $key=>$val){
			$orderIds[]=$val['order_id'];
			$warehouseids[]=$val['warehouse_id'];
			$suppliers[]=$val['supplier_id'];
			$userIdArr[]=$val['uid'];
			$orderInfo[$key]['total_price'] = $this->supplier_order->toatalPrice($val['order_id']);
		}
		if(empty($orderIds)){
			exit("暂无数据!");
		}
		$adminArr = $this->getSystemAdminById($userIdArr, 'admin_id, real_name');
		//查询订购单中的商品
		$field = 'id,order_id,title, sitem_id, item_id, sku_id, number, 
					storage_number, plan_number, price, cost_price, order_price, spec_info,bn';
		unset($where);
		$where=array(
			'order_id'=>array('in',$orderIds),
		);
		$supplierGoodsList = $this->supplier_order_goods->field($field)->where($where)->select();
		//转换数据，方便模板页循环遍历
		foreach ($supplierGoodsList as $key=>$goods){
			$itemIdArr[] = $goods['item_id'];
			$skuIdArr[] = $goods['sku_id'];
		}		
		foreach($orderInfo as $key=>$val){
			foreach($supplierGoodsList as $keys=>$vals){
				if($val['order_id']==$vals['order_id']){
					$orderInfo[$key]['items'][]=$vals;
				}
			}
		}
		unset($supplierGoodsList);		
		//查询仓库名称
		unset($where);
		$where=array(
			'warehouse_id'=>array('in',$warehouseids),
		);			
		$warehouses = $this->supplier_warehouse->field('warehouse_id,name,address,contacts,tel')->where($where)->select();
		foreach($orderInfo as $key=>$val){
			foreach($warehouses as $keys=>$vals){
				if($val['warehouse_id']==$vals['warehouse_id']){
					$orderInfo[$key]['warehouse']=$vals;
				}
			}
		}
		//加载供应商
		unset($where);
		$where=array(
			'supplier_id'=>array('in',$suppliers),
		);			
 		$supplierList = $this->supplier->where($where)->field('supplier_id,company_name')->select();
		$supplierName=$this->arrayAssemble($supplierList,'supplier_id','company_name');
		unset($supplierList);		
		unset($supplierGoodsList);
		//查询仓库地址和联系人
		//加载库存
		unset($where);
		$where=array(
			'sku_id'=>array('in',$skuIdArr)
		);
		if(!empty($skuIdArr)){
			$storeList = $this->sysitem_sku_store->where($where)->field('item_id, sku_id, store')->select();
			$storeArr=$this->arrayAssemble($storeList,'sku_id','store');
			unset($storeList);
			//加载销量
			$skuList = $this->sysitem_sku->where($where)->field('sku_id, sold_quantity')->select();
			$soldArr=$this->arrayAssemble($skuList,'sku_id','sold_quantity');
			unset($skuList);
		}
		foreach($orderInfo as $key=>$val){
			foreach($val['items'] as $keys=>$item){
				if($keys==0){
					$LastExeclData[$item['id']]['order_id']=" ".$val['order_id'];//采购单号
					$LastExeclData[$item['id']]['uid']=	$adminArr[$val['uid']];//采购人
					$LastExeclData[$item['id']]['supplierName']=$supplierName[$val['supplier_id']];//供应商
					$LastExeclData[$item['id']]['build_people']=$val['build_people'];//建立人
					$LastExeclData[$item['id']]['total_price']=" ".$val['total_price'];//采购总价
					$LastExeclData[$item['id']]['settlement_method']=$val['settlement_method'];//结算方式
					$LastExeclData[$item['id']]['remarks']=$val['remarks'];//备注
					$LastExeclData[$item['id']]['status']=$this->orderStatus($val['status']);//状态
					$LastExeclData[$item['id']]['addtime']=$val['build_time'];//建立时间
				}else{
					$LastExeclData[$item['id']]['order_id']="";//采购单号
					$LastExeclData[$item['id']]['uid']=	"";//采购人
					$LastExeclData[$item['id']]['supplierName']="";//供应商
					$LastExeclData[$item['id']]['build_people']="";//建立人
					$LastExeclData[$item['id']]['total_price']="";//采购总价
					$LastExeclData[$item['id']]['settlement_method']="";//结算方式
					$LastExeclData[$item['id']]['remarks']="";//采购总价
					$LastExeclData[$item['id']]['status']="";//状态
					$LastExeclData[$item['id']]['addtime']="";//建立时间					
				}
				$LastExeclData[$item['id']]['title']=$item['title'];//商品标题
				$LastExeclData[$item['id']]['item_id']=$item['item_id'];
				$LastExeclData[$item['id']]['sku_id']=$item['sku_id'];
				$LastExeclData[$item['id']]['spec_info']=$item['spec_info'];//规格
				$LastExeclData[$item['id']]['bn']=" ".$item['bn'];//商品条码
				$LastExeclData[$item['id']]['price']=" ".$item['price'];//商城价
				$LastExeclData[$item['id']]['cost_price']=" ".$item['cost_price'];//计算价
				$LastExeclData[$item['id']]['order_price']=" ".$item['order_price'];//采购价
				$LastExeclData[$item['id']]['lrl']=number_format(($item['price'] - $item['cost_price']) / $item['price']*100,1)."%";//利润率
				$LastExeclData[$item['id']]['store']=$storeArr[$item['sku_id']];//库存
				$LastExeclData[$item['id']]['sold']=$soldArr[$item['sku_id']];//销量
				$LastExeclData[$item['id']]['plan_number']=$item['plan_number'];//计划数
				$LastExeclData[$item['id']]['number']=$item['number'];//订货数
				$LastExeclData[$item['id']]['storage_number']=$item['storage_number'];//入库数
			}	
		}	
		$ex=new Excel;
		$columnName=array('采购单号','采购人','供应商','建立人','采购总价','结算方式','备注','状态','建立时间','标题','商品ID','skuId','规格','商品条码','商城价/元','计算价/元','采购价/元','利润率/%','库存','销量','计划数','订货数','入库数');
		$ex->getExcel($LastExeclData,$columnName,"采购单(".date('YmdHi').")");		 	
	 }
/**
 *数组键值 
 *@param $arr二维数组
 * @param $k 作为新数组的键
 * @param $v 作为新数组的值
 * @param $newArr 新数组 
 * */
 	private function arrayAssemble($arr,$k,$v){
 		if(!is_array($arr)){
 			return null;
 		}
		if(empty($k) || empty($v)){
 			return null;
		}
		foreach($arr as $key=>$val){
			$newArr[$val[$k]]=$val[$v];
		}
		return $newArr;
 	} 
/*
 * 
 * */
 	private function orderStatus($status){
    	switch($status){
			case 0:
				$res="待采购员审核";
				break;
			case 1:
				$res="待供应商审核";
				break;	
			case 2:
				$res="待采购员确认";
				break;	
			case 3:
				$res="待主管审核";
				break;	
			case 4:
				$res="待发货";
				break;	
			case 5:
				$res="已发货";
				break;	
			case 6:
				$res="已入库";
				break;	
			case -1:
				$res="未通过审核";
				break;	
			case -2:
				$res="已取消";
				break;																																
    	}
		return $res;
 	}
 	
	/**
	 * 订单列表 可以与allOrderList、 handleOrderList、cancelOrderList合并 <br/>
	 * 为了权限配置，这里进行拆分，每个方法都可以配置各自的权限
	 * @param unknown $where 查询条件
	 * @param unknown $p 分页
	 * @param unknown $from 1.全部订单 2.已审核的订单 3.已取消的订单
	 * @author Gaolong
	 */
	private function orderList($where, $from){
		$p 			  = I("get.p",1);
		$startdate 	  = I('get.startdate','','trim,urldecode');
		$enddate 	  = I('get.enddate','','trim,urldecode');
		$build_people = I('get.build_people','','trim,urldecode');
		$isSelf 	  = I("get.isSelf", -99, 'intval');
		$supplier 	  = I("get.supplier", -99, 'intval');//供应商id
		$orderId 	  = I("get.orderId", 0, 'trim');//供应商id
		//搜索部分
		$timeCondition = array();
		if(!empty($startdate) && !empty($enddate)){
			$timeCondition = array(array('egt', $startdate), array('elt', $enddate));
		}else{
			if(!empty($startdate)){
				$_GET['startdate'] = $startdate;
			}
			if(!empty($enddate)){
				$_GET['enddate'] = $enddate;
			}
		}
		if(!empty($timeCondition)){
			$_GET['startdate'] = $startdate;
			$_GET['enddate'] = $enddate;
			$where['build_time'] = $timeCondition;
		}
		
		if(!empty($build_people)){
			$_GET['build_people'] = $build_people; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$where['build_people'] = array('like', "%{$build_people}%");
		}
		if($isSelf != -99){
			$where['is_self'] = $isSelf;
		}	
		if($supplier != -99){
			$where['supplier_id'] = $supplier;
		}
		if($orderId){
			$where['order_id'] = $orderId;
		}			
		//采购单导出execl --zhangrui 161222 start
		if(I('execlType') && I('execlType')=="exportExcel"){
			$this->exportPurchaseOrder($where); 
		}	
		//采购单导出execl --zhangrui 161222 end
		$listRows = 10;//每页显示的数据条数
		$field = 'order_id, uid, supplier_id, settlement_method, warehouse_id, remarks, build_people, build_time, status';
		$orderList = $this->supplier_order->field($field)
							->page($p, $listRows)
							->where($where)
							->order('order_id DESC')
							->select();
		$count = $this->supplier_order->where($where)->count('order_id');
		//遍历对应的id
		$adminIdArr = array();
		$supplierIdArr = array();
		foreach ($orderList as &$order){
			$adminIdArr[] = $order['uid'];
			$supplierIdArr[] = $order['supplier_id'];
			$order['total_price'] = $this->supplier_order->toatalPrice($order['order_id']);
		}
		//加载采购人
		$adminArr = $this->getSystemAdminById($adminIdArr, 'admin_id, real_name');
		//加载仓库数据
		$warehouseList = $this->getWarehouseList('warehouse_id, name', null, true);
		//加载供应商数据
		$supplierList = $this->supplier->getSupplierByIds($supplierIdArr,'supplier_id,company_name');
		//所有供应商
		$supplierAll = $this->supplier->getAllSupplier('supplier_id,company_name');
		$page=new \Think\Page($count, $listRows);
		$this->assign('count', $count);
		$this->assign('supplierAll', $supplierAll);
		$this->assign('page',$page->show());
		$this->assign('from', $from);
		$this->assign('startdate',$startdate);
		$this->assign('enddate',$enddate);
		$this->assign('build_people',$build_people);
		$this->assign('orderList', $orderList);
		$this->assign('adminArr', $adminArr);
		$this->assign('isSelf',$isSelf);
		$this->assign('supplier',$supplier);
		$this->assign('orderId',$orderId);
		$this->assign('warehouseList', $warehouseList);
		$this->assign('supplierList', $supplierList);
		$this->display('orderList');
	}
	
	/**
	 * 初审-采购员审核
	 * @author Gaolong
	 */
	public function firstApproveList(){
	
		$p 			  = I("get.p",1);
		$startdate 	  = I('get.startdate','','trim,urldecode');
		$enddate 	  = I('get.enddate','','trim,urldecode');
		$build_people = I('get.build_people','','trim,urldecode');
		$status 	  = I("get.status", 0, 'intval');
	
		if(!is_numeric($status)){
			exit();
		}
		$listRows = 10;//每页显示的数据条数
		$where = array();
	
		//搜索部分
		$timeCondition = array();
		if(!empty($startdate) && !empty($enddate)){
			$timeCondition = array(array('egt', $startdate), array('elt', $enddate));
		}else{
			if(!empty($startdate)){
				$_GET['startdate'] = $startdate;
			}
			if(!empty($enddate)){
				$_GET['enddate'] = $enddate;
			}
		}
	
		if(!empty($timeCondition)){
			$_GET['startdate'] = $startdate;
			$_GET['enddate'] = $enddate;
			$where['build_time'] = $timeCondition;
		}
	
		if(!empty($build_people)){
			$_GET['build_people'] = $build_people; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$where['build_people'] = array('like', "%{$build_people}%");
		}
		
		if($status == 0){ //待审核或待确认
			$where['status'] = array('in',array(0, 2));
		}else if($status == 1){ //已审核
			$where['status'] = array("neq", 0);
		}else if($status == 3){ //已确认
			$where['status'] = array(in, array(3, 4, 5, 6));
		}else{
			exit();
		}
	
		$field = 'order_id, uid, supplier_id, settlement_method, warehouse_id, remarks, build_people, build_time, status';
		$orderList = $this->supplier_order->field($field)
						->page($p, $listRows)
						->where($where)
						->order('order_id DESC')
						->select();
		$count = $this->supplier_order->where($where)->count('order_id');
		//遍历对应的id
		$adminIdArr = array();
		$supplierIdArr = array();
		foreach ($orderList as &$order){
			$adminIdArr[] = $order['uid'];
			$order['total_price'] = $this->supplier_order->toatalPrice($order['order_id']);
			$supplierIdArr[] = $order['supplier_id'];
		}
		//获取管理员
		$adminArr = $this->getSystemAdminById($adminIdArr, 'admin_id, real_name');
		//获取仓库
		$warehouseList = $this->getWarehouseList('warehouse_id, name', null, true);
		//加载供应商数据
		$supplierList = $this->supplier->getSupplierByIds($supplierIdArr,'supplier_id,company_name');
		
		$page=new \Think\Page($count, $listRows);
		$this->assign('count', $count);
		$this->assign('page',$page->show());
		$this->assign('startdate',$startdate);
		$this->assign('enddate',$enddate);
		$this->assign('build_people',$build_people);
		$this->assign('status', $status);
		$this->assign('orderList', $orderList);
		$this->assign('adminArr', $adminArr);
		$this->assign('warehouseList', $warehouseList);
		$this->assign('supplierList', $supplierList);
		$this->display('firstApproveList');
	}
	
	/**
	 * 采购单采购员审核-初审
	 * @author Gaolong
	 */
	public function firstApprove(){
		$orderId= I('post.order_id',-1,'intval');
		$remark = I('post.remark','','trim,strip_tags');
		$approve = I('post.approve',0,'intval'); //-1.未通过 1.通过
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		
		if(!is_numeric($orderId) || $orderId < 1){
			$ret['msg'] = "审批失败(error order)";
			$this->ajaxReturn($ret);
		}
		
		if($approve === -1 || $approve === 1){//待审核
			if(empty($remark)){
				$ret['msg'] = "请填写审核意见";
				$this->ajaxReturn($ret);
			}
		}else if($approve === 3){ //待确认
			
		}else{
			$ret['msg'] = "审批失败(error approve)";
			$this->ajaxReturn($ret);
		}
		
		$where['order_id'] = $orderId;
		$data = array();
		if($approve === 1 || $approve === -1){ //初审
			$where['status'] = 0;
			$data['first_auditor_uid'] =  $this->adminId;
			$data['first_auditor'] = $this->getRealName($this->adminId);
			$data['first_auditor_remarks'] = $remark;
			$data['first_auditor_time'] = date("Y-m-d H:i:s",time()); //当前时间
		}else if($approve === 3){ //提交主管审核
			$where['status'] = 2;
			$data['confirmer_uid'] =  $this->adminId;
			$data['confirmer_time'] = date("Y-m-d H:i:s",time()); //当前时间
		}
		$data['status'] = $approve;
		$result = $this->supplier_order->where($where)->save($data);
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = "success";
		}else{
			$ret['msg'] = "审核操作失败";
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 修改采购单-采购员
	 * @author Gaolong
	 */
	public function editOrder(){
		$orderId = I('get.order_id','','intval');
		$page 	= I("get.p", 0, 'intval');
		$listRows = 10; //每页显示的条数
		//查询订购计划信息
		$order = $this->supplier_order->where("order_id=$orderId")->find();
		if(empty($order)){
			echo '订购单不存在';
			exit();
		}
		//查询订购单中的商品
		$field = 'id, order_id, sitem_id, item_id, sku_id, supplier_id, ssku_id, title, price, cost_price, order_price, spec_info, number';
		$orderGoodsList = $this->supplier_order_goods->field($field)
								->where("order_id=$orderId")
								->page($page, $listRows)
								->select();
		//转换数据，方便模板页循环遍历
		$orderGoodsArr = array();
		$itemIdArr = array();
		$skuIdArr = array();
		$supplierIdArr = array();
		foreach ($orderGoodsList as $goods){
			$itemIdArr[$goods['item_id']] =  $goods['item_id'];
			$skuIdArr[$goods['sku_id']] =  $goods['sku_id'];
			$supplierIdArr[$goods['supplier_id']] = $goods['supplier_id'];
			$sitem_id = $goods['sitem_id'];
			$orderGoodsArr[$sitem_id]['supplier_id'] = $goods['supplier_id'];
			$orderGoodsArr[$sitem_id]['title'] = $goods['title'];
			$orderGoodsArr[$sitem_id]['data'][] = $goods;
		}
		unset($orderGoodsList);
		//查询商品总条数
		$count = $this->supplier_order_goods->where("order_id=$orderId")->count('id');
		//查询仓库信息,这个数据可以被缓存（S）
		$houseList = $this->getWarehouseList('warehouse_id, name');
		//查询仓库地址和联系人
		//加载库存
		$storeList = $this->sysitem_sku_store->getStoreByItemIdArr($itemIdArr);
		//加载销量
		$soldList = $this->sysitem_sku->getSoldBySkuIdArr($skuIdArr);
		//加载供应商
		$supplierList = $this->supplier->getSupplierByIds($supplierIdArr,'supplier_id, company_name');
		//查询货期
		$page=new \Think\Page($count, $listRows);
		$this->assign('page',$page->show());
		$this->assign('orderId', $orderId);
		$this->assign('count' , $count);
		$this->assign('order', $order);
		$this->assign('orderGoodsArr', $orderGoodsArr);
		$this->assign('storeList', $storeList);
		$this->assign('soldList', $soldList);
		$this->assign('supplierList', $supplierList);
		$this->assign('houseList', $houseList);
		$this->display('editOrder');
	}
	
	/**
	 * 保存对采购单商品的修改
	 * @author Gaolong
	 */
	public function saveOrderGoods(){
		$itemid = I('post.itemid',0,'intval');
		$orderPrice = I('post.order_price', 0);
		$ret = array('code'=>-1, 'msg'=>'unknow error');
		if(!is_numeric($itemid) || $itemid < 0){
			$ret['msg'] = 'invalid id';
			$this->ajaxReturn($ret);
		}
		if(!is_numeric($orderPrice) || $orderPrice <= 0){
			//price按逻辑来说，成本价需要在数据库验证，只能比数据库的成本价小
			//这里我们只使用了前端表单提交验证
			$ret['msg'] = '价格有误，请核对';
			$this->ajaxReturn($ret);
		}
		$result = $this->supplier_order_goods->where("id=$itemid")->setField('order_price',$orderPrice); //返回影响的条数
		if(is_numeric($result)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}
		$this->ajaxReturn($ret);
	}
	
	/**
	 * 主管审核-采购单列表
	 * @author Gaolong
	 */
	public function directorApproveList(){
		
		$p 			  = I("get.p",1);
		$startdate 	  = I('get.startdate','','trim,urldecode');
		$enddate 	  = I('get.enddate','','trim,urldecode');
		$build_people = I('get.build_people','','trim,urldecode');
		$status 	  = I("get.status", 3, 'intval');
		
		if(!is_numeric($status)){
			exit();
		}
		$listRows = 10;//每页显示的数据条数
		$where = array();
		
		//搜索部分
		$timeCondition = array();
		if(!empty($startdate) && !empty($enddate)){
			$timeCondition = array(array('egt', $startdate), array('elt', $enddate));
		}else{
			if(!empty($startdate)){
				$_GET['startdate'] = $startdate;
			}
			if(!empty($enddate)){
				$_GET['enddate'] = $enddate;
			}
		}
		
		if(!empty($timeCondition)){
			$_GET['startdate'] = $startdate;
			$_GET['enddate'] = $enddate;
			$where['build_time'] = $timeCondition;
		}
		
		if(!empty($build_people)){
			$_GET['build_people'] = $build_people; //此行代码不要去掉，否则分页链接会发生意想不到的错误
			$where['build_people'] = array('like', "%{$build_people}%");
		}
		
		if($status == 4){
			$where['status'] = array("in",array(-1, 3, 4, 5));
		}else{
			$where['status'] = $status;
		}
		
		$field = 'order_id, uid, supplier_id, settlement_method, warehouse_id, remarks, build_people, build_time, status';
		$orderList = $this->supplier_order->field($field)
							->page($p, $listRows)
							->where($where)
							->order('order_id DESC')
							->select();
		$count = $this->supplier_order->where($where)->count('order_id');
		//遍历对应的id
		$adminIdArr = array();
		$supplierIdArr = array();
		foreach ($orderList as &$order){
			$adminIdArr[] = $order['uid'];
			$order['total_price'] = $this->supplier_order->toatalPrice($order['order_id']);
			$supplierIdArr[] = $order['supplier_id'];
		}
		//获取管理员
		$adminArr = $this->getSystemAdminById($adminIdArr, 'admin_id, real_name');
		//获取仓库
		$warehouseList = $this->getWarehouseList('warehouse_id, name', null, true);
		//加载供应商数据
		$supplierList = $this->supplier->getSupplierByIds($supplierIdArr,'supplier_id,company_name');
		
		$page=new \Think\Page($count, $listRows);
		$this->assign('count', $count);
		$this->assign('page',$page->show());
		$this->assign('startdate',$startdate);
		$this->assign('enddate',$enddate);
		$this->assign('build_people',$build_people);
		$this->assign('status', $status);
		$this->assign('orderList', $orderList);	
		$this->assign('adminArr', $adminArr);
		$this->assign('warehouseList', $warehouseList);
		$this->assign('supplierList', $supplierList);
		$this->display('directorApproveList');
	}
	
	/**
	 * 采购单-主管审批
	 * @author Gaolong
	 */
	public function directorApprove(){
		$orderId= I('post.order_id',-1,'intval');
		$remark = I('post.remark','','strip_tags');
		$approve = I('post.approve',0,'intval'); //-1.未通过 1.通过
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		
		if(empty($remark)){
			$ret['msg'] = "请填写审核意见";
			$this->ajaxReturn($ret);
		}
		
		if($approve !== -1 && $approve !== 4){
			$ret['msg'] = "审批失败(error approve)";
			$this->ajaxReturn($ret);
		}
		
		if(!is_numeric($orderId) || $orderId < 1){
			$ret['msg'] = "审批失败(error order)";
			$this->ajaxReturn($ret);
		}
		
		
		$where['order_id'] = $orderId;
		$where['status'] = 3;
		$data = array();
		$data['auditor_uid'] =  $this->adminId;
		$data['auditor'] = $this->getRealName($this->adminId);
		$data['audit_remarks'] = $remark;
		$data['audit_time'] = date("Y-m-d H:i:s",time()); //当前时间
		$data['status'] = $approve;
		$result = $this->supplier_order->where($where)->save($data);
		if($result){
			$ret['code'] = 1;
			$ret['msg'] = "success";
		}else{
			$ret['msg'] = "审核操作失败";
		}
		$this->ajaxReturn($ret);
	}
	
// 	/**已删除**标记于2016年10月26日
// 	 * 采购单-财务审批列表 
// 	 * @author Gaolong
// 	 */
// 	public function financeApproveList(){
// 		$p 			  = I("get.p",1);
// 		$startdate 	  = I('get.startdate','','trim,urldecode');
// 		$enddate 	  = I('get.enddate','','trim,urldecode');
// 		$build_people = I('get.build_people','','trim,urldecode');
// 		$status		  = I("get.status", 2, 'intval');
		
// 		if(!is_numeric($status)){
// 			exit();
// 		}
		
// 		$listRows = 10;//每页显示的数据条数
// 		$where = array();
// 		//搜索部分
// 		$timeCondition = array();
// 		if(!empty($startdate) && !empty($enddate)){
// 			$timeCondition = array(array('egt', $startdate), array('elt', $enddate));
// 		}else{
// 			if(!empty($startdate)){
// 				$_GET['startdate'] = $startdate;
// 			}
// 			if(!empty($enddate)){
// 				$_GET['enddate'] = $enddate;
// 			}
// 		}
		
// 		if(!empty($timeCondition)){
// 			$_GET['startdate'] = $startdate;
// 			$_GET['enddate'] = $enddate;
// 			$where['build_time'] = $timeCondition;
// 		}
		
// 		if(!empty($build_people)){
// 			$_GET['build_people'] = $build_people; //此行代码不要去掉，否则分页链接会发生意想不到的错误
// 			$where['build_people'] = array('like', "%{$build_people}%");
// 		}
		
// 		if($status == 3){
// 			$where['status'] = array("in",array(-1,3,4,5));
// 		}else{
// 			$where['status'] = $status;
// 		}
// 		$field = 'order_id, uid, settlement_method, warehouse_id, remarks, build_people, build_time, status';
// 		$orderList = $this->supplier_order->field($field)
// 							->page($p, $listRows)
// 							->where($where)
// 							->order('order_id DESC')
// 							->select();
// 		$count = $this->supplier_order->where($where)->count('order_id');
// 		//遍历对应的id
// 		$adminIdArr = array();
// 		foreach ($orderList as &$order){
// 			$adminIdArr[] = $order['uid'];
// 			$order['total_price'] = $this->supplier_order->toatalPrice($order['order_id']);
// 		}
// 		//获取管理员
// 		$adminArr = $this->getSystemAdminById($adminIdArr, 'admin_id, real_name');
// 		//获取仓库
// 		$warehouseList = $this->getWarehouseList('warehouse_id, name', null, true);
		
// 		$page=new \Think\Page($count, $listRows);
// 		$this->assign('count', $count);
// 		$this->assign('page',$page->show());
// 		$this->assign('startdate',$startdate);
// 		$this->assign('enddate',$enddate);
// 		$this->assign('build_people',$build_people);
// 		$this->assign('status', $status);
// 		$this->assign('orderList', $orderList);
// 		$this->assign('adminArr', $adminArr);
// 		$this->assign('warehouseList', $warehouseList);
// 		$this->display('financeApproveList');
// 	}
	
// 	/***已删除**标记于2016年10月26日
// 	 * 采购单-财务主管审批 
// 	 * @author Gaolong
// 	 */
// 	public function financeApprove(){
// 		$orderId= I('post.order_id',-1,'intval');
// 		$approve = I('post.approve',0,'intval'); //-1.未通过 1.通过
// 		$remark= I('post.remark','', 'strip_tags');
// 		$ret = array('code'=>-1, 'msg'=>'unkown error');
		
// 		if(empty($remark)){
// 			$ret['msg'] = "请填写审核意见";
// 			$this->ajaxReturn($ret);
// 		}
		
// 		if($approve !== -1 && $approve !== 3){
// 			$ret['msg'] = "审批失败(error approve)";
// 			$this->ajaxReturn($ret);
// 		}
	
// 		if(!is_numeric($orderId) || $orderId < 1){
// 			$ret['msg'] = "审批失败(error order)";
// 			$this->ajaxReturn($ret);
// 		}
	
// 		$where['order_id'] = $orderId;
// 		$where['status'] = 2;
// 		$data = array();
// 		$data['financial_auditor_uid'] = $this->adminId;
// 		$data['financial_auditor'] = $this->getRealName($this->adminId);
// 		$data['financial_auditor_time'] = date("Y-m-d H:i:s",time());
// 		$data['financial_auditor_remarks'] = $remark;
// 		$data['status'] = $approve;
// 		$result = $this->supplier_order->where($where)->save($data);
// 		if($result){
// 			$ret['code'] = 1;
// 			$ret['msg'] = "success";
// 		}else{
// 			$ret['msg'] = "审核操作失败";
// 		}
// 		$this->ajaxReturn($ret);
// 	}
	
	// 	public function goodsDetail(){

	// 	}
	
	/**
	 * 获取仓库列表
	 * @param unknown $field 需要查询的字段
	 * @param unknown $where 条件
	 * @param string $idToKey 是否将主键作为key值
	 * @return mixed 
	 * @author Gaolong
	 */
	private function getWarehouseList($field, $where = null, $idToKey=false){
		$warehouseList =  $this->supplier_warehouse
							->where($where)
							->field($field)
							->select();
		$warehouseArr = array();
		if($idToKey){
			foreach ($warehouseList as $house){
				$warehouseArr[$house['warehouse_id']] = $house;
			}
			unset($warehouseList);
			return $warehouseArr;
		}
		
		return $warehouseList;
	}
	
	/**
	 * 获取管理员信息
	 * @param unknown $userIdArr 管理员id
	 * @param unknown $field 需要查询的字段
	 * @author Gaolong
	 */
	private function getSystemAdminById($userIdArr, $field){
		if(empty($userIdArr)){
			return array();
		}
		$where['admin_id'] = array('in',$userIdArr);
		$adminList = $this->system_admin->field('admin_id, real_name')->where($where)->select();
		$adminArr = array();
		foreach ($adminList as $admin){
			$adminArr[$admin['admin_id']] = $admin['real_name'];
		}
		return $adminArr;
	}
	
	/**
	 * 根据id获取管理员真实姓名
	 * @param unknown $adminId
	 * @return string 管理员真实姓名
	 * @author Gaolong
	 */
	private function getRealName($adminId){
		if(empty($adminId)) 
			return false;
		return $this->system_admin->where("admin_id=$adminId")->getField('real_name');
	}
}
